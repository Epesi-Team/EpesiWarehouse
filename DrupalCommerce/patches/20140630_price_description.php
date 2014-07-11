<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');

Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_prices',
			array('name' => _M('Model'),	'type'=>'text', 'param'=>64, 'required'=>false, 'extra'=>false, 'visible'=>true,'position'=>'Item Name'));
