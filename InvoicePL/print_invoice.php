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
$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $order_id);

if (!Acl::is_user() || !Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders', 'view', $order)) die('Unauthorized access');

$tcpdf = Libs_TCPDFCommon::new_pdf();

$warehouse = Utils_RecordBrowserCommon::get_record('premium_warehouse', $order['warehouse']);
$company = CRM_ContactsCommon::get_company(CRM_ContactsCommon::get_main_company());

if ($order['transaction_type']==1) {
	if (!$order['invoice_number']) {
		$order['invoice_number'] = Premium_Warehouse_InvoicePLCommon::generate_invoice_number($order);
	}
	$order['invoice_id'] = str_pad($order['invoice_number'], 4, '0', STR_PAD_LEFT).'/'.date('Y',strtotime($order['transaction_date']));
//	$header = 'Faktura VAT nr. '.$order['invoice_id'];
}

if ($order['transaction_type']==0) {
	$order['po_id'] = str_pad($order['id'], 4, '0', STR_PAD_LEFT).'/'.date('Y',strtotime($order['transaction_date']));
	$order['invoice_id'] = $order['invoice_number'];
//	$header = 'Zamówienie '.$order['po_id'];
}

$order['employee_name'] = CRM_ContactsCommon::contact_format_no_company(CRM_ContactsCommon::get_contact($order['employee']));
$order['payment_type_label'] = Utils_CommonDataCommon::get_value('Premium_Items_Orders_Payment_Types/'.$order['payment_type'],true);
$order['terms_label'] = Utils_CommonDataCommon::get_value('Premium_Items_Orders_Terms/'.$order['terms'],true);

Libs_TCPDFCommon::prepare_header($tcpdf,null, '', false);
Libs_TCPDFCommon::add_page($tcpdf);

$buffer = '';

Base_ThemeCommon::install_default_theme('Premium_Warehouse_InvoicePL');

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
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['net_price']);
	$items[$k]['gross_price'] = array();
	$items[$k]['gross_price'][1] = $items[$k]['net_price'][1];
	$items[$k]['gross_price'][0] = round((100+Data_TaxRatesCommon::get_tax_rate($items[$k]['tax_rate']))*$items[$k]['net_price'][0]/100, Utils_CurrencyFieldCommon::get_precission($items[$k]['net_price'][1])); 

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
	$items[$k]['tax_name'] = Data_TaxRatesCommon::get_tax_rate($items[$k]['tax_rate']).'%';

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

$additional_cost = array();
$additional_cost['shipment'] = Utils_CurrencyFieldCommon::get_values($order['shipment_cost']);
$additional_cost['handling'] = Utils_CurrencyFieldCommon::get_values($order['handling_cost']);

foreach (array('shipment'=>'Koszt wysyłki', 'handling'=>'Opłata manipulacyjna') as $k=>$v) {
	if ($additional_cost[$k][0]) {
		$theme = Base_ThemeCommon::init_smarty();

		$gross_val = $additional_cost[$k][0]; 
		$net_val = round($additional_cost[$k][0]/1.22,2);
		$tax_val = $gross_val-$net_val;
		$tax = Utils_CurrencyFieldCommon::format($tax_val, $additional_cost[$k][1]);
		$gross = Utils_CurrencyFieldCommon::format($gross_val, $additional_cost[$k][1]);
		$net = Utils_CurrencyFieldCommon::format($net_val, $additional_cost[$k][1]);

		if (!isset($gross_total_sum[$additional_cost[$k][1]])) $gross_total_sum[$additional_cost[$k][1]] = 0;
		if (!isset($net_total_sum[$additional_cost[$k][1]])) $net_total_sum[$additional_cost[$k][1]] = 0;
		if (!isset($tax_total_sum[$additional_cost[$k][1]])) $tax_total_sum[$additional_cost[$k][1]] = 0;

		$gross_total_sum[$additional_cost[$k][1]] += $gross_val;
		$net_total_sum[$additional_cost[$k][1]] += $net_val;
		$tax_total_sum[$additional_cost[$k][1]] += $tax_val;

		$details = array(
				'item_details'=>array('item_name'=>$v),
				'quantity'=>1,
				'net_price'=>$net,
				'tax_name'=>'22%',
				'gross_total'=>$gross,
				'net_total'=>$net,
				'tax_total'=>$tax
			);
		$theme->assign('details', $details);
		$theme->assign('lp', $lp);
		ob_start();
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_table_row');
		$html = ob_get_clean();
		
		$html = Libs_TCPDFCommon::stripHTML($html);
		Libs_TCPDFCommon::writeHTML($tcpdf, $html);
		$lp++;
	}
}

