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
ini_set("memory_limit","512M");

if(!Acl::is_user()) die('Not logged in');

$dist = Utils_RecordBrowserCommon::get_record('premium_warehouse_distributor', $_GET['id']);
$plugin = Premium_Warehouse_WholesaleCommon::get_plugin($dist['plugin']);

$res = $plugin->update_from_file($_GET['file'], $dist);

if ($res===true) { 
	$time = time();
	Utils_RecordBrowserCommon::update_record('premium_warehouse_distributor', $_GET['id'], array('last_update'=>$time));
	print('<script>parent.$("_last_update__data").innerHTML="'.Base_RegionalSettingsCommon::time2reg($time,'without_seconds').'";</script>');
}

// TODO: cleanup stuff
?>
