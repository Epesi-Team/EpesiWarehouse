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

$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $order_id);
$style = Variable::get('premium_warehouse_invoice_style', false);
if (isset($_REQUEST['print_template'])) {
    $style = $_REQUEST['print_template'];
}
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

if (!$order['invoice_print_date'] && $order['status']>2) {
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

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $order);

ob_start();
@Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/footer');
$footer = ob_get_clean();

$tcpdf->setPrintHeader(false);
preg_match('/footer_height[\s]?=[\s]?([0-9]+)/i', $footer, $matches);
if (isset($matches[1])) {
	$tcpdf->setFooterMargin($matches[1]);
	$tcpdf->SetAutoPageBreak(true, $matches[1]);
}
$tcpdf->set_footer_border('');

Libs_TCPDFCommon::prepare_header($tcpdf,null, '', false, null);
Libs_TCPDFCommon::add_page($tcpdf);

$buffer = '';

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $order);
$theme->assign('warehouse', $warehouse);
$theme->assign('company', $company);
$theme->assign('date', $order['invoice_print_date']);

$labels = array(
	'po' => __('Purchase Order no.'),
	'receipt' => __('Receipt no.'),
	'invoice' => __('Invoice no.'),
	'order' => __('Order no.'),
	'copy' => __('ORIGINAL | COPY'),
	'tel' => __('Tel'),
	'fax' => __('Fax'),
	'sale_date' => __('Date'),
	'seller' => __('Sold from'),
	'seller_address' => __('Address'),
	'seller_id_number' => __('TIN'),
	'buyer' => __('Sold to'),
	'buyer_address' => __('Address'),
	'buyer_id_number' => __('SSN'),
	'shipment_type' => __('Shipment Type'),
	'shipping_to' => __('Shipping to'),
	'shipping_address' => __('Address'),
	'payment_method' => __('Payment method'),
	'due_date' => __('Due Date'),
	'bank' => __('BANK'),
	'cc_info' => __('Payment info'),
	'no' => __('Item No.'),
	'item_name' => __('Item/service name'),
	'classification' => __('NAPCS'),
	'quantity' => __('Qty'),
	'units' => __('Units'),
    'unit_price' => __('Unit Price'),
    'markup_discount_rate'=> __('Markup/Discount'),
	'net_price' => __('Net Price'),
	'tax_rate' => __('Tax Rate'),
	'gross_value' => __('Gross value'),
	'net_value' => __('Net value'),
	'tax_value' => __('Tax Value'),
	'sku' => __('SKU'),
	'comments' => __('Comments')
);

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
Libs_TCPDFCommon::writeHTML($tcpdf, $html, false);

$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$order_id));
$lp = 1;

$gross_total_sum = array();
$net_total_sum = array();
$tax_total_sum = array();

foreach ($items as $k=>$v) {
	$tax = Data_TaxRatesCommon::get_tax_rate($items[$k]['tax_rate']);
	$items[$k]['item_details'] = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v['item_name']);
	$items[$k]['net_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['net_price']);
    $items[$k]['unit_price'] = Utils_CurrencyFieldCommon::get_values($items[$k]['unit_price']);
	$items[$k]['gross_price'] = array();
	$items[$k]['gross_price'][1] = $items[$k]['net_price'][1];
	$items[$k]['gross_price'][0] = round((100+$tax)*$items[$k]['net_price'][0]/100, Utils_CurrencyFieldCommon::get_precission($items[$k]['net_price'][1])); 

	if ($order['tax_calculation']==0) {
		$gross_total = $items[$k]['gross_price'][0]*$items[$k]['quantity'];
		$net_total = $items[$k]['net_price'][0]*$items[$k]['quantity'];
		$tax_total = $gross_total - $net_total;
	} else {
		$gross_total = round((100+$tax)*$items[$k]['net_price'][0]*$items[$k]['quantity']/100, Utils_CurrencyFieldCommon::get_precission($items[$k]['net_price'][1]));
		$net_total = $items[$k]['net_price'][0]*$items[$k]['quantity'];
		$tax_total = $gross_total - $net_total;
	}


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
    $items[$k]['unit_price'] = Utils_CurrencyFieldCommon::format($items[$k]['unit_price'][0], $items[$k]['unit_price'][1]);
    $items[$k]['markup_discount_rate'] .= '%';
	$items[$k]['tax_name'] = $tax.'%';
	$items[$k]['units'] = __( 'ea.');
}

