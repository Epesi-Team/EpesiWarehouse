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

class Premium_Warehouse_SalesOpportunityIntegrationInstall extends ModuleInstall {

	public function install() {
		Base_ThemeCommon::install_default_theme($this->get_type());

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders', array('name'=>'Opportunity', 'type'=>'select', 'required'=>false, 'visible'=>false, 'param'=>'premium_salesopportunity::Opportunity Name;Premium_SalesOpportunityCommon::crm_opportunity_reference_crits', 'position'=>'Notes'));
		Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items_orders', 'Opportunity', array('Premium_Warehouse_SalesOpportunityIntegrationCommon', 'QFfield_salesopp'));

		Utils_RecordBrowserCommon::new_addon('premium_salesopportunity', 'Premium/Warehouse/SalesOpportunityIntegration', 'orders_addon', 'Transactions');

		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::delete_addon('premium_salesopportunity', 'Premium/Warehouse/SalesOpportunityIntegration', 'orders_addon');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders', 'Opportunity');
		return true;
	}
	
	public function version() {
		return array("1.0");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'Utils/ChainedSelect', 'version'=>0), 
			array('name'=>'Premium/Warehouse/Items/Orders', 'version'=>0), 
			array('name'=>'Premium/SalesOpportunity', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'SalesOpportunity & Inventory Integration - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
        return array('package'=>'Inventory Management', 'option'=>'Sales Opportunity Integration');
	}
}

?>
