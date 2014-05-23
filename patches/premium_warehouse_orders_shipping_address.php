<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_Items_Orders')>=0) {
    $a = array(
			array('name' => _M('Shipping Address'),'type'=>'page_split', 'required'=>true),
			array('name' => _M('Shipping Company'), 		'type'=>'crm_company', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_Items_OrdersCommon','company_crits')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Shipping Contact'), 		'type'=>'crm_contact', 'param'=>array('field_type'=>'select', 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Shipping Company Name'), 	'type'=>'text', 'param'=>'128', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_company_name'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon','QFfield_company_name')),
			array('name' => _M('Shipping Last Name'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_first_name')),
			array('name' => _M('Shipping First Name'), 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_last_name')),
			array('name' => _M('Shipping Address 1'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name' => _M('Shipping Address 2'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name' => _M('Shipping City'),	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name' => _M('Shipping Country'),		'type'=>'commondata', 'required'=>false, 'param'=>array('Countries'), 'extra'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name' => _M('Shipping Zone'),			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Country'), 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name' => _M('Shipping Postal Code'),	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Shipping Phone'),	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false));

    foreach($a as $b)
        Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders', $b);
}
?>