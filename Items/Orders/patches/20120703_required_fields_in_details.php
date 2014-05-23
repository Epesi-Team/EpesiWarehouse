<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

DB::Execute('UPDATE premium_warehouse_items_orders_details_field SET required=0 WHERE field=%s OR field=%s OR field=%s', array('Net Price','Gross Price','Tax Rate'));

?>
