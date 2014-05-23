<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
	Utils_RecordBrowserCommon::register_processing_callback('premium_ecommerce_orders', array('Premium_Warehouse_eCommerceCommon', 'submit_ecommerce_order'));
}
?>