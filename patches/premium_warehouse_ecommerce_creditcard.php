<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
    Utils_CommonDataCommon::extend_array('Premium_Items_Orders_Payment_Types',array('CreditCardBasic'=>'Credit Card (basic)'));
}
?>