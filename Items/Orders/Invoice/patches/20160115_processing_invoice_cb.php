<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

Utils_RecordBrowserCommon::register_processing_callback('premium_invoice', array('Premium_Warehouse_Items_Orders_InvoiceCommon', 'submit_invoice'));
Utils_RecordBrowserCommon::register_processing_callback('premium_invoice_items', array('Premium_Warehouse_Items_Orders_InvoiceCommon', 'submit_invoice_items'));
Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders_details',array('name' => _M('Invoice Items'),	'type'=>'multiselect','param'=>'premium_invoice_items::Item Name','extra'=>false, 'required'=>false, 'visible'=>false,'QFfield_callback'=>'Premium_Warehouse_Items_Orders_InvoiceCommon::QFfield_invoices'));