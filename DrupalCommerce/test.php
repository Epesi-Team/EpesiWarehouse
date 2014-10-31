<?php 
define('SET_SESSION',false);
define('CID',false); 
require_once('../../../../include.php');
ModuleManager::load_modules();

Acl::set_sa_user();

$drupal_id = 1;
$x = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'views/epesi_products_search_by_product_id.json?'.http_build_query(array('display_id'=>'services_1','args'=>array(1303,''))));
print_r($x);
//				Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'order/'.$drupal_order_id,array('status'=>$drupal_status));
die();


//$ret = Premium_Warehouse_DrupalCommerceCommon::drupal_request(1,'taxonomy_vocabulary.getTree',array(1,0,99));
$voc = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'taxonomy_vocabulary',array('pagesize'=>10000));
$epesi_vocabulary = null;
foreach($voc as $v) {
  if($v['machine_name']=='epesi_category') {
    $epesi_vocabulary = $v['vid'];
    break;
  }
}
if(!$epesi_vocabulary) continue;

$category_exists = array();
$category_mapping = array();
try {
  $terms = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'taxonomy_vocabulary/getTree',array('vid'=>$epesi_vocabulary,'maxdepth'=>99));
  foreach($terms as $t) {
//    Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'taxonomy_term.delete',array($t['tid']));
//    continue;
    $category_exists[$t['tid']] = 1;
    $term_data = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'taxonomy_term/'.$t['tid']);
    $category_mapping[$term_data['field_epesi_category_id']['und'][0]['value']] = $t['tid'];
  }
} catch(Exception $e) {}

$epesi_categories_temp = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_categories');
$epesi_category_names = array();
$epesi_category_parents = array();
foreach($epesi_categories_temp as $c) {
  if(isset($category_mapping[$c['id']])) {
//    unset($category_mapping[$c['id']]);
    $category_exists[$category_mapping[$c['id']]] = 2;
//    continue;
  }
  $epesi_category_names[$c['id']] = $c['category_name'];
  $epesi_category_parents[$c['id']] = $c['parent_category'];
}

do {
  //TODO: use or remove meta tags from descriptions from ecommerce recordsets
  foreach($epesi_category_names as $id=>$name) {
    if($epesi_category_parents[$id] && !isset($category_mapping[$epesi_category_parents[$id]])) continue;
    $term = new stdClass();
    $term->name = $name;
    $term->name_original = $name;
    $term->vid = $epesi_vocabulary;
    $term->field_epesi_category_id['und'][0]['value']=$id;
    $term->name_field = array();
    $term->description_field = array();
    $term->description_original = '';
    $term->format = 'filtered_html';
    $term->translations['original']='en';
    
    if($epesi_category_parents[$id])
      $term->parent = $category_mapping[$epesi_category_parents[$id]];
    if(isset($category_mapping[$id])) {
      Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'taxonomy_term/'.$category_mapping[$id],array('term'=>$term));
    } else {
      $p = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'taxonomy_term',array('term'=>$term));
      $all_terms = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'taxonomy_vocabulary/getTree',array('vid'=>$epesi_vocabulary,'maxdepth'=>99));
      foreach($all_terms as $t) {
        if(!isset($category_exists[$t['tid']])) {
          $term_data = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'taxonomy_term/'.$t['tid']);
          if($term_data['field_epesi_category_id']['und'][0]['value']==$id) {
            $category_exists[$t['tid']] = 2;
            $category_mapping[$term_data['field_epesi_category_id']['und'][0]['value']] = $t['tid'];
            break;
          }
        }
      }
    }
    
    $translations = Utils_RecordBrowserCommon::get_records('premium_ecommerce_cat_descriptions',array('category'=>$id));
    foreach($translations as $translation) {
      $values = array();
      $values['name_field'][$translation['language']][0]['value'] = $translation['display_name'];
      $values['description_field'][$translation['language']][0]['value'] = $translation['long_description'];
      $values['description_field'][$translation['language']][0]['format'] = 'filtered_html';
      $values['description_field'][$translation['language']][0]['summary'] = $translation['short_description'];
      $info = array(
        'language'=>$translation['language'],
        'source'=>$translation['language']=='en'?'':'en',
        'status'=>1,
        'translate'=>0,
      );
      Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_translation/translate',array('entity_type'=>'taxonomy_term','entity_id'=>$category_mapping[$id],'translation'=>$info,'values'=>$values));
    }

    unset($epesi_category_names[$id]);
  }
} while(!empty($epesi_category_names));

//remove elements with invalid epesi_category field
foreach($category_exists as $tid=>$val) {
  if($val===1) Premium_Warehouse_DrupalCommerceCommon::drupal_delete($drupal_id,'taxonomy_term/'.$tid);
}
