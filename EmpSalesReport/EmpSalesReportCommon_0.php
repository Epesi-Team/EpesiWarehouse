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
 * @subpackage warehouse-empsalesreport
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_EmpSalesReportCommon extends ModuleCommon {
	public static function menu() {
		if (!Base_AclCommon::i_am_admin()) return;
		return array(_M('Reports')=>array('__submenu__'=>1, _M('Employee Sales')=>array()));	
	}
}
?>