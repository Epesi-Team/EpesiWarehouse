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
if(!isset($_REQUEST['cid']) || !isset($_REQUEST['record_id'])) die('Invalid usage - missing param');
$cid = $_REQUEST['cid'];
$order_id = $_REQUEST['record_id'];

if (!is_numeric($order_id)) die('Invalid usage');
define('CID', $cid);
define('READ_ONLY_SESSION',true);
require_once('../../../../include.php');
ModuleManager::load_modules();

//Base_ThemeCommon::install_default_theme('Premium_Warehouse_Invoice');

$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $order_id);
$style = Variable::get('premium_warehouse_invoice_style', false);
if (!$style) $style = 'US';

if (!Acl::is_user() || !Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders', 'view', $order)) die('Unauthorized access');

$tcpdf = Libs_TCPDFCommon::new_pdf();

$warehouse = Utils_RecordBrowserCommon::get_record('premium_warehouse', $order['warehouse']);
$company = CRM_ContactsCommon::get_company(CRM_ContactsCommon::get_main_company());

if ($order['transaction_type']==1) {
	if (!$order['invoice_number']) {
		$order['invoice_number'] = Premium_Warehouse_InvoiceCommon::generate_invoice_number($order);
	}
	$order['proforma_id'] = str_pad($order['id'], 4, '0', STR_PAD_LEFT);
	$order['invoice_id'] = Premium_Warehouse_InvoiceCommon::format_invoice_number($order['invoice_number'], $order);
//	$header = 'Faktura VAT nr. '.$order['invoice_id'];
}

if ($order['transaction_type']==0) {
	$order['po_id'] = Premium_Warehouse_InvoiceCommon::format_invoice_number($order['invoice_number'], $order);
	$order['invoice_id'] = $order['invoice_number'];
//	$header = 'Zamówienie '.$order['po_id'];
}

if (!$order['invoice_print_date']) {
	$order['invoice_print_date'] = date('Y-m-d');
	Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $order['id'], array('invoice_print_date'=>$order['invoice_print_date']));
}

$order['employee_name'] = CRM_ContactsCommon::contact_format_no_company(CRM_ContactsCommon::get_contact($order['employee']));
$order['payment_type_label'] = Utils_CommonDataCommon::get_value('Premium_Items_Orders_Payment_Types/'.$order['payment_type'],true);
$order['terms_label'] = Utils_CommonDataCommon::get_value('Premium_Items_Orders_Terms/'.$order['terms'],true);
if (!$order['company_name']) $order['company_name'] = $order['first_name'].' '.$order['last_name'];
$order['shipment_type'] = Utils_RecordBrowserCommon::get_val('premium_warehouse_items_orders', 'shipment_type', $order, true);

if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
	$recs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_orders', array('transaction_id'=>$order['id']));
	$order['comments'] = array();
	foreach ($recs as $k=>$v) {
		$order['comments'][] = $v['comment'];
	}
}

Libs_TCPDFCommon::prepare_header($tcpdf,null, '', false);
Libs_TCPDFCommon::add_page($tcpdf);

$buffer = '';

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $order);
$theme->assign('warehouse', $warehouse);
$theme->assign('company', $company);
$theme->assign('date', $order['invoice_print_date']);

$labels = array(
	'po' => 'Purchase Order no.',
	'receipt' => 'Receipt no.',
	'invoice' => 'Invoice no.',
	'order' => 'Order no.',
	'copy' => 'ORIGINAL | COPY',
	'tel' => 'tel.',
	'fax' => 'fax.',
	'sale_date' => 'Date:',
	'seller' => 'Sold from:',
	'seller_address' => 'Address:',
	'seller_id_number' => 'TIN:',
	'buyer' => 'Sold to:',
	'buyer_address' => 'Address:',
	'buyer_id_number' => 'SSN:',
	'shipment_type' => 'Shipment Type:',
	'shipping_to' => 'Shipping to:',
	'shipping_address' => 'Address:',
	'payment_method' => 'Payment method:',
	'due_date' => 'due date:',
	'bank' => 'BANK:',
	'cc_info' => 'Payment info:',
	'no' => 'Item No.',
	'item_name' => 'Item/service name',
	'classification' => 'NAPCS',
	'quantity' => 'qty',
	'units' => 'units',
	'net_price' => 'Net price',
	'tax_rate' => 'Tax rate',
	'gorss_value' => 'Gross value',
	'net_value' => 'Net value',
	'tax_value' => 'Tax value',
	'sku' => 'SKU',
	'comments' => 'Comments'
);
foreach ($labels as $k=>$v)
	$labels[$k] = Base_LangCommon::ts('Premium_Warehouse_Invoice', $v);
$theme->assign('labels', $labels);

$file = Libs_TCPDFCommon::get_logo_filename();
if (file_exists($file))
    $theme->assign('logo', '<img src="'.$file.'" />');

$paid = array();
$amount_due = array();

