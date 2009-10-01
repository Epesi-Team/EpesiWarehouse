<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
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
			array('name'=>'Phone', 			'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false, 'visible'=>true),
			array('name'=>'Fax', 			'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false, 'visible'=>false),
			array('name'=>'Tax ID', 		'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false, 'visible'=>false),
			array('name'=>'Bank account', 	'type'=>'text', 'required'=>false, 'param'=>'255', 'extra'=>false, 'visible'=>false),
			array('name'=>'Postal Code', 	'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse', 'Warehouse');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse', true);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse', 'Warehouse');
		Utils_RecordBrowserCommon::set_icon('premium_warehouse', Base_ThemeCommon::get_template_filename('Premium/Warehouse', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse', array('Premium_WarehouseCommon', 'access_warehouse'));
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

		Variable::set('premium_warehouse_weight_units', 'kg');
		Variable::set('premium_warehouse_volume_units', 'm<sup>3</sup>');
		
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse', 'Premium/Warehouse', 'attachment_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse');
		return true;
	}
	
	public static function post_install() {
		$main_company = CRM_ContactsCommon::get_company(CRM_ContactsCommon::get_main_company());
		return array(
				 array('type'=>'text','name'=>'warehouse','label'=>'Warehouse','default'=>'','param'=>array('maxlength'=>128), 'rule'=>array(array('type'=>'required','message'=>'Field required'))),
			     array('type'=>'text','name'=>'address1','label'=>'Address 1','default'=>$main_company['address_1'],'param'=>array('maxlength'=>64), 'rule'=>array(array('type'=>'required','message'=>'Field required'))),
			     array('type'=>'text','name'=>'address2','label'=>'Address 2','default'=>$main_company['address_2'],'param'=>array('maxlength'=>64)),
			     array('type'=>'callback','name'=>'country','func'=>array('Premium_WarehouseInstall','country_element'),'default'=>$main_company['country']),
			     array('type'=>'callback','name'=>'state','func'=>array('Premium_WarehouseInstall','state_element'),'default'=>$main_company['zone']),
			     array('type'=>'text','name'=>'city','label'=>'City','default'=>$main_company['city'],'param'=>array('maxlength'=>64), 'rule'=>array(array('type'=>'required','message'=>'Field required'))),
			     array('type'=>'text','name'=>'postal','label'=>'Postal Code','default'=>$main_company['postal_code'],'param'=>array('maxlength'=>64))
//			     array('type'=>'text','name'=>'phone','label'=>'Phone','default'=>'','param'=>array('maxlength'=>64)),
//			     array('type'=>'text','name'=>'fax','label'=>'Fax','default'=>'','param'=>array('maxlength'=>64)),
//			     array('type'=>'text','name'=>'web','label'=>'Web address','default'=>'','param'=>array('maxlength'=>64))
			     );
	}
	
	private static $country_elem_name;
	public static function country_element($name, $args, & $def_js) {
		self::$country_elem_name = $name;
		return HTML_QuickForm::createElement('commondata',$name,'Country','Countries');
	}

	public static function state_element($name, $args, & $def_js) {
		return HTML_QuickForm::createElement('commondata',$name,'State',array('Countries',self::$country_elem_name),array('empty_option'=>true));
	}


	public static function post_install_process($val) {
		Utils_RecordBrowserCommon::new_record('premium_warehouse',
			array('warehouse'=>$val['warehouse'],
				'address_1'=>isset($val['address1'])?$val['address1']:'',
				'address_2'=>isset($val['address2'])?$val['address2']:'',
				'country'=>isset($val['country'])?$val['country']:'',
				'zone'=>isset($val['state'])?$val['state']:'',
				'city'=>isset($val['city'])?$val['city']:'',
				'postal_code'=>isset($val['postal'])?$val['postal']:''
//				'phone'=>isset($val['phone'])?$val['phone']:'',
//				'fax'=>isset($val['fax'])?$val['fax']:'',
				));
	}

	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'Data/Countries', 'version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Warehouses - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'Commecial');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_warehouse');		
	}
}

?>
