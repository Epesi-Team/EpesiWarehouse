<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past
if(!isset($_GET['file']) || !isset($_GET['id']) || !is_numeric($_GET['id']))
	die('Invalid request');

define('CID',false);
define('READ_ONLY_SESSION',true);
require_once('../../../../include.php');
ModuleManager::load_modules();
@set_time_limit(0);
ini_set("memory_limit","1024M");

if(!Acl::is_user()) die('Not logged in');

$dist = Utils_RecordBrowserCommon::get_record('premium_warehouse_distributor', $_GET['id']);
$plugin = Premium_Warehouse_WholesaleCommon::get_plugin($dist['plugin']);
$params = $plugin->get_parameters();
$i = 1;
foreach ($params as $k=>$v) {
        $params[$k] = $dist['param'.$i];
        $i++;
}

$file = preg_match('/,/',$_GET['file'])?explode(',',$_GET['file']):$_GET['file'];
$res = $plugin->update_from_file($file, $dist, $params);

if ($res===true) { 
	$time = time();
	Utils_RecordBrowserCommon::update_record('premium_warehouse_distributor', $_GET['id'], array('last_update'=>$time));
	print('<script>parent.$("_last_update__data").innerHTML="'.Base_RegionalSettingsCommon::time2reg($time,'without_seconds').'";</script>');
	flush();
}

$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');
if(is_array($file)) {
    foreach($file as $k=>$v) {
        $matches = array();
        preg_match('/scan\_([0-9]+)_'.$k.'\.tmp/', $v, $matches);
        unlink($dir.'current_scan_'.$matches[1].'_'.$k.'.tmp');
    }
} else {
    $matches = array();
    preg_match('/scan\_([0-9]+)\.tmp/', $_GET['file'], $matches);
    unlink($dir.'current_scan_'.$matches[1].'.tmp');
}
/*$its = DB::Execute('SELECT w.id as wid ,it.id as item_id FROM premium_warehouse_wholesale_items w INNER JOIN premium_warehouse_items_data_1 it ON it.f_upc=w.upc WHERE w.item_id is null AND w.upc is not null AND w.upc!=%s AND w.distributor_id=%d',array('',$dist['id']));
$auto_assoc = 0;
while($it = $its->FetchRow()) {
    DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE id=%d', array($it['item_id'], $it['wid']));
    $auto_assoc++;
}

if($auto_assoc) print('<script>alert(\'Auto-matched '.$auto_assoc.' items\');</script>');*/
print('<script>parent.$("premium_wholesale_scan_iframe").parentNode.removeChild(parent.$("premium_wholesale_scan_iframe"));</script>');

?>
