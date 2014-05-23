<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
Utils_RecordBrowserCommon::new_filter('premium_warehouse_items_orders', 'Transaction Type');
Utils_RecordBrowserCommon::new_filter('premium_warehouse_items_orders', 'Status');
Utils_RecordBrowserCommon::new_browse_mode_details_callback('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'browse_mode_details');
?>