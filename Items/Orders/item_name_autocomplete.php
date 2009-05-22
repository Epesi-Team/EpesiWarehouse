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
if(!isset($_POST['item_name']) || !isset($_GET['cid'])  || !isset($_GET['transaction_id']) || !is_numeric($_GET['cid']))
	die('alert(\'Invalid request\')');

define('CID',$_GET['cid']); 
require_once('../../../../../include.php');
ModuleManager::load_modules();

$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $_GET['transaction_id']);

if (!Acl::is_user() || !Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders', 'view', $trans)) die('Unauthorized access');

$trans_type = $trans['transaction_type'];
$qry = array();
$vals = array();
$words = explode(' ', $_POST['item_name']);
if ($trans_type==1 && $trans['status']==2 && $trans['warehouse']) {
	$qry[] = 'EXISTS (SELECT id FROM premium_warehouse_location_data_1 AS pwl WHERE pwl.f_quantity!=0 AND pwl.f_item_sku=pwi.id AND pwl.f_warehouse=%d)';
	$vals[] = $trans['warehouse'];
}
foreach ($words as $w) {
	$qry[] = 'f_item_name LIKE '.DB::Concat(DB::qstr('%'), '%s', DB::qstr('%'));
	$vals[] = $w;
}
$ret = DB::SelectLimit('SELECT * FROM premium_warehouse_items_data_1 AS pwi WHERE '.implode(' AND ',$qry).' AND active=1', 10, 0, $vals);

$my_warehouse = $trans['warehouse'];
if (!$my_warehouse) $my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');

print('<ul>');

$header = '<li><table>'.
		'<tr>'.
			'<th width="200px;" align="center">'.
				'<span class="informal">'.
					Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Item Name').
				'</span>'.
			'</th>'.
			'<th align="center">'.
				'<span class="informal">'.
					Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Item SKU').
				'</span>'.
			'</th>'.
			'<th width="60px;" align="center">'.
				'<span class="informal">'.
					Base_LangCommon::ts('Premium_Warehouse_Items_Orders','QoH').
	 			'</span>'.
			'</th>'.
			'<th width="60px;" align="center">'.
				'<span class="informal">'.
					Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Qty. Res.').
	 			'</span>'.
			'</th>';
			
if ($trans_type==0 || $trans_type==1)
	$header .= 	'<th width="90px;" align="center">'.
				'<span class="informal">'.
					Base_LangCommon::ts('Premium_Warehouse_Items_Orders',$trans_type==0?'Cost':'Net price').
	 			'</span>'.
			'</th>';
$header .= 	'</tr>'.
	'</table></li>';
$empty = true;
while ($row = $ret->FetchRow()) {
	if ($empty) print($header);
	$empty = false;
	$l = '<li><table>'.
			'<tr>'.
				'<td width="200px;">'.
					$row['f_item_name'].
				'</td>'.
				'<td>'.
					'<span class="informal">'.$row['f_sku'].'</span>'.
				'</td>'.
				'<td width="60px;" align="right">'.
					'<span class="informal">'.
						Premium_Warehouse_Items_LocationCommon::display_item_quantity_in_warehouse_and_total(array('id'=>$row['id'], 'item_type'=>$row['f_item_type']), $my_warehouse, true).
		 			'</span>'.
				'</td>'.
				'<td width="60px;" align="right">'.
					'<span class="informal">'.
						Premium_Warehouse_Items_OrdersCommon::display_reserved_qty(array('id'=>$row['id'], 'item_type'=>$row['f_item_type']), true).
		 			'</span>'.
				'</td>';
				
	if ($trans_type==0 || $trans_type==1)
		$l .= 		'<td width="90px;" align="right">'.
						'<span class="informal">'.Utils_CurrencyFieldCommon::format($trans_type==0?$row['f_cost']:$row['f_net_price']).'</span>'.
					'</td>';
	$l .= 	'</tr>'.
		'</table></li>';
	print($l);
}
if ($empty) print('<li><center><span class="informal"><b>'.Base_LangCommon::ts('Premium_Warehouse_Items_Orders','No records found').'</b></span></center></li>');
print('</ul>');
return;
?>