<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
    Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items_orders', 'Shipment Type', 'Premium_Warehouse_eCommerceCommon::QFfield_shipment_type');
    Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items_orders', 'Shipment Type', 'Premium_Warehouse_eCommerceCommon::display_shipment_type');
}
?>