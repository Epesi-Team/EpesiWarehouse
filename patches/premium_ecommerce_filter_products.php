<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
    DB::Execute('UPDATE premium_ecommerce_products_field SET filter=1 WHERE field="Item Name" OR type="checkbox"');
}
?>