<?php 
define('SET_SESSION',false);
define('CID',false); 
require_once('../../../../include.php');
ModuleManager::load_modules();

Acl::set_user(1);

$drupal_id = 1;

$drupal_products_tmp = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'product',array('fields'=>'product_id,sku','filter'=>array('type'=>'epesi_products'),'sort_by'=>'sku','limit'=>999999999999999999));
$drupal_products = array();
$drupal_done = array();
foreach($drupal_products_tmp as $row) {
  $drupal_products[$row['sku']] = $row['product_id'];
}
unset($drupal_products_tmp);

$currencies = DB::GetAssoc('SELECT id,code,decimals FROM utils_currency WHERE active=1');
$taxes = DB::GetAssoc('SELECT id, f_percentage FROM data_tax_rates_data_1 WHERE active=1');

$products = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('publish'=>1),array(),array('item_name'=>'ASC'));
foreach($products as $row) {
  $row = array_merge($row,Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$row['item_name']));
  
  //set prices
  $prices = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$row['id']));
  $data = array('sku'=>$row['sku'],'title'=>$row['item_name'],'type'=>'epesi_products');
  foreach($prices as $price) {
    if(!isset($currencies[$price['currency']])) continue;
    $currency = $currencies[$price['currency']];
    $data['commerce_price_'.strtolower($currency['code'])]=array('amount'=>$price['gross_price']*pow(10,$currency['decimals']),
                    'currency_code'=>$currency['code']);//TODO: taxes
    if(!isset($data['commerce_price']))
      $data['commerce_price'] = $data['commerce_price_'.strtolower($currency['code'])];
  }
  if($row['net_price']) {
    $item_price = Utils_CurrencyFieldCommon::get_values($row['net_price']);
    if($item_price[0] && isset($currencies[$item_price[1]])) {
      $currency = $currencies[$item_price[1]];
      $data['commerce_price']=array('amount'=>round(((float)$item_price[0])*(100+$taxes[$row['tax_rate']])/100,$currency['decimals'])*pow(10,$currency['decimals']),
                    'currency_code'=>$currency['code']);//TODO: taxes
      if(!isset($data['commerce_price_'.strtolower($currency['code'])]))
        $data['commerce_price_'.strtolower($currency['code'])] = $data['commerce_price'];
    }
  }
  $quantity = Premium_Warehouse_Items_LocationCommon::get_item_quantity_in_warehouse($row['id']) - DB::GetOne('SELECT SUM(d.f_quantity) FROM premium_warehouse_items_orders_details_data_1 d INNER JOIN premium_warehouse_items_orders_data_1 o ON (o.id=d.f_transaction_id) WHERE ((o.f_transaction_type=1 AND o.f_status in (-1,2,3,4,5)) OR (o.f_transaction_type=4 AND o.f_status in (2,3))) AND d.active=1 AND o.active=1 AND d.f_item_name=%d',array($row['id']));
  if($quantity<=0) {
    if($row['always_on_stock']) {
      $quantity = 9999999;
    /*} else {
     //TODO: distributors
      $distributors = DB::GetAll('SELECT dist_item.quantity,
					dist_item.quantity_info,
					dist_item.price,
					dist.f_items_availability,
					dist.f_minimal_profit,
					dist.f_percentage_profit,
					dist_item.price_currency
					FROM premium_warehouse_wholesale_items dist_item
					INNER JOIN premium_warehouse_distributor_data_1 dist ON dist.id=dist_item.distributor_id
					WHERE dist_item.item_id=%d AND dist_item.quantity>0 AND dist.active=1',array($row['item_name']));
      $minimal_aExp = null;
      foreach($distributors as $kkk=>$dist) {
        if($dist['quantity']>-$quantity) {
          $dist['quantity'] += $quantity;

          $aExp2 = array();
          $aExp2['distributorQuantity'] = $dist['quantity'];
					$aExp2['iAvailable'] = $dist['iAvailable'];
					$aExp2['sAvailableInfo'] = $dist['quantity_info'];

          if($autoprice && $dist['price_currency']==$currency) {
					    $user_price = $aExp['fPrice'];
						$dist_price = round((float)$dist['price']*(100+$taxes[$aExp['tax2']])/100,2);
						if($user_price>=$dist_price) {
							$aExp2['fPrice'] = $user_price;
							$aExp2['fPrice'] = $aExp['fPriceNet'];
						} else {
							$netto = $dist['price'];
							$profit = $netto*(is_numeric($dist['f_percentage_profit'])?$dist['f_percentage_profit']:$percentage)/100;
							$minimal2 = (is_numeric($dist['f_minimal_profit'])?$dist['f_minimal_profit']:$minimal);
							if($profit<$minimal2) $profit = $minimal2;
							$aExp2['fPrice'] = round((float)($netto+$profit)*(100+$taxes[$aExp['tax2']])/100,2);
							$aExp2['fPriceNet'] = round((float)($netto+$profit),2);
							$aExp2['tax'] = $aExp['tax2'];		
						}
					}
					if($minimal_aExp===null || (!isset($minimal_aExp['fPrice']) && isset($aExp2['fPrice'])) || $minimal_aExp['fPrice']>$aExp2['fPrice'])
                                                $minimal_aExp = $aExp2;
				}
			}
			if($minimal_aExp!==null) {
			        $aExp = array_merge($aExp,$minimal_aExp);
				$reserved[$aExp['iProduct']] = 0;
			}
			unset($distributors);
*/
    }

    if($quantity<=0) continue; //skip if not available
  }
  $data['commerce_stock'] = $quantity;
  
  //get images
  Premium_Warehouse_DrupalCommerceCommon::$images = array();
  Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products',array('Premium_Warehouse_DrupalCommerceCommon','copy_attachment'),true,array($drupal_id,1));
  Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions',array('Premium_Warehouse_DrupalCommerceCommon','copy_attachment'),true,array($drupal_id,0));
  $field_images = array();
  foreach(Premium_Warehouse_DrupalCommerceCommon::$images as $lang=>$fids) {
    foreach($fids as $fid) {
      if($lang=='und') $data['field_images'][]['fid'] = $fid;
      else $field_images[$lang][]['fid'] = $fid;
    }
  }
  //update each language... if there is no field_images translation, default/random language images are displayed
  foreach(Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages') as $lang=>$lang_name)
    if(!isset($field_images[$lang])) $field_images[$lang] = array();
  
  //update product
  $drupal_product_id = 0;
  $nid = 0;
  if(isset($drupal_products[$row['sku']])) {
    //check product
    $drupal_data = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'product/'.$drupal_products[$row['sku']]);
    $update = false;
    foreach($data as $key=>$val) {
      if(is_array($val)) {
        foreach($val as $key2=>$val2) {
          if(!isset($drupal_data[$key][$key2]) || $val2!=$drupal_data[$key][$key2]) {
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
    if($update) Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'product/'.$drupal_products[$row['sku']],$data);
    $drupal_product_id = $drupal_products[$row['sku']];
    $nodes = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'views/epesi_products_search_by_product_id.json?'.http_build_query(array('display_id'=>'services_1','args'=>array($drupal_products[$row['sku']],''))));
    $nid = isset($nodes[0]['nid'])?$nodes[0]['nid']:0;
  } else {
    $product = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'product',$data);
    $drupal_product_id = $product['product_id'];
  }
  

  if($drupal_product_id) {
    //translate product images
    foreach($field_images as $lang=>$images) {
      $values=array();
      $values['field_images'][$lang] = array_merge($data['field_images'],$images);
      $info = array(
        'language'=>$lang,
        'source'=>$lang=='en'?'':'en',
        'status'=>1,
        'translate'=>0,
      );
      Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_translation/translate',array('entity_type'=>'commerce_product','entity_id'=>$drupal_product_id,'translation'=>$info,'values'=>$values));
    }


    //update node of product
    $node = array();
    $node['type']='epesi_products';
    $node['title']=$node['title_field']['en'][0]['value']=$row['item_name'];
    $node['body']['en'][0]['value']=$row['description'];
    $node['body']['en'][0]['format'] = 'filtered_html';
    $node['field_product']['und'][0]['product_id'] = $drupal_product_id;
    $node['promote']=$row['recommended']?1:0;
    $node['sticky']=$row['recommended']?1:0;
    $translations = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$row['id'],'language'=>'en'));
    if($translations) {
      $translations = array_shift($translations);
      $node['title']=$node['title_field']['en'][0]['value'] = $translations['display_name'];
      $node['body']['en'][0]['value']=$translations['long_description'];
      $node['body']['en'][0]['summary']=$translations['short_description'];
    }
    if($nid) {
      Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'node/'.$nid,$node);
    } else {
      $tmp = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'node',$node);
      $nid = $tmp['nid'];
    }

    $translations = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$row['id']));
    foreach($translations as $translation) {
      $values = array();
      $values['title_field'][$translation['language']][0]['value'] = $translation['display_name'];
      $values['body'][$translation['language']][0]['value'] = $translation['long_description'];
      $values['body'][$translation['language']][0]['format'] = 'filtered_html';
      $values['body'][$translation['language']][0]['summary'] = $translation['short_description'];
      $info = array(
        'language'=>$translation['language'],
        'source'=>$translation['language']=='en'?'':'en',
        'status'=>1,
        'translate'=>0,
      );
      Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_translation/translate',array('entity_type'=>'node','entity_id'=>$nid,'translation'=>$info,'values'=>$values));
    }
    
    $drupal_done[$row['sku']] = 1;
  }
}

foreach($drupal_products as $sku=>$id) {
  if(!isset($drupal_done[$sku])) {
    Premium_Warehouse_DrupalCommerceCommon::drupal_delete($drupal_id,'product/'.$id);
//    Premium_Warehouse_DrupalCommerceCommon::drupal_request($drupal_id,'node.delete',array($id)); //tutaj trzebaby pobrac poprawne id node
  }
}
