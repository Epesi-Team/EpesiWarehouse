<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

if(ModuleManager::is_installed('Premium_Warehouse_Wholesale')>=0) {
	Utils_RecordBrowserCommon::new_record_field('premium_warehouse_distributor',
			array('name' => _M('Minimal profit'), 	'type'=>'integer', 'required'=>false, 'extra'=>false, 'visible'=>true, 'style'=>'integer'));
	Utils_RecordBrowserCommon::new_record_field('premium_warehouse_distributor',
			array('name' => _M('Percentage profit'), 	'type'=>'integer', 'required'=>false, 'extra'=>false, 'visible'=>true, 'style'=>'integer'));
}
?>