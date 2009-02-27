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
	private static $cats = array('No. of sales', 'Sales Volume');
	private static $format = '';
	private static $dates = array();
	private static $range_type = '';
	private $rbr = null;

	public function construct() {
		$this->rbr = $this->init_module('Utils/RecordBrowser/Reports');
	}

	public function body() {
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$this->rbr->set_reference_records($recs);
		$this->rbr->set_reference_record_display_callback(array('Premium_WarehouseCommon','display_warehouse'));
		$date_range = $this->rbr->display_date_picker();
		$this->rbr->set_categories(self::$cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total'));
		$this->rbr->set_format(array(	self::$cats[0]=>'numeric', 
										self::$cats[1]=>'currency'
									));
		// TODO: Limit to Sale transactions only (transaction_type=1) - how?
		$this->rbr->set_data_records('premium_warehouse_items_orders',array('transaction_type'=>'1'));
	
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
//		$this->rbr->set_pdf_title($this->t('Projects - Report, %s',array(date('Y-m-d H:i:s'))));
//		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
//		$this->rbr->set_pdf_filename($this->t('Projects_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}

	public function display_cells($ref_rec, $records){
		
		$result = array();
		$hash = array();
		$i = 0;
		foreach ($this->dates as $v) {
			$result[$i] = array(	self::$cats[0]=>0,
									self::$cats[1]=>0);
			$hash[date($this->format, $v)] = $i;
			$i++;
		}
		foreach ($records[0] as $v) {
			$d = date($this->format,strtotime($v['transaction_date']));
			if (isset($hash[$d])) {
				// count no. of transactions
				$result[$hash[$d]][self::$cats[0]]++;
				// Calculate sales volume
				$result[$hash[$d]][self::$cats[1]] += $v['payment'];
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
			if ($result[$i][self::$cats[0]]<>0) {
				$date_type = 'transaction_date';
				$result[$i][self::$cats[0]] = '<a '.$this->create_callback_href(array($this,'display_sales'), array($ref_rec['id'], $date_type, $start, $end)).'>'.$result[$i][self::$cats[0]].'</a>';
			}
			$i++;
		}
		return $result;
	}
	
	public function display_sales($contact_id, $date_type, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','custom_projects','projects_module');
		$rb->set_cut_lengths(array('project_name'=>30,'company_name'=>30));
		$type = ($date_type=='award_date'); 
		$cols = array('award_date'=>$type,'contract_amount'=>$type,'proposal_submitted'=>!$type,'proposal_price'=>!$type);
		$cols['company_name'] = false;
		$cols['outside_job'] = false;
		$cols['tm'] = false;
		$cols['bid_due_date'] = false;
		$rb->set_table_column_order(array('custom_projects'=>array('project_name','zsi_estimator','percent_complete','status','award_date','contract_amount','proposal_submitted','proposal_price')));
		$rb->set_header_properties(array('percent_complete'=>array('name'=>'% Complete','width'=>1),'proposal_price'=>array('width'=>1),'proposal_submitted'=>array('name'=>'Submitted','width'=>1),'contract_amount'=>array('name'=>'Contract','width'=>1)));
		$proj = array(array('zsi_estimator'=>$contact_id, '>='.$date_type=>$start, '<='.$date_type=>$end), $cols, array($date_type=>'ASC'));
		$this->display_module($rb,$proj,'show_data');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		return true;
	}
	
	public function display_change_orders($contact_id, $date_type, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','custom_changeorders','custom_changeorders');
		$cols = array();
		$cols[$date_type] = true;
//		$rb->set_cut_lengths(array('project_name'=>30,'company_name'=>30));
//		$type = ($date_type=='award_date'); 
//		$cols = array('award_date'=>$type,'contract_amount'=>$type,'proposal_submitted'=>!$type,'proposal_price'=>!$type);
//		$cols['company_name'] = false;
//		$cols['outside_job'] = false;
//		$cols['tm'] = false;
//		$cols['bid_due_date'] = false;
//		$rb->set_table_column_order(array('custom_projects'=>array('project_name','zsi_estimator','percent_complete','status','award_date','contract_amount','proposal_submitted','proposal_price')));
//		$rb->set_header_properties(array('percent_complete'=>array('name'=>'% Complete','width'=>1),'proposal_price'=>array('width'=>1),'proposal_submitted'=>array('name'=>'Submitted','width'=>1),'contract_amount'=>array('name'=>'Contract','width'=>1)));
		$proj = array(array('zsi_estimator'=>$contact_id, '>='.$date_type=>$start, '<='.$date_type=>$end), $cols, array($date_type=>'ASC'));
		$this->display_module($rb,$proj,'show_data');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		return true;
	}
	
	public function caption() {
		return 'Sales Report';
	}
}

?>