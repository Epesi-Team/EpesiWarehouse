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
$record_id = $_REQUEST['record_id'];

if (!is_numeric($record_id)) die('Invalid usage');
define('CID', $cid);
require_once('../../../../include.php');
ModuleManager::load_modules();

if (!Base_AclCommon::i_am_admin()) die('Invalid usage - access denied');

//
Base_ThemeCommon::install_default_theme('Premium/Warehouse/InvoicePL');
//

$tcpdf = Libs_TCPDFCommon::new_pdf();

Libs_TCPDFCommon::prepare_header($tcpdf,'something', 'and another');
Libs_TCPDFCommon::add_page($tcpdf);

$record = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $record_id);

$buffer = '';

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $record);
ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_top');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$record_id));
$lp = 1;

$gross_total_sum = 0;
$net_total_sum = 0;
$tax_total_sum = 0;

foreach ($items as $k=>$v) {
	$items[$k]['item_details'] = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v['item_name']);
	$items[$k]['gross_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['gross_price']);
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['net_price']);

	$gross_total = $items[$k]['gross_price'][0]*$items[$k]['quantity'];
	$net_total = $items[$k]['net_price'][0]*$items[$k]['quantity'];
	$tax_total = $gross_total - $net_total;

	$gross_total_sum += $gross_total;
	$net_total_sum += $net_total;
	$tax_total_sum += $tax_total;
	
	$items[$k]['gross_total'] = Utils_CurrencyFieldCommon::format($gross_total, $items[$k]['gross_price'][1], false);
	$items[$k]['net_total'] = Utils_CurrencyFieldCommon::format($net_total, $items[$k]['gross_price'][1], false);
	$items[$k]['tax_total'] = Utils_CurrencyFieldCommon::format($tax_total, $items[$k]['gross_price'][1], false);

	$currency = $items[$k]['gross_price'][1];

	$items[$k]['gross_price'] = Utils_CurrencyFieldCommon::format($items[$k]['gross_price'][0], $items[$k]['gross_price'][1], false);
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::format($items[$k]['net_price'][0], $items[$k]['net_price'][1], false);

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

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $record);
$theme->assign('gross_total', Utils_CurrencyFieldCommon::format($gross_total_sum, $currency, false));
$theme->assign('net_total', Utils_CurrencyFieldCommon::format($net_total_sum, $currency, false));
$theme->assign('tax_total', Utils_CurrencyFieldCommon::format($tax_total_sum, $currency, false));

ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_bottom');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

$buffer = Libs_TCPDFCommon::output($tcpdf);

session_commit();

header('Content-Type: application/pdf');
header('Content-Length: '.strlen($buffer));
header('Content-disposition: attachement; filename="faktura_'.$record['id'].'.pdf"');

print($buffer);
?>
