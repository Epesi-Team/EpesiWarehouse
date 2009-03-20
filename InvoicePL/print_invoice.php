<?php
/**
 * Download file
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @version 1.0
 * @license MIT
 * @package epesi-utils
 * @subpackage RecordBrowser
 */
if(!isset($_REQUEST['cid']) || !isset($_REQUEST['record_id'])) die('Invalid usage - missing param');
$cid = $_REQUEST['cid'];
$order_id = $_REQUEST['record_id'];

if (!is_numeric($order_id)) die('Invalid usage');
define('CID', $cid);
require_once('../../../../include.php');
ModuleManager::load_modules();

$tcpdf = Libs_TCPDFCommon::new_pdf();

$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $order_id);
$warehouse = Utils_RecordBrowserCommon::get_record('premium_warehouse', $order['warehouse']);
$company = CRM_ContactsCommon::get_company(CRM_ContactsCommon::get_main_company());

if (!$order['invoice_number']) {
	$order['invoice_number'] = DB::GetOne('SELECT MAX(f_invoice_number) FROM premium_warehouse_items_orders_data_1 WHERE f_warehouse=%d AND f_transaction_type=%d', array($order['warehouse'], $order['transaction_type']));
	$order['invoice_number']++;
	Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $order_id, array('invoice_number'=>$order['invoice_number']));
}

$order['invoice_id'] = str_pad($order['invoice_number'], 4, '0', STR_PAD_LEFT).'/'.date('Y',strtotime($order['transaction_date']));
$order['employee_name'] = CRM_ContactsCommon::contact_format_no_company(CRM_ContactsCommon::get_contact($order['employee']));
$order['payment_type_label'] = Utils_CommonDataCommon::get_value('Premium_Items_Orders_Payment_Types/'.$order['payment_type'],true);
$order['terms_label'] = Utils_CommonDataCommon::get_value('Premium_Items_Orders_Terms/'.$order['terms'],true);

Libs_TCPDFCommon::prepare_header($tcpdf,'Faktura VAT nr. '.$order['invoice_id'], '', false);
Libs_TCPDFCommon::add_page($tcpdf);

$buffer = '';

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $order);
$theme->assign('warehouse', $warehouse);
$theme->assign('company', $company);
$theme->assign('date', date('Y-m-d'));
ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_top');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$order_id));
$lp = 1;

$gross_total_sum = array();
$net_total_sum = array();
$tax_total_sum = array();

foreach ($items as $k=>$v) {
	$items[$k]['item_details'] = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v['item_name']);
	$items[$k]['gross_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['gross_price']);
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['net_price']);

	$gross_total = $items[$k]['gross_price'][0]*$items[$k]['quantity'];
	$net_total = $items[$k]['net_price'][0]*$items[$k]['quantity'];
	$tax_total = $gross_total - $net_total;

	$items[$k]['gross_total'] = Utils_CurrencyFieldCommon::format($gross_total, $items[$k]['gross_price'][1]);
	$items[$k]['net_total'] = Utils_CurrencyFieldCommon::format($net_total, $items[$k]['gross_price'][1]);
	$items[$k]['tax_total'] = Utils_CurrencyFieldCommon::format($tax_total, $items[$k]['gross_price'][1]);

	$currency = $items[$k]['gross_price'][1];

	if (!isset($gross_total_sum[$currency])) {
		$gross_total_sum[$currency] = 0;
		$net_total_sum[$currency] = 0;
		$tax_total_sum[$currency] = 0;
	}
	$gross_total_sum[$currency] += $gross_total;
	$net_total_sum[$currency] += $net_total;
	$tax_total_sum[$currency] += $tax_total;
	
	$items[$k]['gross_price'] = Utils_CurrencyFieldCommon::format($items[$k]['gross_price'][0], $items[$k]['gross_price'][1]);
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::format($items[$k]['net_price'][0], $items[$k]['net_price'][1]);

	$theme = Base_ThemeCommon::init_smarty();
	$theme->assign('details', $items[$k]);
	$theme->assign('lp', $lp);
	ob_start();
	Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_table_row');
	$html = ob_get_clean();
	
	$html = Libs_TCPDFCommon::stripHTML($html);
	Libs_TCPDFCommon::writeHTML($tcpdf, $html);
	$lp++;
}

/************************ summary **************************/

foreach ($gross_total_sum as $k=>$v) {
	$gross_total_sum[$k] = Utils_CurrencyFieldCommon::format($gross_total_sum[$k], $k);
	$net_total_sum[$k] = Utils_CurrencyFieldCommon::format($net_total_sum[$k], $k);
	$tax_total_sum[$k] = Utils_CurrencyFieldCommon::format($tax_total_sum[$k], $k);
}

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('gross_total', $gross_total_sum);
$theme->assign('net_total', $net_total_sum);
$theme->assign('tax_total', $tax_total_sum);
ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_summary');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

/******************** bottom *************************/

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $order);
$theme->assign('total', implode(', ',$gross_total_sum));
ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_bottom');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

$buffer = Libs_TCPDFCommon::output($tcpdf);

session_commit();

header('Content-Type: application/pdf');
header('Content-Length: '.strlen($buffer));
header('Content-disposition: attachement; filename="faktura_'.$order['id'].'.pdf"');

print($buffer);
?>
