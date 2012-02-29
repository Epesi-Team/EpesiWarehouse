<?php
/**
 * 
 * @author pbukowski@telaxus.com
 * @copyright Telaxus LLC
 * @license Commercial
 * @version 0.1
 * @package epesi-Premium/Warehouse/Items/
 * @subpackage Kit
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_KitInstall extends ModuleInstall {

	public function install() {
		Utils_CommonDataCommon::extend_array('Premium_Warehouse_Items_Type',array('kit'=>'Kit'));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', array('name'=>'Kit items',	'type'=>'multiselect', 'required'=>false, 'filter'=>true, 'extra'=>false, 'visible'=>false, 'param'=>'premium_warehouse_items::Item Name','QFfield_callback'=>array('Premium_Warehouse_Items_KitCommon','QFfield_kit_items')));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items', array('Premium_Warehouse_Items_KitCommon', 'access_items'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items', array('Premium_Warehouse_Items_KitCommon', 'submit_items'));
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Quantity on Hand', array('Premium_Warehouse_Items_KitCommon', 'display_item_quantity'));
		Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items', 'Quantity on Hand', array('Premium_Warehouse_Items_KitCommon', 'QFfield_item_quantity'));
		$this->create_data_dir();
		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items', array('Premium_Warehouse_Items_KitCommon', 'submit_items'));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items', array('Premium_Warehouse_Items_OrdersCommon', 'access_items'));
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items', 'Kit items');
		Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items', 'Quantity on Hand', array('Premium_Warehouse_Items_LocationCommon', 'QFfield_item_quantity'));
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Quantity on Hand', array('Premium_Warehouse_Items_LocationCommon', 'display_item_quantity'));
		return true;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Premium/Warehouse/Items','version'=>0),
			array('name'=>'Premium/Warehouse/eCommerce','version'=>0),
			array('name'=>'Premium/Warehouse/Wholesale','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Location','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Orders','version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'',
			'Author'=>'pbukowski@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
        return array('package'=>'Inventory Management');
	}
	
}

?>