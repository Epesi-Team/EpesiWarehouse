<?php

Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders', array('name' => 'Tax Calculation', 'type'=>'commondata', 'param'=>array('Premium_Items_Orders_TaxCalc'), 'required'=>true, 'extra'=>true, 'filter'=>false, 'visible'=>false, 'position'=>'Related'));
Utils_CommonDataCommon::new_array('Premium_Items_Orders_TaxCalc',array(0=>'Per Item',1=>'By Total'));
DB::Execute('UPDATE premium_warehouse_items_orders_data_1 SET f_tax_calculation=0 WHERE f_tax_calculation IS NULL');

?>
