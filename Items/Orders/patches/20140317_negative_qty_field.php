<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

$recordset = 'premium_warehouse_items';
$definition = array('name' => _M('Allow negative quantity'),
                    'type' => 'checkbox',
                    'extra' => false,
                    'visible' => false,
                    'QFfield_callback' => array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_negative_qty'),
                    'display_callback' => array('Premium_Warehouse_Items_OrdersCommon', 'display_negative_qty')
);
Utils_RecordBrowserCommon::new_record_field($recordset, $definition);
// set default to all
Variable::set('premium_warehouse_negative_qty', 'all');