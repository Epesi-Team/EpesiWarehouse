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
if(!isset($_POST['item_name']) || !isset($_GET['cid'])  || !isset($_GET['transaction_type']) || !is_numeric($_GET['cid']))
	die('alert(\'Invalid request\')');

define('CID',$_GET['cid']); 
require_once('../../../../../include.php');
ModuleManager::load_modules();

$trans_type = $_GET['transaction_type'];
$qry = array();
$vals = array();
$words = explode(' ', $_POST['item_name']);
foreach ($words as $w) {
	$qry[] = 'f_item_name LIKE '.DB::Concat(DB::qstr('%'), '%s', DB::qstr('%'));
	$vals[] = $w;
}
$ret = DB::SelectLimit('SELECT * FROM premium_warehouse_items_data_1 WHERE '.implode(' AND ',$qry).' AND active=1 ', 10, 0, $vals);
print('<ul>');
$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');

while ($row = $ret->FetchRow()) {
	$l = '<li><table>'.
			'<tr>'.
				'<td width="200px;">'.
					$row['f_item_name'].
				'</td>'.
				'<td>'.
					'<span class="informal">'.$row['f_sku'].'</span>'.
				'</td>'.
				'<td width="60px;" align="right">'.
					'<span class="informal">';
	if ($my_warehouse) {
		$l .= Premium_Warehouse_Items_LocationCommon::get_item_quantity_in_warehouse(array('id'=>$row['id']), $my_warehouse).' / ';
	}
	$l .=				$row['f_quantity_on_hand'];
	$l .= 			'</span>'.
				'</td>';
	if ($trans_type==0 || $trans_type==1)
		$l .= 		'<td width="90px;" align="right">'.
						'<span class="informal">'.Utils_CurrencyFieldCommon::format($trans_type==0?$row['f_net_cost']:$row['f_net_price']).'</span>'.
					'</td>';
	$l .= 	'</tr>'.
		'</table></li>';
	print($l);
}
print('</ul>');
return;
?>