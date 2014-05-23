<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (!DB::GetOne('SELECT name FROM modules WHERE name=%s', array('Premium_Warehouse_InvoicePL'))) return;

DB::Execute('UPDATE modules SET name=%s WHERE name=%s', array('Premium_Warehouse_Invoice', 'Premium_Warehouse_InvoicePL'));

Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium_Warehouse_InvoicePL', 'invoicepl');
Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium_Warehouse_Invoice', 'invoice', 'Premium_Warehouse_InvoiceCommon::invoice_addon_parameters');

Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items_orders','Invoice Number',array('Premium_Warehouse_InvoiceCommon', 'display_invoice_number'));
Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items_orders','Invoice Number',array('Premium_Warehouse_InvoiceCommon', 'QFfield_invoice_number'));

?>