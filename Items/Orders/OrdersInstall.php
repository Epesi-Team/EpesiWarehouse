<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Items Orders
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-orders
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_OrdersInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'Transaction ID', 'type'=>'calculated', 'required'=>false, 'param'=>Utils_RecordBrowserCommon::actual_db_type('text',16), 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_transaction_id')),
			array('name'=>'Transaction Type','type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Trans_Types'), 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_transaction_type_order')),
			array('name'=>'Warehouse', 		'type'=>'select', 'required'=>false, 'extra'=>false, 'filter'=>true, 'visible'=>true, 'param'=>'premium_warehouse::Warehouse;::', 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_warehouse')),
			array('name'=>'Target Warehouse','type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>false, 'param'=>'premium_warehouse::Warehouse;::', 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_warehouse')),
			array('name'=>'Ref No', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Employee', 		'type'=>'crm_contact', 'filter'=>true, 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','employee_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Transaction Date','type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Return Date',	'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Expiration Date','type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Payment', 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Payment Type', 	'type'=>'commondata', 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Payment_Types'), 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Payment No', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment Type', 	'type'=>'commondata', 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Shipment_Types'), 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment No',	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_shipment_no')),
			array('name'=>'Shipment Date',	'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment Employee','type'=>'crm_contact', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','employee_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment ETA',	'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Shipment Cost',	'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Handling Cost',	'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Weight', 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_weight')),
			array('name'=>'Volume',	 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_volume')),
			array('name'=>'Terms',			'type'=>'commondata', 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Terms'), 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Total Value',	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_total_value'), 'style'=>'currency'),
			array('name'=>'Tax Value',		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_tax_value'), 'style'=>'currency'),
			array('name'=>'Status',			'type'=>'text', 'extra'=>false, 'param'=>'8', 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_status'),'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_status')),
			array('name'=>'Related',		'type'=>'select', 'required'=>false, 'param'=>'premium_warehouse_items_orders::Transaction ID;Premium_Warehouse_Items_OrdersCommon::related_transactions_crits', 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_related_transaction')),

			array('name'=>'Memo',			'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false),

			array('name'=>'Contact Details','type'=>'page_split', 'required'=>true),

			array('name'=>'Receipt',	 	'type'=>'checkbox', 'param'=>'', 'required'=>false, 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_receipt')),
			array('name'=>'Company', 		'type'=>'crm_company', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_Items_OrdersCommon','company_crits')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Contact', 		'type'=>'crm_contact', 'param'=>array('field_type'=>'select', 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Company Name', 	'type'=>'text', 'param'=>'128', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_company_name'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon','QFfield_company_name')),
			array('name'=>'Last Name', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_first_name')),
			array('name'=>'First Name', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_last_name')),
			array('name'=>'Address 1', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'Address 2', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'City',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name'=>'Country',		'type'=>'commondata', 'required'=>false, 'param'=>array('Countries'), 'extra'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name'=>'Zone',			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Country'), 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name'=>'Postal Code',	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Phone',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Tax ID',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Notes',			'type'=>'calculated',	'visible'=>true,'extra'=>false,	'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_notes')),

			array('name'=>'Shipping Address','type'=>'page_split', 'required'=>true),
			array('name'=>'Shipping Company', 		'type'=>'crm_company', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_Items_OrdersCommon','company_crits')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Shipping Contact', 		'type'=>'crm_contact', 'param'=>array('field_type'=>'select', 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Shipping Company Name', 	'type'=>'text', 'param'=>'128', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_company_name'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon','QFfield_company_name')),
			array('name'=>'Shipping Last Name', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_first_name')),
			array('name'=>'Shipping First Name', 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_last_name')),
			array('name'=>'Shipping Address 1', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name'=>'Shipping Address 2', 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name'=>'Shipping City',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name'=>'Shipping Country',		'type'=>'commondata', 'required'=>false, 'param'=>array('Countries'), 'extra'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name'=>'Shipping Zone',			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Country'), 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name'=>'Shipping Postal Code',	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name'=>'Shipping Phone',	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items_orders', $fields);

//		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items_orders', 'Item');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items_orders', true);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items_orders', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items_orders', 'Items Transactions');
//		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_orders', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items/Orders', 'icon.png'));
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order'));
			
		$fields = array(
			array('name'=>'Transaction ID', 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items_orders::Transaction ID;Premium_Warehouse_Items_OrdersCommon::transactions_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_id_in_details')),

			array('name'=>'Transaction Type', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_type')),
			array('name'=>'Transaction Status', 'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_status')),
			array('name'=>'Transaction Date', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_date')),
			array('name'=>'Warehouse', 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_warehouse')),

			array('name'=>'Item Name', 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::SKU|Item Name;Premium_Warehouse_Items_OrdersCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_item_name'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_item_name')),

			array('name'=>'Description', 		'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false, 'visible'=>true),
			array('name'=>'Debit',				'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_debit'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_debit')),
			array('name'=>'Credit',				'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_credit'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_credit')),
			array('name'=>'Quantity',			'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_quantity')),

			array('name'=>'Return Date', 		'type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_return_date')),
			array('name'=>'Returned', 			'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false),

			array('name'=>'Net Price', 			'type'=>'currency', 'required'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_net_price')),
			array('name'=>'Gross Price', 		'type'=>'currency', 'required'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_gross_price'), 'display_callback'=>array($this->get_type().'Common', 'display_gross_price')),
			array('name'=>'Tax Rate', 			'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'data_tax_rates::Name', 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_details_tax_rate')),
			array('name'=>'Net Total', 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_total'), 'style'=>'currency'),
			array('name'=>'Tax Value', 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_tax_value'), 'style'=>'currency'),
			array('name'=>'Gross Total', 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_gross_price'), 'style'=>'currency'),
			array('name'=>'Serials',			'type'=>'page_split', 'required'=>false),
			array('name'=>'Serial', 			'type'=>'calculated', 'required'=>false, 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_serials'))
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items_orders_details', $fields);
		
//		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items_orders_details', 'Item SKU');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items_orders_details', false);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items_orders_details', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items_orders_details', 'Items Order Details');
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_orders_details', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items/Orders', 'details_icon.png'));
//		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order_details'));
		
		DB::Execute('UPDATE premium_warehouse_items_orders_details_field SET param=%s WHERE field=%s', array('premium_warehouse_items_orders::Transaction Date/Transaction ID', 'Transaction Date'));

		DB::CreateIndex('premium_warehouse_items_ord_det__it_name__idx','premium_warehouse_items_orders_details_data_1',array('f_item_name'));

// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_details_addon', 'Items');
		Utils_AttachmentCommon::new_addon('premium_warehouse_items_orders');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon', 'Transaction History');
		Utils_RecordBrowserCommon::new_addon('contact', 'Premium/Warehouse/Items/Orders', 'contact_orders_addon', 'Premium_Warehouse_Items_OrdersCommon::contact_orders_label');
		Utils_RecordBrowserCommon::new_addon('company', 'Premium/Warehouse/Items/Orders', 'company_orders_addon', 'Premium_Warehouse_Items_OrdersCommon::company_orders_label');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_serial_addon', 'Serial Numbers');
		
		Utils_RecordBrowserCommon::set_addon_pos('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon', 2);
		Utils_RecordBrowserCommon::set_addon_pos('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_addon', 1);

// ************ other ************** //
		Utils_RecordBrowserCommon::field_deny_access('premium_warehouse_items', 'Quantity on Hand', 'edit');
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items', array('Premium_Warehouse_Items_OrdersCommon', 'submit_new_item_from_order'));

		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Trans_Types',array(0=>'Purchase',1=>'Sale',2=>'Inventory Adjustment',3=>'Rental',4=>'Transfer'));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Payment_Types',array(0=>'Cash',1=>'Check'));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Shipment_Types',array(0=>'Pickup',1=>'USPS',2=>'UPS',3=>'DHL',4=>'FedEx',5=>'Courier',6=>'Other'));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Terms',array(0=>'Due on Receipt',15=>'Net 15',30=>'Net 30'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', 'Quantity En Route', 'calculated', true, false, '', 'integer', false, false, 10);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Quantity En Route', array('Premium_Warehouse_Items_OrdersCommon', 'display_quantity_on_route'));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', 'Last Sale Price', 'currency', false, false, '', 'currency', false, false);
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', 'Last Purchase Price', 'currency', false, false, '', 'currency', false, false);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items','Last Sale Price',array('Premium_Warehouse_Items_OrdersCommon', 'display_last_price'));
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items','Last Purchase Price',array('Premium_Warehouse_Items_OrdersCommon', 'display_last_price'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items','Reserved Qty','calculated', true, false, '', 'integer', false, false, 11);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items','Reserved Qty',array('Premium_Warehouse_Items_OrdersCommon', 'display_reserved_qty'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items','Available Qty','calculated', true, false, '', 'integer', false, false, 11);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items','Available Qty',array('Premium_Warehouse_Items_OrdersCommon', 'display_available_qty'));

		DB::CreateTable('premium_warehouse_location_orders_serial',
					'serial_id I,'.
					'order_details_id I',
					array('constraints'=>''));
					
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'view', 'EMPLOYEE');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'view', 'ALL', array('contact'=>'USER'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'view', array('ALL','ACCESS:manager'), array('company'=>'USER_COMPANY'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'add', 'EMPLOYEE', array(), array('transaction_type'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'EMPLOYEE', array('employee'=>'USER', '(>=transaction_date'=>'-7 days', '|<status'=>20), array('transaction_type', 'warehouse'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'EMPLOYEE', array('employee'=>'USER', 'warehouse'=>''), array('transaction_type'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', array('EMPLOYEE','ACCESS:manager'), array(), array('transaction_type', 'warehouse'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'delete', 'EMPLOYEE', array(':Created_by'=>'USER_ID'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'delete', array('EMPLOYEE','ACCESS:manager'));

		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'view', 'EMPLOYEE');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'view', 'ALL', array('transaction_id[contact]'=>'USER'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'view', array('ALL','ACCESS:manager'), array('transaction_id[company]'=>'USER_COMPANY'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'add', 'EMPLOYEE', array('transaction_id[employee]'=>'USER', '(>=transaction_id[transaction_date]'=>'-7 days', '|<transaction_id[status]'=>20), array('transaction_id'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'add', array('EMPLOYEE','ACCESS:manager'), array(), array('transaction_id'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'edit', 'EMPLOYEE', array('transaction_id[employee]'=>'USER', '(>=transaction_id[transaction_date]'=>'-7 days', '|<transaction_id[status]'=>20), array('transaction_id'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'edit', array('EMPLOYEE','ACCESS:manager'), array(), array('transaction_id'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'delete', 'EMPLOYEE', array(':Created_by'=>'USER_ID'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'delete', array('EMPLOYEE','ACCESS:manager'));

		$this->add_aco('sell with loss',array('Employee Manager'));

		return true;
	}
	
	public function uninstall() {
		DB::DropTable('premium_warehouse_location_orders_serial');
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items', array('Premium_Warehouse_Items_OrdersCommon', 'submit_new_item_from_order'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order_details'));
		
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items','Reserved Qty');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items','Available Qty');

		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_details_addon');
		Utils_AttachmentCommon::delete_addon('premium_warehouse_items_orders');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items_orders');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items_orders_details');

		Utils_CommonDataCommon::remove('Premium_Items_Orders_Trans_Types');
		Utils_CommonDataCommon::remove('Premium_Items_Orders_Payment_Types');
		Utils_CommonDataCommon::remove('Premium_Items_Orders_Shipment_Types');
		Utils_CommonDataCommon::remove('Premium_Items_Orders_Terms');
		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Location','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0),
			array('name'=>'Utils/LeightboxPrompt', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Items Orders - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
		return true;
	}
}

?>
