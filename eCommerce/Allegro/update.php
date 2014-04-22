#!/usr/bin/env php
<?php
//die();

define('CID',false);
define('SET_SESSION',false);
if(isset($argv))
	define('EPESI_DIR','/');
require_once('../../../../../include.php');
set_time_limit(0);
ini_set('memory_limit', '512M');
ModuleManager::load_modules();

Acl::set_user(2);
$c = Variable::get('ecommerce_allegro_cats_up',0);
$t = time();

//$a = Premium_Warehouse_eCommerce_AllegroCommon::get_lib(false);
//if(!$a) die();

if($c+3600*24*3<$t) {
	if(Premium_Warehouse_eCommerce_AllegroCommon::update_cats())
		Variable::set('ecommerce_allegro_cats_up',$t);
}
Premium_Warehouse_eCommerce_AllegroCommon::update_statuses();
DB::Execute('DELETE FROM premium_ecommerce_allegro_cross WHERE created_on<%T',array(time()-3600*12));
