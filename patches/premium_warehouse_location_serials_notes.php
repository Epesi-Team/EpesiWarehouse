<?php
if(ModuleManager::is_installed('Premium_Warehouse_Items_Location')>=0) {
    PatchDBAddColumn('premium_warehouse_location_serial','notes', 'C(255)');
    PatchDBAddColumn('premium_warehouse_location_serial','shelf', 'C(255)');
    PatchDBAddColumn('premium_warehouse_location_serial','owner', 'C(32)');
}
?>