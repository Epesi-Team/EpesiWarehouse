<?php defined("_VALID_ACCESS") || die('Direct access forbidden');

$tab = 'premium_warehouse_items_orders';
$definition = array('name' => _M('Split Transaction'),
                    'type' => 'multiselect',
                    'extra' => false,
                    'visible' => false,
                    'param' => "$tab::Transaction ID",
                    'position' => 'Ref No',
                    'QFfield_callback' => array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_split_transaction'),
);
Utils_RecordBrowserCommon::new_record_field($tab, $definition);
Utils_RecordBrowserCommon::field_deny_access($tab, 'Split Transaction', 'add');
Utils_RecordBrowserCommon::field_deny_access($tab, 'Split Transaction', 'edit');
