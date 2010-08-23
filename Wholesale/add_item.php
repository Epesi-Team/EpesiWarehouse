<?php
ob_start();
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past
?>
<html>
<body>
<div style="font-size:9px">
<?php
if(!isset($_GET['iid']) || !is_numeric($_GET['iid'])) die();

define('CID',false);
define('READ_ONLY_SESSION',true);
require_once('../../../../include.php');
ob_end_flush();
ModuleManager::load_modules();
@set_time_limit(0);

$err = '';
if(Acl::is_user()) {
    $rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$_GET['iid']);
    if($rec) {
        print('Getting item: '.$rec['item_name'].'<br />');
        flush();

        Premium_Warehouse_eCommerceCommon::publish_warehouse_item($_GET['iid']);
        print('Done.');
    } else {
        print('no such item');
    }
} else {
    print('access forbidden');
}
?></div>
<?php
print('<script type="text/javascript">setTimeout(function(){var e=parent.$(\'wholesale_add_item_'.$_GET['iid'].'\');if(!e) return;if(e.parentNode.childNodes.length<=1)e.parentNode.parentNode.removeChild(e.parentNode);else e.parentNode.removeChild(e);},3000);</script>');
?>
</body>
</html>