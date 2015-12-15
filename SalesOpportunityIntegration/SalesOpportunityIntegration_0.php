<?php
/**
 * Sales Opportunity Tracker
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-premium
 * @subpackage salesopportunity
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_SalesOpportunityIntegration extends Module {

	public function orders_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders');
		$order = array(
			array('opportunity'=>$arg['id']), 
			array(), 
			array('transaction_date'=>'DESC', 'transaction_id'=>'DESC'));
		$me = CRM_ContactsCommon::get_my_record();
		$defaults = array(	'country'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_country'),
							'zone'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_state'),
							'transaction_date'=>date('Y-m-d'),
							'employee'=>$me['id'],
							'warehouse'=>Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse'),
							'opportunity'=>$arg['id'], 
							'transaction_type'=>1, 
							'status'=>1, 
							'payment'=>1
							);
		$rb->set_header_properties(array(
			'status'=>array('width'=>15, 'wrapmode'=>'nowrap'),
			'transaction_id'=>array('width'=>10, 'wrapmode'=>'nowrap', 'name'=>__('Trans. ID')),
			'transaction_type'=>array('width'=>10, 'wrapmode'=>'nowrap', 'name'=>__('Type')),
			'transaction_date'=>array('width'=>10, 'wrapmode'=>'nowrap', 'name'=>__('Date'))
		));
		$rb->set_defaults($defaults);
		$this->display_module($rb,$order,'show_data');
	}
}

?>