<?php
if(!isset($_GET['id']) || !$_GET['id']) die('invalid request');
    
define('CID',false);
define('READ_ONLY_SESSION',true);
if(isset($argv))
	define('EPESI_DIR','/');
require_once('../../../../../include.php');
//set_time_limit(0);
//ini_set('memory_limit', '64M');
ModuleManager::load_modules();
if (!Acl::is_user()) die('Unauthorized access');

DB::Execute('UPDATE premium_ecommerce_allegro_auctions SET active=-1 WHERE auction_id=%s',array($_GET['id']));
print("OK");
