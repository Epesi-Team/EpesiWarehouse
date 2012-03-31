<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce_CurrencyUpdatePrices')>=0) {
		Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_prices',array('name'=>'Auto update',	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true));
		DB::Execute('UPDATE premium_ecommerce_prices_data_1 SET f_auto_update=1 WHERE f_currency!=1');
}
?>