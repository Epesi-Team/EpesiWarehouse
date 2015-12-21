<?php defined("_VALID_ACCESS") || die('Direct access forbidden');
if(ModuleManager::is_installed('Premium/Payments')<0 || ModuleManager::is_installed('Premium/Warehouse/Items/Orders/Invoice')<0) return;
$ret = Utils_RecordBrowserCommon::get_records('premium_payments_addons',array('recordset'=>'premium_warehouse_items_orders'));
$addon = array_shift($ret);
if(!$addon) return;
$old_entries = DB::GetAssoc('SELECT id,f_record_id FROM premium_payments_entries_data_1 WHERE f_recordset=%s',array('premium_payments_entries_data_1'));
foreach($old_entries as $pay_id=>$record_id) {
    $items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details',array('transaction_id'=>$record_id));
    foreach($items as $item) {
        if(!$item['invoices']) continue;
        $invoice = array_shift($item['invoices']);
        if(!$invoice) continue;
        DB::Execute('UPDATE premium_payments_entries_data_1 SET f_recordset=%s,f_record_id=%d WHERE id=%d',array('premium_invoice',$invoice,$pay_id));
    }
}
