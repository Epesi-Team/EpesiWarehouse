<?php

PatchUtil::db_add_column('premium_warehouse_sales_report_purchase_fifo_tmp', 'order_details_id', 'I4');
PatchUtil::db_add_column('premium_warehouse_sales_report_purchase_lifo_tmp', 'order_details_id', 'I4');

DB::CreateTable('premium_warehouse_sales_report_related_order_details_fifo',
    'order_details_id_sold I4,' .
    'order_details_id_bought I4',
    array('constraints' => '')
);
DB::CreateTable('premium_warehouse_sales_report_related_order_details_lifo',
    'order_details_id_sold I4,' .
    'order_details_id_bought I4',
    array('constraints' => '')
);
