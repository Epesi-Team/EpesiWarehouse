<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');
$duplicates = array();
$products = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products');
foreach($products as $p) {
    foreach($p['items'] as $i) {
        if(!isset($duplicates[$i])) $duplicates[$i] = array();
        $duplicates[$i][] = $p['id'];
    }
}

foreach($duplicates as $d){
    if(count($d)>1) {
        $main = array_shift($d);
        foreach($d as $d2) {
            DB::Execute('UPDATE utils_attachment_local SET local="premium_ecommerce_products/'.$main.'" WHERE local="premium_ecommerce_products/'.$d2.'"');
            DB::Execute('UPDATE premium_ecommerce_descriptions_data_1 SET f_product='.$main.' WHERE f_product='.$d2.'');
            DB::Execute('UPDATE premium_ecommerce_prices_data_1 SET f_product='.$main.' WHERE f_product='.$d2.'');
            DB::Execute('UPDATE premium_ecommerce_associations_data_1 SET f_product='.$main.' WHERE f_product='.$d2.'');
            DB::Execute('UPDATE premium_ecommerce_products_parameters_data_1 SET f_product='.$main.' WHERE f_product='.$d2.'');
            Utils_RecordBrowserCommon::delete_record('premium_ecommerce_products',$d2);
        }
    }
}
?>
