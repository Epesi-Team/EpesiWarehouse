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
	public function menu() {
		if (!Base_AclCommon::i_am_admin()) return;
		return array('Reports'=>array('__submenu__'=>1, 
			'Sales by Warehouse'=>array('mode'=>'sales_by_warehouse'), 
			'Sales by Transaction'=>array('mode'=>'sales_by_transaction'),	
			'Sales by Item'=>array('mode'=>'sales_by_item')	
		));	
	}
	
	public static function currency_exchange($val, $c1, $c2) {
		return 0;// TODO
	} 
	
	public static function recalculate() {
		$currency = 1;
		$prec = Utils_CurrencyFieldCommon::get_precission($currency);
		$multip = pow(10,$prec);

		DB::Execute('DELETE FROM premium_warehouse_sales_report_purchase_fifo_tmp');
		DB::Execute('DELETE FROM premium_warehouse_sales_report_purchase_lifo_tmp');
		$purchases = DB::Execute('SELECT * FROM premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND (o.f_transaction_type=0 OR o.f_transaction_type=4) AND o.f_status=20 ORDER BY o.f_transaction_date ASC, o.f_transaction_type ASC, o.created_on ASC');
		$id = 0;
		while ($trans = $purchases->FetchRow()) {
			if ($trans['f_transaction_type']==0) {
				$trans['f_price'] = Utils_CurrencyFieldCommon::get_values($trans['f_net_price']);
				$net_price = $trans['f_price'][0];
				if ($trans['f_price'][1]!=$currency)
					$net_price = self::currency_exchange($net_price, $trans['f_price'][1], $currency);
				$gross_price = round((100+Data_TaxRatesCommon::get_tax_rate($trans['f_tax_rate']))*$net_price/100, $prec);
				DB::Execute('INSERT INTO premium_warehouse_sales_report_purchase_fifo_tmp VALUES (%d, %d, %d, %d, %d, %d)', array($id, $trans['f_item_name'], $trans['f_quantity'], $trans['f_warehouse'], $net_price*$multip, $gross_price*$multip));
				DB::Execute('INSERT INTO premium_warehouse_sales_report_purchase_lifo_tmp VALUES (%d, %d, %d, %d, %d, %d)', array($id, $trans['f_item_name'], $trans['f_quantity'], $trans['f_warehouse'], $net_price*$multip, $gross_price*$multip));
				$id++;
			} else {
 				$trans_qty = $trans['f_quantity'];
 				$items = DB::Execute('SELECT * FROM premium_warehouse_sales_report_purchase_fifo_tmp WHERE item_id=%d AND warehouse=%d ORDER BY id ASC', array($trans['f_item_name'], $trans['f_warehouse']));
				while ($trans_qty>0 && $item=$items->FetchRow()) {
					$qty = min($trans_qty, $item['quantity']);
					$trans_qty -= $qty;
					if ($item['quantity']>$qty) {
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_fifo_tmp SET quantity=%d WHERE id=%d', array($item['quantity']-$qty, $item['id']));
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_fifo_tmp SET id=id+1 WHERE id>%d ORDER BY id DESC', array($item['id']));
						DB::Execute('INSERT INTO premium_warehouse_sales_report_purchase_fifo_tmp VALUES (%d, %d, %d, %d, %d, %d)', array($item['id']+1, $item['item_id'], $qty, $trans['f_target_warehouse'], $item['net_price'], $item['gross_price']));
						$id++;
					} else {
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_fifo_tmp SET warehouse=%d WHERE id=%d', array($trans['f_target_warehouse'], $item['id']));
					}
				}

 				$trans_qty = $trans['f_quantity'];
 				$items = DB::Execute('SELECT * FROM premium_warehouse_sales_report_purchase_lifo_tmp WHERE item_id=%d AND warehouse=%d ORDER BY id DESC', array($trans['f_item_name'], $trans['f_warehouse']));
				while ($trans_qty>0 && $item=$items->FetchRow()) {
					$qty = min($trans_qty, $item['quantity']);
					$trans_qty -= $qty;
					if ($item['quantity']>$qty) {
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_lifo_tmp SET quantity=%d WHERE id=%d', array($item['quantity']-$qty, $item['id']));
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_lifo_tmp SET id=id+1 WHERE id>%d ORDER BY id DESC', array($item['id']));
						DB::Execute('INSERT INTO premium_warehouse_sales_report_purchase_lifo_tmp VALUES (%d, %d, %d, %d, %d, %d)', array($item['id']+1, $item['item_id'], $qty, $trans['f_target_warehouse'], $item['net_price'], $item['gross_price']));
						$id++;
					} else {
						DB::Execute('UPDATE premium_warehouse_sales_report_purchase_lifo_tmp SET warehouse=%d WHERE id=%d', array($trans['f_target_warehouse'], $item['id']));
					}
				}
			}
		}
		DB::Execute('DELETE FROM premium_warehouse_sales_report_earning');
		$sales = DB::Execute('SELECT *, od.id AS od_id FROM premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 ORDER BY o.f_transaction_date ASC');
		while ($trans = $sales->FetchRow()) {
			$trans['f_price'] = Utils_CurrencyFieldCommon::get_values($trans['f_net_price']);
			$net_price = $trans['f_price'][0];
			if ($trans['f_price'][1]!=$currency)
				$net_price = self::currency_exchange($net_price, $trans['f_price'][1], $currency);
			$gross_price = round((100+Data_TaxRatesCommon::get_tax_rate($trans['f_tax_rate']))*$net_price/100, $prec);
			$net_price *= $multip;
			$gross_price *= $multip;

			$earnings = array('g_lifo'=>0, 'g_fifo'=>0, 'n_lifo'=>0, 'n_fifo'=>0);
			
			$sold_qty = $trans['f_quantity'];
			$items = DB::Execute('SELECT * FROM premium_warehouse_sales_report_purchase_fifo_tmp WHERE item_id=%d AND warehouse=%d ORDER BY id ASC', array($trans['f_item_name'], $trans['f_warehouse']));
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
			$qty_fifo = $trans['f_quantity']-$sold_qty;
			$sold_qty = $trans['f_quantity'];
			$items = DB::Execute('SELECT * FROM premium_warehouse_sales_report_purchase_lifo_tmp WHERE item_id=%d AND warehouse=%d ORDER BY id DESC', array($trans['f_item_name'], $trans['f_warehouse']));
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
			$qty_lifo = $trans['f_quantity']-$sold_qty;
			
			DB::Execute('INSERT INTO premium_warehouse_sales_report_earning VALUES(%d,%d,%d,%d,%d,%d,%d)', array($trans['od_id'], $qty_lifo, $qty_fifo, $earnings['g_lifo'], $earnings['g_fifo'], $earnings['n_lifo'], $earnings['n_fifo']));
		}
	}  
}

?>