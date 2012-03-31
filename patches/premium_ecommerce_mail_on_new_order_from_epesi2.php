<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
	$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
	foreach($langs as $k=>$name) {
		$txt = Variable::get('ecommerce_order_email_'.$k,false);
		if($txt)
			Utils_RecordBrowserCommon::new_record('premium_ecommerce_emails',array('send_on_status'=>"-1",'language'=>$k,'subject'=>'New order','content'=>$txt));
		Variable::delete('ecommerce_order_email_'.$k,false);
	}
	$txt = Variable::get('ecommerce_order_email');
	if($txt)
		Utils_RecordBrowserCommon::new_record('premium_ecommerce_emails',array('send_on_status'=>"-1",'subject'=>'New order','content'=>$txt));
	Variable::delete('ecommerce_order_email');
	
	Utils_RecordBrowserCommon::register_processing_callback('premium_payments', array('Premium_Warehouse_eCommerceCommon', 'submit_payment'));
}
?>