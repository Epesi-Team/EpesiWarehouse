<?php

$new_param = "Countries::Shipping Country";
$old_param = "Countries::Country";
$field = "Shipping Zone";
$sql = "UPDATE premium_warehouse_items_orders_field SET param=%s WHERE param=%s AND field=%s";
DB::Execute($sql, array($new_param, $old_param, $field));
