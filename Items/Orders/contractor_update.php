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
define('READ_ONLY_SESSION',true);
require_once('../../../../../include.php');
ModuleManager::load_modules();

$tab = trim($_POST['parameters'], '"');
$id = trim($_POST['rec_id'], '"');
$rec = Utils_RecordBrowserCommon::get_record($tab,$id);
$shipping = isset($_POST['ship']) && ($_POST['ship']==1 || $_POST['ship']=='true');

if (!Acl::is_user() || !Utils_RecordBrowserCommon::get_access($tab, 'view', $rec)) die('Unauthorized access');

if (!$rec || empty($rec)) return;
$js = '';
foreach ($rec as $k=>$v)
	if (is_string($v)) $rec[$k] = Epesi::escapeJS(htmlspecialchars_decode($v));
if ($tab=='contact') {
	$js .= '$("'.($shipping?'shipping_':'').'first_name").value="'.$rec['first_name'].'";';
	$js .= '$("'.($shipping?'shipping_':'').'last_name").value="'.$rec['last_name'].'";';
	$js .= '$("'.($shipping?'shipping_':'').'phone").value="'.$rec['work_phone'].'";';
} else {
	$js .= '$("'.($shipping?'shipping_':'').'company_name").value="'.$rec['company_name'].'";';
	$js .= '$("'.($shipping?'shipping_':'').'phone").value="'.$rec['phone'].'";';
}
if (isset($rec['tax_id']) && !$shipping) $js .= '$("'.($shipping?'shipping_':'').'tax_id").value="'.$rec['tax_id'].'";';
$js .= '$("'.($shipping?'shipping_':'').'address_1").value="'.$rec['address_1'].'";';
$js .= '$("'.($shipping?'shipping_':'').'address_2").value="'.$rec['address_2'].'";';
$js .= '$("'.($shipping?'shipping_':'').'city").value="'.$rec['city'].'";';
$js .= '$("'.($shipping?'shipping_':'').'postal_code").value="'.$rec['postal_code'].'";';
$js .= '$("'.($shipping?'shipping_':'').'country").value="'.$rec['country'].'";';

$js .= '$("'.($shipping?'shipping_':'').'country").fire(\'e_u_cd:load\');';
$js .= 'setTimeout("'.
			'$(\''.($shipping?'shipping_':'').'zone\').value=\''.$rec['zone'].'\';'.
		'",1000);';
print($js);
?>