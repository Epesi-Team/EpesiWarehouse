<?php
if(!isset($_GET['initial_price']) || !isset($_GET['buy_price']) || !isset($_GET['minimal_price']) || !isset($_GET['id']) ||
    !isset($_GET['post_service_price']) || !isset($_GET['post_service_price_p']) || !isset($_GET['ups_price']) || !isset($_GET['ups_price_p']) ||
    !isset($_GET['days']) || !isset($_GET['name']) ||
    !isset($_GET['qty']) || !$_GET['id'] || !$_GET['qty']) die('invalid request');

define('CID',false);
define('READ_ONLY_SESSION',true);
if(isset($argv))
	define('EPESI_DIR','/');
require_once('../../../../../include.php');
set_time_limit(0);
ini_set('memory_limit', '512M');
ModuleManager::load_modules();

list($item_id,$prefs) = DB::GetRow('SELECT item_id,prefs FROM premium_ecommerce_allegro_auctions WHERE auction_id=%s',array($_GET['id']));
$vals = array();
//$prefs
if(!($vals = @unserialize($prefs))) die('błąd');
unset($vals['publish']);
$vals['initial_price'] = $_GET['initial_price'];
$vals['buy_price'] = $_GET['buy_price'];
$vals['minimal_price'] = $_GET['minimal_price'];
$vals['qty'] = $_GET['qty'];
$vals['post_service_price'] = $_GET['post_service_price'];
$vals['post_service_price_p'] = $_GET['post_service_price_p'];
$vals['ups_price'] = $_GET['ups_price'];
$vals['ups_price_p'] = $_GET['ups_price_p'];
$vals['days'] = $_GET['days'];
$vals['title'] = $_GET['name'];

$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$item_id);

$fields = Premium_Warehouse_eCommerce_AllegroCommon::get_publish_array($item,$vals);
Premium_Warehouse_eCommerce_AllegroCommon::delete_photos();

$a = Premium_Warehouse_eCommerce_AllegroCommon::get_lib();
$ret = $a->check_new_auction_price($fields);
$err = $a->error();
$auction_cost = 0;
if($err) die($err);
elseif(isset($ret['itemPrice']) && isset($ret['itemPriceDesc'])) {
    $ret['itemPrice'] = str_replace(',','.',$ret['itemPrice']);
    if(preg_match('/([\d]+(\.[\d]+)?)/', $ret['itemPrice'], $match)) {
        $auction_cost += (float)$match[0];
    }
    $sell_price = 0;
    if(isset($vals['buy_price']) && $vals['buy_price']) {
        $sell_price = $vals['buy_price'];
    } elseif(isset($vals['minimal_price']) && $vals['minimal_price']) {
        $sell_price = $vals['minimal_price'];
    } elseif(isset($vals['initial_price']) && $vals['initial_price']) {
        $sell_price = $vals['initial_price'];
    }
    if($sell_price) {
	if($sell_price<=100)
	    $sell_price *= 5/100;
	elseif($sell_price<=1000)
	    $sell_price = 5+($sell_price-100)*1.90/100;
	elseif($sell_price<=5000)
	    $sell_price = 22.1+($sell_price-1000)*0.50/100;
	else
	    $sell_price = 42.1+($sell_price-5000)*0.20/100;
	$sell_price = round($sell_price,2);
	$auction_cost += $sell_price;
    }
    print('Wystawienie: '.$ret['itemPrice']."<br />".'Sprzedaż: '.$sell_price.' zł'."<br /><b>Razem: <span id='allegro_auction_cost_".$_GET['id']."'>".$auction_cost.'</span> zł</b><input type="hidden" value="'.$auction_cost.'" id="allegro_auction_cost_'.$_GET['id'].'" />');
} else die('błąd pobierania');
