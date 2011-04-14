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

header("Content-type: text/javascript");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past

if(!isset($_POST['record_id']) || !isset($_SERVER['HTTP_X_CLIENT_ID']))
	die('alert(\'Invalid request\');');

$order_id = $_REQUEST['record_id'];

if (!is_numeric($order_id)) die('alert(\'Invalid usage\');');
define('JS_OUTPUT',true);
require_once('../../../../include.php');
ModuleManager::load_modules();

$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $order_id);
if($order['invoice_print_date']) die('alert(\'Paragon został wydrukowany w dniu: '.$order['invoice_print_date'].'\');');

if (!Acl::is_user() || !Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders', 'view', $order)) die('alert(\'Unauthorized access\');');

$ret = '(function() {';
$my_rec = CRM_ContactsCommon::get_my_record();
$cash_id = 1;
$employee_name = CRM_ContactsCommon::contact_format_no_company($my_rec,true);
if(!isset($_SESSION['receipt_user_id']) || $_SESSION['receipt_user_id']!=Acl::get_user()) {
    $_SESSION['receipt_user_id'] = Acl::get_user();
    $ret .= 'if(!posnet_applet.Login("'.Epesi::escapeJS($employee_name,true,false).'",'.$cash_id.')){ alert("Błąd logowania do kasy");return;}';
} elseif(!isset($_SESSION['client']['receipt_user_id'])) {
    $_SESSION['client']['receipt_user_id'] = 1;
    $ret .= 'if(!posnet_applet.Login("'.Epesi::escapeJS($employee_name,true,false).'",'.$cash_id.',false)){ alert("Błąd logowania do kasy");return;}';
}
session_commit();

$warehouse = Utils_RecordBrowserCommon::get_record('premium_warehouse', $order['warehouse']);
$company = CRM_ContactsCommon::get_company(CRM_ContactsCommon::get_main_company());

$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$order_id));
$currency = null;
$ret2 = '';
$total = 0;
$taxes = Data_TaxRatesCommon::get_tax_details();
foreach ($items as $k=>$v) {
	$item_details = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v['item_name']);
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['net_price']);
	if(!isset($taxes[$items[$k]['tax_rate']])) 
	    die('alert(\'Nieprawidłowa stawka podatku dla przedmiotu: "'.Epesi::escapeJS($item_details['name']).'".\');');
	$gross_price = round((100+$taxes[$items[$k]['tax_rate']]['percentage'])*$items[$k]['net_price'][0]/100, Utils_CurrencyFieldCommon::get_precission($items[$k]['net_price'][1])); 
	$total = $gross_price * $items[$k]['quantity'];
	if(!isset($taxes[$items[$k]['tax_rate']]['tax_code']) || !$taxes[$items[$k]['tax_rate']]['tax_code']) 
	    die('alert(\'Stawka podatku '.$taxes[$items[$k]['tax_rate']]['percentage'].'% nie posiada poprawnego symbolu PTU.\');');
	$tax_code = $taxes[$items[$k]['tax_rate']]['tax_code'];

    if($currency!=null && $currency != $items[$k]['net_price'][1])
        die('alert(\'Nie można drukować paragonu z produktami w wielu walutach.\');');
	$currency = $items[$k]['net_price'][1];
              
    $ret2 .= 'if(!posnet_applet.AddItemToTrans("'.$item_details['item_name'].'",'.$items[$k]['quantity'].','.$gross_price.',"A")) return;';
}

$ret .= 'var cash='.$total.'; while(true){cash=prompt("Wprowadź kwotę wpłaconą przez klienta (minimum '.$total.')",cash);if(cash=="" || cash==null)break; if(isNaN(cash=parseFloat(cash))){cash="Nieprawidłowa kwota";continue;}if(cash<'.$total.'){cash="Za mała kwota";continue;}break;};if(cash==null || cash=="")return;';
$ret .= $ret2;
$ret .= 'posnet_applet.CommitTrans(cash,"#'.$order['transaction_id'].'#","EpesiBIM - systemy dla biznesu","'.$cash_id.$my_rec['first_name'][0].$my_rec['last_name'][0].'");';

print($ret.'Epesi.href("receipt_printed='.$order['id'].'","","");})()');
?>