<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_products',
array('name' => _M('Items'), 		'type'=>'multiselect', 'required'=>true, 'param'=>'premium_warehouse_items::SKU|Item Name;Premium_Warehouse_DrupalCommerceCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_DrupalCommerce'.'Common', 'display_items'), 'filter'=>true,'position'=>'Item Name'));
DB::Execute('UPDATE premium_ecommerce_products_data_1 SET f_items=CONCAT("__",f_item_name,"__")');
DB::Execute('UPDATE utils_attachment_local SET local=REPLACE(local,"premium_ecommerce_products/","premium_ecommerce_products_tmp/")');
$assoc_products = DB::GetAssoc('SELECT f_item_name,id FROM premium_ecommerce_products_data_1');
foreach($assoc_products as $warehouse=>$ecommerce) {
    DB::Execute('UPDATE utils_attachment_local SET local="premium_ecommerce_products/'.$ecommerce.'" WHERE local="premium_ecommerce_products_tmp/'.$warehouse.'"');
}
Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_products','Item Name');

//product descriptions
Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_descriptions',
array('name' => _M('Product'), 			'type'=>'select', 'required'=>true, 'param'=>'premium_ecommerce_products::Items', 'extra'=>false, 'visible'=>true,'position'=>'Item Name'));
$descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions');
foreach($descs as $desc) {
    DB::Execute('UPDATE utils_attachment_local SET local="premium_ecommerce_descriptions/'.$desc['id'].'" WHERE local="premium_ecommerce_descriptions/'.$desc['language'].'/'.$desc['item_name'].'"');
    $p = isset($assoc_products[$desc['item_name']])?$assoc_products[$desc['item_name']]:null;
    if($p) Utils_RecordBrowserCommon::update_record('premium_ecommerce_descriptions',$desc['id'],array('product'=>$p));
    else Utils_RecordBrowserCommon::delete_record('premium_ecommerce_descriptions',$desc['id']);
}
Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_descriptions','Item Name');

//prices
Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_prices',
array('name' => _M('Product'), 			'type'=>'select', 'required'=>true, 'param'=>'premium_ecommerce_products::Items', 'extra'=>false, 'visible'=>true,'position'=>'Item Name'));
$descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices');
foreach($descs as $desc) {
    $p = isset($assoc_products[$desc['item_name']])?$assoc_products[$desc['item_name']]:null;
    if($p) Utils_RecordBrowserCommon::update_record('premium_ecommerce_prices',$desc['id'],array('product'=>$p));
    else Utils_RecordBrowserCommon::delete_record('premium_ecommerce_prices',$desc['id']);
}
Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_prices','Item Name');
DB::DropIndex('ecommerce_prices_name_currency__idx','premium_ecommerce_prices_data_1');
DB::CreateIndex('ecommerce_prices_name_currency__idx','premium_ecommerce_prices_data_1',array('f_product','f_currency','active'));

//params
Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_products_parameters',
array('name' => _M('Product'), 			'type'=>'select', 'required'=>true, 'param'=>'premium_ecommerce_products::Items', 'extra'=>false, 'visible'=>true,'position'=>'Item Name'));
$descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters');
foreach($descs as $desc) {
    $p = isset($assoc_products[$desc['item_name']])?$assoc_products[$desc['item_name']]:null;
    if($p) Utils_RecordBrowserCommon::update_record('premium_ecommerce_products_parameters',$desc['id'],array('product'=>$p));
    else Utils_RecordBrowserCommon::delete_record('premium_ecommerce_products_parameters',$desc['id']);
}
Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_products_parameters','Item Name');

//assocs
Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_associations',
array('name' => _M('Product'), 			'type'=>'select', 'required'=>true, 'param'=>'premium_ecommerce_products::Items', 'extra'=>false, 'visible'=>true,'position'=>'Item Name'));
$descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_associations');
foreach($descs as $desc) {
    $p = isset($assoc_products[$desc['item_name']])?$assoc_products[$desc['item_name']]:null;
    if($p) Utils_RecordBrowserCommon::update_record('premium_ecommerce_associations',$desc['id'],array('product'=>$p));
    else Utils_RecordBrowserCommon::delete_record('premium_ecommerce_associations',$desc['id']);
}
Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_associations','Item Name');
?>
