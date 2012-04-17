<?php

if (Utils_RecordBrowserCommon::get_records_count('data_tax_rates', array())==0)
	Utils_RecordBrowserCommon::new_record('data_tax_rates', array('name'=>'Non-taxable', 'percentage'=>0));

?>
