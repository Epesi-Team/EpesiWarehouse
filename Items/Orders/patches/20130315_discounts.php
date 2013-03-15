<?php
Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders_details',
    array('name' => _M('Discount Rate'), 			'type'=>'float', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_discount_rate'),'position'=>'Returned')
);
Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders_details',
    array('name' => _M('Unit Price'), 			'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_unit_price'),'position'=>'Returned')
);
DB::Execute('UPDATE premium_warehouse_items_orders_details_data_1 SET f_unit_price=f_net_price, f_discount_rate=0 WHERE f_net_price is not null');
