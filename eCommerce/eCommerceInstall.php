<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * eCommerce
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerceInstall extends ModuleInstall {

	public function install() {
		set_time_limit(0);
//		Base_LangCommon::install_translations($this->get_type());
//		Base_ThemeCommon::install_default_theme($this->get_type());

		$fields = array(
			array('name' => _M('SKU'), 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_eCommerceCommon', 'display_sku')),
			array('name' => _M('Product Name'), 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_product_name')),
			array('name' => _M('Item Name'), 		'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::SKU|Item Name;Premium_Warehouse_eCommerceCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name'), 'filter'=>true),
			array('name' => _M('Publish'), 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true, 'filter'=>true),
			array('name' => _M('Recommended'), 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false, 'filter'=>true),
			array('name' => _M('Exclude compare services'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false, 'filter'=>true),
			array('name' => _M('Always on stock'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false, 'filter'=>true),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Available'),	 	'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>false, 'param'=>'premium_ecommerce_availability::Availability Code'),
			array('name' => _M('Description'), 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_description')),
			array('name' => _M('Related products'), 	'type'=>'multiselect', 'param'=>'premium_ecommerce_products::Item Name;Premium_Warehouse_eCommerceCommon::related_products_crits;Premium_Warehouse_eCommerceCommon::adv_related_products_params', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array($this->get_type().'Common', 'display_related_product_name'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_related_products')),
			array('name' => _M('Popup products'), 	'type'=>'multiselect', 'param'=>'premium_ecommerce_products::Item Name;Premium_Warehouse_eCommerceCommon::related_products_crits;Premium_Warehouse_eCommerceCommon::adv_related_products_params', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array($this->get_type().'Common', 'display_related_product_name'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_related_products'))
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_products', $fields);
		
		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_products', true);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_products', _M('eCommerce - Products'));
		Utils_RecordBrowserCommon::set_icon('premium_ecommerce_products', Base_ThemeCommon::get_template_filename('Premium/Warehouse/eCommerce', 'icon.png'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_products', array('Premium_Warehouse_eCommerceCommon', 'submit_products_position'));

		$fields = array(
			array('name' => _M('Category'), 		'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items_categories::Category Name', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Language'), 		'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_description_language')),
			array('name' => _M('Display Name'),	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Short Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Long Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Page Title'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>64, 'visible'=>false),
			array('name' => _M('Meta Description'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>256, 'visible'=>false),
			array('name' => _M('Keywords'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>128, 'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_cat_descriptions', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_cat_descriptions', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_cat_descriptions', _M('eCommerce - Cat. Descriptions'));

		$fields = array(
			array('name' => _M('Item Name'), 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name' => _M('Language'), 		'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_description_language')),
			array('name' => _M('Display Name'),	'type'=>'text', 'param'=>128, 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Short Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Long Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Page Title'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>64, 'visible'=>false),
			array('name' => _M('Meta Description'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>256, 'visible'=>false),
			array('name' => _M('Keywords'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>128, 'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_descriptions', $fields);
		DB::CreateIndex('ecommerce_desc_name_idx','premium_ecommerce_descriptions_data_1',array('f_display_name','f_language','active'));

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_descriptions', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_descriptions', _M('eCommerce - Descriptions'));

		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Payment_Types',array('DotPay'=>_M('DotPay'),'Przelewy24'=>_M('Przelewy24'),'PayPal'=>_M('PayPal'), 'Platnosci.pl'=>_M('Platnosci.pl'), 'Zagiel'=>_M('Żagiel'),'CreditCardBasic'=>_M('Credit Card (basic)')));
		//payments and carriers
		$fields = array(
			array('name' => _M('Payment'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Payment_Types')),
			array('name' => _M('Shipment'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Shipment_Types')),
			array('name' => _M('Shipment Service Type'), 	'type'=>'commondata', 'required'=>false, 'extra'=>false, 'visible'=>true, 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Shipment_Types','Shipment'), 'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon','QFfield_shipment_service_type')),
			array('name' => _M('Currency'), 	'type'=>'float', 'required'=>true, 'extra'=>false,'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_currency'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_currency')),
			array('name' => _M('Price'), 		'type'=>'integer', 'required'=>true, 'extra'=>false,'visible'=>true),
			array('name' => _M('Percentage Of Amount'), 		'type'=>'float', 'required'=>true, 'extra'=>false,'visible'=>true),
			array('name' => _M('Max Weight'),	'type'=>'float', 'required'=>false, 'extra'=>false,'visible'=>true, 'display_callback'=>array('Premium_Warehouse_ItemsCommon', 'display_weight')),
			array('name' => _M('Order Terms'),		'type'=>'commondata', 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Terms'), 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false,'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_payments_carriers', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_payments_carriers', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_payments_carriers', _M('eCommerce - Payments and Carriers'));

		//product prices
		$fields = array(
			array('name' => _M('Item Name'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name' => _M('Currency'), 	'type'=>'integer', 'required'=>true, 'extra'=>false,'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_currency'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_currency')),
			array('name' => _M('Gross Price'),'type'=>'float', 'required'=>true, 'extra'=>false,'visible'=>true),
			array('name' => _M('Tax Rate'), 	'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'data_tax_rates::Name', 'style'=>'integer')
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_prices', $fields);
		DB::CreateIndex('ecommerce_prices_name_currency__idx','premium_ecommerce_prices_data_1',array('f_item_name','f_currency','active'));

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_prices', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_prices', _M('eCommerce - prices'));

		//product parameters
		$fields = array(
			array('name' => _M('Parameter Code'), 'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Label'), 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_eCommerceCommon','display_parameter_label')),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false,'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameters', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameters', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameters', _M('eCommerce - Parameters'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_parameters', array('Premium_Warehouse_eCommerceCommon', 'submit_parameters_position'));

		$fields = array(
			array('name' => _M('Parameter'), 	'type'=>'select', 'param'=>'premium_ecommerce_parameters::Parameter Code', 'required'=>true, 'extra'=>false),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages')),
			array('name' => _M('Label'), 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameter_labels', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameter_labels', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameter_labels', _M('eCommerce - Parameters'));
		//Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_parameter_labels', array('Premium_Warehouse_eCommerceCommon', 'submit_parameter_labels'));

		//parameter groups
		$fields = array(
			array('name' => _M('Group Code'), 'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Label'), 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_eCommerceCommon','display_parameter_group_label')),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false,'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameter_groups', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameter_groups', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameter_groups', _M('eCommerce - Parameter Groups'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_parameter_groups', array('Premium_Warehouse_eCommerceCommon', 'submit_parameter_groups_position'));

		$fields = array(
			array('name' => _M('Group'), 		'type'=>'select', 'param'=>'premium_ecommerce_parameter_groups::Group Code', 'required'=>true, 'extra'=>false),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages')),
			array('name' => _M('Label'), 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_param_group_labels', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_param_group_labels', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_param_group_labels', _M('eCommerce - Parameters'));
		//Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_param_group_labels', array('Premium_Warehouse_eCommerceCommon', 'submit_parameter_labels'));
		
		//product-group-parameter-value
		$fields = array(
			array('name' => _M('Item Name'), 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name' => _M('Language'), 		'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('Premium/Warehouse/eCommerce/Languages')),
			array('name' => _M('Parameter'), 		'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'premium_ecommerce_parameters::Parameter Code'),
			array('name' => _M('Group'), 		'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'premium_ecommerce_parameter_groups::Group Code'),
			array('name' => _M('Value'), 			'type'=>'text', 'param'=>256, 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_products_parameters', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_products_parameters', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_products_parameters', _M('eCommerce - Products Paramteres'));

		//product availability
		$fields = array(
			array('name' => _M('Availability Code'), 'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_availability', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_availability', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_availability', _M('eCommerce - Product Available'));

		$fields = array(
			array('name' => _M('Availability'), 	'type'=>'select', 'param'=>'premium_ecommerce_availability::Availability Code', 'required'=>true, 'extra'=>false),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Label'), 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_availability_labels', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_availability_labels', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_availability_labels', _M('eCommerce - Product Available'));

		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_availability', 'Premium/Warehouse/eCommerce', 'availability_labels_addon', _M('Labels'));

		//pages
		$fields = array(
			array('name' => _M('Page Name'), 		'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Parent Page'),	'type'=>'select', 'param'=>'premium_ecommerce_pages::Page Name;Premium_Warehouse_eCommerceCommon::parent_page_crits' ,'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Type'),			'type'=>'integer',	'required'=>true, 'extra'=>false, 'visible'=>true,'display_callback'=>array($this->get_type().'Common', 'display_page_type'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_page_type')),
			array('name' => _M('Show subpages as'),	'type'=>'integer',	'required'=>true, 'extra'=>false, 'visible'=>true,'display_callback'=>array($this->get_type().'Common', 'display_subpages'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_subpages')),
			array('name' => _M('Publish'), 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false,'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_pages', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_pages', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_pages', _M('eCommerce - Pages'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'subpages_addon', _M('Subpages'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_pages', array('Premium_Warehouse_eCommerceCommon', 'submit_pages_position'));

		$fields = array(
			array('name' => _M('Page'), 	'type'=>'select', 'param'=>'premium_ecommerce_pages::Page Name', 'required'=>true, 'extra'=>false),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Name'), 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Short Description'), 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Long Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Page Title'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>64, 'visible'=>false),
			array('name' => _M('Meta Description'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>256, 'visible'=>false),
			array('name' => _M('Keywords'), 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>128, 'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_pages_data', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_pages_data', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_pages_data', _M('eCommerce - Pages'));
		
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'pages_info_addon', _M('Info'));

		//orders
		$ret = DB::CreateTable('premium_ecommerce_orders_temp','
			customer C(32) NOTNULL,
			product I4 NOTNULL,
			quantity I2 NOTNULL,
			price C(128) NOTNULL,
			tax C(64) NOTNULL,
			name C(128) NOTNULL,
			created_on T DEFTIMESTAMP,
			weight F',
			array('constraints'=>' ,PRIMARY KEY(customer,product)'));
		if(!$ret){
			print('Unable to create table premium_ecommerce_orders_temp.<br>');
			return false;
		}
		
		$fields = array(
			array('name' => _M('Transaction ID'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items_orders::Transaction ID;Premium_Warehouse_Items_OrdersCommon::transactions_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_id_in_details')),
			array('name' => _M('Language'), 		'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Email'), 			'type'=>'email', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('IP'), 			'type'=>'text', 'required'=>false, 'param'=>'32', 'extra'=>false, 'visible'=>false),
			array('name' => _M('Comment'),		'type'=>'long text', 'required'=>false, 'extra'=>false),
			array('name' => _M('Invoice'), 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Payment Channel'),	'type'=>'text', 'param'=>4,	'required'=>false, 'extra'=>false, 'visible'=>true,'display_callback'=>array($this->get_type().'Common', 'display_payment_channel'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_payment_channel')),
			array('name' => _M('Payment Realized'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true,'display_callback'=>array($this->get_type().'Common', 'display_payment_realized'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_payment_realized')),
			array('name' => _M('Promotion Employee'), 	'type'=>'crm_contact', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','employee_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Promotion Shipment Discount'), 	'type'=>'integer', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array($this->get_type().'Common', 'display_promotion_shipment_discount'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_promotion_shipment_discount'))
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_orders', $fields);

		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_orders', _M('eCommerce - Orders'));
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/eCommerce', 'orders_addon', 'Premium_Warehouse_eCommerceCommon::orders_addon_parameters');
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_orders', array('Premium_Warehouse_eCommerceCommon', 'submit_ecommerce_order'));
		

		//quickcarts
		$ret = DB::CreateTable('premium_ecommerce_quickcart','
			path C(255) KEY NOTNULL',
			array('constraints'=>''));
		if(!$ret){
			print('Unable to create table premium_ecommerce_quickcart.<br>');
			return false;
		}
		
		//********************************** quickcart pro compatibility *******************************
		//stats
		$ret = DB::CreateTable('premium_ecommerce_products_stats','
			obj I4 NOTNULL,
			visited_on T NOTNULL',
			array('constraints'=>' , FOREIGN KEY (obj) REFERENCES premium_warehouse_items_data_1(id)'));
		if(!$ret){
			print('Unable to create table premium_ecommerce_products_stats.<br>');
			return false;
		}
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'products_stats_addon', _M('Visits'));
		$ret = DB::CreateTable('premium_ecommerce_pages_stats','
			obj I4 NOTNULL,
			visited_on T NOTNULL',
			array('constraints'=>' , FOREIGN KEY (obj) REFERENCES premium_ecommerce_pages_data_1(id)'));
		if(!$ret){
			print('Unable to create table premium_ecommerce_pages_stats.<br>');
			return false;
		}
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'pages_stats_addon', _M('Visits'));
		$ret = DB::CreateTable('premium_ecommerce_categories_stats','
			obj I4 NOTNULL,
			visited_on T NOTNULL',
			array('constraints'=>' , FOREIGN KEY (obj) REFERENCES premium_warehouse_items_categories_data_1(id)'));
		if(!$ret){
			print('Unable to create table premium_ecommerce_categories_stats.<br>');
			return false;
		}
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_categories', 'Premium/Warehouse/eCommerce', 'categories_stats_addon', _M('Visits'));
		$ret = DB::CreateTable('premium_ecommerce_searched_stats','
			obj X NOTNULL,
			visited_on T NOTNULL',
			array('constraints'=>''));
		if(!$ret){
			print('Unable to create table premium_ecommerce_searched_stats.<br>');
			return false;
		}

		//polls
		$fields = array(
			array('name' => _M('Question'), 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Publish'), 	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false,'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_polls', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_polls', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_polls', _M('eCommerce - Polls'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_polls', array('Premium_Warehouse_eCommerceCommon', 'submit_polls_position'));
		
		$fields = array(
			array('name' => _M('Poll'), 	'type'=>'select', 'param'=>'premium_ecommerce_polls::Question', 'required'=>true, 'extra'=>false),
			array('name' => _M('Answer'), 		'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Votes'), 		'type'=>'integer', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_poll_votes'))
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_poll_answers', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_poll_answers', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_poll_answers', _M('eCommerce - Poll Answers'));
		
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_polls', 'Premium/Warehouse/eCommerce', 'poll_answers_addon', _M('Answers'));
		
		//boxes
		$fields = array(
			array('name' => _M('Name'), 		'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Content'), 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Publish'), 	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false,'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_boxes', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_boxes', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_boxes', _M('eCommerce - Boxes'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_boxes', array('Premium_Warehouse_eCommerceCommon', 'submit_boxes_position'));

        //emails
		$fields = array(
			array('name' => _M('Subject'),	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Content'), 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>false, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Send On Status'), 		'type'=>'integer', 'required'=>true, 'extra'=>false,'visible'=>false, 'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon', 'QFfield_order_status'), 'display_callback'=>array('Premium_Warehouse_eCommerceCommon', 'display_order_status'))
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_emails', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_emails', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_emails', _M('eCommerce - e-mails'));

		//promotion codes
		$fields = array(
			array('name' => _M('Employee'), 	'type'=>'crm_contact', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','employee_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Expiration'), 	'type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Promotion Code'), 	'type'=>'text', 'param'=>64, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Discount'), 	'type'=>'currency', 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_promotion_codes', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_promotion_codes', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_promotion_codes', _M('eCommerce - Promotion codes'));

		//banners
		$fields = array(
			array('name' => _M('File'), 		'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_banner_file'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_banner_file')),
			array('name' => _M('Link'), 		'type'=>'text', 'param'=>255, 'required'=>true, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon', 'display_webaddress'), 'QFfield_callback'=>array('CRM_ContactsCommon', 'QFfield_webaddress')),
			array('name' => _M('Type'),		'type'=>'integer',	'required'=>true, 'extra'=>false, 'visible'=>true,'display_callback'=>array($this->get_type().'Common', 'display_banner_type'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_banner_type')),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Width'),		'type'=>'integer',	'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Height'),		'type'=>'integer',	'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Color'),		'type'=>'text',	'param'=>8,	'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Views Limit'),		'type'=>'integer',	'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Views'),		'type'=>'integer',	'required'=>false, 'extra'=>false, 'visible'=>true,'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_freeze_int')),
			array('name' => _M('Clicks'),		'type'=>'integer',	'required'=>false, 'extra'=>false, 'visible'=>true,'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_freeze_int')),
			array('name' => _M('Publish'), 	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_banners', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_banners', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_banners', _M('eCommerce - Banners'));

		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_banners', array('Premium_Warehouse_eCommerceCommon','banners_processing'));
		
		//users
		$fields = array(
			array('name' => _M('Contact'), 	'type'=>'crm_contact', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_eCommerceCommon','customer_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_default')), 'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Password'), 	'type'=>'text', 'required'=>true, 'param'=>'32', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_eCommerceCommon', 'display_password'), 'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon', 'QFfield_password'))
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_users', $fields);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_users', _M('eCommerce - Users'));
		Utils_RecordBrowserCommon::new_addon('contact', 'Premium/Warehouse/eCommerce', 'users_addon', 'Premium_Warehouse_eCommerceCommon::users_addon_parameters');
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_users', array('Premium_Warehouse_eCommerceCommon', 'submit_user'));
		
		//newsletter
		$fields = array(
			array('name' => _M('Email'), 			'type'=>'email', 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_newsletter', $fields);

		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_newsletter', _M('eCommerce - Newsletter'));
		
		//comments
		$fields = array(
			array('name' => _M('Item Name'), 	'type'=>'select', 'param'=>'premium_warehouse_items::Item Name', 'required'=>true, 'extra'=>false),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Name'), 		'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Content'), 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Time'), 		'type'=>'timestamp', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('IP'), 		'type'=>'text', 'param'=>32, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Publish'), 	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_product_comments', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_product_comments', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_product_comments', _M('eCommerce - Product Comments'));
		
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'product_comments_addon', _M('Comments'));
		
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders',array('name' => _M('Online order'),	'type'=>'checkbox', 'required'=>false, 'filter'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon','QFfield_online_order')));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_distributor',array('name' => _M('Items Availability'), 'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>false, 'param'=>'premium_ecommerce_availability::Availability Code'));

		$fields = array(
			array('name' => _M('Name'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_Warehouse_eCommerceCommon', 'display_3rdp_name')),
			array('name' => _M('Plugin'), 		'type'=>'select', 'required'=>true, 'extra'=>false, 'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon','QFfield_3rdp_plugin')),
			array('name' => _M('Company'), 		'type'=>'crm_company', 'param'=>array('field_type'=>'select'), 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Param1'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param2'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param3'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param4'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param5'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param6'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_3rdp_info', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_ecommerce_3rdp_info', 'Name');
		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_3rdp_info', true);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_3rdp_info', _M('3rd party info'));
		Utils_RecordBrowserCommon::set_icon('premium_ecommerce_3rdp_info', Base_ThemeCommon::get_template_filename('Premium/Warehouse/eCommerce', 'icon.png'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_3rdp_info', array('Premium_Warehouse_eCommerceCommon', 'submit_3rdp_info'));

		DB::CreateTable('premium_ecommerce_3rdp_plugin',
						'id I4 AUTO KEY,'.
						'name C(64),'.
						'filename C(64),'.
						'active I1',
						array('constraints'=>''));
		

// ************* addons ************ //
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'parameters_addon', 'Premium_Warehouse_eCommerceCommon::parameters_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'descriptions_addon', 'Premium_Warehouse_eCommerceCommon::descriptions_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'prices_addon', 'Premium_Warehouse_eCommerceCommon::prices_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_categories', 'Premium/Warehouse/eCommerce', 'cat_descriptions_addon', _M('Descriptions'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_parameters', 'Premium/Warehouse/eCommerce', 'parameter_labels_addon', _M('Labels'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_parameter_groups', 'Premium/Warehouse/eCommerce', 'parameter_group_labels_addon', _M('Labels'));

		//addon for warehouse items
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/eCommerce', 'warehouse_item_addon', _M('eCommerce'));

// ************ other ************** //
		Utils_CommonDataCommon::new_array('Premium/Warehouse/eCommerce/Languages',array('en'=>_M('English'),'pl'=>_M('Polish'),'it'=>_M('Italian'),'fr'=>_M('French'),'nl'=>_M('Dutch'),'ru'=>_M('Russian')));
		Utils_RecordBrowserCommon::new_record('premium_ecommerce_availability',
				array('availability_code'=>'24h',
					'position'=>0));
		Utils_RecordBrowserCommon::new_record('premium_ecommerce_availability',
				array('availability_code'=>'48h',
					'position'=>0));
		Utils_RecordBrowserCommon::new_record('premium_ecommerce_availability',
				array('availability_code'=>'72h',
					'position'=>0));

		//icecat
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium_Warehouse_eCommerce', 'get_3rd_party_info_addon', 'Premium_Warehouse_eCommerceCommon::get_3rd_party_info_addon_parameters');
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_categories',array('name' => _M('Available languages'),	'type'=>'calculated', 'required'=>false, 'filter'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_eCommerceCommon','display_category_available_languages')));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse',array('name' => _M('Pickup Place'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true));
		
//		Utils_AttachmentCommon::new_addon('premium_ecommerce_descriptions');
//		Utils_AttachmentCommon::new_addon('premium_ecommerce_pages_data');
//		Utils_AttachmentCommon::new_addon('premium_ecommerce_pages_data');
//		Utils_AttachmentCommon::new_addon('premium_ecommerce_products');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_descriptions', 'Premium/Warehouse/eCommerce', 'attachment_product_desc_addon', _M('Notes'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'attachment_page_addon', _M('Notes'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages_data', 'Premium/Warehouse/eCommerce', 'attachment_page_desc_addon', _M('Notes'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'attachment_product_addon', _M('Notes'));

		Variable::set('ecommerce_rules','You can edit this page in Administration - eCommerce settings.');
		Variable::set('ecommerce_contactus','You can edit this page in Administration - eCommerce settings.');
		Variable::set('ecommerce_home', 'Welcome on your new ecommerce site. Have a nice day with Epesi!');

		Variable::set('ecommerce_autoprice',false);
		Variable::set('ecommerce_minimal_profit','');
		Variable::set('ecommerce_percentage_profit','');

		Variable::set('ecommerce_item_prices',true);
		Variable::set('ecommerce_item_descriptions',true);
		Variable::set('ecommerce_item_parameters',true);
		
		Premium_PaymentsCommon::new_addon('premium_warehouse_items_orders');
		
		Utils_RecordBrowserCommon::register_processing_callback('premium_payments', array('Premium_Warehouse_eCommerceCommon', 'submit_payment'));

		$this->create_data_dir();
		@mkdir($this->get_data_dir().'banners');
		Base_ThemeCommon::install_default_theme('Premium/Warehouse/eCommerce');
		recursive_copy('modules/Premium/Warehouse/eCommerce/quickcart33pro/templates.default', 'modules/Premium/Warehouse/eCommerce/quickcart33pro/templates');
		
		$areas = array(
				"EU,07"=>_M('EU, UPS Express'),
				"EU,08"=>_M('EU, UPS Expedited'),
				"EU,11"=>_M('EU, UPS Standard'),
				"EU,54"=>_M('EU, UPS Worldwide Express Plus'),
				"EU,64"=>_M('EU, UPS Express NA1'),
				"EU,65"=>_M('EU, UPS Express Saver'),
				"US,01"=>_M('USA, UPS Next Day Air'),
				"US,02"=>_M('USA, UPS 2nd Day Air'),
				"US,03"=>_M('USA, UPS Ground'),
				"US,07"=>_M('USA, UPS Worldwide Express'),
				"US,08"=>_M('USA, UPS Worldwide Expedited'),
				"US,11"=>_M('USA, UPS Standard'),
				"US,12"=>_M('USA, UPS 3 Day Select'),
				"US,13"=>_M('USA, UPS Next Day Air Saver'),
				"US,14"=>_M('USA, UPS Next Day Early A.M.'),
				"US,54"=>_M('USA, UPS Worldwide Express Plus'),
				"US,59"=>_M('USA, UPS 2nd Day Air A.M.'),
				"US,65"=>_M('USA, UPS Express Saver'),
				"CA,01"=>_M('Canada, UPS Express'),
				"CA,02"=>_M('Canada, UPS Expedited'),
				"CA,07"=>_M('Canada, UPS Worldwide Express'),
				"CA,08"=>_M('Canada, UPS Worldwide Expedited'),
				"CA,11"=>_M('Canada, UPS Standard'),
				"CA,12"=>_M('Canada, UPS 3 Day Select'),
				"CA,13"=>_M('Canada, UPS Express Saver'),
				"CA,14"=>_M('Canada, UPS Express Early A.M.'),
				"CA,54"=>_M('Canada, UPS Worldwide Express Plus')
				);
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Shipment_Types/2',$areas,false,true);

		Utils_RecordBrowserCommon::add_access('premium_ecommerce_products', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_products', 'add', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_products', 'edit', 'ACCESS:employee', array(), array('currency','gross_price'));
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_products', 'delete', 'ACCESS:employee');

		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_emails');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_3rdp_info');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_cat_descriptions');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_descriptions');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_parameters');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_parameter_labels');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_parameter_groups');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_param_group_labels');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_products_parameters');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_availability');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_availability_labels');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_pages');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_pages_data');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_prices');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_payments_carriers');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_polls');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_poll_answers');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_boxes');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_promotion_codes');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_banners');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_product_comments');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_orders');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_users');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_newsletter');

		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'ACCESS:employee', array('online_order'=>1, '<status'=>6), array('transaction_type'));

		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme('Premium/Warehouse/eCommerce');

		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_products', array('Premium_Warehouse_eCommerceCommon', 'submit_products_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_parameters', array('Premium_Warehouse_eCommerceCommon', 'submit_parameters_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_orders', array('Premium_Warehouse_eCommerceCommon', 'submit_ecommerce_order'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_payments', array('Premium_Warehouse_eCommerceCommon', 'submit_payment'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_parameter_groups', array('Premium_Warehouse_eCommerceCommon', 'submit_parameter_groups_position'));
		//Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_param_group_labels', array('Premium_Warehouse_eCommerceCommon', 'submit_parameter_labels'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_pages', array('Premium_Warehouse_eCommerceCommon', 'submit_pages_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_polls', array('Premium_Warehouse_eCommerceCommon', 'submit_polls_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_boxes', array('Premium_Warehouse_eCommerceCommon', 'submit_boxes_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_banners', array('Premium_Warehouse_eCommerceCommon','banners_processing'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_users', array('Premium_Warehouse_eCommerceCommon', 'submit_user'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_3rdp_info', array('Premium_Warehouse_eCommerceCommon', 'submit_3rdp_info'));

		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories','Available languages');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_distributor','Items Availability');
		DB::DropTable('premium_ecommerce_orders_temp');
		DB::DropTable('premium_ecommerce_3rdp_plugin');
		DB::DropTable('premium_ecommerce_quickcart');
		DB::DropTable('premium_ecommerce_products_stats');
		DB::DropTable('premium_ecommerce_pages_stats');
		DB::DropTable('premium_ecommerce_categories_stats');
		DB::DropTable('premium_ecommerce_searched_stats');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'pages_stats_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'products_stats_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_categories', 'Premium/Warehouse/eCommerce', 'categories_stats_addon');
		Premium_PaymentsCommon::delete_addon('premium_warehouse_items_orders');

	
		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
		foreach($langs as $k=>$name) {
			Variable::delete('ecommerce_home_'.$k,false);
			Variable::delete('ecommerce_rules_'.$k,false);
			Variable::delete('ecommerce_contactus_'.$k,false);
		}
		Variable::delete('ecommerce_home');
		Variable::delete('ecommerce_rules');
		Variable::delete('ecommerce_contactus');
		Variable::delete('ecommerce_autoprice');
		Variable::delete('ecommerce_minimal_profit');
		Variable::delete('ecommerce_percentage_profit');

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium_Warehouse_eCommerce', 'get_3rd_party_info_addon');

		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium_Warehouse_eCommerce', 'warehouse_item_addon');

		Utils_CommonDataCommon::remove('Premium/Warehouse/eCommerce/Languages');
		Base_ThemeCommon::uninstall_default_theme($this->get_type());

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'parameters_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'availability_addon');
		
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Page Title');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Meta Description');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Keywords');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders', 'Online order');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse','Pickup Place');
		
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'descriptions_addon');
//		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_categories', 'Premium/Warehouse/eCommerce', 'cat_descriptions_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_parameters', 'Premium/Warehouse/eCommerce', 'parameter_labels_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_parameter_groups', 'Premium/Warehouse/eCommerce', 'parameter_group_labels_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'subpages_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'prices_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_polls', 'Premium/Warehouse/eCommerce', 'poll_answers');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'product_comments_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium/Warehouse/eCommerce', 'orders_addon');
		Utils_RecordBrowserCommon::delete_addon('contact', 'Premium/Warehouse/eCommerce', 'users_addon');

//		Utils_AttachmentCommon::delete_addon('premium_ecommerce_descriptions');
//		Utils_AttachmentCommon::delete_addon('premium_ecommerce_pages_data');
//		Utils_AttachmentCommon::delete_addon('premium_ecommerce_pages_data');
//		Utils_AttachmentCommon::delete_addon('premium_ecommerce_products');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'attachment_page_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages_data', 'Premium/Warehouse/eCommerce', 'attachment_page_desc_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'attachment_product_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_descriptions', 'Premium/Warehouse/eCommerce', 'attachment_product_desc_addon');

		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_emails');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_3rdp_info');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_products');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_cat_descriptions');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_descriptions');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_parameters');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_parameter_labels');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_parameter_groups');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_param_group_labels');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_products_parameters');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_availability');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_availability_labels');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_pages');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_pages_data');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_prices');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_payments_carriers');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_polls');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_poll_answers');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_boxes');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_promotion_codes');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_banners');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_product_comments');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_orders');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_users');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_newsletter');
		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'CRM/Contacts','version'=>0),
			array('name'=>'Premium/Warehouse/Items','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Orders','version'=>0),
			array('name'=>'Utils/CurrencyField','version'=>0),
			array('name'=>'Utils/Image','version'=>0),
			array('name'=>'Libs/OpenFlashChart','version'=>0),
			array('name'=>'Libs/Leightbox','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0),
			array('name'=>'Utils/RecordBrowser/RecordPicker', 'version'=>0),
            array('name'=>'Premium/Warehouse/Wholesale', 'version'=>0),
            array('name'=>'Premium/Payments', 'version'=>0),
            array('name'=>'Premium/MultipleAddresses', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Warehouses - Premium Module',
			'Author'=>'pbukowski@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
        return array('package'=>__('eCommerce'), 'version'=>'0.8');
	}

	public static function post_install() {
	        if(!DB::GetOne('SELECT 1 FROM premium_ecommerce_quickcart'))
	                Premium_Warehouse_eCommerceCommon::register_qc('modules/Premium/Warehouse/eCommerce/quickcart33pro');
		return array(
				array('type'=>'header','label'=>__('Please set your virtual host for ecommerce site'),'name'=>null),
				array('type'=>'static','label'=>'','values'=>'Your current ecommerce directory is set to: <i>'.dirname(__FILE__).'/quickcart33pro</i><br />Please set your virtual host in web server settings.<br />Please set all files in <i>'.dirname(__FILE__).'/quickcart33pro/config directory writable by web server user.')
			);
	}

	public static function post_install_process($val) {
	}
}

?>
