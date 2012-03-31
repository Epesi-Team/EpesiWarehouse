<?php

if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
		Variable::set('ecommerce_item_prices',true);
		Variable::set('ecommerce_item_descriptions',true);
		Variable::set('ecommerce_item_parameters',true);
}
?>