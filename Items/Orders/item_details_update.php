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
require_once('../../../../../include.php');
ModuleManager::load_modules();

$id = trim($_POST['rec_id'], '"');
$trans_id = trim($_POST['trans'], '"');
if (!is_numeric($id)) {
	$new_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'item_name', $id);
	if (!is_numeric($new_id)) {
//		if ($id) 
//			die('alert("'.Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Item \"%s\" not found', array($id)).'");');
//		else 
			die('');
	}
	$id = $new_id;
}
if (!is_numeric($trans_id)) die('');
$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$id);

if (!Acl::is_user() || !Utils_RecordBrowserCommon::get_access('premium_warehouse_items', 'view', $rec)) die('Unauthorized access');

$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$trans_id);

$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id,'warehouse'=>$trans['warehouse'],'!quantity'=>0));
$location_id = array_shift($location_id);
if (!isset($location_id) || !$location_id)
	$location_id = null;

$js = '';
$js .= '$("description").value="'.Epesi::escapeJS($rec['description']).'";';
if ($trans['transaction_type']<2) {
	$js .= 'if($("last_item_price"))$("last_item_price").value="'.$rec['last_purchase_price'].'";';
	$js .= 'if($("tax_rate"))$("tax_rate").value="'.$rec['tax_rate'].'";';
	$price = ($trans['transaction_type']==0?(isset($rec['last_purchase_price'])&&$rec['last_purchase_price']?$rec['last_purchase_price']:$rec['cost']):(isset($rec['last_sale_price'])&&$rec['last_sale_price']?$rec['last_sale_price']:$rec['net_price']));
	$price = Utils_CurrencyFieldCommon::get_values($price);
	$gross_price = Utils_CurrencyFieldCommon::get_values(Utils_CurrencyFieldCommon::format_default($price[0]*(100+Data_TaxRatesCommon::get_tax_rate($rec['tax_rate']))/100, $price[1]));
	$js .= 	'if($("net_price")){'.
				'obj=$("__net_price__currency");for(i=0;i<obj.options.length;i++)if(obj.options[i].value=='.$price[1].'){cur_key=i;break;}'.
				'$("net_price").value="'.implode(Utils_currencyFieldCommon::get_decimal_point(),explode('.',$price[0])).'";'.
				'$("gross_price").value="'.implode(Utils_currencyFieldCommon::get_decimal_point(),explode('.',$gross_price[0])).'";'.
				'switch_currencies(cur_key,"net_price","gross_price");'.
			'}';

	{
		$js .= 'if($("quantity"))$("quantity").style.display="inline";';
		$js .= 'if(!$("quantity").value)$("quantity").value=1;';
	}
}
if ($trans['transaction_type']==2) {
	if ($location_id!==null) $js .= '$("debit").style.display="inline";';
	else $js .= '$("debit").style.display="none";';
}
if ($trans['transaction_type']==3) {
	$js .= 'var new_opts={';
	$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id, '!quantity'=>0, 'warehouse'=>$trans['warehouse']), array(), array('serial'=>'ASC'));
	$first = true;
	foreach ($locs as $k=>$v) {
		if (!$first) $js .= ',';
		$first = false;
		$js .= '"'.$v['id'].'":"'.Premium_Warehouse_Items_LocationCommon::mark_used($v['used']).$v['serial'].'"';
	}
	$js .= '};';
}

print($js);
?>