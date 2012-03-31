<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
    ModuleManager::install('Premium_Payments');
	Premium_PaymentsCommon::new_addon('premium_warehouse_items_orders');
}
?>