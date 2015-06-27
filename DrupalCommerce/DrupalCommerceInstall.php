<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * DrupalCommerce
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2013, Telaxus LLC
 * @license Commercial
 * @version 1.0.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_DrupalCommerceInstall extends ModuleInstall {
    const version = '1.0.0';

	public function install() {
		set_time_limit(0);
//		Base_LangCommon::install_translations($this->get_type());
//		Base_ThemeCommon::install_default_theme($this->get_type());

		$fields = array(
			array('name' => _M('SKU'), 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_DrupalCommerceCommon', 'display_sku')),
			array('name' => _M('Product Name'), 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_product_name')),
			array('name' => _M('Item Name'), 		'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::SKU|Item Name;Premium_Warehouse_DrupalCommerceCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name'), 'filter'=>true),
			array('name' => _M('Publish'), 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true, 'filter'=>true),
			array('name' => _M('Recommended'), 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false, 'filter'=>true),
//			array('name' => _M('Exclude compare services'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false, 'filter'=>true),
			array('name' => _M('Always in stock'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false, 'filter'=>true),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Available'),	 	'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>false, 'param'=>'premium_ecommerce_availability::Availability Code'),
			array('name' => _M('Description'), 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_description')),
//			array('name' => _M('Related products'), 	'type'=>'multiselect', 'param'=>'premium_ecommerce_products::Item Name;Premium_Warehouse_DrupalCommerceCommon::related_products_crits;Premium_Warehouse_DrupalCommerceCommon::adv_related_products_params', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array($this->get_type().'Common', 'display_related_product_name'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_related_products')),
//			array('name' => _M('Popup products'), 	'type'=>'multiselect', 'param'=>'premium_ecommerce_products::Item Name;Premium_Warehouse_DrupalCommerceCommon::related_products_crits;Premium_Warehouse_DrupalCommerceCommon::adv_related_products_params', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array($this->get_type().'Common', 'display_related_product_name'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_related_products'))
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_products', $fields);
		
		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_products', true);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_products', _M('eCommerce - Products'));
		Utils_RecordBrowserCommon::set_icon('premium_ecommerce_products', Base_ThemeCommon::get_template_filename('Premium/Warehouse/DrupalCommerce', 'icon.png'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_products', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_products_position'));

		$fields = array(
			array('name' => _M('Category'), 		'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items_categories::Category Name', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Language'), 		'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_description_language')),
			array('name' => _M('Display Name'),	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Short Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Long Description'), 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
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

		//product prices
		$fields = array(
			array('name' => _M('Item Name'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name' => _M('Model'),	'type'=>'text', 'param'=>64, 'required'=>false, 'extra'=>false, 'visible'=>true),
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
			array('name' => _M('Label'), 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','display_parameter_label')),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false,'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameters', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameters', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameters', _M('eCommerce - Parameters'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_parameters', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_parameters_position'));

		$fields = array(
			array('name' => _M('Parameter'), 	'type'=>'select', 'param'=>'premium_ecommerce_parameters::Parameter Code', 'required'=>true, 'extra'=>false),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages')),
			array('name' => _M('Label'), 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameter_labels', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameter_labels', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameter_labels', _M('eCommerce - Parameters'));
		//Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_parameter_labels', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_parameter_labels'));

		//parameter groups
		$fields = array(
			array('name' => _M('Group Code'), 'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Label'), 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','display_parameter_group_label')),
			array('name' => _M('Position'), 		'type'=>'hidden', 'param'=>Utils_RecordBrowserCommon::actual_db_type('integer'), 'required'=>true, 'extra'=>false,'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameter_groups', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameter_groups', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameter_groups', _M('eCommerce - Parameter Groups'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_parameter_groups', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_parameter_groups_position'));

		$fields = array(
			array('name' => _M('Group'), 		'type'=>'select', 'param'=>'premium_ecommerce_parameter_groups::Group Code', 'required'=>true, 'extra'=>false),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages')),
			array('name' => _M('Label'), 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_param_group_labels', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_param_group_labels', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_param_group_labels', _M('eCommerce - Parameters'));
		//Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_param_group_labels', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_parameter_labels'));
		
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

		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_availability', 'Premium/Warehouse/DrupalCommerce', 'availability_labels_addon', _M('Labels'));

		//drupal
		$fields = array(
			array('name' => _M('URL'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Login'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Password'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>false,'QFfield_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','QFfield_password'), 'display_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','display_password')),
			array('name' => _M('Endpoint'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Export Net Price'), 			'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Pathauto i18n'), 			'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_drupal', $fields);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_drupal', _M('eCommerce - Drupal'));

		//orders
		$fields = array(
			array('name' => _M('Transaction ID'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items_orders::Transaction ID;Premium_Warehouse_Items_OrdersCommon::transactions_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_id_in_details')),
			array('name' => _M('Drupal'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_ecommerce_drupal::URL', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Drupal order ID'), 	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true),
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
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/DrupalCommerce', 'orders_addon', 'Premium_Warehouse_DrupalCommerceCommon::orders_addon_parameters');
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_orders', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_ecommerce_order'));
		
        //emails
		$fields = array(
			array('name' => _M('Subject'),	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Content'), 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>false, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Send On Status'), 		'type'=>'integer', 'required'=>true, 'extra'=>false,'visible'=>false, 'QFfield_callback'=>array('Premium_Warehouse_DrupalCommerceCommon', 'QFfield_order_status'), 'display_callback'=>array('Premium_Warehouse_DrupalCommerceCommon', 'display_order_status'))
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_emails', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_emails', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_emails', _M('eCommerce - e-mails'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders',array('name' => _M('Online order'),	'type'=>'checkbox', 'required'=>false, 'filter'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','QFfield_online_order')));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_distributor',array('name' => _M('Items Availability'), 'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>false, 'param'=>'premium_ecommerce_availability::Availability Code'));

		$fields = array(
			array('name' => _M('Name'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_Warehouse_DrupalCommerceCommon', 'display_3rdp_name')),
			array('name' => _M('Plugin'), 		'type'=>'select', 'required'=>true, 'extra'=>false, 'QFfield_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','QFfield_3rdp_plugin')),
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
		Utils_RecordBrowserCommon::set_icon('premium_ecommerce_3rdp_info', Base_ThemeCommon::get_template_filename('Premium/Warehouse/DrupalCommerce', 'icon.png'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_3rdp_info', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_3rdp_info'));

		DB::CreateTable('premium_ecommerce_3rdp_plugin',
						'id I4 AUTO KEY,'.
						'name C(64),'.
						'filename C(64),'.
						'active I1',
						array('constraints'=>''));
		

// ************* addons ************ //
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'parameters_addon', 'Premium_Warehouse_DrupalCommerceCommon::parameters_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'descriptions_addon', 'Premium_Warehouse_DrupalCommerceCommon::descriptions_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'prices_addon', 'Premium_Warehouse_DrupalCommerceCommon::prices_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_categories', 'Premium/Warehouse/DrupalCommerce', 'cat_descriptions_addon', _M('Descriptions'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_parameters', 'Premium/Warehouse/DrupalCommerce', 'parameter_labels_addon', _M('Labels'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_parameter_groups', 'Premium/Warehouse/DrupalCommerce', 'parameter_group_labels_addon', _M('Labels'));

		//addon for warehouse items
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'warehouse_item_addon', _M('eCommerce').'#'._M('eCommerce Overview'));
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'descriptions_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::descriptions_addon_item_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'parameters_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::parameters_addon_item_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'prices_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::prices_addon_item_parameters');

// ************ other ************** //
        Utils_CommonDataCommon::new_id('Premium/Warehouse/eCommerce/Languages', true);
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
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium_Warehouse_DrupalCommerce', 'get_3rd_party_info_addon', 'Premium_Warehouse_DrupalCommerceCommon::get_3rd_party_info_addon_parameters');
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_categories',array('name' => _M('Available languages'),	'type'=>'calculated', 'required'=>false, 'filter'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','display_category_available_languages')));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse',array('name' => _M('Pickup Place'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true));
		
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_descriptions', 'Premium/Warehouse/DrupalCommerce', 'attachment_product_desc_addon', _M('Pictures'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'attachment_product_addon', _M('Pictures'));
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'attachment_product_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::attachment_product_addon_item_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_categories', 'Premium/Warehouse/DrupalCommerce', 'attachment_categories_addon', _M('Pictures'));
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_cat_descriptions', 'Premium/Warehouse/DrupalCommerce', 'attachment_categories_desc_addon', _M('Pictures'));

		Variable::set('ecommerce_autoprice',false);
		Variable::set('ecommerce_minimal_profit','');
		Variable::set('ecommerce_percentage_profit','');

		Variable::set('ecommerce_item_prices',true);
		Variable::set('ecommerce_item_descriptions',true);
		Variable::set('ecommerce_item_parameters',true);
		
		Premium_PaymentsCommon::new_addon('premium_warehouse_items_orders');
		
		Utils_RecordBrowserCommon::register_processing_callback('premium_payments', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_payment'));

		Utils_CommonDataCommon::extend_array('Premium_Items_Orders_Payment_Types',array('Drupal'=>_M('Drupal')),false,true);
		Utils_CommonDataCommon::extend_array('Premium_Items_Orders_Shipment_Types',array('Drupal'=>_M('Drupal')),false,true);

		$this->create_data_dir();
		Base_ThemeCommon::install_default_theme('Premium/Warehouse/DrupalCommerce');
		
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
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_prices');
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_orders');

		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'ACCESS:employee', array('online_order'=>1, '<status'=>6), array('transaction_type'));

		Utils_RecordBrowserCommon::add_access('premium_ecommerce_drupal', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_drupal', 'add', 'ADMIN');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_drupal', 'edit', 'ADMIN');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_drupal', 'delete', 'ADMIN');

		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme('Premium/Warehouse/DrupalCommerce');

		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_products', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_products_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_parameters', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_parameters_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_orders', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_ecommerce_order'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_payments', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_payment'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_parameter_groups', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_parameter_groups_position'));
		//Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_param_group_labels', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_parameter_labels'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_3rdp_info', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_3rdp_info'));

		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories','Available languages');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_distributor','Items Availability');
		DB::DropTable('premium_ecommerce_3rdp_plugin');
		Premium_PaymentsCommon::delete_addon('premium_warehouse_items_orders');

		Variable::delete('ecommerce_autoprice');
		Variable::delete('ecommerce_minimal_profit');
		Variable::delete('ecommerce_percentage_profit');
		Variable::delete('ecommerce_item_prices');
		Variable::delete('ecommerce_item_descriptions');
		Variable::delete('ecommerce_item_parameters');

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium_Warehouse_DrupalCommerce', 'get_3rd_party_info_addon');

		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium_Warehouse_DrupalCommerce', 'warehouse_item_addon');

		Utils_CommonDataCommon::remove('Premium/Warehouse/eCommerce/Languages');

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'parameters_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'availability_addon');
		
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Page Title');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Meta Description');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Keywords');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders', 'Online order');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse','Pickup Place');
		
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'descriptions_addon');
//		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_categories', 'Premium/Warehouse/DrupalCommerce', 'cat_descriptions_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_parameters', 'Premium/Warehouse/DrupalCommerce', 'parameter_labels_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_parameter_groups', 'Premium/Warehouse/DrupalCommerce', 'parameter_group_labels_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'prices_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium/Warehouse/DrupalCommerce', 'orders_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_categories', 'Premium/Warehouse/DrupalCommerce', 'attachment_categories_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_cat_descriptions', 'Premium/Warehouse/DrupalCommerce', 'attachment_categories_desc_addon');

//		Utils_AttachmentCommon::delete_addon('premium_ecommerce_descriptions');
//		Utils_AttachmentCommon::delete_addon('premium_ecommerce_pages_data');
//		Utils_AttachmentCommon::delete_addon('premium_ecommerce_pages_data');
//		Utils_AttachmentCommon::delete_addon('premium_ecommerce_products');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'attachment_product_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_descriptions', 'Premium/Warehouse/DrupalCommerce', 'attachment_product_desc_addon');

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
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_prices');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_orders');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_drupal');
		return true;
	}
	
	public function version() {
		return array(self::version);
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
            array('name'=>'Premium/Payments', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Warehouses - Premium Module',
			'Author'=>'pbukowski@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
        return array('package'=>__('eCommerce'), 'version' => self::version);
	}

	public static function post_install() {
		return array(
				array('type'=>'header','label'=>__('You have installed Epesi - Drupal Commerce bridge. Go to'),'name'=>null),
				array('type'=>'static','label'=>'','values'=>'1. '.__('Menu')),
				array('type'=>'static','label'=>'','values'=>'2. '.__('Administrator')),
				array('type'=>'static','label'=>'','values'=>'3. '.__('eCommerce')),
				array('type'=>'static','label'=>'','values'=>'4. '.__('Drupal')),
				array('type'=>'static','label'=>'','values'=>'5. '.__('Follow tutorial to complete Drupal and bridge setup')),
			);
	}

	public static function post_install_process($val) {
	}
}

?>
