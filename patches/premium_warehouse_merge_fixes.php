<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if(ModuleManager::is_installed('Premium_Warehouse')>=0) {
	Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_serial_addon', 'Serial Numbers');
	Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_serial_addon', 'Premium_Warehouse_Items_LocationCommon::location_serial_addon_parameters');
}
?>