function cmp_items($a,$b) {
    return strcasecmp($a['item_details']['item_name'],$b['item_details']['item_name']);
}
uasort($items,'cmp_items');

foreach($items as $k=>$v) {
	$theme = Base_ThemeCommon::init_smarty();
	$theme->assign('details', $items[$k]);
	$theme->assign('lp', $lp);
	$theme->assign('order', $order);
	ob_start();
	Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/table_row');
	$html = ob_get_clean();
	
	$html = Libs_TCPDFCommon::stripHTML($html);
	Libs_TCPDFCommon::writeHTML($tcpdf, $html, false);
	$lp++;
}

$additional_cost = array();
$additional_cost['shipment'] = Utils_CurrencyFieldCommon::get_values($order['shipment_cost']);
$additional_cost['handling'] = Utils_CurrencyFieldCommon::get_values($order['handling_cost']);

foreach (array('shipment'=>__('Shipment'), 'handling'=>__('Handling')) as $k=>$v) {
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
				'units'=>__( 'ea.'),
			);
		$theme->assign('details', $details);
		$theme->assign('order', $order);
		$theme->assign('lp', $lp);
		ob_start();
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/table_row');
		$html = ob_get_clean();
		
		$html = Libs_TCPDFCommon::stripHTML($html);
		Libs_TCPDFCommon::writeHTML($tcpdf, $html, false);
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
$theme->assign('order', $order);
ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/summary');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
$html = explode('<!-- BREAK -->', $html);
foreach ($html as $h)
    Libs_TCPDFCommon::writeHTML($tcpdf, $h);

/******************** bottom *************************/
function cash2word_pl ($arg, $thsd_0=null, $thsd_cents=null) {
	$word_xxx = array('','sto','dwieście','trzysta','czterysta','pięćset','sześćset','siedemset','osiemset','dziewięćset');
	$word_xx = array('','dziesięć','dwadzieścia','trzydzieści','czterdzieści','pięćdziesiąt','sześćdziesiąt','siedemdziesiąt','osiemdziesiąt','dziewięćdziesiąt');
	$word_x = array('','jeden','dwa','trzy','cztery','pięć','sześć','siedem','osiem','dziewięć');
	$word_1x = array('dziesięć','jedenaście','dwanaście','trzynaście','czternaście','piętnaście','szesnaście','siedemnaście','osiemnaście','dziewiętnaście');
	
	$thsd_8 = array('kwadrylion','kwadrylionów','kwadryliony');
	$thsd_7 = array('tryliard','tryliardów','tryliardy');
	$thsd_6 = array('trylion','trylionów','tryliony');
	$thsd_5 = array('biliard','biliardów','biliardy');
	$thsd_4 = array('bilion','bilionów','bilony');
	$thsd_3 = array('miliard','miliardów','miliardy');
	$thsd_2 = array('milion','milionów','miliony');
	$thsd_1 = array('tysiąc','tysięcy','tysiące');
	if ($thsd_0===null)
		$thsd_0 = array('złoty','złotych','złote');
	if ($thsd_cents===null)
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
	$text = str_replace('  ', ' ', $text);
	return $text;
}

