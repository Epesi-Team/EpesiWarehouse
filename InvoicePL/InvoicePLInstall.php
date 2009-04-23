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
 * @subpackage warehouse-items-invoicepl
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_InvoicePLInstall extends ModuleInstall {

	public function install() {
		Base_ThemeCommon::install_default_theme($this->get_type());

		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium_Warehouse_InvoicePL', 'invoicepl', 'Premium_Warehouse_InvoicePLCommon::invoice_pl_addon_parameters');
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders','Invoice Number','integer', true, false, '', 'integer', false, false);
		Utils_RecordBrowserCommon::set_display_method('premium_warehouse_items_orders','Invoice Number','Premium_Warehouse_InvoicePLCommon', 'display_invoice_number');
		Utils_RecordBrowserCommon::set_QFfield_method('premium_warehouse_items_orders','Invoice Number','Premium_Warehouse_InvoicePLCommon', 'QFfield_invoice_number');

		Utils_RecordBrowserCommon::set_processing_method('premium_warehouse_items_orders', array('Premium_Warehouse_InvoicePLCommon', 'submit_order'));

		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::set_processing_method('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order'));

		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium_Warehouse_InvoicePL', 'invoicepl');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders','Invoice Number');

		return true;
	}
	
	public function version() {
		return array("0.9");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Orders','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Polish Invoice Report - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'MIT');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_warehouse_location');		
	}
}

?>
