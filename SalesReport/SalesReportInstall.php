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
		return true;
	}
	
	public function uninstall() {
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