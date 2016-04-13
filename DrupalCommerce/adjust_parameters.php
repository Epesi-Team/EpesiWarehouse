<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-eCommerce
 */
if (!isset($_POST['plugin_id']) || !isset($_POST['cid']))
	die('alert(\'Invalid request\')');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']); 
define('READ_ONLY_SESSION',true);
require_once('../../../../include.php');
ModuleManager::load_modules();

if (!Acl::is_user()) die('Unauthorized access');

$plugin_id = trim($_POST['plugin_id'], '"');

print(Premium_Warehouse_DrupalCommerceCommon::get_change_parameters_labels_js($plugin_id));
?>