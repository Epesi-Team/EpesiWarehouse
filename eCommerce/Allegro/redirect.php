<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past
define('CID',false); //i know that i won't access $_SESSION['client']
require_once('../../../../../include.php');
ModuleManager::load_modules();

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	if(!isset($_SERVER['HTTP_REFERER'])) die('Nie ma takiej aukcji');
	header('Location: '.$_SERVER['HTTP_REFERER']);
	die();
}

$auctions = Premium_Warehouse_eCommerce_AllegroCommon::get_other_auctions($_GET['id']);
if(!isset($auctions[$_GET['i']]))  {
	if(!isset($_SERVER['HTTP_REFERER'])) die('Nie ma takiej aukcji');
	header('Location: '.$_SERVER['HTTP_REFERER']);
	die();
}

if(Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country')!=1)
    $url = 'http://testwebapi.pl/i'.$auctions[$_GET['i']]['auction'].'.html';
else
    $url = 'http://allegro.pl/ShowItem2.php?item='.$auctions[$_GET['i']]['auction'];
header('Location: '.$url);
die();
