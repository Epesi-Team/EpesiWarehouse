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

class Premium_Warehouse_SalesReportCommon extends ModuleCommon {
		public function menu() {
		return array('Reports'=>array('__submenu__'=>1, 'Sales by Warehouse'=>array('mode'=>'sales_by_warehouse'), 'Sales by Item'=>array('mode'=>'sales_by_item')));	
	}
}

?>