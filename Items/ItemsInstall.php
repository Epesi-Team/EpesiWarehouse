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
    const version = '1.5.1';

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name' => _M('SKU'), 			'type'=>'calculated', 'required'=>false, 'filter'=>true, 'param'=>Utils_RecordBrowserCommon::actual_db_type('text',16), 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_ItemsCommon','display_sku')),
			array('name' => _M('Item Type'), 		'type'=>'commondata', 'param'=>array('order_by_key'=>true,'Premium_Warehouse_Items_Type'), 'filter'=>true, 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Item Name'), 		'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_item_name')),
			array('name' => _M('Product Code'), 	'type'=>'text', 'required'=>false, 'param'=>'32', 'extra'=>false, 'visible'=>false),
			array('name' => _M('UPC'), 			'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>false, 'visible'=>false),
			array('name' => _M('Quantity on Hand'),'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Quantity Sold'),	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_quantity_sold')),
			array('name' => _M('Reorder point'), 	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Weight'), 		'type'=>'float', 'required'=>true, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_weight')),
			array('name' => _M('Volume'),	 		'type'=>'float', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_volume')),
			array('name' => _M('Manufacturer Part Number'), 'type'=>'text', 'required'=>false, 'param'=>'32', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Manufacturer'),	'type'=>'crm_company',	'required'=>false,	'extra'=>false,	'filter'=>true,	'visible'=>true,
				'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','manufacturer_crits'))),
			array('name' => _M('Vendor'),		 	'type'=>'crm_company', 'required'=>false, 'extra'=>false, 'filter'=>true, 'visible'=>true, 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','vendors_crits'))),
			array('name' => _M('Net Price'), 		'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Tax Rate'), 		'type'=>'select', 'required'=>false, 'extra'=>false, 'visible'=>false, 'param'=>'data_tax_rates::Name', 'style'=>'integer'),
			array('name' => _M('Gross Price'), 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'style'=>'currency', 'display_callback'=>array('Premium_Warehouse_ItemsCommon','display_gross_price'), 'QFfield_callback'=>array('Premium_Warehouse_ItemsCommon','QFfield_gross_price')),
			array('name' => _M('Cost'), 			'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Category'),		'type'=>'multiselect', 'required'=>false, 'visible'=>false, 'extra'=>false, 'filter'=>true, 'param'=>'premium_warehouse_items_categories::Category Name', 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_item_category')),
			array('name' => _M('Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items', 'Item Name');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items', true);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items', _M('Items'));
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items', 'icon.png'));
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items', array('Premium_Warehouse_ItemsCommon','watchdog_label'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items', array('Premium_Warehouse_ItemsCommon', 'submit_item'));

		$fields = array(
			array('name' => _M('Category Name'), 	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Parent Category'),'type'=>'select', 'param'=>'premium_warehouse_items_categories::Category Name' ,'required'=>false, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_category_parent')),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>false, 'extra'=>false, 'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items_categories', $fields);
		
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items_categories', _M('Items Categories'));
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_categories', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items', 'icon.png'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items_categories', array('Premium_Warehouse_ItemsCommon', 'submit_position'));

// ************ addons ************** //
		Utils_AttachmentCommon::new_addon('premium_warehouse_items');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_categories', 'Premium/Warehouse/Items', 'subcategories_addon', _M('Subcategories'));

// ************ other ************** //	
		Utils_CommonDataCommon::new_array('Premium_Warehouse_Items_Type',array(0=>_M('Inventory Item'), 1=>_M('Serialized Item'), 2=>_M('Non-Inventory Item'), 3=>_M('Service')),true,true);
        Utils_CommonDataCommon::new_id('Premium_Warehouse_Items_Categories', true);
        Utils_CommonDataCommon::new_array('Premium_Warehouse_Items_Categories',array());
		Utils_CommonDataCommon::extend_array('Companies_Groups',array('manufacturer'=>_M('Manufacturer')));

		Utils_RecordBrowserCommon::add_access('premium_warehouse_items', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items', 'add', 'ACCESS:employee', array(), array('item_type'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items', 'edit', 'ACCESS:employee', array(), array('item_type'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items', 'delete', array('ACCESS:employee', 'ACCESS:manager'));

		Utils_RecordBrowserCommon::add_default_access('premium_warehouse_items_categories');

		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items', array('Premium_Warehouse_ItemsCommon', 'submit_item'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items_categories', array('Premium_Warehouse_ItemsCommon', 'submit_position'));
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_categories', 'Premium/Warehouse/Items', 'subcategories_addon');
		Utils_AttachmentCommon::delete_addon('premium_warehouse_items');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items_categories');

		Utils_CommonDataCommon::remove('Premium_Warehouse_Items_Type'); 
		Utils_CommonDataCommon::remove('Premium_Warehouse_Items_Categories');
		return true;
	}
	
	public function version() {
		return array(self::version);
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
        return array('package'=>__('Inventory Management'));
	}
}

?>
