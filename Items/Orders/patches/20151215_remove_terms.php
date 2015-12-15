<?php defined("_VALID_ACCESS") || die('Direct access forbidden');
Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders','Terms');
Utils_CommonDataCommon::remove('Premium_Items_Orders_Terms');