/************************ summary **************************/
$gross_total_sum_f = array();
$net_total_sum_f = array();
$tax_total_sum_f = array();
foreach ($gross_total_sum as $k=>$v) {
	$gross_total_sum_f[$k] = Utils_CurrencyFieldCommon::format($gross_total_sum[$k], $k);
	$net_total_sum_f[$k] = Utils_CurrencyFieldCommon::format($net_total_sum[$k], $k);
	$tax_total_sum_f[$k] = Utils_CurrencyFieldCommon::format($tax_total_sum[$k], $k);
}

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('gross_total', $gross_total_sum_f);
$theme->assign('net_total', $net_total_sum_f);
$theme->assign('tax_total', $tax_total_sum_f);
ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_summary');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

/******************** bottom *************************/
function cash2word ($arg) {
	$word_xxx = array('','sto','dwieście','trzysta','czterysta','pięćset','sześćset','siedemset','osiemset','dziewięćset');
	$word_xx = array('','dziesięć','dwadzieścia','trzydzieści','czterdzieści','pięćdziesiąt','sześćdziesiąt','siedemdziesiąt','osiemdziesiąt','dziewięćdziesiąt');
	$word_x = array('','jeden','dwa','trzy','cztery','pięć','sześć','siedem','osiem','dziewięć');
	$word_1x = array('dziesięć','jedenaście','dwanaście','trzynaście','czternaście','piętnaście','szesnaście','siednaście','osiemnaście','dziewiętnaście');
	
	$thsd_8 = array('kwadrylion','kwadrylionów','kwadryliony');
	$thsd_7 = array('tryliard','tryliardów','tryliardy');
	$thsd_6 = array('trylion','trylionów','tryliony');
	$thsd_5 = array('biliard','biliardów','biliardy');
	$thsd_4 = array('bilion','bilionów','bilony');
	$thsd_3 = array('miliard','miliardów','miliardy');
	$thsd_2 = array('milion','milionów','miliony');
	$thsd_1 = array('tysiąc','tysięcy','tysiące');
	$thsd_0 = array('złoty','złotych','złote');
	$thsd_cents = array('grosz','groszy','grosze');

	$parts=explode('.',(string)$arg);
	if (intval($parts[0])!=0) {
		$ln=strlen($parts[0]);

		$target_length=ceil($ln/3)*3;
		$parts[0] = str_pad($parts[0], $target_length, '0', STR_PAD_LEFT);

		$treesome = (strlen($parts[0])/3)-1;
		$current_thsd = $treesome;
		$result = array();
		for($i=0;$i<=$treesome;$i++) {
			$t_tmp='thsd_'.$current_thsd;
			$current_thsd--;
			$current_part=substr($parts[0],($i*3),3);
			$current_part_desc = $word_xxx[$current_part{0}].' '. ($current_part{1}!=1 ? $word_xx[$current_part{1}].' '.$word_x[$current_part{2}] : $word_1x[$current_part{2}] );
			if(($current_part{0}==0)&&($current_part{1}==0)&&($current_part{2}==1)) $current_units=${$t_tmp}[0];
			else if (($current_part{2}>=2 && $current_part{2}<=4)&&$current_part{1}!=1) $current_units=${$t_tmp}[2];
			else $current_units=${$t_tmp}[1];
			$result[] = $current_part_desc.' '.$current_units;
		}
		$result = implode(' ',$result);
	} else {
		$result = 'zero '.$thsd_0[1];
	}
	if (!isset($parts[1])) $parts[1] = '00';
	else $parts[1] = str_pad(substr($parts[1], 0, 2), 2, '0', STR_PAD_RIGHT);
	if ($parts[1]=='00') $cents = 'zero '.$thsd_cents[1];
	else {
		$cents = $parts[1]{0}!=1 ? $word_xx[$parts[1]{0}].' '.$word_x[$parts[1]{1}] : $word_1x[$parts[1]{1}];
		if(($parts[1]{0}==0)&&($parts[1]{1}==1)) $cents.=' '.$thsd_cents[0];
		else if (($parts[1]{1}>=2 && $parts[1]{1}<=4)&&$parts[1]{0}!=1) $cents.=' '.$thsd_cents[2];
		else $cents.=' '.$thsd_cents[1];
	}
	$text = $result.'  '.$cents;
	return $text;
}

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $order);
$theme->assign('total', implode(', ',$gross_total_sum_f));
$PLN = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s', array('PLN'));
if (is_numeric($PLN) && isset($gross_total_sum[$PLN])) $wording = cash2word($gross_total_sum[$PLN]);
else $wording = '';
$theme->assign('total_word', $wording);

ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_InvoicePL','invoice_form_bottom');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

$buffer = Libs_TCPDFCommon::output($tcpdf);

session_commit();

header('Content-Type: application/pdf');
header('Content-Length: '.strlen($buffer));
header('Content-disposition: attachement; filename="faktura_'.$order['invoice_id'].'.pdf"');

print($buffer);
?>
