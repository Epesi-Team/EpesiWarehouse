<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-wholesale
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_WholesaleInstall extends ModuleInstall {
    const version = '1.5.0';

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		Base_ThemeCommon::install_default_theme($this->get_type());
		$this->create_data_dir();

		$fields = array(
			array('name' => _M('Name'), 			'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_Warehouse_WholesaleCommon', 'display_distributor')),
			array('name' => _M('Plugin'), 		'type'=>'select', 'required'=>true, 'extra'=>false, 'QFfield_callback'=>array('Premium_Warehouse_WholesaleCommon','QFfield_plugin')),
			array('name' => _M('Company'), 		'type'=>'crm_company', 'param'=>array('field_type'=>'select'), 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Last update'), 	'type'=>'timestamp', 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Tax Rate'), 	'type'=>'select', 'required'=>false, 'extra'=>false, 'visible'=>true, 'param'=>'data_tax_rates::Name', 'style'=>'integer'),
			array('name' => _M('Minimal profit'), 	'type'=>'integer', 'required'=>false, 'extra'=>false, 'visible'=>true, 'style'=>'integer'),
			array('name' => _M('Percentage profit'), 	'type'=>'integer', 'required'=>false, 'extra'=>false, 'visible'=>true, 'style'=>'integer'),
			array('name' => _M('Param1'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param2'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param3'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param4'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param5'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
			array('name' => _M('Param6'), 		'type'=>'text', 'required'=>false, 'param'=>'128', 'extra'=>true, 'visible'=>false),
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_distributor', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_distributor', 'Name');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_distributor', true);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_distributor', _M('Distributors'));
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_distributor', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Wholesale', 'icon.png'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_distributor', array('Premium_Warehouse_WholesaleCommon', 'submit_distributor'));
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_distributor', array('Premium_Warehouse_WholesaleCommon','watchdog_label'));
		
// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Wholesale', 'distributors_addon', 'Distributors');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_distributor', 'Premium/Warehouse/Wholesale', 'items_addon', 'Items');
		Utils_AttachmentCommon::new_addon('premium_warehouse_distributor');

// ************ other ************** //	
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Quantity on Hand', array('Premium_Warehouse_WholesaleCommon', 'display_item_quantity'));
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Available Qty', array('Premium_Warehouse_WholesaleCommon', 'display_available_qty'));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', _M('Dist Qty'), 'calculated', true, false, '', 'integer', false, false, 'Reserved Qty');
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Dist Qty', array('Premium_Warehouse_WholesaleCommon','display_distributor_qty'));

		DB::CreateTable('premium_warehouse_wholesale_plugin',
						'id I4 AUTO KEY,'.
						'name C(64),'.
						'filename C(64),'.
						'active I1',
						array('constraints'=>''));

		DB::CreateTable('premium_warehouse_wholesale_items',
						'id I4 AUTO KEY,'.
						'item_id I4,'.
						'internal_key C(32),'.
						'distributor_item_name C(128),'.
						'distributor_id I4,'.
						'price F,'.
						'price_currency I4,'.
						'quantity I4,'.
						'quantity_info C(64),'.
						'distributor_category I4,'.
						'manufacturer I4,'.
						'manufacturer_part_number C(32),'.
						'upc C(128),'.
						'thirdp C(255)',
						array('constraints'=>''));
		DB::CreateIndex('premium_warehouse_wholesale_items__ik_di__idx', 'premium_warehouse_wholesale_items', array('internal_key','distributor_id'));
		DB::CreateIndex('premium_warehouse_wholesale_items__item_id__idx', 'premium_warehouse_wholesale_items', 'item_id');

		DB::Execute('UPDATE premium_warehouse_distributor_field SET param = 1 WHERE field = %s', array('Details'));

		$fields = array(
			array('name' => _M('Distributor'), 			'type'=>'select','param'=>'premium_warehouse_distributor::Name', 'required'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_WholesaleCommon', 'QFfield_distributor_name')),
			array('name' => _M('Foreign Category Name'), 	'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_WholesaleCommon', 'QFfield_category_name')),
			array('name' => _M('Epesi Category'),			'type'=>'multiselect', 'required'=>false, 'visible'=>true, 'extra'=>false,  'param'=>'premium_warehouse_items_categories::Category Name', 'QFfield_callback'=>array('Premium_Warehouse_ItemsCommon', 'QFfield_item_category'), 'display_callback'=>array('Premium_Warehouse_WholesaleCommon','display_epesi_cat_name'))
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_distr_categories', $fields);
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_distr_categories', false);
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_distributor', 'Premium/Warehouse/Wholesale', 'categories_addon', 'Categories');
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_distr_categories', _M('Distributor Categories'));

		Utils_RecordBrowserCommon::add_access('premium_warehouse_distributor', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_distributor', 'add', 'ACCESS:employee', array(), array('last_update'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_distributor', 'edit', 'ACCESS:employee', array(), array('last_update'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_distributor', 'delete', array('ACCESS:employee', 'ACCESS:manager'));

		Utils_RecordBrowserCommon::add_access('premium_warehouse_distr_categories', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_distr_categories', 'add', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_distr_categories', 'edit', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_distr_categories', 'delete', array('ACCESS:employee', 'ACCESS:manager'));

		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_distributor', array('Premium_Warehouse_WholesaleCommon', 'submit_distributor'));
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Quantity on Hand', array('Premium_Warehouse_Items_LocationCommon', 'display_item_quantity'));
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Available Qty', array('Premium_Warehouse_Items_OrdersCommon', 'display_available_qty'));
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		DB::DropTable('premium_warehouse_wholesale_plugin');
		DB::DropTable('premium_warehouse_wholesale_items');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items', 'Dist Qty');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Wholesale', 'distributors_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_distributor', 'Premium/Warehouse/Wholesale', 'items_addon');
		Utils_AttachmentCommon::delete_addon('premium_warehouse_distributor');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_distributor', 'Premium/Warehouse/Wholesale', 'categories_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_distributor');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_distr_categories');
		return true;
	}
	
	public function version() {
		return array(self::version);
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0),
			array('name'=>'Premium/Warehouse', 'version'=>0),
			array('name'=>'Premium/Warehouse/Items/Orders', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Wholesale - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
        return array('package'=>__('Inventory Management'), 'option'=>__('Wholesale data sync'));
	}
}

?>
