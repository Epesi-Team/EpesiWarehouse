<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if(ModuleManager::is_installed('Premium_Warehouse_Items_Orders')>=0) {
	$ret = DB::Execute('SELECT * FROM premium_warehouse_items_orders_data_1');
	while ($row = $ret->FetchRow()) {
		if (!isset($row['f_tracking_info'])) continue;
		if ($row['f_tracking_info'] &&
			$row['f_shipment_no'] &&
			$row['f_tracking_info'] != $row['f_shipment_no']) {
			error_log('Transaction ID: '.$row['id'].' SN: '.$row['f_shipment_no'].' TI: '.$row['f_tracking_info']."\n",3,'data/tracking_info_removal.log');
		} else {
			if ($row['f_tracking_info'] && !$row['f_shipment_no'])
				DB::Execute('UPDATE premium_warehouse_items_orders_data_1 SET f_shipment_no=%s WHERE id=%d', array($row['f_tracking_info'], $row['id']));
		}
	}
	Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders', 'Tracking Info');
}

?>