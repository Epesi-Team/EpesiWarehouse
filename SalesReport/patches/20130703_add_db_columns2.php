<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

PatchUtil::db_add_column('premium_warehouse_sales_report_related_order_details_fifo', 'quantity', 'I4');
PatchUtil::db_add_column('premium_warehouse_sales_report_related_order_details_lifo', 'quantity', 'I4');
