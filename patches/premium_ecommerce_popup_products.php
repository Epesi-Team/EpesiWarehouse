<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
	Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_products',array('name' => _M('Popup products'), 	'type'=>'multiselect', 'param'=>'premium_ecommerce_products::Item Name;Premium_Warehouse_eCommerceCommon::related_products_crits;Premium_Warehouse_eCommerceCommon::adv_related_products_params', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_eCommerceCommon', 'display_popup_product_name'), 'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon', 'QFfield_popup_products')));
}
?>