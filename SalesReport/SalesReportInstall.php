<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Janusz Tylek <jtylek@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-salesreport
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_SalesReportInstall extends ModuleInstall {

	public function install() {
		DB::CreateTable('premium_warehouse_sales_report_earning',
						'order_details_id I4 KEY,'.
						'quantity_lifo I4,'.
						'quantity_fifo I4,'.
						'g_earning_lifo I4,'.
						'g_earning_fifo I4,'.
						'n_earning_lifo I4,'.
						'n_earning_fifo I4',
						array('constraints'=>'')
		);
		DB::CreateTable('premium_warehouse_sales_report_purchase_fifo_tmp',
						'id I4 KEY,'.
						'item_id I4,'.
						'quantity I4,'.
						'warehouse I4,'.
						'net_price I4,'.
						'gross_price I4',
						array('constraints'=>'')
		);
		DB::CreateTable('premium_warehouse_sales_report_purchase_lifo_tmp',
						'id I4 KEY,'.
						'item_id I4,'.
						'quantity I4,'.
						'warehouse I4,'.
						'net_price I4,'.
						'gross_price I4',
						array('constraints'=>'')
		);
		DB::CreateTable('premium_warehouse_sales_report_exchange',
						'id I4 AUTO KEY,'.
						'order_id I4,'.
						'currency I4,'.
						'exchange_rate F',
						array('constraints'=>'')
		);
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/SalesReport', 'currency_exchange_addon', 'Exchange Rates');
		Variable::set('premium_warehouse_ex_currency', 1);
		return true;
	}
	
	public function uninstall() {
		DB::DropTable('premium_warehouse_sales_report_exchange');
		DB::DropTable('premium_warehouse_sales_report_earning');
		DB::DropTable('premium_warehouse_sales_report_purchase_fifo_tmp');
		DB::DropTable('premium_warehouse_sales_report_purchase_lifo_tmp');
		return true;
	}
	
	public function version() {
		return array("1.0");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Utils/RecordBrowser/Reports','version'=>0),
			array('name'=>'Premium/Warehouse','version'=>0),
			array('name'=>'Libs/OpenFlashChart','version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'',
			'Author'=>'Janusz Tylek <jtylek@telaxus.com>',
			'License'=>'<a href="modules/Premium/Warehouse/SalesReport/license.html" TARGET="_blank">Commercial</a>');
	}
	
	public static function simple_setup() {
		return true;
	}
	
}

?>