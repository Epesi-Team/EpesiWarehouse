<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Janusz Tylek <jtylek@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-salesreport
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_SalesReportCommon extends ModuleCommon {
	public static function admin_caption() {
		return array('label'=>__('Sales Report'), 'section'=>__('Features Configuration'));
	}
	public static function currency_exchange_addon_access() {
		return Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',Utils_RecordBrowser::$last_record);
	}
	public static function menu() {
		if (!Base_AclCommon::i_am_admin()) return;
		return array(_M('Reports')=>array('__submenu__'=>1, 
			_M('Sales by Warehouse')=>array('mode'=>'sales_by_warehouse'), 
			_M('Sales by Transaction')=>array('mode'=>'sales_by_transaction'),	
			_M('Sales by Item')=>array('mode'=>'sales_by_item'),
			_M('Stock Value by Warehouse')=>array('mode'=>'value_by_warehouse')	
		));	
	}
	
	public static function currency_exchange($val, $cur, $order_id) {
		$rate = DB::GetOne('SELECT exchange_rate FROM premium_warehouse_sales_report_exchange WHERE order_id=%d AND currency=%d', array($order_id, $cur));
		if (!$rate) $rate = 0;
		return $val*$rate;
	} 
	
	public static function recalculate() {
		set_time_limit(0);
		$currency = Variable::get('premium_warehouse_ex_currency');
		$prec = Utils_CurrencyFieldCommon::get_precission($currency);
		$multip = pow(10,$prec);

		DB::Execute('DELETE FROM premium_warehouse_sales_report_purchase_fifo_tmp');
		DB::Execute('DELETE FROM premium_warehouse_sales_report_purchase_lifo_tmp');
//		$purchases = DB::Execute('SELECT *, o.id AS order_id FROM premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND (o.f_transaction_type=0 OR o.f_transaction_type=4) AND o.f_status=20 ORDER BY (SELECT MAX(oeh.edited_on) FROM premium_warehouse_items_orders_edit_history_data AS oehd LEFT JOIN premium_warehouse_items_orders_edit_history AS oeh ON oehd.edit_id=oeh.id WHERE oeh.premium_warehouse_items_orders_id=o.id AND oehd.field="status" AND oehd.old_value!="") ASC, o.f_transaction_type ASC, o.created_on ASC');
		$purchases = DB::Execute('SELECT *, o.id AS order_id FROM premium_warehouse_items_orders_details_data_1 AS od INNER JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND o.f_transaction_type=0 AND o.f_status=20 ORDER BY o.f_transaction_date ASC, o.created_on ASC');
		$id = 0;
		DB::Execute('DELETE FROM premium_warehouse_sales_report_earning');
//		$sales = DB::Execute('SELECT *, od.id AS od_id FROM premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 ORDER BY (SELECT MAX(oeh.edited_on) FROM premium_warehouse_items_orders_edit_history_data AS oehd LEFT JOIN premium_warehouse_items_orders_edit_history AS oeh ON oehd.edit_id=oeh.id WHERE oeh.premium_warehouse_items_orders_id=o.id AND oehd.field="status" AND oehd.old_value!="") ASC');
//		$sales = DB::Execute('SELECT *, od.id AS od_id FROM premium_warehouse_items_orders_details_data_1 AS od INNER JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id INNER JOIN (premium_warehouse_items_orders_edit_history h,premium_warehouse_items_orders_edit_history_data hd) ON (h.premium_warehouse_items_orders_id=o.id AND hd.edit_id=h.id AND hd.field="status" AND (hd.old_value is null OR hd.old_value IN (2,5,6,7))) WHERE od.active=1 AND (o.f_transaction_type=1 OR o.f_transaction_type=4) AND o.f_status=20 GROUP BY od.id ORDER BY MAX(h.edited_on) ASC');
		$sales = DB::Execute('SELECT *, od.id AS od_id FROM premium_warehouse_items_orders_details_data_1 AS od INNER JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id INNER JOIN premium_warehouse_items_orders_edit_history h ON h.premium_warehouse_items_orders_id=o.id WHERE od.active=1 AND (o.f_transaction_type=1 OR o.f_transaction_type=4) AND o.f_status=20 GROUP BY od.id ORDER BY MAX(h.edited_on) ASC');
		$sale = $sales->FetchRow();
		do {
			$trans = $purchases->FetchRow();
			while ($sale && !$trans) {
			    if ($sale['f_transaction_type']==1) {
				$sale['f_price'] = Utils_CurrencyFieldCommon::get_values($sale['f_net_price']);
				$net_price = $sale['f_price'][0];
				if ($sale['f_price'][1]!=$currency)
					$net_price = self::currency_exchange($net_price, $sale['f_price'][1], $currency);
				$gross_price = round((100+Data_TaxRatesCommon::get_tax_rate($sale['f_tax_rate']))*$net_price/100, $prec);
				$net_price *= $multip;
				$gross_price *= $multip;
	
				$earnings = array('g_lifo'=>0, 'g_fifo'=>0, 'n_lifo'=>0, 'n_fifo'=>0);
				$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $sale['f_item_name']);
				if ($item['item_type']>1) {
					$qty = $sale['f_quantity'];
					DB::Execute('INSERT INTO premium_warehouse_sales_report_earning VALUES(%d,%d,%d,%d,%d,%d,%d)', array($sale['od_id'], $qty, $qty, $gross_price, $gross_price, $net_price, $net_price));
					$sale = $sales->FetchRow();
					continue;
				}
				
				$sold_qty = $sale['f_quantity'];
				$items = DB::Execute('SELECT * FROM premium_warehouse_sales_report_purchase_fifo_tmp WHERE item_id=%d AND warehouse=%d ORDER BY id ASC', array($sale['f_item_name'], $sale['f_warehouse']));
				while ($sold_qty>0 && $item=$items->FetchRow()) {
					$qty = min($sold_qty, $item['quantity']);
					$sold_qty -= $qty;
					$earnings['g_fifo'] += ($gross_price-$item['gross_price'])*$qty;
					$earnings['n_fifo'] += ($net_price-$item['net_price'])*$qty;
					if ($item['quantity']>$qty)
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_fifo_tmp SET quantity=%d WHERE id=%d', array($item['quantity']-$qty, $item['id']));
					else
						DB::Execute('DELETE FROM premium_warehouse_sales_report_purchase_fifo_tmp WHERE id=%d', array($item['id']));
				}
				$qty_fifo = $sale['f_quantity']-$sold_qty;
				$sold_qty = $sale['f_quantity'];
				$items = DB::Execute('SELECT * FROM premium_warehouse_sales_report_purchase_lifo_tmp WHERE item_id=%d AND warehouse=%d ORDER BY id DESC', array($sale['f_item_name'], $sale['f_warehouse']));
				while ($sold_qty>0 && $item=$items->FetchRow()) {
					$qty = min($sold_qty, $item['quantity']);
					$sold_qty -= $qty;
					$earnings['g_lifo'] += ($gross_price-$item['gross_price'])*$qty;
					$earnings['n_lifo'] += ($net_price-$item['net_price'])*$qty;
					if ($item['quantity']>$qty)
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_lifo_tmp SET quantity=%d WHERE id=%d', array($item['quantity']-$qty, $item['id']));
					else
						DB::Execute('DELETE FROM premium_warehouse_sales_report_purchase_lifo_tmp WHERE id=%d', array($item['id']));
				}
				$qty_lifo = $sale['f_quantity']-$sold_qty;
				
				DB::Execute('INSERT INTO premium_warehouse_sales_report_earning VALUES(%d,%d,%d,%d,%d,%d,%d)', array($sale['od_id'], $qty_lifo, $qty_fifo, $earnings['g_lifo'], $earnings['g_fifo'], $earnings['n_lifo'], $earnings['n_fifo']));
			    } else {
 				$trans_qty = $sale['f_quantity'];
 				$items = DB::Execute('SELECT * FROM premium_warehouse_sales_report_purchase_fifo_tmp WHERE item_id=%d AND warehouse=%d ORDER BY id ASC', array($sale['f_item_name'], $sale['f_warehouse']));
				while ($trans_qty>0 && $item=$items->FetchRow()) {
					$qty = min($trans_qty, $item['quantity']);
					$trans_qty -= $qty;
					if ($item['quantity']>$qty) {
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_fifo_tmp SET quantity=%d WHERE id=%d', array($item['quantity']-$qty, $item['id']));
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_fifo_tmp SET id=id+1 WHERE id>%d ORDER BY id DESC', array($item['id']));
						DB::Execute('INSERT INTO premium_warehouse_sales_report_purchase_fifo_tmp VALUES (%d, %d, %d, %d, %d, %d)', array($item['id']+1, $item['item_id'], $qty, $sale['f_target_warehouse'], $item['net_price'], $item['gross_price']));
						$id++;
					} else {
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_fifo_tmp SET warehouse=%d WHERE id=%d', array($sale['f_target_warehouse'], $item['id']));
					}
				}

 				$trans_qty = $sale['f_quantity'];
 				$items = DB::Execute('SELECT * FROM premium_warehouse_sales_report_purchase_lifo_tmp WHERE item_id=%d AND warehouse=%d ORDER BY id DESC', array($sale['f_item_name'], $sale['f_warehouse']));
				while ($trans_qty>0 && $item=$items->FetchRow()) {
					$qty = min($trans_qty, $item['quantity']);
					$trans_qty -= $qty;
					if ($item['quantity']>$qty) {
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_lifo_tmp SET quantity=%d WHERE id=%d', array($item['quantity']-$qty, $item['id']));
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_lifo_tmp SET id=id+1 WHERE id>%d ORDER BY id DESC', array($item['id']));
						DB::Execute('INSERT INTO premium_warehouse_sales_report_purchase_lifo_tmp VALUES (%d, %d, %d, %d, %d, %d)', array($item['id']+1, $item['item_id'], $qty, $sale['f_target_warehouse'], $item['net_price'], $item['gross_price']));
						$id++;
					} else {
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_lifo_tmp SET warehouse=%d WHERE id=%d', array($sale['f_target_warehouse'], $item['id']));
					}
				}
			    }
			    $sale = $sales->FetchRow();
			}
			if (!$trans) break;
			$trans['f_price'] = Utils_CurrencyFieldCommon::get_values($trans['f_net_price']);
			$net_price = $trans['f_price'][0];
			if ($trans['f_price'][1]!=$currency)
				$net_price = self::currency_exchange($net_price, $trans['f_price'][1], $trans['order_id']);
			$gross_price = round((100+Data_TaxRatesCommon::get_tax_rate($trans['f_tax_rate']))*$net_price/100, $prec);
			DB::Execute('INSERT INTO premium_warehouse_sales_report_purchase_fifo_tmp VALUES (%d, %d, %d, %d, %d, %d)', array($id, $trans['f_item_name'], $trans['f_quantity'], $trans['f_warehouse'], $net_price*$multip, $gross_price*$multip));
			DB::Execute('INSERT INTO premium_warehouse_sales_report_purchase_lifo_tmp VALUES (%d, %d, %d, %d, %d, %d)', array($id, $trans['f_item_name'], $trans['f_quantity'], $trans['f_warehouse'], $net_price*$multip, $gross_price*$multip));
			$id++;
		} while (true);
	}  
}

?>