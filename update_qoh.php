<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2007, Telaxus LLC
 * @version 1.0
 * @license SPL
 * @package warehouse
 */

define('CID',false); //i know that i won't access $_SESSION['client']
require_once('../../../include.php');
ModuleManager::load_modules();

if (!Base_AclCommon::i_am_admin()) die('Unauthorized access');

$it = Utils_RecordBrowserCommon::get_records('premium_warehouse_items');
foreach($it as $i) {
	$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', array('item_sku'=>$i['id']));
	$sum = 0;
	foreach ($locs as $v) $sum += $v['quantity'];
	Utils_RecordBrowserCommon::update_record('premium_warehouse_items', $i['id'], array('quantity_on_hand'=>$sum));
}

print('Script run successfully');

?>