function cash2word_en ($number, $thsd_0=null, $thsd_cents=null) {
/*****
     * A recursive function to turn digits into words
     * Numbers must be integers from -999,999,999,999 to 999,999,999,999 inclussive.    
     *
     *  (C) 2010 Peter Ajtai
     *    This program is free software: you can redistribute it and/or modify
     *    it under the terms of the GNU General Public License as published by
     *    the Free Software Foundation, either version 3 of the License, or
     *    (at your option) any later version.
     *
     *    This program is distributed in the hope that it will be useful,
     *    but WITHOUT ANY WARRANTY; without even the implied warranty of
     *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *    GNU General Public License for more details.
     *
     *    See the GNU General Public License: <http://www.gnu.org/licenses/>.
     *
     */
    // zero is a special case, it cause problems even with typecasting if we don't deal with it here
    $max_size = pow(10,18);
	$number = (float)($number);
	$decimals = $number - floor($number);
	$number = (int)($number);
	$curr = $thsd_0[1];
    if (!$number) $string = "zero";
	elseif (is_int($number) && $number < abs($max_size)) 
   {            
        switch ($number) 
        {
            // set up some rules for converting digits to words
            case $number < 0:
                $prefix = "negative";
                $suffix = cash2word_en(-1*$number);
                $string = $prefix . " " . $suffix;
                break;
            case 1:
                $string = "one";
				$curr = $thsd_0[0];
                break;
            case 2:
                $string = "two";
                break;
            case 3:
                $string = "three";
                break;
            case 4: 
                $string = "four";
                break;
            case 5:
                $string = "five";
                break;
            case 6:
                $string = "six";
                break;
            case 7:
                $string = "seven";
                break;
            case 8:
                $string = "eight";
                break;
            case 9:
                $string = "nine";
                break;                
            case 10:
                $string = "ten";
                break;            
            case 11:
                $string = "eleven";
                break;            
            case 12:
                $string = "twelve";
                break;            
            case 13:
                $string = "thirteen";
                break;            
            // fourteen handled later
            case 15:
                $string = "fifteen";
                break;            
            case $number < 20:
                $string = cash2word_en($number%10);
                // eighteen only has one "t"
                if ($number == 18)
                {
                $suffix = "een";
                } else 
                {
                $suffix = "teen";
                }
                $string .= $suffix;
                break;            
            case 20:
                $string = "twenty";
                break;            
            case 30:
                $string = "thirty";
                break;            
            case 40:
                $string = "forty";
                break;            
            case 50:
                $string = "fifty";
                break;            
            case 60:
                $string = "sixty";
                break;            
            case 70:
                $string = "seventy";
                break;            
            case 80:
                $string = "eighty";
                break;            
            case 90:
                $string = "ninety";
                break;                
            case $number < 100:
                $prefix = cash2word_en($number-$number%10);
                $suffix = cash2word_en($number%10);
                $string = $prefix . "-" . $suffix;
                break;
            // handles all number 100 to 999
            case $number < pow(10,3):   
                // floor return a float not an integer
                $prefix = cash2word_en(intval(floor($number/pow(10,2)))) . " hundred";
                if ($number%pow(10,2)) {
					$suffix = " and " . cash2word_en($number%pow(10,2));
					$string = $prefix . $suffix;
				} else $string = $prefix;
                break;
            case $number < pow(10,6):
                // floor return a float not an integer
                $prefix = cash2word_en(intval(floor($number/pow(10,3)))) . " thousand";
                if ($number%pow(10,3)) {
					$suffix = cash2word_en($number%pow(10,3));
					$string = $prefix . " " . $suffix;
				} else $string = $prefix;
                break;
            case $number < pow(10,9):
                // floor return a float not an integer
                $prefix = cash2word_en(intval(floor($number/pow(10,6)))) . " million";
                if ($number%pow(10,6)) {
					$suffix = cash2word_en($number%pow(10,6));
					$string = $prefix . " " . $suffix;
				} else $string = $prefix;
                break;                    
            case $number < pow(10,12):
                // floor return a float not an integer
                $prefix = cash2word_en(intval(floor($number/pow(10,9)))) . " billion";
                if ($number%pow(10,9)) {
					$suffix = cash2word_en($number%pow(10,9));
					$string = $prefix . " " . $suffix;    
				} else $string = $prefix;
                break;
            case $number < pow(10,15):
                // floor return a float not an integer
                $prefix = cash2word_en(intval(floor($number/pow(10,12)))) . " trillion";
                if ($number%pow(10,12)) {
					$suffix = cash2word_en($number%pow(10,12));
					$string = $prefix . " " . $suffix;    
				}
                break;        
            // Be careful not to pass default formatted numbers in the quadrillions+ into this function
            // Default formatting is float and causes errors
            case $number < pow(10,18):
                // floor return a float not an integer
                $prefix = cash2word_en(intval(floor($number/pow(10,15)))) . " quadrillion";
                if ($number%pow(10,15)) {
					$suffix = cash2word_en($number%pow(10,15));
					$string = $prefix . " " . $suffix;    
				} else $string = $prefix;
                break;                    
        }
    } else
    {
        return "ERROR with - $number<br/> Number must be an integer between -" . number_format($max_size, 0, ".", ",") . " and " . number_format($max_size, 0, ".", ",") . " exclussive.";
    }
	if ($thsd_0!==null) {
		$string .= ' '.$curr;
	}
	if ($thsd_cents!==null) {
		$string .= ' '.cash2word_en(round($decimals*100), $thsd_cents);
	}
    return $string;
}

