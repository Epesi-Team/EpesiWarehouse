<?php
/**
 * Warehouse - Inventory Items
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_InventoryItemsInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'Item Name', 'type'=>'text', 'required'=>true, 'param'=>'128', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_Warehouse_InventoryItemsCommon', 'display_item_name')),
			array('name'=>'Quantity', 'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Description', 'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_inventoryitems', $fields);
		
		Utils_RecordBrowserCommon::set_quickjump('premium_inventoryitems', 'Item Name');
		Utils_RecordBrowserCommon::set_favorites('premium_inventoryitems', true);
		Utils_RecordBrowserCommon::set_recent('premium_inventoryitems', 15);
		Utils_RecordBrowserCommon::set_caption('premium_inventoryitems', 'Inventory Items');
		Utils_RecordBrowserCommon::set_icon('premium_inventoryitems', Base_ThemeCommon::get_template_filename('Premium/Warehouse/InventoryItems', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_inventoryitems', 'Premium_Warehouse_InventoryItemsCommon', 'access_inventoryitems');
		Utils_RecordBrowserCommon::enable_watchdog('premium_inventoryitems', array('Premium_Warehouse_InventoryItemsCommon','watchdog_label'));
		
// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_inventoryitems', 'Premium/Warehouse/InventoryItems', 'attachment_addon', 'Notes');

// ************ other ************** //	
		$this->add_aco('browse inventoryitems',array('Employee'));
		$this->add_aco('view inventoryitems',array('Employee'));
		$this->add_aco('edit inventoryitems',array('Employee'));
		$this->add_aco('delete inventoryitems',array('Employee Manager'));

		$this->add_aco('view protected notes','Employee');
		$this->add_aco('view public notes','Employee');
		$this->add_aco('edit protected notes','Employee Administrator');
		$this->add_aco('edit public notes','Employee');
		
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_inventoryitems', 'Premium/Warehouse/InventoryItems', 'premium_inventoryitems_attachment_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_inventoryitems');
		return true;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Inventory Items - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'SPL');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_inventoryitems');		
	}
}

?>
