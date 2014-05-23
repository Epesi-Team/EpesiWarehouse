<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

$mod = 'Base/Print';
if (ModuleManager::is_installed($mod) < 0) { // not installed
    ModuleManager::install($mod, 0);
}

Base_PrintCommon::register_printer(new Premium_Warehouse_Invoice_Printer());
