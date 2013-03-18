<?php
Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders_details','Discount Rate');
Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders_details',
    array('name' => _M('Markup/Discount Rate'), 			'type'=>'float', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_discount_rate'),'position'=>'Unit Price')
);
DB::Execute('UPDATE premium_warehouse_items_orders_details_data_1 SET f_unit_price=f_net_price, f_markup_discount_rate=0 WHERE f_net_price is not null');
