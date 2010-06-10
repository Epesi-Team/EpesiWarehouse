<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce_3rdp__Plugin_bdk implements Premium_Warehouse_eCommerce_3rdp__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'BDK';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array();
	}

    public function download($parameters,$item,$langs,$verbose) {
        if(!in_array('pl',$langs)) return;
        if(!$item['upc']) {
            if($verbose)
                 Epesi::alert("BDK: Missing UPC/EAN.");
            return;
        }

    	include("xmlrpc.inc");
        $GLOBALS['xmlrpc_internalencoding']='UTF-8';

	    $c = new xmlrpc_client("/export/test/", "www.kupic.pl", 80);
    	$c->return_type = 'phpvals'; // let client give us back php values instead of xmlrpcvals
    	$f = new xmlrpcmsg('test.getProductByEAN', array(php_xmlrpc_encode($item['upc'])));
    	$r =& $c->send($f);
    	if($r->faultCode()) {
    	    if(preg_match('/denied/si',$r->faultString()))
    	        Epesi::alert('BDK: access denied');
	        return;
	    }
	    set_time_limit(0);
	    
	    //parse data
	    $ret = $r->value();
   		foreach($ret['attributes'] as & $a) {
   			if($a['tech']) {
   				foreach($a['values'] as & $t) {
   					$tret = $c->send(new xmlrpcmsg('test.getTechDescription', array(php_xmlrpc_encode($t))));
   					if($tret->faultCode()) continue;
   					$t2 = $tret->value();
   					$t = $t2['name'].(isset($t2['synonyms']) && $t2['synonyms']?' ('.implode(', ',$t2['synonyms']).')':'');
   				}
   			}
   			unset($a['tech']);
	    }
	    
	    if(!$item['manufacturer'] && isset($ret['brand']) && $ret['brand']) {
            $manufacturers = CRM_ContactsCommon::get_companies(array('company_name'=>$ret['brand']), array('group','company_name'));
            if($manufacturer = array_shift($manufacturers)) {
                Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$item['id'],array('manufacturer'=>$manufacturer['id']));
                $item['manufacturer'] = $manufacturer['id'];
                if(!in_array('manufacturer', $manufacturer['group'])) {
                    $manufacturer['group']['manufacturer'] = 'manufacturer';
                    Utils_RecordBrowserCommon::update_record('company',$manufacturer['id'],array('group'=>$manufacturer['group']));
                }
            }
        }

        $display_name = $ret['name'].($ret['name']!=$ret['model']?' '.$ret['model']:'');
        if(strlen($display_name)<128 && $item['manufacturer']) {
                if(!isset($manufacturer) || !$manufacturer)
                    $manufacturer = CRM_ContactsCommon::get_company($item['manufacturer']);
                if(!preg_match('/'.$manufacturer['company_name'].'/i',$display_name) && strlen($manufacturer['company_name'].' '.$display_name)<128)
                    $display_name = $manufacturer['company_name'].' '.$display_name;
        }
        
        
        $descriptions_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$item['id'],'language'=>'pl'),array('id','language'));
        $descriptions = null;
        foreach($descriptions_tmp as $rr) {
            $descriptions = $rr['id'];
            break;
        }
        unset($descriptions_tmp);

        $product_desc = array('item_name'=>$item['id'],
                        'language'=>'pl',
                        'display_name'=>substr($display_name,0,128),
                        'short_description'=>str_replace('\n','<br />',$ret['descr']));
        if(isset($descriptions)) {
            if($product_desc['display_name']=='') unset($product_desc['display_name']);
            if($product_desc['short_description']=='') unset($product_desc['short_description']);
            Utils_RecordBrowserCommon::update_record('premium_ecommerce_descriptions',$descriptions,$product_desc);
        } else
            $descriptions = Utils_RecordBrowserCommon::new_record('premium_ecommerce_descriptions',$product_desc);

        //parameters
        $item_parameters_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters',array('item_name'=>$item['id'],'language'=>'pl'),array('id','parameter'));
        $item_parameters = array();
        foreach($item_parameters_tmp as $rr)
            $item_parameters[$rr['parameter']] = $rr['id'];
        unset($item_parameters_tmp);


        foreach($ret['attributes'] as $a) {
            if(!$a['group']) $a['group'] = 'Podstawowe informacje';
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

            $item_params = array('item_name'=>$item['id'],
                            'parameter'=>$param,
                            'group'=>$group,
                            'language'=>'pl',
                            'value'=>substr(str_replace('\n','<br />',(string)implode('<br />',$a['values'])),0,256));
            if(isset($item_parameters[$param])) {
                Utils_RecordBrowserCommon::update_record('premium_ecommerce_products_parameters',$item_parameters[$param],$item_params);
                unset($item_parameters[$param]);
            } else
                Utils_RecordBrowserCommon::new_record('premium_ecommerce_products_parameters',$item_params);

        }

        $pret = $c->send(new xmlrpcmsg('test.getProductPhotos', array(php_xmlrpc_encode($ret['id']))));
        if(!$pret->faultCode()) {
            $pictures = $pret->value();
            foreach($pictures as $p) {
                $old_pics = array();
                $ooo = Utils_AttachmentCommon::get('Premium/Warehouse/eCommerce/Products/'.$item['id']);
                if(is_array($ooo))
                    foreach($ooo as $oo) {
                        if(!$oo['text'] && preg_match('/^bdk_/',$oo['original']))
                            $old_pics[$oo['original']] = $oo['id'];
                    }
                foreach($pictures as $pp) {
                    $base_pp = 'bdk_'.md5(basename($pp['photo'])).'.jpg';
                    if(!isset($old_pics[$base_pp])) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL,$pp['photo']);
                        $temp_file = DATA_DIR.'/Premium_Warehouse_eCommerce/'.md5(microtime(true));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $response = curl_exec ($ch);
                        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close ($ch);

                        if($response_code==200) {
                            file_put_contents($temp_file,$response);
                            $temp_file2 = Utils_ImageCommon::create_thumb($temp_file,800,600);
                            $temp_file2 = $temp_file2['thumb'];
                            Utils_AttachmentCommon::add('Premium/Warehouse/eCommerce/Products/'.$item['id'],
                                        0,Acl::get_user(),'',$base_pp,$temp_file2,null,null,array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
                            @unlink($temp_file2);
                            @unlink($temp_file);
                        }
                    } else {
                        unset($old_pics[$base_pp]);
                    }
                }
                if(!empty($old_pics))
                    Utils_AttachmentCommon::persistent_mass_delete('Premium/Warehouse/eCommerce/Products/'.$item['id'],false,array_values($old_pics));
            }
        }
        
        return array('pl');
    }	
}
?>
