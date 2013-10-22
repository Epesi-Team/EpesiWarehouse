<?php 
define('SET_SESSION',false);
define('CID',false); 
require_once('../../../../include.php');
ModuleManager::load_modules();

Acl::set_user(1);

$drupal_id = 1;

$drupal_products_tmp = Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'product.index',array('product_id,sku',1,'true',array('type'=>'epesi_products'),array(),'sku','ASC',999999999999999999));
$drupal_products = array();
$drupal_done = array();
foreach($drupal_products_tmp as $row) {
  $drupal_products[$row['sku']] = $row['product_id'];
}
unset($drupal_products_tmp);

$products = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('publish'=>1),array(),array('item_name'=>'ASC'));
foreach($products as $row) {
  $row = array_merge($row,Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$row['item_name']));
  $data = array('sku'=>$row['sku'],'title'=>$row['item_name'],
    'commerce_price'=>array('amount'=>1, //TODO mnożenie x 100 aby pozbyc sie przecinkow
                    'currency_code'=>'USD'),
    'type'=>'epesi_products',
    );
  if(isset($drupal_products[$row['sku']])) {
    //check product
    $drupal_data = Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'product.retrieve',array($drupal_products[$row['sku']]));
    $update = false;
    foreach($data as $key=>$val) {
      if(is_array($val)) {
        foreach($val as $key2=>$val2) {
          if($val2!=$drupal_data[$key][$key2]) {
            $update = true;
            break;
          }
        }
      } else {
        if($val!=$drupal_data[$key]) {
          $update = true;
          break;
        }
      }
    }
    if($update) Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'product.update',array($drupal_products[$row['sku']],$data));
    
    //check node
    $nodes = Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'views.retrieve',array('view_name'=>'epesi_products_search_by_product_id','display_id'=>'services_1','args'=>array($drupal_products[$row['sku']])));
//    print_r($drupal_data);
    print_r($nodes);
  } else {
    //create
    $product = Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'product.create',array($data));
    $node = new stdClass();
    $node->type='epesi_products';
    $node->title=$node->title_field['und'][0]['value']=$row['item_name'];
    $node->body=$row['description'];
    $node->field_product['und'][0]['product_id'] = $product->product_id;
    Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'node.create',array($node));
  }
  $drupal_done[$row['sku']] = 1;
}

foreach($drupal_products as $sku=>$id) {
  if(!isset($drupal_done[$sku])) {
    Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'product.delete',array($id));
//    Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'node.delete',array($id)); //tutaj trzebaby pobrac poprawne id node
  }
}
