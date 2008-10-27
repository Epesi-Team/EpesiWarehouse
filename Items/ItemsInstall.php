<?php
/**
 * Warehouse - Items
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_ItemsInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'SKU', 			'type'=>'calculated', 'required'=>false, 'param'=>'VARCHAR(16)', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_ItemsCommon','display_sku')),
			array('name'=>'Item Type', 		'type'=>'select', 'param'=>'__COMMON__::Premium_Warehouse_Items_Type', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Item Name', 		'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_item_name')),
			array('name'=>'Product Code', 	'type'=>'text', 'required'=>false, 'param'=>'32', 'extra'=>false, 'visible'=>false),
			array('name'=>'UPC', 			'type'=>'text', 'required'=>false, 'param'=>'12', 'extra'=>false, 'visible'=>true),
			array('name'=>'Manufacturer Part Number', 'type'=>'text', 'required'=>false, 'param'=>'32', 'extra'=>false, 'visible'=>true),
			array('name'=>'Vendor',		 	'type'=>'crm_company', 'required'=>false, 'extra'=>false, 'visible'=>true, 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','vendors_crits'))),
			array('name'=>'Net Price', 		'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Tax Rate', 		'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'__COMMON__::Premium_Warehouse_Items_Tax', 'style'=>'integer'),
			array('name'=>'Gross Price', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'style'=>'currency', 'display_callback'=>array('Premium_Warehouse_ItemsCommon','display_gross_price')),
			array('name'=>'Quantity', 		'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Cost', 			'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Reorder point', 	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name'=>'Description', 	'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items', 'Item Name');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items', true);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items', 'Items');
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items', 'Premium_Warehouse_ItemsCommon', 'access_items');
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items', array('Premium_Warehouse_ItemsCommon','watchdog_label'));
		Utils_RecordBrowserCommon::set_processing_method('premium_warehouse_items', array('Premium_Warehouse_ItemsCommon', 'submit_item'));
		
// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items', 'attachment_addon', 'Notes');

// ************ other ************** //	
		Utils_CommonDataCommon::new_array('Premium_Warehouse_Items_Type',array(0=>'Inventory Items', 1=>'Serialized Items', 2=>'Non-Inventory Item', 3=>'Service')); 
		Utils_CommonDataCommon::new_array('Premium_Warehouse_Items_Tax',array(0=>'Non-Taxable')); // Notice: Key should be a percent value of the tax

		$this->add_aco('browse items',array('Employee'));
		$this->add_aco('view items',array('Employee'));
		$this->add_aco('edit items',array('Employee'));
		$this->add_aco('delete items',array('Employee Manager'));

		$this->add_aco('view protected notes','Employee');
		$this->add_aco('view public notes','Employee');
		$this->add_aco('edit protected notes','Employee Administrator');
		$this->add_aco('edit public notes','Employee');
		
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Items', 'attachment_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items');
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
			'Description'=>'Warehouse Items - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'SPL');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_warehouse_items');		
	}
}

?>
