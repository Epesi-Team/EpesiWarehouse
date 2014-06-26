<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');

Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_drupal',
			array('name' => _M('Export Net Price'), 			'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true));
