<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

//product associations/kits
$fields = array(
    array('name' => _M('Item Name'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_DrupalCommerce'.'Common', 'display_item_name')),
    array('name' => _M('Associated Item'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>true),
    array('name' => _M('Type'),	'type'=>'commondata', 'param'=>array('Premium/Warehouse/eCommerce/AssociationTypes'), 'required'=>true, 'extra'=>false, 'visible'=>true),
    array('name' => _M('Associated Item Quantity'), 	'type'=>'integer', 'required'=>true, 'extra'=>false,'visible'=>true),
    array('name' => _M('Associated Item Price Change (%%)'),'type'=>'float', 'required'=>true, 'extra'=>false,'visible'=>true),
);
Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_associations', $fields);
Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_associations', false);
Utils_RecordBrowserCommon::set_caption('premium_ecommerce_associations', _M('eCommerce - associations/kits'));

Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/DrupalCommerce', 'associations_addon_item', 'Premium_Warehouse_DrupalCommerceCommon::associations_addon_item_parameters');
Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/DrupalCommerce', 'associations_addon', 'Premium_Warehouse_DrupalCommerceCommon::associations_addon_parameters');

Utils_CommonDataCommon::new_id('Premium/Warehouse/eCommerce/AssociationTypes', true);
Utils_CommonDataCommon::new_array('Premium/Warehouse/eCommerce/AssociationTypes',array());

Variable::set('ecommerce_item_associations',true);
Utils_RecordBrowserCommon::add_default_access('premium_ecommerce_associations');
