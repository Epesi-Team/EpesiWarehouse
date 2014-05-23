<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
    DB::Execute('update premium_ecommerce_orders_field set required=0 where field="Payment Channel"');
    ModuleManager::install('Premium_MultipleAddresses');
}
?>