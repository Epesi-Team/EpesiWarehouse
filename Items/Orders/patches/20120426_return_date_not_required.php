<?php
Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items_orders_details','Return Date','Premium_Warehouse_Items_OrdersCommon::QFfield_return_date');
DB::Execute('UPDATE premium_warehouse_items_orders_details_field SET required=0 WHERE field=%s', array('Return Date'));
?>