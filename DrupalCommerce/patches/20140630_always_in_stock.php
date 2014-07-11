<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');

Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_products',
			array('name' => _M('Always in stock'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false, 'filter'=>true,'position'=>'Always on stock'));
DB::Execute('UPDATE premium_ecommerce_products_data_1 SET f_always_in_stock=f_always_on_stock');
Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_products','Always on stock');

Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_cat_descriptions','Page Title');
Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_cat_descriptions','Meta Description');
Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_cat_descriptions','Keywords');
