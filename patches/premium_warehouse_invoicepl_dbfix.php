<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_Invoice')>=0) {
    DB::Execute('update recordbrowser_browse_mode_definitions set module="Premium/Warehouse/Invoice" where tab="premium_warehouse_items_orders"');
}
?>