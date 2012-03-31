<?php
if (ModuleManager::is_installed('Premium_Warehouse_Items_Orders')>=0) {
	Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items_orders', 'Shipment No', array('Premium_Warehouse_Items_OrdersCommon','display_shipment_no'));
}
?>