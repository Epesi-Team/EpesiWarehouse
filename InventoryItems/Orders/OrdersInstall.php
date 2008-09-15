<?php
/**
 * Warehouse - Inventory Items Orders
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_InventoryItems_OrdersInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'Order ID', 	'type'=>'calculated', 'required'=>false, 'param'=>'VARCHAR(16)', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_InventoryItems_OrdersCommon','display_order_id')),
			array('name'=>'Item', 		'type'=>'select', 'required'=>true, 'param'=>'premium_inventoryitems::Item Name;Premium_Warehouse_InventoryItems_OrdersCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_InventoryItems_OrdersCommon', 'display_item_name')),
			array('name'=>'Operation', 	'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'__COMMON__::Premium_InventoryItems_Orders'),
			array('name'=>'Quantity', 	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Description','type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_inventoryitems_orders', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_inventoryitems_orders', 'Item');
		Utils_RecordBrowserCommon::set_favorites('premium_inventoryitems_orders', true);
		Utils_RecordBrowserCommon::set_recent('premium_inventoryitems_orders', 15);
		Utils_RecordBrowserCommon::set_caption('premium_inventoryitems_orders', 'Inventory Items Orders');
		Utils_RecordBrowserCommon::set_icon('premium_inventoryitems_orders', Base_ThemeCommon::get_template_filename('Premium/Warehouse/InventoryItems/Orders', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_inventoryitems_orders', 'Premium_Warehouse_InventoryItems_OrdersCommon', 'access_orders');
		Utils_RecordBrowserCommon::set_access_callback('premium_inventoryitems', 'Premium_Warehouse_InventoryItems_OrdersCommon', 'access_inventoryitems');
		Utils_RecordBrowserCommon::enable_watchdog('premium_inventoryitems_orders', array('Premium_Warehouse_InventoryItems_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::set_processing_method('premium_inventoryitems_orders', array('Premium_Warehouse_InventoryItems_OrdersCommon', 'submit_order'));
		
// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_inventoryitems_orders', 'Premium/Warehouse/InventoryItems/Orders', 'attachment_addon', 'Notes');

// ************ other ************** //
		Utils_CommonDataCommon::new_array('Premium_InventoryItems_Orders',array(0=>'Input',1=>'Output',2=>'Difference'));
	
		$this->add_aco('browse orders',array('Employee'));
		$this->add_aco('view orders',array('Employee'));
		$this->add_aco('edit orders',array('Employee'));
		$this->add_aco('delete orders',array('Employee Manager'));

		$this->add_aco('view protected notes','Employee');
		$this->add_aco('view public notes','Employee');
		$this->add_aco('edit protected notes','Employee Administrator');
		$this->add_aco('edit public notes','Employee');
		
		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::set_access_callback('premium_inventoryitems', 'Premium_Warehouse_InventoryItemsCommon', 'access_inventoryitems');

		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_inventoryitems_orders', 'Premium/Warehouse/InventoryItems', 'premium_inventoryitems_attachment_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_inventoryitems_orders');
		return true;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Premium/Warehouse/InventoryItems','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Inventory Items Orders - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'SPL');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_inventoryitems_orders');		
	}
}

?>
