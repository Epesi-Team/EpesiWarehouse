<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');


Utils_RecordBrowserCommon::unregister_processing_callback('premium_payments', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_payment'));

Utils_RecordBrowserCommon::register_processing_callback('premium_payments_entries', array('Premium_Warehouse_DrupalCommerceCommon', 'submit_payment'));