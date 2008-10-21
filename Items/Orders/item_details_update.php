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
$js .= '$("price").value="'.$rec['net_price'].'";';
$js .= '$("item_name").value="'.$rec['item_name'].'";';
if ($rec['single_pieces']) {
	$js .= '$("quantity").style.display="none";';
	$js .= '$("serial").style.display="inline";';
	$js .= '$("quantity").value=1;';
} else {
	$js .= '$("quantity").style.display="inline";';
	$js .= '$("serial").style.display="none";';
	$js .= '$("serial").value="";';
}

print(json_encode($js));
?>