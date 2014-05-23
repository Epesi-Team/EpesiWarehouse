<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')==-1) return;

Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products',array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions',array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
?>