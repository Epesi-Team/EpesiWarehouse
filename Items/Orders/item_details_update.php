<?php
if(!isset($_POST['rec_id']) || !isset($_POST['trans']))
	die('alert(\'Invalid request\')');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']); 
require_once('../../../../../include.php');
ModuleManager::load_modules();

$id = trim($_POST['rec_id'], '"');
$trans_id = trim($_POST['trans'], '"');
if (!is_numeric($id)) die(json_encode(''));
if (!is_numeric($trans_id)) die(json_encode(''));
$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$id);
$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$trans_id);

$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id,'warehouse'=>$trans['warehouse'],'!quantity'=>0));
$location_id = array_shift($location_id);
if (!isset($location_id) || !$location_id)
	$location_id = null;

$js = '';
$js .= '$("item_name").value="'.$rec['item_name'].'";';
if ($trans['transaction_type']<2) {
	$js .= '$("tax_rate").value="'.$rec['tax_rate'].'";';
	$js .= 'if($("net_price"))$("net_price").value="'.($trans['transaction_type']==0?(isset($rec['last_purchase_price'])&&$rec['last_purchase_price']?$rec['last_purchase_price']:$rec['cost']):(isset($rec['last_sale_price'])&&$rec['last_sale_price']?$rec['last_sale_price']:$rec['net_price'])).'";';
	if ($rec['item_type']==1) {
		$js .= '$("quantity").style.display="none";';
		$js .= '$("serial").style.display="inline";';
		$js .= '$("quantity").value=1;';
		$js .= 'focus_by_id("serial");';
		if ($trans['transaction_type']==1) {
			$js .= 'var new_opts={';
			$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id, '!quantity'=>0, 'warehouse'=>$trans['warehouse'], 'rental_item'=>0), array(), array('serial'=>'ASC'));
			$first = true;
			foreach ($locs as $k=>$v) {
				if (!$first) $js .= ',';
				$first = false;
				$js .= '"'.$v['serial'].'":"'.$v['serial'].'"';
			}
			$js .= '};';
			$js .= 'var obj=$("serial");';
			$js .= 'var opts=obj.options;';
			$js .= 'opts.length=0;';
			$js .= 'for(y in new_opts) {';
			$js .= 'opts[opts.length] = new Option(new_opts[y],y);';
			$js .= '}';
		}
	} else {
		$js .= '$("quantity").style.display="inline";';
		$js .= '$("serial").style.display="none";';
		$js .= '$("serial").value="";';
		$js .= 'if(!$("quantity").value)$("quantity").value=1;';
		$js .= 'focus_by_id("quantity");';
	}
}
if ($trans['transaction_type']==2) {
	$js .= '$("quantity").value=1;';
	$js .= '$("serial").style.display="none";';
	if ($rec['item_type']==1) {
		$js .= '$("serial").style.display="none";';
		$js .= '$("serial_debit").style.display="none";';
		$js .= '$("'.Utils_RecordBrowserCommon::get_calcualted_id('premium_warehouse_items_orders_details', 'credit', null).'").innerHTML="'.Epesi::escapeJS('<input type="radio" name="order_details_credit_or_debit" value="credit" onclick="$(\'serial\').style.display=\'inline\';$(\'serial_debit\').style.display=\'none\';" />').'";';
		if ($location_id!==null) $js .= '$("'.Utils_RecordBrowserCommon::get_calcualted_id('premium_warehouse_items_orders_details', 'debit', null).'").innerHTML="'.Epesi::escapeJS('<input type="radio" name="order_details_credit_or_debit" value="debit" onclick="$(\'serial\').style.display=\'none\';$(\'serial_debit\').style.display=\'inline\';" />').'";';
		else $js .= '$("'.Utils_RecordBrowserCommon::get_calcualted_id('premium_warehouse_items_orders_details', 'debit', null).'").innerHTML="";';
		$js .= 'var new_opts={';
		$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id, '!quantity'=>0, 'warehouse'=>$trans['warehouse']), array(), array('serial'=>'ASC'));
		$first = true;
		foreach ($locs as $k=>$v) {
			if (!$first) $js .= ',';
			$first = false;
			$js .= '"'.$v['serial'].'":"'.$v['serial'].'"';
		}
		$js .= '};';
		$js .= 'var obj=$("serial_debit");';
		$js .= 'var opts=obj.options;';
		$js .= 'opts.length=0;';
		$js .= 'for(y in new_opts) {';
		$js .= 'opts[opts.length] = new Option(new_opts[y],y);';
		$js .= '}';
	} else {
		$js .= '$("serial_debit").style.display="none";';
		$js .= '$("'.Utils_RecordBrowserCommon::get_calcualted_id('premium_warehouse_items_orders_details', 'credit', null).'").innerHTML="'.Epesi::escapeJS('<input type="text" name="order_details_credit" id="order_details_credit" value="" onkeyup="if(this.value)$(\'order_details_debit\').style.display=\'none\';else $(\'order_details_debit\').style.display=\'inline\';" />').'";';
		$js .= '$("'.Utils_RecordBrowserCommon::get_calcualted_id('premium_warehouse_items_orders_details', 'debit', null).'").innerHTML="'.Epesi::escapeJS('<input type="text" name="order_details_debit" id="order_details_debit" value="" onkeyup="if(this.value)$(\'order_details_credit\').style.display=\'none\';else $(\'order_details_credit\').style.display=\'inline\';" />').'";';
		if ($location_id!==null) $js .= '$("order_details_debit").style.display="inline";';
		else $js .= '$("order_details_debit").style.display="none";';
	}
}
if ($trans['transaction_type']==3) {
	$js .= 'var new_opts={';
	$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id, '!quantity'=>0, 'warehouse'=>$trans['warehouse'], 'rental_item'=>1), array(), array('serial'=>'ASC'));
	$first = true;
	foreach ($locs as $k=>$v) {
		if (!$first) $js .= ',';
		$first = false;
		$js .= '"'.$v['serial'].'":"'.$v['serial'].'"';
	}
	$js .= '};';
	$js .= 'var obj=$("serial");';
	$js .= 'var opts=obj.options;';
	$js .= 'opts.length=0;';
	$js .= 'for(y in new_opts) {';
	$js .= 'opts[opts.length] = new Option(new_opts[y],y);';
	$js .= '}';
}

print($js);
?>