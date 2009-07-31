<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Items
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items
 */

defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_ItemsInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'SKU', 			'type'=>'calculated', 'required'=>false, 'filter'=>true, 'param'=>Utils_RecordBrowserCommon::actual_db_type('text',16), 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_ItemsCommon','display_sku')),
			array('name'=>'Item Type', 		'type'=>'commondata', 'param'=>array('order_by_key'=>true,'Premium_Warehouse_Items_Type'), 'filter'=>true, 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Item Name', 		'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_item_name')),
			array('name'=>'Product Code', 	'type'=>'text', 'required'=>false, 'param'=>'32', 'extra'=>false, 'visible'=>false),
			array('name'=>'UPC', 			'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>false, 'visible'=>false),
			array('name'=>'Quantity on Hand','type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Quantity Sold',	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_quantity_sold')),
			array('name'=>'Reorder point', 	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name'=>'Weight', 		'type'=>'float', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_weight')),
			array('name'=>'Volume',	 		'type'=>'float', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_volume')),
			array('name'=>'Manufacturer Part Number', 'type'=>'text', 'required'=>false, 'param'=>'32', 'extra'=>false, 'visible'=>true),
			array('name'=>'Vendor',		 	'type'=>'crm_company', 'required'=>false, 'extra'=>false, 'filter'=>true, 'visible'=>true, 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','vendors_crits'))),
			array('name'=>'Net Price', 		'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Tax Rate', 		'type'=>'select', 'required'=>false, 'extra'=>false, 'visible'=>false, 'param'=>'data_tax_rates::Name', 'style'=>'integer'),
			array('name'=>'Gross Price', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'style'=>'currency', 'display_callback'=>array('Premium_Warehouse_ItemsCommon','display_gross_price'), 'QFfield_callback'=>array('Premium_Warehouse_ItemsCommon','QFfield_gross_price')),
			array('name'=>'Cost', 			'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Category',		'type'=>'multiselect', 'required'=>false, 'visible'=>false, 'extra'=>false, 'filter'=>true, 'param'=>'premium_warehouse_items_categories::Category Name', 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_item_category')),
			array('name'=>'Description', 	'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items', 'Item Name');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items', true);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items', 'Items');
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items', array('Premium_Warehouse_ItemsCommon', 'access_items'));
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items', array('Premium_Warehouse_ItemsCommon','watchdog_label'));
		Utils_RecordBrowserCommon::set_processing_callback('premium_warehouse_items', array('Premium_Warehouse_ItemsCommon', 'submit_item'));

		$fields = array(
			array('name'=>'Category Name', 	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Parent Category','type'=>'select', 'param'=>'premium_warehouse_items_categories::Category Name' ,'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Position', 		'type'=>'integer', 'required'=>false, 'extra'=>false, 'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items_categories', $fields);
		
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items_categories', 'Items Categories');
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_categories', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items_categories', 'Premium_Warehouse_ItemsCommon', 'access_items_categories');
		Utils_RecordBrowserCommon::set_processing_callback('premium_warehouse_items_categories', array('Premium_Warehouse_ItemsCommon', 'submit_position'));

// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items', 'attachment_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_categories', 'Premium/Warehouse/Items', 'subcategories_addon', 'Subcategories');

// ************ other ************** //	
		Utils_CommonDataCommon::new_array('Premium_Warehouse_Items_Type',array(0=>'Inventory Item', 1=>'Serialized Item', 2=>'Non-Inventory Item', 3=>'Service'));
		Utils_CommonDataCommon::new_array('Premium_Warehouse_Items_Categories',array());
		Utils_CommonDataCommon::extend_array('Companies_Groups',array('manufacturer'=>'Manufacturer'));

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
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_categories', 'Premium/Warehouse/Items', 'subcategories_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Items', 'attachment_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items_categories');

		Utils_CommonDataCommon::remove('Premium_Warehouse_Items_Type'); 
		Utils_CommonDataCommon::remove('Premium_Warehouse_Items_Categories');
		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Data/TaxRates','version'=>0),
			array('name'=>'Base','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Warehouse Items - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_warehouse_items');		
	}
}

?>
