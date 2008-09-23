<?php
/**
 * Warehouse
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_WarehouseInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'Warehouse', 		'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_WarehouseCommon', 'display_warehouse')),
			array('name'=>'Address 1', 		'type'=>'text', 'required'=>true, 'param'=>'64', 'extra'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'Address 2', 		'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'City', 			'type'=>'text', 'required'=>true, 'param'=>'64', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'Country', 		'type'=>'commondata', 'required'=>true, 'param'=>array('Countries'), 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name'=>'Zone', 			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Country'), 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name'=>'Postal Code', 	'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse', 'Warehouse');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse', true);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse', 'Warehouse');
		Utils_RecordBrowserCommon::set_icon('premium_warehouse', Base_ThemeCommon::get_template_filename('Premium/Warehouse', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse', 'Premium_WarehouseCommon', 'access_warehouse');
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse', array('Premium_WarehouseCommon','watchdog_label'));
		
// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_warehouse', 'Premium/Warehouse', 'attachment_addon', 'Notes');

// ************ other ************** //	
		$this->add_aco('browse warehouses',array('Employee'));
		$this->add_aco('view warehouses',array('Employee'));
		$this->add_aco('edit warehouses',array('Employee'));
		$this->add_aco('delete warehouses',array('Employee Manager'));

		$this->add_aco('view protected notes','Employee');
		$this->add_aco('view public notes','Employee');
		$this->add_aco('edit protected notes','Employee Administrator');
		$this->add_aco('edit public notes','Employee');
		
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse', 'Premium/Warehouse', 'attachment_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse');
		return true;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Warehouses - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'SPL');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_warehouse');		
	}
}

?>