if (ModuleManager::is_installed('Premium_Payments')>=0) {
	$payments = Utils_RecordBrowserCommon::get_records('premium_payments', array('record_type'=>'premium_warehouse_items_orders', 'record_id'=>$order['id']));
	foreach ($payments as $k=>$v) {
		$payments[$k]['amount_label'] = Utils_CurrencyFieldCommon::format($v['amount']);
		$payments[$k]['card_number'] = str_pad(substr($payments[$k]['card_number'],-4),strlen($payments[$k]['card_number']),'*',STR_PAD_LEFT);
		$payments[$k]['cvc_cvv'] = '***';
		$p = Utils_CurrencyFieldCommon::get_values($v['amount']);
		if (!isset($paid[$p[1]])) $paid[$p[1]] = 0;
		if ($v['status']==2) $paid[$p[1]] += $p[0];
	}
	$theme->assign('payments', $payments);
}

ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/top');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$order_id));
$lp = 1;

$gross_total_sum = array();
$net_total_sum = array();
$tax_total_sum = array();

foreach ($items as $k=>$v) {
	$tax = Data_TaxRatesCommon::get_tax_rate($items[$k]['tax_rate']);
	$items[$k]['item_details'] = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v['item_name']);
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['net_price']);
	$items[$k]['gross_price'] = array();
	$items[$k]['gross_price'][1] = $items[$k]['net_price'][1];
	$items[$k]['gross_price'][0] = round((100+$tax)*$items[$k]['net_price'][0]/100, Utils_CurrencyFieldCommon::get_precission($items[$k]['net_price'][1])); 

	$gross_total = $items[$k]['gross_price'][0]*$items[$k]['quantity'];
	$net_total = $items[$k]['net_price'][0]*$items[$k]['quantity'];
	$tax_total = $gross_total - $net_total;

	$items[$k]['gross_total'] = Utils_CurrencyFieldCommon::format($gross_total, $items[$k]['gross_price'][1]);
	$items[$k]['net_total'] = Utils_CurrencyFieldCommon::format($net_total, $items[$k]['gross_price'][1]);
	$items[$k]['tax_total'] = Utils_CurrencyFieldCommon::format($tax_total, $items[$k]['gross_price'][1]);

	$currency = $items[$k]['gross_price'][1];

	if (!isset($gross_total_sum[$currency])) {
		$gross_total_sum[$currency] = array();
		$net_total_sum[$currency] = array();
		$tax_total_sum[$currency] = array();
	}
	if (!isset($gross_total_sum[$currency][$tax])) {
		$gross_total_sum[$currency][$tax] = 0;
		$net_total_sum[$currency][$tax] = 0;
		$tax_total_sum[$currency][$tax] = 0;
	}
	$gross_total_sum[$currency][$tax] += $gross_total;
	$net_total_sum[$currency][$tax] += $net_total;
	$tax_total_sum[$currency][$tax] += $tax_total;
	
	$items[$k]['gross_price'] = Utils_CurrencyFieldCommon::format($items[$k]['gross_price'][0], $items[$k]['gross_price'][1]);
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::format($items[$k]['net_price'][0], $items[$k]['net_price'][1]);
	$items[$k]['tax_name'] = $tax.'%';
	$items[$k]['units'] = Base_LangCommon::ts('Premium_Warehouse_Invoice', 'ea.');

	$theme = Base_ThemeCommon::init_smarty();
	$theme->assign('details', $items[$k]);
	$theme->assign('lp', $lp);
	ob_start();
	Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/table_row');
	$html = ob_get_clean();
	
	$html = Libs_TCPDFCommon::stripHTML($html);
	Libs_TCPDFCommon::writeHTML($tcpdf, $html);
	$lp++;
}

$additional_cost = array();
$additional_cost['shipment'] = Utils_CurrencyFieldCommon::get_values($order['shipment_cost']);
$additional_cost['handling'] = Utils_CurrencyFieldCommon::get_values($order['handling_cost']);

