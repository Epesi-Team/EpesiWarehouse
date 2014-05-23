<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
    Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_payments_carriers',
	array('name' => _M('Shipment Service Type'), 	'type'=>'commondata', 'required'=>false, 'extra'=>false, 'visible'=>true, 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Shipment_Types','Shipment'), 'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon','QFfield_shipment_service_type'),'position'=>'Shipment')
    );
}
?>