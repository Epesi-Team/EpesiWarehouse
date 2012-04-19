<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')==-1) return;

		Utils_AttachmentCommon::delete_addon('premium_ecommerce_descriptions');
		Utils_AttachmentCommon::delete_addon('premium_ecommerce_pages');
		Utils_AttachmentCommon::delete_addon('premium_ecommerce_pages_data');
		Utils_AttachmentCommon::delete_addon('premium_ecommerce_products');

		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_descriptions', 'Premium/Warehouse/eCommerce', 'attachment_product_desc_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages', 'Premium/Warehouse/eCommerce', 'attachment_page_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_pages_data', 'Premium/Warehouse/eCommerce', 'attachment_page_desc_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce', 'attachment_product_addon', 'Notes');
?>