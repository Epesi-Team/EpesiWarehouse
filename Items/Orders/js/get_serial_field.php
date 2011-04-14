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
if(!isset($_POST['order_details']) || !isset($_POST['serial']) || !isset($_POST['cid']))
	die('alert(\'Invalid request\')');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']); 
define('READ_ONLY_SESSION',true);
require_once('../../../../../../include.php');
ModuleManager::load_modules();

$det = json_decode($_POST['order_details']);
$serial = json_decode($_POST['serial']);

$v = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders_details', $det);
$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $v['transaction_id']);

if ($trans['transaction_type']==0) {
	$serial_html = '<input type="text" name="serial_'.$serial.'" ';
	$note_html = '<input type="text" name="note_'.$serial.'" ';
	$shelf_html = '<input type="text" name="shelf_'.$serial.'" ';

	$ret = DB::SelectLimit('SELECT * FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial s ON s.id=os.serial_id WHERE os.order_details_id=%d ORDER BY s.location_id ASC, s.id ASC', 1, $serial-1, array($det));
	$row = $ret->FetchRow();
	if ($row) {
		$serial_html .= 'value="'.$row['serial'].'" ';
		$note_html .= 'value="'.$row['notes'].'" ';
		$shelf_html .= 'value="'.$row['shelf'].'" ';
	}

	$serial_html .= '>';
	$shelf_html .= '>';
	$note_html .= '>';
}


if ($trans['transaction_type']==1 || $trans['transaction_type']==4) {
	$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku', 'warehouse'), array($v['item_name'], $trans['warehouse']));
	if (!$loc_id) return;
	$selected_serials = DB::GetAssoc('SELECT s.id, s.serial FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial s ON s.id=os.serial_id WHERE os.order_details_id=%d ORDER BY s.location_id ASC, s.id ASC', array($det));
	$selected_serials_ids = array();
	foreach ($selected_serials as $id=>$s) $selected_serials_ids[] = $id;
	$item_serials_raw = DB::Execute('SELECT * FROM premium_warehouse_location_serial WHERE location_id=%d OR id IN ('.implode(',',$selected_serials_ids).') ORDER BY serial', array($loc_id));
	$item_serials = array();
	$empty = 0;
	$i = 1;
	$default = null;
	while ($row = $item_serials_raw->FetchRow()) {
		if ($i == $serial) $default = $row['id'];
		if (!$row['serial']) {
			if (!$empty) $item_serials['NULL'] = array('serial'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders','n/a'), 'note'=>'', 'shelf'=>'');
			$empty++;
		} else $item_serials[$row['id']] = array('serial'=>$row['serial'], 'note'=>$row['notes'], 'shelf'=>$row['shelf']);
		$i++;
	}
	if ($default===null) {
		$serial_html = '<b style="color: red;">'.Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Not enough serials supplied').'</b>';
		$note_html = '';
		$shelf_html = '';
	} else {
		print('allowed_empty_serials[1] = '.(string)$empty.';');
		$serial_html = '<input type="hidden" name="serial_'.$serial.'" value="1">';
		$serial_html .= '<select name="serial__1__'.$serial.'" id="serial__1__'.($serial-1).'" onchange="check_serial_duplicates(1);update_note_and_shelf('.($serial-1).');">';
		foreach ($item_serials as $k=>$v)
			$serial_html .= '<option value="'.$k.'" '.($default==$k?'selected="1" ':'').'>'.$v['serial'].'</option>';
		$serial_html .= '</select>';
		$note_html = '<input type="text" name="note_'.$serial.'" id="serial__note__'.($serial-1).'" value="'.$item_serials[$default]['note'].'">';
		$shelf_html = '<input type="text" name="shelf_'.$serial.'" id="serial__shelf__'.($serial-1).'" value="'.$item_serials[$default]['shelf'].'">';
		$js = 'var serials_data=[];';
		foreach ($item_serials as $k=>$v)
			$js .= 'serials_data['.$k.'] = {"note":"'.$v['note'].'", "shelf":"'.$v['shelf'].'"};';
		$js .= 	'update_note_and_shelf=function(span_id){';
		$js .= 		'var current_val = $("serial__1__"+span_id).value;';
		$js .= 		'$("serial__note__"+span_id).value=serials_data[current_val]["note"];';
		$js .= 		'$("serial__shelf__"+span_id).value=serials_data[current_val]["shelf"];';
		$js .= 	'};';
		print($js);
	}
}

print('if($("serial_field_space_'.$serial.'"))$("serial_field_space_'.$serial.'").innerHTML = "'.Epesi::escapeJS($serial_html).'";');
print('if($("note_field_space_'.$serial.'"))$("note_field_space_'.$serial.'").innerHTML = "'.Epesi::escapeJS($note_html).'";');
print('if($("shelf_field_space_'.$serial.'"))$("shelf_field_space_'.$serial.'").innerHTML = "'.Epesi::escapeJS($shelf_html).'";');

if ($trans['transaction_type']==1 || $trans['transaction_type']==4)
	print('check_serial_duplicates(1);');

?>