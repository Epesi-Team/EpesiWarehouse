<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Items Orders
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-orders
 */
if(!isset($_POST['parameters']) || !isset($_POST['rec_id']) || !isset($_POST['cid']))
	die('alert(\'Invalid request\')');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']); 
require_once('../../../../../include.php');
ModuleManager::load_modules();

$tab = trim($_POST['parameters'], '"');
$id = trim($_POST['rec_id'], '"');
$rec = Utils_RecordBrowserCommon::get_record($tab,$id);

if (!Acl::is_user() || !Utils_RecordBrowserCommon::get_access($tab, 'view', $rec)) die('Unauthorized access');

if (!$rec || empty($rec)) return;
$js = '';
foreach ($rec as $k=>$v)
	if (is_string($v)) $rec[$k] = htmlspecialchars_decode($v);
if ($tab=='contact') {
	$js .= '$("first_name").value="'.$rec['first_name'].'";';
	$js .= '$("last_name").value="'.$rec['last_name'].'";';
	$js .= '$("phone").value="'.$rec['work_phone'].'";';
} else {
	$js .= '$("company_name").value="'.$rec['company_name'].'";';
	$js .= '$("phone").value="'.$rec['phone'].'";';
}
if (isset($rec['tax_id'])) $js .= '$("tax_id").value="'.$rec['tax_id'].'";';
$js .= '$("address_1").value="'.$rec['address_1'].'";';
$js .= '$("address_2").value="'.$rec['address_2'].'";';
$js .= '$("city").value="'.$rec['city'].'";';
$js .= '$("postal_code").value="'.$rec['postal_code'].'";';
$js .= '$("country").value="'.$rec['country'].'";';

$js .= '$("country").fire(\'e_u_cd:load\');';
$js .= 'zone=$("zone");';
$js .= 'setTimeout("'.
			'k=0;while(k<zone.options.length)if(zone.options[k].value==\''.$rec['zone'].'\')break;else k++;'.
			'$(zone).selectedIndex=k;'.
		'",900);';
print($js);
?>