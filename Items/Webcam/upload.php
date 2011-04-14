<?php
if(!isset($_REQUEST['cid']) || !is_numeric($_REQUEST['cid']) || !isset($_REQUEST['rid']) || !isset($_FILES['filedata'])) {
	die('invalid request');
}

//define('JS_OUTPUT',1);
define('CID',$_REQUEST['cid']);
define('READ_ONLY_SESSION',true);
require_once('../../../../../include.php');

if(!Acl::is_user()) {
	die('user not logged');
}

ModuleManager::load_modules();
if(is_numeric($_REQUEST['rid'])) {
    Utils_AttachmentCommon::add('premium_warehouse_items/'.$_REQUEST['rid'],0,Acl::get_user(),'webcam photo',$_FILES['filedata']['name'],$_FILES['filedata']['tmp_name'],array('Premium_Warehouse_ItemsCommon','search_format'),array($_REQUEST['rid']));
} else {
    $dir = DATA_DIR.'/Premium_Warehouse_Items_Webcam/'.session_id().'/'.CID.'/'.$_REQUEST['rid'];
    @mkdir($dir,0777,true);
    move_uploaded_file($_FILES['filedata']['tmp_name'],$dir.'/'.str_replace(array(',','.'),'_',microtime(true)).'.jpg');
    $dirs = scandir(DATA_DIR.'/Premium_Warehouse_Items_Webcam/');
    $sessions = DB::GetAssoc('SELECT name,1 FROM session');
    foreach($dirs as $d) {
        if($d!='.' && $d!='..') {
            if(!isset($sessions[$d]))
                @recursive_rmdir(DATA_DIR.'/Premium_Warehouse_Items_Webcam/'.$d);
        }
    }
}
?>