$theme = Base_ThemeCommon::init_smarty();
$theme->assign('order', $order);
$total = array();
foreach($gross_total_sum_f as $gr) $total[] = $gr['x'];
$total_net = array();
foreach($net_total_sum_f as $gr) $total_net[] = $gr['x'];
foreach($amount_due as $k=>$v) {
	if (!isset($paid[$k])) $paid[$k] = 0;
	$v -= $paid[$k];
	$amount_due[$k] = Utils_CurrencyFieldCommon::format($v, $k);
}
foreach($paid as $k=>$v) $paid[$k] = Utils_CurrencyFieldCommon::format($v, $k);
$theme->assign('total', implode('<br/>',$total));
$theme->assign('net_total', implode('<br/>',$total_net));
$theme->assign('paid', implode('<br/>',$paid));
$theme->assign('amount_due', implode(', ',$amount_due));
$PLN = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s', array('PLN'));
$USD = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s', array('USD'));
$EUR = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s', array('EUR'));
$wording = array();
$wording_en = array();

if (is_numeric($PLN) && isset($gross_total_sum[$PLN]['x'])) {
	$wording[] = cash2word_pl($gross_total_sum[$PLN]['x']);
	$wording_en[] = cash2word_en($gross_total_sum[$PLN]['x'], array('zloty', 'zlote'), array('grosz', 'groszy'));
}
if (is_numeric($USD) && isset($gross_total_sum[$USD]['x'])) {
	$wording[] = cash2word_pl($gross_total_sum[$USD]['x'], array('dolar','dolarów','dolary'), array('cent','centów','centy'));
	$wording_en[] = cash2word_en($gross_total_sum[$USD]['x'], array('dolar', 'dolars'), array('cent', 'cents'));
}
if (is_numeric($EUR) && isset($gross_total_sum[$EUR]['x'])) {
	$wording[] = cash2word_pl($gross_total_sum[$EUR]['x'], array('euro','euro','euro'), array('cent','centów','centy'));
	$wording_en[] = cash2word_en($gross_total_sum[$EUR]['x'], array('euro', 'euro'), array('cent', 'cent'));
}
$theme->assign('total_word', implode('<br/>',$wording));
$theme->assign('total_word_en', implode('<br/>',$wording_en));

$labels = array(
	'amount_due'=>__('AMOUNT DUE'),
	'amount_paid'=>__('AMOUNT PAID'),
	'total_price'=>__('TOTAL PRICE'),
	'in_words'=>__('IN WORDS'),
	'employee_sig'=>__('Employee signature'),
	'legal_notice'=>__('legal_notice'),
	'receiver_sig'=>''
);
if (!$order['receipt']) {
	$labels['receiver_sig'] = __('Receiver signature');
	$labels['employee_sig'] = __('Employee signature').'<br/>'.__('Issuing invoice');
}

if ($labels['legal_notice'] == 'legal_notice') $labels['legal_notice'] = '';

$theme->assign('labels', $labels);


ob_start();
Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Invoice',$style.'/bottom');
$html = ob_get_clean();

$html = Libs_TCPDFCommon::stripHTML($html);
$html = explode('<!-- BREAK -->', $html);
foreach ($html as $h)
    Libs_TCPDFCommon::writeHTML($tcpdf, $h);

$footer_y = $tcpdf->getPageHeight() - $tcpdf->getFooterMargin();
$margins = $tcpdf->getOriginalMargins();
$tcpdf->SetAutoPageBreak(false);
$tcpdf->SetXY($margins['left'], $footer_y);
Libs_TCPDFCommon::writeHTML($tcpdf, $footer, false);

$buffer = Libs_TCPDFCommon::output($tcpdf);

$print_filename = ($order['transaction_type']==0?__('Purchase Order'):($order['status']>2?__('Invoice'):__('Sales Quote'))).'_'.$order['id'].'.pdf';
header('Content-Type: application/pdf');
header('Content-Length: '.strlen($buffer));
header('Content-disposition: inline; filename="'.$print_filename.'"');

print($buffer);
?>
