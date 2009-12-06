<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-premium
 * @subpackage epesitray
 */
if(!isset($_GET['user']) || !isset($_GET['pass']))
	die('Invalid request');
	
define('CID',false);
require_once('../../../../include.php');
ModuleManager::load_modules();
if(ModuleManager::is_installed('Premium/Warehouse/Wholesale')<0) die('not installed');

function login() {
	$t = Variable::get('host_ban_time');
	if($t>0) {
		$fails = DB::GetOne('SELECT count(*) FROM user_login_ban WHERE failed_on>%d AND from_addr=%s',array(time()-$t,$_SERVER['REMOTE_ADDR']));
		if($fails>=3) {
			die('ban');
		}
	}
	$ret = DB::GetOne('SELECT 1 FROM user_login u JOIN user_password p ON u.id=p.user_login_id WHERE u.login=%s AND p.password=%s AND u.active=1', array($_GET['user'], $_GET['pass']));
	if(!$ret) {
		$t = Variable::get('host_ban_time');
		if($t>0) {
			DB::Execute('DELETE FROM user_login_ban WHERE failed_on<=%d',array(time()-$t));
			DB::Execute('INSERT INTO user_login_ban(failed_on,from_addr) VALUES(%d,%s)',array(time(),$_SERVER['REMOTE_ADDR']));
		}
		die('auth failed');
	}
	Base_User_LoginCommon::set_logged($_GET['user']);
}

login();
if(!Acl::check('Premium_Warehouse_Items','browse items') || !Acl::check('Premium_Warehouse_Items','view items'))
	die('permission denied');
session_commit();

set_time_limit(0);

$keys = array(
			'Category',
			'Name',
			'Price',
			'Currency',
			'Quantity',
			'UPC',
			'Manufacturer',
			'MPN',
			'SKU'
		);

$fp = fopen('php://output','w');
fputcsv($fp,$keys);
$prods = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('>net_price'=>0,'>quantity_on_hand'=>0));
foreach($prods as $p) {
	$price = Utils_CurrencyFieldCommon::get_values($p['net_price']);
	$category = array_shift($p['category']);
	$category = explode('/',$category);
	foreach($category as $k=>$v) {
		$xx = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_categories',$v,array('category_name'));
		$category[$k] = $xx['category_name'];
	}
	$manufacturer = CRM_ContactsCommon::get_company($p['manufacturer']);
	$manufacturer = $manufacturer['company_name'];
	fputcsv($fp,array(implode('/',$category),$p['item_name'],$price[0],Utils_CurrencyFieldCommon::get_code($price[1]),$p['quantity_on_hand'],$p['upc'],
				$manufacturer, $p['manufacturer_part_number'],$p['sku']));
}
fclose($fp);
?>