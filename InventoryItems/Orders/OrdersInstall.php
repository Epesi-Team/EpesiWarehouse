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
			array('name'=>'Order ID', 		'type'=>'calculated', 'required'=>false, 'param'=>'VARCHAR(16)', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_InventoryItems_OrdersCommon','display_order_id')),
//	TODO:	array('name'=>'Warehouse', 		'type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Ref No', 		'type'=>'text', 'param'=>'64', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Employee', 		'type'=>'crm_contact', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_InventoryItemsCommon','employee_crits')), 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Transaction Date','type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Transaction Type','type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'__COMMON__::Premium_InventoryItems_Orders_Trans_Types'),
// TODO: separate tab?
// TODO: company and contact as chained select? Perhaps just to fill other fields?
			array('name'=>'Company Name', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Last Name', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'First Name', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Address 1', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Address 2', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'City',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Country',		'type'=>'commondata', 'required'=>true, 'param'=>array('Countries'), 'extra'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name'=>'Zone',			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Country'), 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name'=>'Postal Code',	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
// TODO: which one to pick?			
			array('name'=>'Phone',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),

// Payment/Shipment type - eCommerce only?
			array('name'=>'Payment Type', 	'type'=>'select', 'param'=>'__COMMON__::Premium_InventoryItems_Orders_Payment_Types', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Payment No', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment Type', 	'type'=>'select', 'param'=>'__COMMON__::Premium_InventoryItems_Orders_Shipment_Types', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment No',	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
// Terms?			
//			array('name'=>'Terms',			'type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>true),			
			array('name'=>'Paid',			'type'=>'checkbox', 'extra'=>false, 'visible'=>true),
			array('name'=>'Delivered',		'type'=>'checkbox', 'extra'=>false, 'visible'=>true),

			array('name'=>'Memo',			'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_inventoryitems_orders', $fields);

//		Utils_RecordBrowserCommon::set_quickjump('premium_inventoryitems_orders', 'Item');
		Utils_RecordBrowserCommon::set_favorites('premium_inventoryitems_orders', true);
		Utils_RecordBrowserCommon::set_recent('premium_inventoryitems_orders', 15);
		Utils_RecordBrowserCommon::set_caption('premium_inventoryitems_orders', 'Inventory Items Orders');
//		Utils_RecordBrowserCommon::set_icon('premium_inventoryitems_orders', Base_ThemeCommon::get_template_filename('Premium/Warehouse/InventoryItems/Orders', 'icon.png'));
//		Utils_RecordBrowserCommon::set_access_callback('premium_inventoryitems_orders', 'Premium_Warehouse_InventoryItems_OrdersCommon', 'access_orders');
		Utils_RecordBrowserCommon::enable_watchdog('premium_inventoryitems_orders', array('Premium_Warehouse_InventoryItems_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::set_processing_method('premium_inventoryitems_orders', array('Premium_Warehouse_InventoryItems_OrdersCommon', 'submit_order'));
			
		$fields = array(
// TODO: Advanced?
			array('name'=>'Order ID', 	'type'=>'select', 'required'=>true, 'param'=>'premium_inventoryitems_orders::Order ID;Premium_Warehouse_InventoryItems_OrdersCommon::orders_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_InventoryItems_OrdersCommon', 'display_order_id_in_details')),
			array('name'=>'Item SKU', 	'type'=>'select', 'required'=>true, 'param'=>'premium_inventoryitems::Item Name;Premium_Warehouse_InventoryItems_OrdersCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_InventoryItems_OrdersCommon', 'display_item_name')),
			array('name'=>'Quantity', 	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Price', 		'type'=>'currency', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Total', 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Description','type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_inventoryitems_orders_details', $fields);
		
//		Utils_RecordBrowserCommon::set_quickjump('premium_inventoryitems_orders_details', 'Item SKU');
		Utils_RecordBrowserCommon::set_favorites('premium_inventoryitems_orders_details', false);
		Utils_RecordBrowserCommon::set_recent('premium_inventoryitems_orders_details', 15);
		Utils_RecordBrowserCommon::set_caption('premium_inventoryitems_orders_details', 'Inventory Items Order Details');
		Utils_RecordBrowserCommon::set_icon('premium_inventoryitems_orders_details', Base_ThemeCommon::get_template_filename('Premium/Warehouse/InventoryItems/Orders', 'details_icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_inventoryitems_orders_details', 'Premium_Warehouse_InventoryItems_OrdersCommon', 'access_orders');
//		Utils_RecordBrowserCommon::enable_watchdog('premium_inventoryitems_orders_details', array('Premium_Warehouse_InventoryItems_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::set_processing_method('premium_inventoryitems_orders_details', array('Premium_Warehouse_InventoryItems_OrdersCommon', 'submit_order_details'));

// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_inventoryitems_orders', 'Premium/Warehouse/InventoryItems/Orders', 'order_details_addon', 'Items');
		Utils_RecordBrowserCommon::new_addon('premium_inventoryitems_orders', 'Premium/Warehouse/InventoryItems/Orders', 'attachment_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('premium_inventoryitems', 'Premium/Warehouse/InventoryItems/Orders', 'transaction_history_addon', 'Transaction History');

// ************ other ************** //
		Utils_RecordBrowserCommon::set_access_callback('premium_inventoryitems', 'Premium_Warehouse_InventoryItems_OrdersCommon', 'access_inventoryitems');

		Utils_CommonDataCommon::new_array('Premium_InventoryItems_Orders_Trans_Types',array(0=>'Purchase',1=>'Sale',2=>'Inventory Adjustment'));
		Utils_CommonDataCommon::new_array('Premium_InventoryItems_Orders_Payment_Types',array(0=>'Cash',1=>'Check'));
		Utils_CommonDataCommon::new_array('Premium_InventoryItems_Orders_Shipment_Types',array(0=>'Acceptance',1=>'Mail delivery'));
	
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
		Utils_RecordBrowserCommon::uninstall_recordset('premium_inventoryitems_orders_details');
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
