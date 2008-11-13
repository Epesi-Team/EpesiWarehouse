<?php
/**
 * Warehouse - Items Orders
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.9
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_OrdersInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'Transaction ID', 'type'=>'calculated', 'required'=>false, 'param'=>'VARCHAR(16)', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_transaction_id')),
			array('name'=>'Transaction Type','type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'__COMMON__::Premium_Items_Orders_Trans_Types'),
			array('name'=>'Warehouse', 		'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'premium_warehouse::Warehouse;::', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_warehouse')),
			array('name'=>'Ref No', 		'type'=>'text', 'param'=>'64', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Employee', 		'type'=>'crm_contact', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','employee_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Transaction Date','type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Return Date',	'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Payment', 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Payment Type', 	'type'=>'select', 'param'=>'__COMMON__::Premium_Items_Orders_Payment_Types', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Payment No', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment Type', 	'type'=>'select', 'param'=>'__COMMON__::Premium_Items_Orders_Shipment_Types', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment No',	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Terms',			'type'=>'select', 'param'=>'__COMMON__::Premium_Items_Orders_Terms', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Total Value',	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_total_value'), 'style'=>'currency'),
			array('name'=>'Tax Value',		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_tax_value'), 'style'=>'currency'),
			array('name'=>'Paid',			'type'=>'checkbox', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_paid')),
			array('name'=>'Delivered',		'type'=>'checkbox', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_delivered')),

			array('name'=>'Memo',			'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false),

			array('name'=>'Contact Details','type'=>'page_split', 'required'=>true),

			array('name'=>'Company', 		'type'=>'crm_company', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_Items_OrdersCommon','company_crits')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Contact', 		'type'=>'crm_contact', 'param'=>array('field_type'=>'select','crits'=>array('ChainedSelect','company'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Company Name', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_company_name')),
			array('name'=>'Last Name', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_first_name')),
			array('name'=>'First Name', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_last_name')),
			array('name'=>'Address 1', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'Address 2', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'City',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'Country',		'type'=>'commondata', 'required'=>true, 'param'=>array('Countries'), 'extra'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name'=>'Zone',			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Country'), 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name'=>'Postal Code',	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Phone',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items_orders', $fields);

//		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items_orders', 'Item');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items_orders', true);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items_orders', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items_orders', 'Items Transactions');
//		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_orders', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items/Orders', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items_orders', 'Premium_Warehouse_Items_OrdersCommon', 'access_orders');
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::set_processing_method('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order'));
			
		$fields = array(
			array('name'=>'Transaction ID', 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items_orders::Transaction ID;Premium_Warehouse_Items_OrdersCommon::transactions_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_id_in_details')),

			array('name'=>'Transaction Type', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_type')),
			array('name'=>'Transaction Date', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_date')),
			array('name'=>'Warehouse', 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_warehouse')),

			array('name'=>'Item SKU', 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::SKU|Item Name;Premium_Warehouse_Items_OrdersCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_item_sku'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_item_name')),

			array('name'=>'Item Name', 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true),
			array('name'=>'Debit',				'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_debit')),
			array('name'=>'Credit',				'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_credit')),
			array('name'=>'Serial',				'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_serial'), 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_serial')),
			array('name'=>'Quantity',			'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_quantity')),

			array('name'=>'Return Date', 		'type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_return_date')),
			array('name'=>'Returned', 			'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false),

			array('name'=>'Net Price', 			'type'=>'currency', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Net Total', 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_total'), 'style'=>'currency'),
			array('name'=>'Tax Rate', 			'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'__COMMON__::Premium_Warehouse_Items_Tax'),
			array('name'=>'Tax Value', 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_tax_value'), 'style'=>'currency'),
			array('name'=>'Gross Total', 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_gross_price'), 'style'=>'currency'),
			array('name'=>'Quantity On Hand',	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_qty'), 'style'=>'integer')
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items_orders_details', $fields);
		
//		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items_orders_details', 'Item SKU');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items_orders_details', false);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items_orders_details', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items_orders_details', 'Items Order Details');
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_orders_details', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items/Orders', 'details_icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items_orders_details', 'Premium_Warehouse_Items_OrdersCommon', 'access_order_details');
//		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::set_processing_method('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order_details'));

// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_details_addon', 'Items');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'attachment_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon', 'Transaction History');
		
		Utils_RecordBrowserCommon::set_addon_pos('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon', 2);
		Utils_RecordBrowserCommon::set_addon_pos('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_addon', 1);

// ************ other ************** //
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items', 'Premium_Warehouse_Items_OrdersCommon', 'access_items');

		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Trans_Types',array(0=>'Purchase',1=>'Sale',2=>'Inventory Adjustment',3=>'Rental'));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Payment_Types',array(0=>'Cash',1=>'Check'));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Shipment_Types',array(0=>'Acceptance',1=>'Mail delivery'));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Terms',array(0=>'Due on Receipt',15=>'Net 15',30=>'Net 30'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', 'Quantity on Route', 'calculated', true, false, '', 'integer', false, false, 10);
		Utils_RecordBrowserCommon::set_display_method('premium_warehouse_items', 'Quantity on Route', 'Premium_Warehouse_Items_OrdersCommon', 'display_quantity_on_route');
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', 'Last Sale Price', 'currency', false, false, '', 'currency', false, false);
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', 'Last Purchase Price', 'currency', false, false, '', 'currency', false, false);
	
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
		Utils_RecordBrowserCommon::set_access_callback('premium_warehouse_items', 'Premium_Warehouse_ItemsCommon', 'access_items');

		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_details_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'attachment_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items_orders');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items_orders_details');
		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Location','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Items Orders - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'SPL');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return array_merge(
				Utils_RecordBrowserCommon::get_tables('premium_warehouse_items_orders'),		
				Utils_RecordBrowserCommon::get_tables('premium_warehouse_items_orders_details')
			);
	}
}

?>
