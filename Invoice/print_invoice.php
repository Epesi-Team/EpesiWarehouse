<?php
/**
 * Download invoice
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @version 1.0
 * @license MIT
 * @package epesi-utils
 * @subpackage RecordBrowser
 */
if (!isset($_REQUEST['cid']) || !isset($_REQUEST['record_id'])) die('Invalid usage - missing param');
$cid = $_REQUEST['cid'];
$order_id = $_REQUEST['record_id'];
$style = & $_REQUEST['print_template'];

if (!is_numeric($order_id)) die('Invalid usage');
define('CID', $cid);
define('READ_ONLY_SESSION', true);
require_once('../../../../include.php');
ModuleManager::load_modules();
require_once 'Printer.php';

$printer = new Premium_Warehouse_Invoice_Printer();
$buffer = $printer->print_pdf($order_id, $style);

header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($buffer));
header('Cache-Control: no-cache');
header('Content-disposition: inline; filename="' . $printer->get_printed_filename() . '"');

print($buffer);
?>