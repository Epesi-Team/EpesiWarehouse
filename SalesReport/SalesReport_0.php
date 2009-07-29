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

class Premium_Warehouse_SalesReport extends Module {
	private $cats = array();
	private $format = '';
	private $columns = array();
	private $range_type = '';
	private $rbr = null;

	public function construct() {
		$this->rbr = $this->init_module('Utils/RecordBrowser/Reports');
	}

	public function recalculate() {
		Premium_Warehouse_SalesReportCommon::recalculate();
	}

/************************************************************************************/
	public function body() {
		if (!Base_AclCommon::i_am_admin()) {
			print($this->t('You don\'t have permission to access this module'));
			return;
		}

		if (isset($_REQUEST['mode'])) $this->set_module_variable('mode',$_REQUEST['mode']);
		$mode = $this->get_module_variable('mode',null);

		switch ($mode) {
			case 'sales_by_warehouse': $this->sales_by_warehouse(); break;
			case 'sales_by_item': $this->sales_by_item(); break;
			case 'sales_by_transaction': $this->sales_by_transaction(); break;
			default: print($this->t('Unknown mode'));
		}
	}
	
	public function sales_by_warehouse() {
		$this->cats = array('Sales Trans.','Sales Volume','Purchase Trans.','Purchase Volume','Net Profit');
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$this->rbr->set_reference_records($recs);
		$this->rbr->set_reference_record_display_callback(array('Premium_WarehouseCommon','display_warehouse'));
		$date_range = $this->rbr->display_date_picker();
		$this->rbr->set_categories($this->cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total'));
		$this->rbr->set_format(array(	$this->cats[0]=>'numeric', 
										$this->cats[1]=>'currency',
										$this->cats[2]=>'numeric',
										$this->cats[3]=>'currency',
										$this->cats[4]=>'currency'
									));
		$header = array('Warehouse');
		$this->columns = $date_range['dates'];
		$this->range_type = $date_range['type'];
		switch ($date_range['type']) {
			case 'day': $this->format ='d M Y'; break;
			case 'week': $this->format ='W Y'; break;
			case 'month': $this->format ='M Y'; break;
			case 'year': $this->format ='Y'; break;
		} 
		foreach ($this->columns as $v)
			$header[] = date($this->format, $v);
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_sales_by_warehouse_cells'));
		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}

	
/************************************************************************************/
	public function display_sales_by_warehouse_cells($ref_rec){
		$result = array();
		$hash = array();
		$i = 0;
		foreach ($this->columns as $v) {
		// all $cats must be initialized here individually to avoid: "Message: Undefined index: Purchase Volume" error - see private static $cats = array(... above
			$result[$i] = array(	$this->cats[0]=>0,
									$this->cats[1]=>array(),
									$this->cats[2]=>0,
									$this->cats[3]=>array(),
									$this->cats[4]=>array());
			$hash[date($this->format, $v)] = $i;
			$i++;
		}
		$currency = 1;
		
		$records = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('warehouse'=>$ref_rec['id']));
		// TODO: transaction status filter
		// TODO: warehouse transfer
		// transactions types: 0=>'Purchase',1=>'Sale',2=>'Inventory Adjustment',3=>'Rental',4=>'Warehouse Transfer
		foreach ($records as $v) {
			$d = date($this->format,strtotime($v['transaction_date']));
			if (isset($hash[$d])) {
				// count no. of Sales/Purchase Transactions
				// and sales/purchase volume
				// Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value()
				// returns array where keys are currency IDs and values are result numbers
		
				// Epesi::debug(print_r(Premium_Warehouse_Items_OrdersCommon::get_status_array($v)));
				switch ($v['transaction_type']) {
					/********************** Purchase *******************/
					case '0':
						// Include only Completed transactions - status=20
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
//							if (!isset($purchase_amount[$currency])) break;
							foreach ($purchase_amount as $c=>$v) {
								if (!isset($result[$hash[$d]][$this->cats[3]][$c])) $result[$hash[$d]][$this->cats[3]][$c] = 0;
								if (!isset($result[$hash[$d]][$this->cats[4]][$c])) $result[$hash[$d]][$this->cats[4]][$c] = 0;
								$result[$hash[$d]][$this->cats[3]][$c] += $v;
								// Net loss/profit - Decrease - note -=
								$result[$hash[$d]][$this->cats[4]][$c] -= $v;
							}
							}
						break;
					/********************** Sale *******************/
					case '1':
						// Include only 7=>'Shipped', 20=>'Delivered'
						if ($v['status']==7 || $v['status']==20) {
							$result[$hash[$d]][$this->cats[0]]++;
							$sale_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
//							if (!isset($sale_amount[$currency])) break;
							foreach ($sale_amount as $c=>$v) {
								if (!isset($result[$hash[$d]][$this->cats[1]][$c])) $result[$hash[$d]][$this->cats[1]][$c] = 0;
								if (!isset($result[$hash[$d]][$this->cats[4]][$c])) $result[$hash[$d]][$this->cats[4]][$c] = 0;
								$result[$hash[$d]][$this->cats[1]][$c] += $v;
								// Net loss/profit - Increase - note +=
								$result[$hash[$d]][$this->cats[4]][$c] += $v;
							}
						}
						break;
						/********************** Inventory Adjustment *******************/
					case '2':
						// 20=>'Completed'
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
//							if (!isset($purchase_amount[$currency])) break;
							foreach ($purchase_amount as $c=>$v) {
								if (!isset($result[$hash[$d]][$this->cats[3]][$c])) $result[$hash[$d]][$this->cats[3]][$c] = 0;
								if (!isset($result[$hash[$d]][$this->cats[4]][$c])) $result[$hash[$d]][$this->cats[4]][$c] = 0;
								$result[$hash[$d]][$this->cats[3]][$c] += $v;
								// Net loss/profit - Decrease - note -=
								$result[$hash[$d]][$this->cats[4]][$c] -= $v;
							}
							}
						break;
						/********************** WAREHOUSE TRANSFER *******************/
						/* Ignore - Sales/Purchase Volume = 0
					case 4:
						// ''=>'New', 1=>'Transfer Quote', 2=>'Pending', 3=>'Order Fullfilment', 4=>'On Hold', 5=>'Ready to Ship', 6=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing'
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							Epesi::debug($v['warehouse']);
							$result[$hash[$d]][$this->cats[3]]+=$purchase_amount[1];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][$this->cats[4]] -= $purchase_amount[1];
							}
						break;
						*/
				} // end of switch
			}
		}
		
		$i = 0;
		foreach ($this->columns as $v) {
			switch ($this->range_type) {
				case 'day':		$start = date('Y-m-d',$v);
								$end = date('Y-m-d',$v);
								break;
				case 'week':	$m = date('N',$v)-1;
								$start = date('Y-m-d',$v-$m*86400);
								$end = date('Y-m-d',$v+(6-$m)*86400);
								break;
				case 'month':	$start = date('Y-m-01',$v);
								$end = date('Y-m-t',$v);
								break;
				case 'year':	$start = date('Y-01-01',$v);
								$end = date('Y-12-31',$v);
								break;
			}
			$end = date('Y-m-d',strtotime($end)+1);
			
			// drill-in report links
			// Sales transactions link
			if ($result[$i][$this->cats[0]]<>0) {
				$result[$i][$this->cats[0]] = '<a '.$this->create_callback_href(array($this,'display_sales'), array($ref_rec['id'], $start, $end)).'>'.$result[$i][$this->cats[0]].'</a>';
			}
			// Purchases transactions link
			if ($result[$i][$this->cats[2]]<>0) {
				$result[$i][$this->cats[2]] = '<a '.$this->create_callback_href(array($this,'display_purchases'), array($ref_rec['id'], $start, $end)).'>'.$result[$i][$this->cats[2]].'</a>';
			}
			$i++;
		}
		return $result;
	}

	public function sales_by_item() {
		Base_ActionBarCommon::add('search', 'Scan', $this->create_callback_href(array($this, 'recalculate')));
		print('<br><b>Numbers in brackets indicate items sold that were omitted in earning calculation.</b><br><br>');
		$form = $this->init_module('Libs/QuickForm');
		$form->addElement('select', 'method', $this->t('Method'), array('fifo'=>$this->t('FIFO'), 'lifo'=>$this->t('LIFO')));
		$form->addElement('select', 'prices', $this->t('Prices'), array('net'=>$this->t('Net'), 'gross'=>$this->t('Gross')));
		$form->setDefaults(array('method'=>'fifo','prices'=>'net'));
		$this->cats = array('Qty Sold','Earnings');
		$this->range_type = $this->rbr->display_date_picker(array(), $form);
		$order = '_earning_';
		if ($this->range_type['other']['method']=='fifo') $order = $order.'fifo';
		else $order = $order.'lifo';
		if ($this->range_type['other']['prices']=='net') $order = 'n'.$order;
		else $order = 'g'.$order;
		$items_ids = DB::GetCol('SELECT od.f_item_name FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 AND o.f_transaction_date>=%D AND o.f_transaction_date<=%D GROUP BY od.f_item_name ORDER BY SUM('.$order.') DESC', array($this->range_type['start'], $this->range_type['end']));
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$items_amount = Utils_RecordBrowserCommon::get_records_count('premium_warehouse_items',array('id'=>$items_ids));
		$limit = $this->rbr->enable_paging($items_amount);
		$items_ids = array_splice($items_ids, $limit['offset'], $limit['numrows']);
		$items_recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('id'=>$items_ids),array(),array('item_name'=>'ASC'));
		$items = array();
		foreach ($items_ids as $v) {
			$items[$v] = $items_recs[$v];
		}
		$this->rbr->set_reference_records($items);
		$this->rbr->set_reference_record_display_callback(array('Premium_Warehouse_ItemsCommon','display_item_name'));
		$this->rbr->set_categories($this->cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total', 'callback'=>array($this,'sales_by_item_row_total')));
		$this->rbr->set_format(array(	$this->cats[0]=>'numeric', 
										$this->cats[1]=>'currency'
									));
		$header = array('Item Name');
		$this->columns = array();
		foreach ($warehouses as $v) {
			$header[] = $v['warehouse'];
			$this->columns[$v['id']] = $v['warehouse'];
		}
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_sales_by_item_cells'));
		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}	

	public function sales_by_item_row_total($results, $total, $cat) {
		if ($cat==$this->cats[1]) return $total;
		$total = array(0=>0, 1=>0);
		foreach ($results as $v) {
			$val = explode('(',trim($v[$this->cats[0]],')'));
			$total[0] += $val[0];
			if (isset($val[1])) $total[1] += $val[1];
		}
		$total_disp = $total[0];
		if ($total[1]!=0) $total_disp .= ' ('.$total[1].')';
		return $total_disp;
	}
	
	public function display_sales_by_item_cells($ref_rec) {
		$currency = 1;
		$prec = Utils_CurrencyFieldCommon::get_precission($currency);
		$multip = pow(10,$prec);
		
		$transs = DB::Execute('SELECT * FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND od.f_item_name=%d AND o.f_status=20', array($ref_rec['id']));
		$ret = array();
		$i=0;
		$hash = array();
		$qty_with_unkn_price = array();
		foreach ($this->columns as $k=>$v) {
			$ret[$i] = array(	$this->cats[0]=>0,
								$this->cats[1]=>array($currency=>0));
			$qty_with_unkn_price[$i] = 0;
			$hash[$k] = $i;
			$i++;
		}
		while ($trans=$transs->FetchRow()) {
			$w = $hash[$trans['f_warehouse']];
			$ret[$w][$this->cats[0]] += $trans['f_quantity'];
			$ret[$w][$this->cats[1]][$currency] += $trans[$this->range_type['other']['prices'][0].'_earning_'.$this->range_type['other']['method']]/$multip;
			$qty_with_unkn_price[$w] += $trans['f_quantity']-$trans['quantity_'.$this->range_type['other']['method']];
		}
		foreach ($this->columns as $k=>$v) { 
			if (isset($qty_with_unkn_price[$hash[$k]]) && $qty_with_unkn_price[$hash[$k]]!=0) {
				$ret[$hash[$k]][$this->cats[0]] = $ret[$hash[$k]][$this->cats[0]].' ('.$qty_with_unkn_price[$hash[$k]].')';
			}
		}
		return $ret;
	}
	
	public function sales_by_transaction() {
		Base_ActionBarCommon::add('search', 'Scan', $this->create_callback_href(array($this, 'recalculate')));
		print('<br><b>Numbers in brackets indicate items sold that were omitted in earning calculation.</b><br><br>');
		$form = $this->init_module('Libs/QuickForm');
		$form->addElement('select', 'method', $this->t('Method'), array('fifo'=>$this->t('FIFO'), 'lifo'=>$this->t('LIFO')));
		$form->addElement('select', 'prices', $this->t('Prices'), array('net'=>$this->t('Net'), 'gross'=>$this->t('Gross')));
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$warehouse_choice = array();
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		foreach ($warehouses as $k=>$v) {
			if (!$my_warehouse) $my_warehouse = $v['id'];
			$warehouse_choice[$v['id']] = $v['warehouse'];
		}
		$form->addElement('select', 'warehouse', $this->t('Warehouse'), $warehouse_choice);
		$form->setDefaults(array('method'=>'fifo','prices'=>'net', 'warehouse'=>$my_warehouse));
		$this->cats = array('Qty Sold','Earnings');
		$this->range_type = $this->rbr->display_date_picker(array(), $form);
		$transactions_count = Utils_RecordBrowserCommon::get_records_count('premium_warehouse_items_orders', array('>=transaction_date'=>$this->range_type['start'], '<=transaction_date'=>$this->range_type['end'], 'transaction_type'=>1, 'warehouse'=>$this->range_type['other']['warehouse']));
		$limit = $this->rbr->enable_paging($transactions_count);
		$order = '_earning_';
		if ($this->range_type['other']['method']=='fifo') $order = $order.'fifo';
		else $order = $order.'lifo';
		if ($this->range_type['other']['prices']=='net') $order = 'n'.$order;
		else $order = 'g'.$order;
		$trans_ids_tmp = DB::SelectLimit('SELECT o.id FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 AND o.f_transaction_date>=%D AND o.f_transaction_date<=%D GROUP BY o.id ORDER BY SUM('.$order.') DESC', $limit['numrows'], $limit['offset'], array($this->range_type['start'], $this->range_type['end']));
		$trans_ids = array();
		while ($x=$trans_ids_tmp->FetchRow()) $trans_ids[] = $x['id'];
		$trans_recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',array('id'=>$trans_ids));
		$transactions = array();
		foreach ($trans_ids as $v) {
			$transactions[$v] = $trans_recs[$v];
		}
		$this->rbr->set_reference_records($transactions);
		$this->rbr->set_reference_record_display_callback(array($this,'display_transaction_id'));
		$this->rbr->set_categories($this->cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total', 'callback'=>array($this,'sales_by_item_row_total')));
		$this->rbr->set_format(array(	$this->cats[0]=>'numeric', 
										$this->cats[1]=>'currency'
									));
		$header = array('Transaction', $warehouses[$my_warehouse]['warehouse']);
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_sales_by_transaction_cells'));
//		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
//		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
//		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}	
	
	public function display_transaction_id($r) {
		return Premium_Warehouse_Items_OrdersCommon::display_transaction_id($r,false).' - '.Base_RegionalSettingsCommon::time2reg($r['transaction_date'], false).' - '.CRM_ContactsCommon::contact_format_no_company($r['employee']);
	}
	
	public function display_sales_by_transaction_cells($transaction) {
		$currency = 1;
		$prec = Utils_CurrencyFieldCommon::get_precission($currency);
		$multip = pow(10,$prec);
		
		$transs = DB::Execute('SELECT * FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND o.id=%d AND o.f_status=20', array($transaction['id']));
		$ret = array(0=>array(	$this->cats[0]=>0,
							$this->cats[1]=>array($currency=>0)));
		$qty_with_unkn_price = 0;
		while ($trans=$transs->FetchRow()) {
			$ret[0][$this->cats[0]] += $trans['f_quantity'];
			$ret[0][$this->cats[1]][$currency] += $trans[$this->range_type['other']['prices'][0].'_earning_'.$this->range_type['other']['method']]/$multip;
			$qty_with_unkn_price += $trans['f_quantity']-$trans['quantity_'.$this->range_type['other']['method']];
		}
		if ($qty_with_unkn_price!=0) {
			$ret[0][$this->cats[0]] = $ret[0][$this->cats[0]].' ('.$qty_with_unkn_price.')';
		}
		return $ret;
	}
	
/************************************************************************************/
	public function display_sales($warehouse_id, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','orders_module');
		$order_date='transaction_date';
		$cols='';
		// sales status can be 7 or 20
		$orders = array(array('warehouse'=>$warehouse_id,'transaction_type'=>'1','status'=>array(20,7),'>='.$order_date=>$start, '<='.$order_date=>$end), $cols, array($order_date=>'DESC'));
		$rb->set_header_properties(array('terms'=>array('width'=>1, 'wrapmode'=>'nowrap'),'status'=>array('width'=>1, 'wrapmode'=>'nowrap')));
		$this->display_module($rb,$orders,'show_data');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		return true;
	}
	
/************************************************************************************/
	public function display_purchases($warehouse_id, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','orders_module');
		$order_date='transaction_date';
		$cols='';
		$orders = array(array('warehouse'=>$warehouse_id,'transaction_type'=>'0','status'=>'20', '>='.$order_date=>$start, '<='.$order_date=>$end), $cols, array($order_date=>'DESC'));
		$rb->set_header_properties(array('terms'=>array('width'=>1, 'wrapmode'=>'nowrap'),'status'=>array('width'=>1, 'wrapmode'=>'nowrap')));
		$this->display_module($rb,$orders,'show_data');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		return true;
	}
	
/************************************************************************************/
	public function caption() {
		return 'Sales Report';
	}
}

?>