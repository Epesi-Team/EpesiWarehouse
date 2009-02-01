<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * eCommerce
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerceInstall extends ModuleInstall {

	public function install() {
//		Base_LangCommon::install_translations($this->get_type());
//		Base_ThemeCommon::install_default_theme($this->get_type());

		$fields = array(
			array('name'=>'Item Name', 		'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::SKU|Item Name;Premium_Warehouse_eCommerceCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name'=>'Product Name', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_product_name')),
			array('name'=>'Publish', 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Position', 		'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>false),
			array('name'=>'Available',	 	'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>false, 'param'=>'premium_ecommerce_availability::Availability Code'),
			array('name'=>'Description', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_description'))
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_products', $fields);
		
		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_products', true);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_products', 'eCommerce - Products');
		Utils_RecordBrowserCommon::set_icon('premium_ecommerce_products', Base_ThemeCommon::get_template_filename('Premium/Warehouse/eCommerce', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_products', 'Premium_Warehouse_eCommerceCommon', 'access_products');
		
		$fields = array(
			array('name'=>'Category', 		'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items_categories::Category Name', 'extra'=>false, 'visible'=>true),
			array('name'=>'Language', 		'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_description_language')),
			array('name'=>'Display Name',	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Short Description', 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Long Description', 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Page Title', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>64, 'visible'=>false),
			array('name'=>'Meta Description', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>256, 'visible'=>false),
			array('name'=>'Keywords', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>128, 'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_cat_descriptions', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_cat_descriptions', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_cat_descriptions', 'eCommerce - Cat. Descriptions');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_cat_descriptions', 'Premium_Warehouse_eCommerceCommon', 'access_cat_descriptions');

		$fields = array(
			array('name'=>'Item Name', 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name'=>'Language', 		'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_description_language')),
			array('name'=>'Display Name',	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Short Description', 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Long Description', 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Page Title', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>64, 'visible'=>false),
			array('name'=>'Meta Description', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>256, 'visible'=>false),
			array('name'=>'Keywords', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>128, 'visible'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_descriptions', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_descriptions', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_descriptions', 'eCommerce - Descriptions');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_descriptions', 'Premium_Warehouse_eCommerceCommon', 'access_descriptions');

		//product prices
		$fields = array(
			array('name'=>'Item Name', 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name'=>'Currency', 	'type'=>'integer', 'required'=>true, 'extra'=>false,'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_currency'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_currency')),
			array('name'=>'Price', 		'type'=>'integer', 'required'=>true, 'extra'=>false,'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_prices', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_prices', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_prices', 'eCommerce - prices');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_prices', 'Premium_Warehouse_eCommerceCommon', 'access_products');

		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'prices_addon', 'Prices');

		//product parameters
		$fields = array(
			array('name'=>'Parameter Code', 'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Position', 		'type'=>'integer', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameters', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameters', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameters', 'eCommerce - Parameters');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_parameters', 'Premium_Warehouse_eCommerceCommon', 'access_parameters');

		$fields = array(
			array('name'=>'Parameter', 	'type'=>'select', 'param'=>'premium_ecommerce_parameters::Parameter Code', 'required'=>true, 'extra'=>false),
			array('name'=>'Language', 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages')),
			array('name'=>'Label', 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameter_labels', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameter_labels', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameter_labels', 'eCommerce - Parameters');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_parameter_labels', 'Premium_Warehouse_eCommerceCommon', 'access_parameter_labels');
		Utils_RecordBrowserCommon::set_processing_method('premium_ecommerce_parameter_labels', array('Premium_Warehouse_eCommerceCommon', 'submit_parameter_labels'));

		$fields = array(
			array('name'=>'Item Name', 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name'=>'Parameter', 		'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'premium_ecommerce_parameters::Parameter Code'),
			array('name'=>'Value', 			'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_products_parameters', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_products_parameters', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_products_parameters', 'eCommerce - Products Paramteres');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_products_parameters', 'Premium_Warehouse_eCommerceCommon', 'access_products_parameters');

		//product availability
		$fields = array(
			array('name'=>'Availability Code', 'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Position', 		'type'=>'integer', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_availability', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_availability', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_availability', 'eCommerce - Product Available');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_availability', 'Premium_Warehouse_eCommerceCommon', 'access_parameters');

		$fields = array(
			array('name'=>'Availability', 	'type'=>'select', 'param'=>'premium_ecommerce_availability::Availability Code', 'required'=>true, 'extra'=>false),
			array('name'=>'Language', 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name'=>'Label', 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_availability_labels', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_availability_labels', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_availability_labels', 'eCommerce - Product Available');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_availability_labels', 'Premium_Warehouse_eCommerceCommon', 'access_parameters');

		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_availability', 'Premium/Warehouse/eCommerce', 'availability_labels_addon', 'Labels');

		//pages
		$fields = array(
			array('name'=>'Page Name', 		'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Parent Page',	'type'=>'select', 'param'=>'premium_ecommerce_pages::Page Name;Premium_Warehouse_eCommerceCommon::parent_page_crits' ,'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Type',			'type'=>'integer',	'required'=>true, 'extra'=>false, 'visible'=>true,'display_callback'=>array($this->get_type().'Common', 'display_page_type'),'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_page_type')),
			array('name'=>'Publish', 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Position', 		'type'=>'integer', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_pages', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_pages', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_pages', 'eCommerce - Pages');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_pages', 'Premium_Warehouse_eCommerceCommon', 'access_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'subpages_addon', 'Subpages');

		$fields = array(
			array('name'=>'Page', 	'type'=>'select', 'param'=>'premium_ecommerce_pages::Page Name', 'required'=>true, 'extra'=>false),
			array('name'=>'Language', 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name'=>'Name', 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Short Description', 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Long Description', 	'type'=>'long text', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name'=>'Page Title', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>64, 'visible'=>false),
			array('name'=>'Meta Description', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>256, 'visible'=>false),
			array('name'=>'Keywords', 	'type'=>'text', 'required'=>false, 'extra'=>false, 'param'=>128, 'visible'=>false)
		);

//sTemplate' => 4, 'sTheme' => 5, 'sUrl' => 6, 'sBanner' => 7


		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_pages_data', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_pages_data', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_pages_data', 'eCommerce - Pages');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_pages_data', 'Premium_Warehouse_eCommerceCommon', 'access_parameters');
		
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'pages_info_addon', 'Info');

// ************* addons ************ //
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'parameters_addon', 'Parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'descriptions_addon', 'Descriptions');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_categories', 'Premium/Warehouse/eCommerce', 'cat_descriptions_addon', 'Descriptions');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_parameters', 'Premium/Warehouse/eCommerce', 'parameter_labels_addon', 'Labels');

// ************ other ************** //
		Utils_CommonDataCommon::new_array('Premium/Warehouse/eCommerce/Languages',array('en'=>'English','pl'=>'Polish'));
		Utils_RecordBrowserCommon::new_record('premium_ecommerce_availability',
				array('availability_code'=>'24h',
					'position'=>0));
		Utils_RecordBrowserCommon::new_record('premium_ecommerce_availability',
				array('availability_code'=>'48h',
					'position'=>0));
		Utils_RecordBrowserCommon::new_record('premium_ecommerce_availability',
				array('availability_code'=>'72h',
					'position'=>0));

		$this->add_aco('browse ecommerce',array('Employee'));
		$this->add_aco('view ecommerce',array('Employee'));
		$this->add_aco('edit ecommerce',array('Employee'));
		$this->add_aco('delete ecommerce',array('Employee Manager'));

		Variable::set('ecommerce_start_page','This is start page of quickcart shop with epesi backend. You can edit it in Warehouse - eCommerce settings.');
		Variable::set('ecommerce_rules','You can edit this page in Warehouse - eCommerce settings.');

		return true;
	}
	
	public function uninstall() {
		Variable::delete('ecommerce_start_page');
		Variable::delete('ecommerce_rules');
		Utils_CommonDataCommon::remove('Premium/Warehouse/eCommerce/Languages');
		Base_ThemeCommon::uninstall_default_theme($this->get_type());

		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'parameters_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'availability_addon');
		
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Page Title');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Meta Description');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_categories', 'Keywords');
		
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'descriptions_addon');
//		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_categories', 'Premium/Warehouse/eCommerce', 'cat_descriptions_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_parameters', 'Premium/Warehouse/eCommerce', 'parameter_labels_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'subpages_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'prices_addon');

		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_products');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_cat_descriptions');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_descriptions');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_parameters');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_parameter_labels');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_products_parameters');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_availability');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_availability_labels');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_pages');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_pages_data');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_prices');
		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Orders','version'=>0),
			array('name'=>'Utils/CurrencyField','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Warehouses - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'MIT');
	}
	
	public static function simple_setup() {
		return true;
	}

	public static function backup() {
		return array_merge(
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_cat_descriptions'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_products'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_descriptions'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_parameters'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_products_parameters'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_parameter_labels'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_availability'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_availability_labels'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_pages'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_pages_data')
			);
	}
}

?>
