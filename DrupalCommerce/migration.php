<?php 
die('Please comment out or delete second line from this file to run migration script.'."\n");

define('SET_SESSION',false);
define('CID',false); 
require_once('../../../../include.php');
ModuleManager::load_modules();

Acl::set_sa_user();

		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_pages', array('Premium_Warehouse_eCommerceCommon', 'submit_pages_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_polls', array('Premium_Warehouse_eCommerceCommon', 'submit_polls_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_boxes', array('Premium_Warehouse_eCommerceCommon', 'submit_boxes_position'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_banners', array('Premium_Warehouse_eCommerceCommon','banners_processing'));
		DB::DropTable('premium_ecommerce_orders_temp');

		DB::DropTable('premium_ecommerce_products_stats');
		DB::DropTable('premium_ecommerce_pages_stats');
		DB::DropTable('premium_ecommerce_categories_stats');
		DB::DropTable('premium_ecommerce_searched_stats');
		DB::DropTable('premium_ecommerce_quickcart');

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'pages_stats_addon');

		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
		foreach($langs as $k=>$name) {
			Variable::delete('ecommerce_home_'.$k,false);
			Variable::delete('ecommerce_rules_'.$k,false);
			Variable::delete('ecommerce_contactus_'.$k,false);
		}
		Variable::delete('ecommerce_home');
		Variable::delete('ecommerce_rules');
		Variable::delete('ecommerce_contactus');

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'subpages_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_polls', 'Premium/Warehouse/eCommerce', 'poll_answers');

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'attachment_page_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'pages_info_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'pages_stats_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'subpages_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages_data', 'Premium/Warehouse/eCommerce', 'attachment_page_desc_addon');

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'products_stats_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_categories', 'Premium/Warehouse/eCommerce', 'categories_stats_addon');

		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_pages');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_pages_data');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_polls');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_poll_answers');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_boxes');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_promotion_codes');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_banners');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_product_comments');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_newsletter');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_payments_carriers');

        DB::Execute('UPDATE base_dashboard_applets SET module_name="Premium_Warehouse_DrupalCommerce" WHERE module_name="Premium_Warehouse_eCommerce"');
        DB::Execute('UPDATE modules SET name="Premium_Warehouse_DrupalCommerce" WHERE name="Premium_Warehouse_eCommerce"');
        DB::Execute('UPDATE premium_ecommerce_3rdp_info_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_cat_descriptions_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_descriptions_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_emails_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_orders_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_parameter_groups_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_parameters_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_prices_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_products_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_products_parameters_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_ecommerce_users_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_warehouse_items_categories_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');
        DB::Execute('UPDATE premium_warehouse_items_orders_callback SET callback=REPLACE(callback,"Premium_Warehouse_eCommerce","Premium_Warehouse_DrupalCommerce") WHERE callback LIKE "%Premium_Warehouse_eCommerce%"');

        DB::Execute('UPDATE premium_ecommerce_products_field SET param=REPLACE(param,"Premium_Warehouse_eCommerceCommon","Premium_Warehouse_DrupalCommerceCommon") WHERE param LIKE "%Premium_Warehouse_eCommerceCommon%"');
        DB::Execute('UPDATE premium_ecommerce_users_field SET param=REPLACE(param,"Premium_Warehouse_eCommerceCommon","Premium_Warehouse_DrupalCommerceCommon") WHERE param LIKE "%Premium_Warehouse_eCommerceCommon%"');

        DB::Execute('UPDATE recordbrowser_processing_methods SET func=REPLACE(func,"Premium_Warehouse_eCommerceCommon","Premium_Warehouse_DrupalCommerceCommon") WHERE func LIKE "%Premium_Warehouse_eCommerceCommon%"');
        DB::Execute('UPDATE recordbrowser_addon SET module="Premium_Warehouse_DrupalCommerce",label=REPLACE(label,"Premium_Warehouse_eCommerceCommon","Premium_Warehouse_DrupalCommerceCommon") WHERE module="Premium_Warehouse_eCommerce"');
        DB::Execute('UPDATE recordbrowser_table_properties SET icon=REPLACE(icon,"Premium/Warehouse/eCommerce","Premium/Warehouse/DrupalCommerce") WHERE icon LIKE "%Premium/Warehouse/eCommerce%"');
        
		Base_ThemeCommon::install_default_theme('Premium/Warehouse/DrupalCommerce');

		//drupal
		$fields = array(
			array('name' => _M('URL'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Login'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Password'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>false,'QFfield_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','QFfield_password'), 'display_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','display_password')),
			array('name' => _M('Endpoint'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Export Net Price'), 			'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_drupal', $fields);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_drupal', _M('eCommerce - Drupal'));

		Utils_RecordBrowserCommon::add_access('premium_ecommerce_drupal', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_drupal', 'add', 'ADMIN');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_drupal', 'edit', 'ADMIN');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_drupal', 'delete', 'ADMIN');

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'product_comments_addon');

		Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_orders', array('name' => _M('Drupal'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_ecommerce_drupal::URL', 'extra'=>false, 'visible'=>true));
		Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_orders', array('name' => _M('Drupal order ID'), 	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true));

		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Payment_Types',array('Drupal'=>_M('Drupal')));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Shipment_Types',array('Drupal'=>_M('Drupal')));

		//users
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_users');
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_ecommerce_users', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_user'));
		Utils_RecordBrowserCommon::delete_addon('contact', 'Premium/Warehouse/DrupalCommerce', 'users_addon');

		//new addons
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'parameters_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::parameters_addon_item_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'descriptions_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::descriptions_addon_item_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'prices_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::prices_addon_item_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'attachment_product_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::attachment_product_addon_item_parameters');

        Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_products','Exclude compare services');
        Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_products','Related products');
        Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_products','Popup products');
        