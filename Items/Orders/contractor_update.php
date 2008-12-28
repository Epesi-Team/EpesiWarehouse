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
$js .= '$("address_1").value="'.$rec['address_1'].'";';
$js .= '$("address_2").value="'.$rec['address_2'].'";';
$js .= '$("city").value="'.$rec['city'].'";';
$js .= '$("postal_code").value="'.$rec['postal_code'].'";';
$js .= '$("country").value="'.$rec['country'].'";';
// TODO: copy zone
print($js);
?>