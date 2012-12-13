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
		set_time_limit(0);
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name' => _M('Warehouse'), 		'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_WarehouseCommon', 'display_warehouse')),
			array('name' => _M('Address 1'), 		'type'=>'text', 'required'=>true, 'param'=>'64', 'extra'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name' => _M('Address 2'), 		'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name' => _M('City'), 			'type'=>'text', 'required'=>true, 'param'=>'64', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name' => _M('Country'), 		'type'=>'commondata', 'required'=>true, 'param'=>array('Countries'), 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name' => _M('Zone'), 			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Country'), 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name' => _M('Phone'), 			'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Fax'), 			'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false, 'visible'=>false),
			array('name' => _M('Tax ID'), 		'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false, 'visible'=>false),
			array('name' => _M('Bank account'), 	'type'=>'text', 'required'=>false, 'param'=>'255', 'extra'=>false, 'visible'=>false),
			array('name' => _M('Postal Code'), 	'type'=>'text', 'required'=>false, 'param'=>'64', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse', 'Warehouse');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse', true);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse', _M('Warehouse'));
		Utils_RecordBrowserCommon::set_icon('premium_warehouse', Base_ThemeCommon::get_template_filename('Premium/Warehouse', 'icon.png'));
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse', array('Premium_WarehouseCommon','watchdog_label'));
		
// ************ addons ************** //
		Utils_AttachmentCommon::new_addon('premium_warehouse');

// ************ other ************** //	
		Variable::set('premium_warehouse_weight_units', 'kg');
		Variable::set('premium_warehouse_volume_units', 'm<sup>3</sup>');

		Utils_RecordBrowserCommon::add_access('premium_warehouse', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse', 'add', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse', 'edit', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse', 'delete', array('ACCESS:employee', 'ACCESS:manager'));
		
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_AttachmentCommon::delete_addon('premium_warehouse');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse');
		return true;
	}
	
	public static function post_install() {
		$main_company = CRM_ContactsCommon::get_company(CRM_ContactsCommon::get_main_company());
		return array(
				 array('type'=>'text','name'=>'warehouse','label'=>__('Warehouse'),'default'=>'','param'=>array('maxlength'=>128), 'rule'=>array(array('type'=>'required','message'=>__('Field required')))),
			     array('type'=>'text','name'=>'address1','label'=>__('Address 1'),'default'=>$main_company['address_1'],'param'=>array('maxlength'=>64), 'rule'=>array(array('type'=>'required','message'=>__('Field required')))),
			     array('type'=>'text','name'=>'address2','label'=>__('Address 2'),'default'=>$main_company['address_2'],'param'=>array('maxlength'=>64)),
			     array('type'=>'callback','name'=>'country','func'=>array('Premium_WarehouseInstall','country_element'),'default'=>$main_company['country']),
			     array('type'=>'callback','name'=>'state','func'=>array('Premium_WarehouseInstall','state_element'),'default'=>$main_company['zone']),
			     array('type'=>'text','name'=>'city','label'=>__('City'),'default'=>$main_company['city'],'param'=>array('maxlength'=>64), 'rule'=>array(array('type'=>'required','message'=>__('Field required')))),
			     array('type'=>'text','name'=>'postal','label'=>__('Postal Code'),'default'=>$main_company['postal_code'],'param'=>array('maxlength'=>64))
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
        return array('package'=>__('Inventory Management'), 'version'=>'1.4.2');
	}
}

?>
