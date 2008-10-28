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
$js = '';
$js .= '$("item_name").value="'.$rec['item_name'].'";';
$js .= '$("tax_rate").value="'.$rec['tax_rate'].'";';
$js .= 'if($("net_price"))$("net_price").value="'.($trans['transaction_type']==0?$rec['cost']:$rec['net_price']).'";';
if ($rec['item_type']==1) {
	$js .= '$("quantity").style.display="none";';
	$js .= '$("serial").style.display="inline";';
	$js .= '$("quantity").value=1;';
	$js .= 'focus_by_id("serial");';
	if ($trans['transaction_type']==1) {
		$js .= 'var new_opts={';
		$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id, '!quantity'=>0, 'warehouse'=>$trans['warehouse']), array(), array('serial'=>'ASC'));
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

print(json_encode($js));
?>