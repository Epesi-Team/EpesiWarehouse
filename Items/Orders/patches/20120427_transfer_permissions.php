<?php

Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'EMPLOYEE', array('transaction_type'=>4, '<status'=>20), array('transaction_type', 'warehouse'));
Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'EMPLOYEE', array('online_order'=>1, '<status'=>6), array('transaction_type'));

?>