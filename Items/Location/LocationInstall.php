<?php
/**
 * Warehouse - Location
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.9
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_LocationInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());

		$fields = array(
			array('name'=>'Item SKU', 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_item_sku')),
			array('name'=>'Item Name', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_item_name')),
			array('name'=>'Quantity',	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Serial',		'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true),
			array('name'=>'Rental Item','type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Used',		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Warehouse', 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse::Warehouse;::', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_LocationCommon', 'display_warehouse'))
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_location', $fields);

//		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_location', 'Item');
//		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_location', true);
//		Utils_RecordBrowserCommon::set_recent('premium_warehouse_location', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_location', 'Item Location');
//		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_orders', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items/Orders', 'icon.png'));
//		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items_orders', 'Premium_Warehouse_Items_OrdersCommon', 'access_orders');
//		Utils_RecordBrowserCommon::set_processing_method('premium_warehouse_location', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order'));
			
// ************ addons ************** //
//		Utils_RecordBrowserCommon::new_addon('premium_warehouse_location', 'Premium/Warehouse/Location', 'attachment_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_addon', 'Premium_Warehouse_Items_LocationCommon::location_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse', 'Premium/Warehouse/Items/Location', 'warehouse_item_list_addon', 'Item List');
		Utils_RecordBrowserCommon::set_addon_pos('premium_warehouse', 'Premium/Warehouse/Items/Location', 'warehouse_item_list_addon', 1);

// ************ other ************** //
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_location', 'Premium_Warehouse_Items_LocationCommon', 'access_location');
		Utils_RecordBrowserCommon::set_display_method('premium_warehouse_items', 'Quantity on Hand', 'Premium_Warehouse_Items_LocationCommon', 'display_item_quantity');

		$this->add_aco('browse location',array('Employee'));
		$this->add_aco('view location',array('Employee'));
		$this->add_aco('edit location',array('Employee'));
		$this->add_aco('delete location',array('Employee Manager'));

/*		$this->add_aco('view protected notes','Employee');
		$this->add_aco('view public notes','Employee');
		$this->add_aco('edit protected notes','Employee Administrator');
		$this->add_aco('edit public notes','Employee');*/
		
		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_addon');
		Utils_RecordBrowserCommon::unset_display_method('premium_warehouse_items', 'Quantity on Hand');
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Location', 'location_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_location');
		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Premium/Warehouse','version'=>0),
			array('name'=>'Premium/Warehouse/Items','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Items Transactions - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'SPL');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_warehouse_location');		
	}
}

?>
