<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if(ModuleManager::is_installed('Premium_Warehouse_Items')>=0) {
    Utils_RecordBrowserCommon::set_QFfield_callback('premium_warehouse_items', 'Description', 'Libs_CKEditorCommon::QFfield_cb');
    Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Description', 'Libs_CKEditorCommon::display_cb');
}
if(ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
    Utils_RecordBrowserCommon::set_QFfield_callback('premium_ecommerce_cat_descriptions', 'Short Description', 'Libs_CKEditorCommon::QFfield_cb');
    Utils_RecordBrowserCommon::set_display_callback('premium_ecommerce_cat_descriptions', 'Short Description', 'Libs_CKEditorCommon::display_cb');
    Utils_RecordBrowserCommon::set_QFfield_callback('premium_ecommerce_cat_descriptions', 'Long Description', 'Libs_CKEditorCommon::QFfield_cb');
    Utils_RecordBrowserCommon::set_display_callback('premium_ecommerce_cat_descriptions', 'Long Description', 'Libs_CKEditorCommon::display_cb');

    Utils_RecordBrowserCommon::set_QFfield_callback('premium_ecommerce_descriptions', 'Short Description', 'Libs_CKEditorCommon::QFfield_cb');
    Utils_RecordBrowserCommon::set_display_callback('premium_ecommerce_descriptions', 'Short Description', 'Libs_CKEditorCommon::display_cb');
    Utils_RecordBrowserCommon::set_QFfield_callback('premium_ecommerce_descriptions', 'Long Description', 'Libs_CKEditorCommon::QFfield_cb');
    Utils_RecordBrowserCommon::set_display_callback('premium_ecommerce_descriptions', 'Long Description', 'Libs_CKEditorCommon::display_cb');

    Utils_RecordBrowserCommon::set_QFfield_callback('premium_ecommerce_pages_data', 'Short Description', 'Libs_CKEditorCommon::QFfield_cb');
    Utils_RecordBrowserCommon::set_display_callback('premium_ecommerce_pages_data', 'Short Description', 'Libs_CKEditorCommon::display_cb');
    Utils_RecordBrowserCommon::set_QFfield_callback('premium_ecommerce_pages_data', 'Long Description', 'Libs_CKEditorCommon::QFfield_cb');
    Utils_RecordBrowserCommon::set_display_callback('premium_ecommerce_pages_data', 'Long Description', 'Libs_CKEditorCommon::display_cb');

    Utils_RecordBrowserCommon::set_QFfield_callback('premium_ecommerce_boxes', 'Content', 'Libs_CKEditorCommon::QFfield_cb');
    Utils_RecordBrowserCommon::set_display_callback('premium_ecommerce_boxes', 'Content', 'Libs_CKEditorCommon::display_cb');
}
?>