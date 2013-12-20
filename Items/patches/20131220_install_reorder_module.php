<?php

$module = 'Premium/Warehouse/Items/ReorderPoint';
if (ModuleManager::is_installed($module) < 0) {
    ModuleManager::install($module);
}
