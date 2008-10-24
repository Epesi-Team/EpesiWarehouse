<?php
if(!isset($_POST['rec_id']))
	die('alert(\'Invalid request\')');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']); 
require_once('../../../../../include.php');
ModuleManager::load_modules();

$id = trim($_POST['rec_id'], '"');
$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$id);
$js = '';
$js .= 'if($("net_price"))$("net_price").value="'.$rec['net_price'].'";';
$js .= '$("item_name").value="'.$rec['item_name'].'";';
$js .= '$("tax_rate").value="'.$rec['tax'].'";';
if ($rec['item_type']==1) {
	$js .= '$("quantity").style.display="none";';
	$js .= '$("serial").style.display="inline";';
	$js .= '$("quantity").value=1;';
	$js .= 'focus_by_id("serial");';
} else {
	$js .= '$("quantity").style.display="inline";';
	$js .= '$("serial").style.display="none";';
	$js .= '$("serial").value="";';
	$js .= 'if(!$("quantity").value)$("quantity").value=1;';
	$js .= 'focus_by_id("quantity");';
}

print(json_encode($js));
?>