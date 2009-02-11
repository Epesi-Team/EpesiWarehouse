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

$correct_errors = isset($_GET['correct']) && $_GET['correct'];

$count = 0;
$locs = array();

$items_w_dupli = array();

$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location');
foreach ($recs as $v) {
	if (!isset($locs[$v['warehouse']])) $locs[$v['warehouse']] = array();
	if (!isset($locs[$v['warehouse']][$v['item_sku']])) $locs[$v['warehouse']][$v['item_sku']] = array();
	$locs[$v['warehouse']][$v['item_sku']][$v['id']] = $v['quantity'];
}

foreach($locs as $w=>$data)
	foreach($data as $in=>$ids) {
		if (count($ids)>1) {
			$total = 0;
			$first = null;
			foreach ($ids as $id=>$qty) {
				if ($first===null) {
					$first=$id;
					$items_w_dupli[] = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $id, 'item_sku');
				}
				else {
					if ($correct_errors) Utils_RecordBrowserCommon::delete_record('premium_warehouse_location', $id, true);
					$count++;
				}
				$total += $qty;
			}
			if ($correct_errors) Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $first, array('quantity'=>$total));
		}
	}


print('Script run successfully, found '.$count.' duplicates - items '.implode(', ',$items_w_dupli).'.<br>');

?>
