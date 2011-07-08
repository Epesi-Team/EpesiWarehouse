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
 * @subpackage warehouse-items-invoice
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_InvoiceInstall extends ModuleInstall {

	public function install() {
		Base_ThemeCommon::install_default_theme($this->get_type());

		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium_Warehouse_Invoice', 'invoice', 'Premium_Warehouse_InvoiceCommon::invoice_addon_parameters');
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders','Invoice Print Date','date', false, false, '', '', false, false);
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders','Invoice Number','text', true, false, '32', 'integer', false, false);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items_orders','Invoice Number',array('Premium_Warehouse_InvoiceCommon', 'display_invoice_number'));
		Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items_orders','Invoice Number',array('Premium_Warehouse_InvoiceCommon', 'QFfield_invoice_number'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse','Invoice Display Name','text', false, false, '64', '', false, false);
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse','Invoice Number Code','text', false, false, '16', '', false, false);

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders_details','SWW','text', true, false, '32', 'integer', false, false, 12);

		Utils_RecordBrowserCommon::new_record_field('data_tax_rates','Tax Code','text', true, false, '8', '', false, false);

		Variable::set('premium_warehouse_invoice_style', 'US');

		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::delete_browse_mode_details_callback('premium_warehouse_items_orders', 'Premium/Warehouse/Invoice', 'browse_mode_details');

		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium_Warehouse_Invoice', 'invoice');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders','Invoice Number');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders','Invoice Print Date');

		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse','Invoice Number Code');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse','Invoice Display Name');

		Utils_RecordBrowserCommon::delete_record_field('data_tax_rates','Tax Code');

		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders_details','SWW');

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
			'License'=>'Commecial');
	}
	
	public static function simple_setup() {
		return true;
	}
}

?>