foreach (array('shipment'=>Base_LangCommon::ts('Premium_Warehouse_Invoice','Shipment'), 'handling'=>Base_LangCommon::ts('Premium_Warehouse_Invoice','Handling')) as $k=>$v) {
	if ($additional_cost[$k][0]) {
		$theme = Base_ThemeCommon::init_smarty();

		$gross_val = $additional_cost[$k][0]; 
		
		if ($company['country']=='US')
			$vat = 0;
		else {
			$vat = 22;
			if ($order['transaction_date']>='2011-01-01') $vat = 23;
		}
		$net_val = round($additional_cost[$k][0]/(1+$vat/100),2);
		$tax_val = $gross_val-$net_val;
		$tax = Utils_CurrencyFieldCommon::format($tax_val, $additional_cost[$k][1]);
		$gross = Utils_CurrencyFieldCommon::format($gross_val, $additional_cost[$k][1]);
		$net = Utils_CurrencyFieldCommon::format($net_val, $additional_cost[$k][1]);

		if (!isset($gross_total_sum[$additional_cost[$k][1]])) {
			$gross_total_sum[$additional_cost[$k][1]] = array();
			$net_total_sum[$additional_cost[$k][1]] = array();
			$tax_total_sum[$additional_cost[$k][1]] = array();
		}
		if (!isset($gross_total_sum[$additional_cost[$k][1]][$vat])) {
			$gross_total_sum[$additional_cost[$k][1]][$vat] = 0;
			$net_total_sum[$additional_cost[$k][1]][$vat] = 0;
			$tax_total_sum[$additional_cost[$k][1]][$vat] = 0;
		}

		$gross_total_sum[$additional_cost[$k][1]][$vat] += $gross_val;
		$net_total_sum[$additional_cost[$k][1]][$vat] += $net_val;
		$tax_total_sum[$additional_cost[$k][1]][$vat] += $tax_val;

		$details = array(
				'item_details'=>array('item_name'=>$v),
				'quantity'=>1,
				'net_price'=>$net,
				'tax_name'=>$vat.'%',
				'gross_total'=>$gross,
				'net_total'=>$net,
				'tax_total'=>$tax,
				'units'=>Base_LangCommon::ts('Premium_Warehouse_Invoice', 'ea.'),
			);
		$theme->assign('details', $details);
		$theme->assign('lp', $lp);
		ob_start();
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/table_row');
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
	$gross_total_sum_f[$k] = array();
	$net_total_sum_f[$k] = array();
	$tax_total_sum_f[$k] = array();
	$sum = array('gross'=>0,'net'=>0,'tax'=>0);
	foreach($v as $vat=>$vv) {
		$sum['gross'] += $gross_total_sum[$k][$vat];
		$gross_total_sum_f[$k][$vat.'%'] = Utils_CurrencyFieldCommon::format($gross_total_sum[$k][$vat], $k);
		$sum['net'] += $net_total_sum[$k][$vat];
		$net_total_sum_f[$k][$vat.'%'] = Utils_CurrencyFieldCommon::format($net_total_sum[$k][$vat], $k);
		$sum['tax'] += $tax_total_sum[$k][$vat];
		$tax_total_sum_f[$k][$vat.'%'] = Utils_CurrencyFieldCommon::format($tax_total_sum[$k][$vat], $k);
	}
	$gross_total_sum[$k]['x'] = $sum['gross'];
	$net_total_sum[$k]['x'] = $sum['net'];
	$tax_total_sum[$k]['x'] = $sum['tax'];
	$gross_total_sum_f[$k]['x'] = Utils_CurrencyFieldCommon::format($sum['gross'], $k);
	if (!isset($amount_due[$k])) $amount_due[$k]=0;
	$amount_due[$k] += $sum['gross'];
	$net_total_sum_f[$k]['x'] = Utils_CurrencyFieldCommon::format($sum['net'], $k);
	$tax_total_sum_f[$k]['x'] = Utils_CurrencyFieldCommon::format($sum['tax'], $k);
}

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('gross_total', $gross_total_sum_f);
$theme->assign('net_total', $net_total_sum_f);
$theme->assign('tax_total', $tax_total_sum_f);
ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/summary');
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
$total = array();
foreach($gross_total_sum_f as $gr) $total[] = $gr['x'];
foreach($amount_due as $k=>$v) {
	if (!isset($paid[$k])) $paid[$k] = 0;
	$v -= $paid[$k];
	$amount_due[$k] = Utils_CurrencyFieldCommon::format($v, $k);
}
foreach($paid as $k=>$v) $paid[$k] = Utils_CurrencyFieldCommon::format($v, $k);
$theme->assign('total', implode(', ',$total));
$theme->assign('paid', implode(', ',$paid));
$theme->assign('amount_due', implode(', ',$amount_due));
$PLN = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s', array('PLN'));
if (is_numeric($PLN) && isset($gross_total_sum[$PLN]['x'])) $wording = cash2word($gross_total_sum[$PLN]['x']);
else $wording = '';
$theme->assign('total_word', $wording);

$labels = array(
	'amount_due'=>'AMOUNT DUE:',
	'amount_paid'=>'AMOUNT PAID:',
	'total_price'=>'TOTAL PRICE:',
	'in_words'=>'IN WORDS:',
	'receiver_sig'=>'',
	'employee_sig'=>'Employee signature',
	'legal_notice'=>'legal_notice'
);
if (!$order['receipt']) {
	$labels['receiver_sig'] = 'Receiver signature';
	$labels['employee_sig'] .= '<br/>Issuing invoice';
}

foreach ($labels as $k=>$v)
	if ($v) $labels[$k] = Base_LangCommon::ts('Premium_Warehouse_Invoice', $v);
	else $labels[$k] = '';

if ($labels['legal_notice'] == 'legal_notice') $labels['legal_notice'] = '';

$theme->assign('labels', $labels);


ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/bottom');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
Libs_TCPDFCommon::writeHTML($tcpdf, $html);

$buffer = Libs_TCPDFCommon::output($tcpdf);

header('Content-Type: application/pdf');
header('Content-Length: '.strlen($buffer));
header('Content-disposition: attachement; filename="'.Base_LangCommon::ts('Premium_Warehouse_Invoice','Invoice').'_'.$order['invoice_id'].'.pdf"');

print($buffer);
?>
