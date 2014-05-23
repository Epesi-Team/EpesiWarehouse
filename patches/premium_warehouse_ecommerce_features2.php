<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'parameters_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'descriptions_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'prices_addon');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'parameters_addon', 'Premium_Warehouse_eCommerceCommon::parameters_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'descriptions_addon', 'Premium_Warehouse_eCommerceCommon::descriptions_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'prices_addon', 'Premium_Warehouse_eCommerceCommon::prices_addon_parameters');
}
?>