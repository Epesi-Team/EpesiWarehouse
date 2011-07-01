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

class Premium_Warehouse_Invoice extends Module {
	public function browse_mode_details($form, $filters, $vals, $crits, $dont_hide, $rb) {
		$rb->set_search_calculated_callback(array($this, 'search_invoice_number'));
	}
	
	public function search_invoice_number($search) {
		$crits = array();
		foreach ($search as $k=>$v) {
			if (is_array($v)) $v = reset($v);
			if (strpos($k, 'invoice_number')!==false) {
				$parse = explode('/', $v);
				if (!isset($parse[2])) continue;
				$date = strtotime($parse[0].'-'.str_pad($parse[1], 2, '0', STR_PAD_LEFT).'-01');
				if (!$date) continue;
				$parse[2] = explode('-', $parse[2]);
				if (!isset($parse[2][1])) continue;
				$war = Utils_RecordBrowserCommon::get_records('premium_warehouse', array('invoice_number_code'=>$parse[2][1]));
				if (empty($war)) continue;
				$crits['receipt'] = false;
				$crits['transaction_type'] = 1;
				$crits['>=transaction_date'] = date('Y-m-d', $date);
				$crits['<=transaction_date'] = date('Y-m-t', $date);
				$crits['invoice_number'] = $parse[2][0];
				$war_id = array();
				foreach ($war as $w) $war_id[] = $w['id'];
				$crits['warehouse'] = $war_id;
				$search = array();
				break;
			}
		}
		return $crits;
	}
}
?>
