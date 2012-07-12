<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Location
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-location
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_LocationInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());

		$fields = array(
			array('name' => _M('Item SKU'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::SKU;Premium_Warehouse_Items_OrdersCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_item_sku')),
			array('name' => _M('Item Name'), 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_item_name')),
			array('name' => _M('Quantity'),	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_LocationCommon', 'display_quantity')),
			array('name' => _M('Warehouse'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse::Warehouse;::', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_LocationCommon', 'display_warehouse'))
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_location', $fields);

		DB::Execute('UPDATE premium_warehouse_location_field SET param=%s WHERE field=%s', array('premium_warehouse_items::Item Name/Item SKU', 'Item Name'));

//		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_location', 'Item');
//		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_location', true);
//		Utils_RecordBrowserCommon::set_recent('premium_warehouse_location', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_location', _M('Item Location'));
//		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_orders', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items/Orders', 'icon.png'));
//		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_location', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order'));

		DB::CreateIndex('premium_warehouse_location__qsw__idx', 'premium_warehouse_location_data_1', 'f_quantity,f_item_sku,f_warehouse');
			
// ************ addons ************** //
//		Utils_RecordBrowserCommon::new_addon('premium_warehouse_location', 'Premium/Warehouse/Location', 'attachment_addon', _M('Notes'));
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_addon', 'Premium_Warehouse_Items_LocationCommon::location_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_serial_addon', 'Premium_Warehouse_Items_LocationCommon::location_serial_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('company', 'Premium/Warehouse/Items/Location', 'company_items_addon', 'Premium_Warehouse_Items_LocationCommon::company_items_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse', 'Premium/Warehouse/Items/Location', 'warehouse_item_list_addon', _M('Item List'));
		Utils_RecordBrowserCommon::set_addon_pos('premium_warehouse', 'Premium/Warehouse/Items/Location', 'warehouse_item_list_addon', 1);

// ************ other ************** //
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Quantity on Hand', array('Premium_Warehouse_Items_LocationCommon', 'display_item_quantity'));
		Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items', 'Quantity on Hand', array('Premium_Warehouse_Items_LocationCommon', 'QFfield_item_quantity'));
		
		DB::CreateTable('premium_warehouse_location_serial',
					'id I AUTO KEY,'.
					'location_id I,'.
					'serial C(128),'.
					'notes C(255),'.
					'shelf C(255),'.
					'owner C(32)',
					array('constraints'=>''));

		Utils_RecordBrowserCommon::add_access('premium_warehouse_location', 'view', 'ACCESS:employee');

		return true;
	}
	
	public function uninstall() {
		DB::DropTable('premium_warehouse_location_serial');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_addon');
		Utils_RecordBrowserCommon::unset_display_callback('premium_warehouse_items', 'Quantity on Hand');
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Location', 'location_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'company_items_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_location');
		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Premium/Warehouse','version'=>0),
			array('name'=>'Premium/Warehouse/Items','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Items Transactions - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
        return array('package'=>__('Inventory Management'));
	}
}

?>
