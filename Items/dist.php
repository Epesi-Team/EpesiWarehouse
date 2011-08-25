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
if(ModuleManager::is_installed('Premium/Warehouse/Items')<0) die('not installed');

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
			'Tax',
			'Gross Price',
			'Currency',
			'Quantity',
			'UPC',
			'Manufacturer',
			'MPN',
			'SKU'
		);

$fp = fopen('php://output','w');
fputcsv($fp,$keys);

$crits = array('>net_price'=>0);
if(ModuleManager::is_installed('Premium/Warehouse/Items/Location')<0) 
	$crits['>quantity_on_hand']=0;
$prods = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',$crits);

$reserved = DB::GetAssoc('SELECT d.f_item_name, SUM(d.f_quantity) FROM premium_warehouse_items_orders_details_data_1 d INNER JOIN premium_warehouse_items_orders_data_1 o ON (o.id=d.f_transaction_id) WHERE ((o.f_transaction_type=1 AND o.f_status in (-2,-1,2,3,4,5)) OR (o.f_transaction_type=4 AND o.f_status in (2,3))) AND d.active=1 AND o.active=1 GROUP BY d.f_item_name');

foreach($prods as $p) {
	$quantity = $p['quantity_on_hand'] - (isset($reserved[$p['id']])?$reserved[$p['id']]:0);
	if(ModuleManager::is_installed('Premium/Warehouse/Items/Location')>=0) {
		$qqq = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$p['id']),array('quantity'));
		foreach($qqq as $qq) 
			$quantity += $qq['quantity'];
	}
	if($quantity==0) continue;
	
	$price = Utils_CurrencyFieldCommon::get_values($p['net_price']);
	$tax = Data_TaxRatesCommon::get_tax_rate($p['tax_rate']);
	$g_price = round($price[0]*(100+$tax)/100,Utils_CurrencyFieldCommon::get_precission($price[1]));
	$category = array_shift($p['category']);
	$category = explode('/',$category);
	foreach($category as $k=>$v) {
		$xx = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_categories',$v,array('category_name'));
		$category[$k] = $xx['category_name'];
	}
	$manufacturer = CRM_ContactsCommon::get_company($p['manufacturer']);
	$manufacturer = $manufacturer['company_name'];
	fputcsv($fp,array(implode('/',$category),$p['item_name'],$price[0],$tax,$g_price,Utils_CurrencyFieldCommon::get_code($price[1]),$quantity,$p['upc'],
				$manufacturer, $p['manufacturer_part_number'],$p['sku']));
}
fclose($fp);
?>