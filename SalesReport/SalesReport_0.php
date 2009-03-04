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
	private static $cats = array('Sales Trans.','Sales Volume','Purchase Trans.','Purchase Volume','Net Profit');
	private static $format = '';
	private static $dates = array();
	private static $range_type = '';
	private $rbr = null;

	public function construct() {
		$this->rbr = $this->init_module('Utils/RecordBrowser/Reports');
	}

/************************************************************************************/
	public function body() {

		if (!Base_AclCommon::i_am_admin() || !Base_AclCommon::i_am_sa()) {
		print($this->t('You don\'t have permission to access this module'));
		return;
		}

		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$this->rbr->set_reference_records($recs);
		$this->rbr->set_reference_record_display_callback(array('Premium_WarehouseCommon','display_warehouse'));
		$date_range = $this->rbr->display_date_picker();
		$this->rbr->set_categories(self::$cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total'));
		$this->rbr->set_format(array(	self::$cats[0]=>'numeric', 
										self::$cats[1]=>'currency',
										self::$cats[2]=>'numeric',
										self::$cats[3]=>'currency',
										self::$cats[4]=>'currency'
									));
		$this->rbr->set_data_records('premium_warehouse_items_orders');
		$this->rbr->set_data_record_relation('premium_warehouse_items_orders', array('warehouse'=>':id'));
		$header = array('Warehouse');
		$this->dates = $date_range['dates'];
		$this->range_type = $date_range['type'];
		switch ($date_range['type']) {
			case 'day': $this->format ='d M Y'; break;
			case 'week': $this->format ='W Y'; break;
			case 'month': $this->format ='M Y'; break;
			case 'year': $this->format ='Y'; break;
		} 
		foreach ($this->dates as $v)
			$header[] = date($this->format, $v);
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_cells'));
		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}

	
/************************************************************************************/
	public function display_cells($ref_rec, $records){
		
		$result = array();
		$hash = array();
		$i = 0;
		foreach ($this->dates as $v) {
		// all $cats must be initialized here individually to avoid: "Message: Undefined index: Purchase Volume" error - see private static $cats = array(... above
			$result[$i] = array(	self::$cats[0]=>0,
									self::$cats[1]=>0,
									self::$cats[2]=>0,
									self::$cats[3]=>0,
									self::$cats[4]=>0);
			$hash[date($this->format, $v)] = $i;
			$i++;
		}
		
		// TODO: transaction status filter
		// TODO: warehouse transfer
		// transactions types: 0=>'Purchase',1=>'Sale',2=>'Inventory Adjustment',3=>'Rental',4=>'Warehouse Transfer
		foreach ($records[0] as $v) {
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
							$result[$hash[$d]][self::$cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							$result[$hash[$d]][self::$cats[3]]+=$purchase_amount[1];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][self::$cats[4]] -= $purchase_amount[1];
							}
						break;
					/********************** Sale *******************/
					case '1':
						// Include only 7=>'Shipped', 20=>'Delivered'
						if ($v['status']==7 || $v['status']==20) {
							$result[$hash[$d]][self::$cats[0]]++;
							$sale_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							$result[$hash[$d]][self::$cats[1]]+=$sale_amount[1];
							// Net loss/profit - Increase - note +=
							$result[$hash[$d]][self::$cats[4]] += $sale_amount[1];
						}
						break;
						/********************** Inventory Adjustment *******************/
					case '2':
						// 20=>'Completed'
						if ($v['status']==20) {
							$result[$hash[$d]][self::$cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							$result[$hash[$d]][self::$cats[3]]+=$purchase_amount[1];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][self::$cats[4]] -= $purchase_amount[1];
							}
						break;
						/********************** WAREHOUSE TRANSFER *******************/
						/* Ignore - Sales/Purchase Volume = 0
					case 4:
						// ''=>'New', 1=>'Transfer Quote', 2=>'Pending', 3=>'Order Fullfilment', 4=>'On Hold', 5=>'Ready to Ship', 6=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing'
						if ($v['status']==20) {
							$result[$hash[$d]][self::$cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							Epesi::debug($v['warehouse']);
							$result[$hash[$d]][self::$cats[3]]+=$purchase_amount[1];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][self::$cats[4]] -= $purchase_amount[1];
							}
						break;
						*/
				} // end of switch
				
			}
		}
		
		$i = 0;
		foreach ($this->dates as $v) {
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
			if ($result[$i][self::$cats[0]]<>0) {
				$result[$i][self::$cats[0]] = '<a '.$this->create_callback_href(array($this,'display_sales'), array($ref_rec['id'], $start, $end)).'>'.$result[$i][self::$cats[0]].'</a>';
			}
			// Purchases transactions link
			if ($result[$i][self::$cats[2]]<>0) {
				$result[$i][self::$cats[2]] = '<a '.$this->create_callback_href(array($this,'display_purchases'), array($ref_rec['id'], $start, $end)).'>'.$result[$i][self::$cats[2]].'</a>';
			}
			$i++;
		}
		return $result;
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