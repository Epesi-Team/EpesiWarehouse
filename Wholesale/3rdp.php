<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past
if(!isset($_GET['upc']) && (!isset($_GET['mpn']) || !is_numeric($_GET['man'])))
	die('');

define('CID',false);
define('READ_ONLY_SESSION',true);
require_once('../../../../include.php');
ModuleManager::load_modules();
@set_time_limit(0);

if(!Acl::is_user()) die('forbidden');

$ret = Premium_Warehouse_eCommerceCommon::check_3rd_party_item_data($_GET['upc'],$_GET['man'],$_GET['mpn']);
foreach($ret as $name=>$langs) {
    print('<b>'.$name.'</b> - <i>'.implode(', ',$langs).'</i><br/>');
}
?>
