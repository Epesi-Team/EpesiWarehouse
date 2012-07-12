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
 * @subpackage warehouse-empsalesreport
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_EmpSalesReport extends Module {
	private static $cats = array('Sales Trans.','Sales Volume');
	private static $format = '';
	private static $dates = array();
	private static $range_type = '';
	private $currency = 1;
	private $rbr = null;

	public function construct() {
		$this->rbr = $this->init_module('Utils/RecordBrowser/Reports');
	}

/************************************************************************************/
	public function body() {

		if (!Base_AclCommon::i_am_admin()) {
		print(__('You don\'t have permission to access this module'));
		return;
		}

		$recs = Utils_RecordBrowserCommon::get_records('contact',array('(company_name'=>CRM_ContactsCommon::get_main_company(),'|related_companies'=>array(CRM_ContactsCommon::get_main_company())),array(),array('last_name'=>'ASC','first_name'=>'ASC'));
		$this->rbr->set_reference_records($recs);
		$this->rbr->set_reference_record_display_callback(array('CRM_ContactsCommon','contact_format_no_company'));

		$date_range = $this->rbr->display_date_picker();
		$this->rbr->set_categories(self::$cats);
		$this->rbr->set_summary('col', array('label'=>__('Total')));
		$this->rbr->set_summary('row', array('label'=>__('Total')));
		$this->rbr->set_format(array(	self::$cats[0]=>'numeric', 
										self::$cats[1]=>'currency'
									));
		$header = array('Employee');
		$this->dates = $date_range['dates'];
		$this->range_type = $date_range['type'];
		$this->rbr->set_currency($this->currency); // TODO: this method was removed
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
		$this->rbr->set_pdf_title(__('Employee Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename(__('Emp_Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}

	
/************************************************************************************/
	public function display_cells($ref_rec){
		
		$result = array();
		$hash = array();
		$i = 0;
		foreach ($this->dates as $v) {
		// all $cats must be initialized here individually to avoid: "Message: Undefined index: Purchase Volume" error - see private static $cats = array(... above
			$result[$i] = array(	self::$cats[0]=>0,
									self::$cats[1]=>0);
			$hash[date($this->format, $v)] = $i;
			$i++;
		}
		
		// transactions types: 0=>'Purchase',1=>'Sale',2=>'Inventory Adjustment',3=>'Rental',4=>'Warehouse Transfer
		
		$records = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('employee'=>$ref_rec['id']));
		
		foreach ($records as $v) {
			$d = date($this->format,strtotime($v['transaction_date']));
			if (isset($hash[$d])) {
				// count no. of Sales/Purchase Transactions
				// and sales/purchase volume
				// Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value()
				// returns array where keys are currency IDs and values are result numbers

				switch ($v['transaction_type']) {
					/********************** Purchase *******************/
					case '0':
						// ignore purchases
						break;
						/*
						// Include only Completed transactions - status=20
						if ($v['status']==20) {
							$result[$hash[$d]][self::$cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							if (!isset($purchase_amount[$this->currency])) break;
							$result[$hash[$d]][self::$cats[3]]+=$purchase_amount[$this->currency];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][self::$cats[4]] -= $purchase_amount[$this->currency];
							}
						break;
						*/
					/********************** Sale *******************/
					case '1':
						// Include only 7=>'Shipped', 20=>'Delivered'
						if ($v['status']==7 || $v['status']==20) {
							$result[$hash[$d]][self::$cats[0]]++;
							$sale_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							if (!isset($sale_amount[$this->currency])) break;
							$result[$hash[$d]][self::$cats[1]]+=$sale_amount[$this->currency];
						}
						break;
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
			$i++;
		}
		return $result;
	}
	
	
/************************************************************************************/
	public function display_sales($employee_id, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','orders_module');
		$order_date='transaction_date';
		$cols='';
		// sales status can be 7 or 20
		$orders = array(array('employee'=>$employee_id,'transaction_type'=>'1','status'=>array(20,7),'>='.$order_date=>$start, '<='.$order_date=>$end), $cols, array($order_date=>'DESC'));
		$rb->set_header_properties(array('terms'=>array('width'=>10, 'wrapmode'=>'nowrap'),'status'=>array('width'=>10, 'wrapmode'=>'nowrap')));
		$this->display_module($rb,$orders,'show_data');
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		return true;
	}
	
/************************************************************************************/
	public function caption() {
		return __('Employee Sales Report');
	}
}

?>