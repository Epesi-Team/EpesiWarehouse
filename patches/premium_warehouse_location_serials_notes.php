<?php
if(ModuleManager::is_installed('Premium_Warehouse_Items_Location')>=0) {
    PatchUtil::db_add_column('premium_warehouse_location_serial','notes', 'C(255)');
    PatchUtil::db_add_column('premium_warehouse_location_serial','shelf', 'C(255)');
    PatchUtil::db_add_column('premium_warehouse_location_serial','owner', 'C(32)');
}
?>