<?php
if(!isset($_GET['cat']) || !$_GET['cat'] || !isset($_GET['id']) || !$_GET['id'] ||
    !isset($_GET['name']) || !$_GET['name']) die('invalid request');
    
define('CID',false);
define('READ_ONLY_SESSION',true);
if(isset($argv))
	define('EPESI_DIR','/');
require_once('../../../../../include.php');
set_time_limit(0);
ini_set('memory_limit', '512M');
ModuleManager::load_modules();
if (!Acl::is_user()) die('Unauthorized access');

$_GET['name'] = trim($_GET['name']);

$prefs = DB::GetOne('SELECT prefs FROM premium_ecommerce_allegro_auctions WHERE auction_id=%s',array($_GET['id']));
if($prefs && $prefs = @unserialize($prefs)) {
    $prefs['search'] = $_GET['name'];
    DB::Execute('UPDATE premium_ecommerce_allegro_auctions SET prefs=%s WHERE auction_id=%s',array(serialize($prefs),$_GET['id']));
}

$allegro = Premium_Warehouse_eCommerce_AllegroCommon::get_lib();
$ret = $allegro->search($_GET['name'],$_GET['cat']);
if($ret) {
    usort($ret,'sort_auctions');
    foreach(array_slice($ret,0,10) as $it) {
	print('<a target="_blank" href="http://allegro.pl/ShowItem2.php?item='.$it->sItId.'">'.$it->sItName.'</a> ');
        if($it->sItIsBuyNow) {
		print('<font color="red">KT '.$it->sItBuyNowPrice.'</font> ');
        }
	if($it->sItPrice)
		print('<font color="orange">L '.$it->sItPrice.'</font> ');
	foreach($it->sItAttribsList as $attrs) {
		if(is_object($attrs)) $attrs = array($attrs);
		foreach($attrs as $attr) {
			if($attr->attribName=='Stan') print($attr->attribValues->item);
		}
	}
	print('<br />');
    }
} else 
    print('---');
    
function sort_auctions($a,$b) {
    $aa = 0;
    $bb = 0;
    if($a->sItIsBuyNow) {
	$aa = $a->sItBuyNowPrice;
    } else {
	$aa = $a->sItPrice;
    }
    if($b->sItIsBuyNow) {
	$bb = $b->sItBuyNowPrice;
    } else {
	$bb = $b->sItPrice;
    }
    return $aa-$bb;
}