<?php
if(!isset($_GET['initial_price']) || !isset($_GET['buy_price']) || !isset($_GET['minimal_price']) || !isset($_GET['id']) ||
    !isset($_GET['post_service_price']) || !isset($_GET['post_service_price_p']) || !isset($_GET['ups_price']) || !isset($_GET['ups_price_p']) || 
    !isset($_GET['days']) || !isset($_GET['name']) || !isset($_GET['add_auction_cost']) ||
    !isset($_GET['qty']) || !$_GET['id'] || !$_GET['qty']) die('invalid request');
    
define('CID',false);
define('READ_ONLY_SESSION',true);
if(isset($argv))
	define('EPESI_DIR','/');
require_once('../../../../../include.php');
set_time_limit(0);
ini_set('memory_limit', '512M');
ModuleManager::load_modules();
if (!Acl::is_user()) die('Unauthorized access');

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
$vals['add_auction_cost'] = $_GET['add_auction_cost'];
$auction_cost = isset($_GET['auction_cost'])?$_GET['auction_cost']:0;

$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$item_id);

$fields = Premium_Warehouse_eCommerce_AllegroCommon::get_publish_array($item,$vals,$auction_cost);
if(!$fields) die('Skonfiguruj moduł Allegro');

$a = Premium_Warehouse_eCommerce_AllegroCommon::get_lib();
if(!$a) die('Skonfiguruj moduł Allegro');

$local_id = Acl::get_user()*1000000+mt_rand(0,999999);
$a->new_auction($fields,$local_id);
$err = $a->error();
if($err) die($err);
Epesi::alert('Aukcja została dodana.');
$ret = $a->verify_new_auction($local_id);
$buy_now = $vals['buy_price']?$vals['buy_price']+((isset($vals['add_auction_cost']) && $vals['add_auction_cost'] && $auction_cost)?$auction_cost:0):'';
DB::Execute('INSERT INTO premium_ecommerce_allegro_auctions (auction_id,item_id,created_by,started_on,buy_price,prefs) VALUES(%s,%d,%d,%T,%f,%s)',array($ret['itemId'],$item_id,Acl::get_user(),$ret['itemStartingTime'],$buy_now?$buy_now:null,serialize($vals)));

Premium_Warehouse_eCommerce_AllegroCommon::delete_photos();

print('<a href="http://allegro.pl/ShowItem2.php?item='.$ret['itemId'].'" target="_blank">OK</a>');