<?php

DB::Execute('UPDATE premium_warehouse_items_orders_field SET required=0 WHERE field=%s', array('Target Warehouse'));

?>
