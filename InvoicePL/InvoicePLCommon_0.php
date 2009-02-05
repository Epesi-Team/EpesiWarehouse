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

class Premium_Warehouse_InvoicePLCommon extends ModuleCommon {
	public static function invoice_pl_addon_parameters($record) {
		Base_ActionBarCommon::add('print', 'Print Invoice', 'href="modules/Premium/Warehouse/InvoicePL/print_invoice.php?'.http_build_query(array('record_id'=>$record['id'], 'cid'=>CID)).'"');
		return array('show'=>false);
	}
}
?>
