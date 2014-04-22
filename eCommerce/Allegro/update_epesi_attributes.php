#!/usr/bin/env php
<?php

define('CID',false);
define('SET_SESSION',false);
if(isset($argv))
	define('EPESI_DIR','/');
require_once('../../../../../include.php');
set_time_limit(0);
ini_set('memory_limit', '512M');
ModuleManager::load_modules();
Acl::set_user(2);
$allegro = Premium_Warehouse_eCommerce_AllegroCommon::get_lib();
//if(!$allegro) die();

$ids = DB::GetAssoc('SELECT auction_id,item_id FROM premium_ecommerce_allegro_auctions WHERE active=1');
foreach($ids as $auction_id=>$iid) {
//        	$auction_id = '3859751621';
        	$attributes = array();

		$ret = $allegro->get_auction_info($auction_id,0,0,1,0,0);
		foreach($ret['itemAttribList']->item as $it) {
		    if(!isset($it->attribName) || !is_string($it->attribName) || !isset($it->attribValues->item) ) continue;
		    if(is_array($it->attribValues->item)) $it->attribValues->item = implode(",\n",$it->attribValues->item);
		    if(!is_string($it->attribValues->item)) continue;
		    $attributes[] = array('name'=>$it->attribName,'values'=>$it->attribValues->item);
		}

	        $item_parameters_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters',array('item_name'=>$iid,'language'=>'pl'),array('id','parameter'));
		$item_parameters = array();
	        foreach($item_parameters_tmp as $rr)
		    $item_parameters[$rr['parameter']] = $rr['id'];
	        unset($item_parameters_tmp);

	        foreach($attributes as $a) {
	    	    $param_value = substr(str_replace("\n",'<br />',strip_tags($a['values'])),0,256);
	    	    if(!$param_value) continue;
		    $a['group'] = 'Podstawowe informacje';
		    $group = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameter_groups',array('group_code'=>$a['group']),array('id'));
		    if($group) {
		        $group = array_shift($group);
		        $group = $group['id'];
		    } else {
		        $group = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_groups',array('group_code'=>$a['group']));
		        $parameter_group_label = array('group'=>$group,
                            'language'=>'pl',
                            'label'=>$a['group']);
		        Utils_RecordBrowserCommon::new_record('premium_ecommerce_param_group_labels',$parameter_group_label);
		    }
		    $param = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameters',array('parameter_code'=>$a['name']),array('id'));
		    if($param) {
		        $param = array_shift($param);
		        $param = $param['id'];
		    } else {
		        $param = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameters',array('parameter_code'=>$a['name']));
		        $parameter_label = array('parameter'=>$param,
		                    'language'=>'pl',
		                    'label'=>$a['name']);
		        Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_labels',$parameter_label);
		    }

		    $item_params = array('item_name'=>$iid,
                            'parameter'=>$param,
                            'group'=>$group,
                            'language'=>'pl',
                            'value'=>$param_value);
		    if(isset($item_parameters[$param])) {
		        Utils_RecordBrowserCommon::update_record('premium_ecommerce_products_parameters',$item_parameters[$param],$item_params);
		        unset($item_parameters[$param]);
		    } else
		        Utils_RecordBrowserCommon::new_record('premium_ecommerce_products_parameters',$item_params);
		}
}
//$ret = $a->get_bids_user_data($bids_ids);
//print_r($ret);
//$transactions = $a->get_transactions($bids_ids);
//print_r($transactions);
