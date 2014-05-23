<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
		$fields = array(
			array('name' => _M('Subject'),	'type'=>'text', 'param'=>128, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Content'), 	'type'=>'long text', 'required'=>true, 'extra'=>false, 'visible'=>false, 'QFfield_callback'=>array('Libs_CKEditorCommon', 'QFfield_cb'), 'display_callback'=>array('Libs_CKEditorCommon', 'display_cb')),
			array('name' => _M('Language'), 	'type'=>'commondata', 'required'=>false, 'extra'=>false, 'param'=>array('Premium/Warehouse/eCommerce/Languages'), 'visible'=>true),
			array('name' => _M('Send On Status'), 		'type'=>'integer', 'required'=>true, 'extra'=>false,'visible'=>false, 'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon', 'QFfield_order_status'), 'display_callback'=>array('Premium_Warehouse_eCommerceCommon', 'display_order_status'))
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_emails', $fields);

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_emails', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_emails', _M('eCommerce - e-mails'));
		Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_emails');


		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
		foreach($langs as $k=>$name) {
			$subj = Variable::get('ecommerce_order_shi_email_'.$k.'S',false);
			$content = Variable::get('ecommerce_order_shi_email_'.$k,false);
			if($content) {
			    Utils_RecordBrowserCommon::new_record('premium_ecommerce_emails',array('subject'=>$subj,'content'=>$content,'language'=>$k,'send_on_status'=>7));
			}
			Variable::delete('ecommerce_order_shi_email_'.$k,false);
			Variable::delete('ecommerce_order_shi_email_'.$k.'S',false);

			$subj = Variable::get('ecommerce_order_rec_email_'.$k.'S',false);
			$content = Variable::get('ecommerce_order_rec_email_'.$k,false);
			if($content) {
			    Utils_RecordBrowserCommon::new_record('premium_ecommerce_emails',array('subject'=>$subj,'content'=>$content,'language'=>$k,'send_on_status'=>2));
			}
			Variable::delete('ecommerce_order_rec_email_'.$k,false);
			Variable::delete('ecommerce_order_rec_email_'.$k.'S',false);
		}

		$subj = Variable::get('ecommerce_order_rec_emailS',false);
		$content = Variable::get('ecommerce_order_rec_email',false);
		if($content) {
		    Utils_RecordBrowserCommon::new_record('premium_ecommerce_emails',array('subject'=>$subj,'content'=>$content,'send_on_status'=>2));
		}
		Variable::delete('ecommerce_order_rec_email',false);
		Variable::delete('ecommerce_order_rec_emailS',false);

		$subj = Variable::get('ecommerce_order_shi_emailS',false);
		$content = Variable::get('ecommerce_order_shi_email',false);
		if($content) {
		    Utils_RecordBrowserCommon::new_record('premium_ecommerce_emails',array('subject'=>$subj,'content'=>$content,'send_on_status'=>7));
		}
		Variable::delete('ecommerce_order_shi_email',false);
		Variable::delete('ecommerce_order_shi_emailS',false);
}
?>