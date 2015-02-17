<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Items Orders
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-orders
 */
if(!isset($_POST['rec_id']) || !isset($_POST['trans']) || !isset($_POST['cid']))
	die('alert(\'Invalid request\')');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']); 
define('READ_ONLY_SESSION',true);
require_once('../../../../../include.php');
ModuleManager::load_modules();

$id = json_decode($_POST['rec_id']);
$id = explode('/',$id,2);
$trans_id = json_decode($_POST['trans']);
if (!is_numeric($id[0])) {
	$new_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'item_name', $id[0]);
	if (!is_numeric($new_id)) {
//		if ($id) 
//			die('alert("'.__('Item \"%s\" not found', array($id)).'");');
//		else 
			die('');
	}
	$id[0] = $new_id;
}
if (!is_numeric($trans_id)) die('');
$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$id[0]);

if (!Acl::is_user() || !Utils_RecordBrowserCommon::get_access('premium_warehouse_items', 'view', $rec)) die('Unauthorized access');

$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$trans_id);

$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id[0],'warehouse'=>$trans['warehouse'],'!quantity'=>0));
$location_id = array_shift($location_id);
if (!isset($location_id) || !$location_id)
	$location_id = null;

$ja = array();
$js = '';
$ja['description'] = $rec['description'];
if ($trans['transaction_type']<2) {
    $ja['last_item_price'] = $rec['last_purchase_price'];
    $ja['tax_rate'] = $rec['tax_rate'];

    if(!isset($id[1]) || !$id[1] || ModuleManager::is_installed('Premium/Warehouse/DrupalCommerce')<0) {
        $price = '';
        $use_sales_or_purchase_price = Variable::get('premium_warehouse_use_last_price', false);
        if ($use_sales_or_purchase_price) {
            if ($trans['transaction_type'] == 0) { // purchase
                $price = & $rec['last_purchase_price'];
            } else { // sale
                $price = & $rec['last_sale_price'];
            }
        }
        $price = Utils_CurrencyFieldCommon::get_values($price);
        if (!$price[0]) {
            if ($trans['transaction_type'] == 0) { // purchase
                $price = & $rec['cost'];
            } else { // sale
                $price = & $rec['net_price'];
            }
        }
        $price_p = $price;
        unset($price); // destroy reference
	$price = Utils_CurrencyFieldCommon::get_values($price_p);
    } else {
        $price = Utils_RecordBrowserCommon::get_record('premium_ecommerce_prices',$id[1]);
        $ja['tax_rate'] = $price['tax_rate'];
        $price = array($price['gross_price']*100/(100+Data_TaxRatesCommon::get_tax_rate($price['tax_rate'])),$price['currency']);
    }
    $cost = Utils_CurrencyFieldCommon::get_values($rec['cost']);

	$gross_price = Utils_CurrencyFieldCommon::get_values(Utils_CurrencyFieldCommon::format_default($price[0]*(100+Data_TaxRatesCommon::get_tax_rate($rec['tax_rate']))/100, $price[1]));
    $ja['net_price'] = implode(Utils_currencyFieldCommon::get_decimal_point(),explode('.',$price[0]));
    $ja['__net_price__currency'] = $price[1];
    $ja['unit_price'] = $ja['net_price'];
    $ja['__unit_price__currency'] = $price[1];
    $ja['gross_price'] = implode(Utils_currencyFieldCommon::get_decimal_point(),explode('.',$gross_price[0]));
    $ja['__gross_price__currency'] = $gross_price[1];
    $ja['cost'] = implode(Utils_currencyFieldCommon::get_decimal_point(),explode('.',$cost[0]));
    $ja['__cost__currency'] = $cost[1];
    $ja['markup_discount_rate'] = 0;

	$js .= 'jq("#quantity").css("display","inline");';
	$js .= 'if(!jq("#quantity").val()){jq("#quantity").val(1);';
	$js .= 'if(typeof(set_serials_based_on_quantity)!="undefined") set_serials_based_on_quantity(-1);}';
}
if ($trans['transaction_type']==2) {
	if ($location_id!==null) $js .= 'if(!$("credit").value)$("debit").style.display="inline";';
	else $js .= '$("debit").style.display="none";';
	$js .= 'if(!$("debit").value)$("credit").style.display="inline";';
}
if ($trans['transaction_type']==3) {
	$js .= 'var new_opts={';
	$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id[0], '!quantity'=>0, 'warehouse'=>$trans['warehouse']), array(), array('serial'=>'ASC'));
	$first = true;
	foreach ($locs as $k=>$v) {
		if (!$first) $js .= ',';
		$first = false;
		$js .= '"'.$v['id'].'":"'.Premium_Warehouse_Items_LocationCommon::mark_used($v['used']).$v['serial'].'"';
	}
	$js .= '};';
}
if ($trans['transaction_type']==0 && !$trans['payment']) {
	$js .= 'if($("serials_section"))item_type_changed('.$rec['item_type'].');';
}

foreach ($ja as $id => $value) {
    $js .= 'jq("#' . $id . '").val("' . Epesi::escapeJS($value) . '");' . "\n";
}
print($js);
?>