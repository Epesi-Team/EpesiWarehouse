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
			array('name'=>'Description', 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_description')),
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_products', $fields);
		
		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_products', true);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_products', 'eCommerce - Products');
		Utils_RecordBrowserCommon::set_icon('premium_ecommerce_products', Base_ThemeCommon::get_template_filename('Premium/Warehouse/eCommerce', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_products', 'Premium_Warehouse_eCommerceCommon', 'access_products');
		
		$fields = array(
			array('name'=>'Item', 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name'=>'Language', 		'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('eCommerce_Languages'), 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_description_language')),
			array('name'=>'Product Name',	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Description', 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_descriptions', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_descriptions', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_descriptions', 'eCommerce - Descriptions');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_descriptions', 'Premium_Warehouse_eCommerceCommon', 'access_descriptions');

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
			array('name'=>'Language', 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'param'=>array('eCommerce_Languages')),
			array('name'=>'Label', 		'type'=>'text', 'param'=>'128', 'required'=>true, 'extra'=>false)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_parameter_labels', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_parameter_labels', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_parameter_labels', 'eCommerce - Parameters');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_parameter_labels', 'Premium_Warehouse_eCommerceCommon', 'access_parameter_labels');
		Utils_RecordBrowserCommon::set_processing_method('premium_ecommerce_parameter_labels', array('Premium_Warehouse_eCommerceCommon', 'submit_parameter_labels'));

		$fields = array(
			array('name'=>'Item', 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array($this->get_type().'Common', 'display_item_name')),
			array('name'=>'Parameter', 		'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'premium_ecommerce_parameters::Parameter Code'),
			array('name'=>'Value', 			'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_products_parameters', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_products_parameters', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_products_parameters', 'eCommerce - Products Paramteres');
		Utils_RecordBrowserCommon::set_access_callback('premium_ecommerce_products_parameters', 'Premium_Warehouse_eCommerceCommon', 'access_products_parameters');
		
// ************* addons ************ //
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'parameters_addon', 'Parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'names_addon', 'Names');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'descriptions_addon', 'Descriptions');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_parameters', 'Premium/Warehouse/eCommerce', 'parameter_labels_addon', 'Labels');

// ************ other ************** //
		Utils_CommonDataCommon::new_array('eCommerce_Languages',array('en'=>'English'));

		$this->add_aco('browse ecommerce',array('Employee'));
		$this->add_aco('view ecommerce',array('Employee'));
		$this->add_aco('edit ecommerce',array('Employee'));
		$this->add_aco('delete ecommerce',array('Employee Manager'));

		return true;
	}
	
	public function uninstall() {
		Utils_CommonDataCommon::remove('eCommerce_Languages');
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_products');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_descriptions');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_parameters');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_parameter_labels');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_products_parameters');
		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Orders','version'=>0),
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
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_products'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_names'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_descriptions'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_parameters'),
				Utils_RecordBrowserCommon::get_tables('premium_ecommerce_products_parameters')
			);
	}
}

?>
