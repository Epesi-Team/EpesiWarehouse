<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce_3rdp__Plugin_icecat implements Premium_Warehouse_eCommerce_3rdp__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Icecat';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array(
			'Login'=>'text',
			'Password'=>'password'
		);
	}

    private $user;
    private $pass;
    public function download($parameters,$item,$langs,$verbose) {
        $this->user = $parameters['Login'];
        $this->pass = $parameters['Password'];

        $query_arr = array();
        $ret = false;
        if($item['upc']) {
            $query_arr['ean_upc'] = $item['upc'];
            $ret = $this->icecat_get($query_arr+array('lang'=>'en','output'=>'productxml'),'default');
        }
        if(!$ret) {
            $query_arr = array();
            $prod_id = $item['manufacturer_part_number'];
            if(!$prod_id)
                    $prod_id = $item['product_code'];
            if(!$prod_id) {
                if($verbose)
                        Epesi::alert("Icecat: missing product code or manufacturer part number.");
                return false;
            }
            if(!$item['manufacturer']) {
                if($verbose)
                    Epesi::alert("Icecat: missing product manufacturer.");
                return false;
            }
            if($item['manufacturer'] && $prod_id) {
                $manufacturer = CRM_ContactsCommon::get_company($item['manufacturer']);
                $query_arr['prod_id'] = $prod_id;
                $query_arr['vendor'] = $manufacturer['company_name'];
                $ret = $this->icecat_get($query_arr+array('lang'=>'en','output'=>'productxml'),'default');
                if(!$ret && $item['product_code']) {
                    $query_arr['prod_id'] = $item['product_code'];
                    $ret = $this->icecat_get($query_arr+array('lang'=>'en','output'=>'productxml'),'default');
                }
            }
        }
        if(!$ret) {
            Epesi::alert("There is no product data on icecat server.");
            return;
        }

        //descriptions in all langs
        $descriptions_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$item['id']),array('id','language'));
        $descriptions = array();
        foreach($descriptions_tmp as $rr)
            $descriptions[$rr['language']] = $rr['id'];
        unset($descriptions_tmp);

        //parameters codes
        $parameters_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameters',array('~parameter_code'=>'icecat_%'),array('id','parameter_code'));
        $parameters = array();
        foreach($parameters_tmp as $rr)
            $parameters[$rr['parameter_code']] = $rr['id'];
        unset($parameters_tmp);

        //parameter group codes
        $parameter_groups_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameter_groups',array('~group_code'=>'icecat_%'),array('id','group_code'));
        $parameter_groups = array();
        foreach($parameter_groups_tmp as $rr)
            $parameter_groups[$rr['group_code']] = $rr['id'];
        unset($parameter_groups_tmp);

        set_time_limit(0);
        $langs_ok = array();
        foreach($langs as $code) {
            $obj = $this->icecat_get($query_arr+array('lang'=>$code,'output'=>'productxml'));
            if(!$obj) continue;
            $langs_ok[] = $code;

            if($obj) {
                //supplier
                if(!$item['manufacturer'] && isset($obj->Supplier['Name'])) {
                    $manufacturers = CRM_ContactsCommon::get_companies(array('company_name'=>$obj->Supplier['Name']), array('group','company_name'));
                    if($manufacturer = array_shift($manufacturers)) {
                        Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$item['id'],array('manufacturer'=>$manufacturer['id']));
                        $item['manufacturer'] = $manufacturer['id'];
                        if(!in_array('manufacturer', $manufacturer['group'])) {
                            $manufacturer['group']['manufacturer'] = 'manufacturer';
                            Utils_RecordBrowserCommon::update_record('company',$manufacturer['id'],array('group'=>$manufacturer['group']));
                        }
                    }
                }


            //description
            $display_name = (string)$obj->Product[0]['Name'];
            if(strlen($display_name)<128 && $item['manufacturer']) {
                if(!isset($manufacturer) || !$manufacturer)
                    $manufacturer = CRM_ContactsCommon::get_company($item['manufacturer']);
                if(!preg_match('/'.$manufacturer['company_name'].'/i',$display_name) && strlen($manufacturer['company_name'].' '.$display_name)<128)
                    $display_name = $manufacturer['company_name'].' '.$display_name;

            }
            $product_desc = array('item_name'=>$item['id'],
                        'language'=>$code,
                        'display_name'=>substr($display_name,0,128),
                        'short_description'=>str_replace('\n','<br />',(string)(isset($obj->Product[0]->ProductDescription['ShortDesc']) && $obj->Product[0]->ProductDescription['ShortDesc']?$obj->Product[0]->ProductDescription['ShortDesc']:(isset($obj->Product[0]->ProductDescription[0]) && $obj->Product[0]->ProductDescription[0]?$obj->Product[0]->ProductDescription[0]:(isset($obj->Product[0]->SummaryDescription->ShortSummaryDescription) && $obj->Product[0]->SummaryDescription->ShortSummaryDescription?$obj->Product[0]->SummaryDescription->ShortSummaryDescription:'')))),
                        'long_description'=>str_replace('\n','<br />',(string)(isset($obj->Product[0]->ProductDescription['LongDesc']) && $obj->Product[0]->ProductDescription['LongDesc']?$obj->Product[0]->ProductDescription['LongDesc']:(isset($obj->Product[0]->SummaryDescription->LongSummaryDescription) && $obj->Product[0]->SummaryDescription->LongSummaryDescription?$obj->Product[0]->SummaryDescription->LongSummaryDescription:''))));
            if(isset($descriptions[$code])) {
                if($product_desc['display_name']=='') unset($product_desc['display_name']);
                if($product_desc['short_description']=='') unset($product_desc['short_description']);
                if($product_desc['long_description']=='') unset($product_desc['long_description']);
                Utils_RecordBrowserCommon::update_record('premium_ecommerce_descriptions',$descriptions[$code],$product_desc);
            } else
                $descriptions[$code] = Utils_RecordBrowserCommon::new_record('premium_ecommerce_descriptions',$product_desc);

            //parameters
            $item_parameters_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters',array('item_name'=>$item['id'],'language'=>$code),array('id','parameter'));
            $item_parameters = array();
            foreach($item_parameters_tmp as $rr)
                $item_parameters[$rr['parameter']] = $rr['id'];
            unset($item_parameters_tmp);

            //parameter groups
            $parameter_group_labels_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_param_group_labels',array('language'=>$code),array('id','group'));
            $parameter_group_labels = array();
            foreach($parameter_group_labels_tmp as $rr)
                $parameter_group_labels[$rr['group']] = $rr['id'];
            foreach($obj->Product[0]->CategoryFeatureGroup as $cg) {
                $key = 'icecat_'.$cg['ID'];
                if(!isset($parameter_groups[$key]))
                    $parameter_groups[$key] = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_groups',array('group_code'=>$key));
                elseif(isset($parameter_group_labels[$parameter_groups[$key]]))
                    continue;
                $parameter_group_label = array('group'=>$parameter_groups[$key],
                            'language'=>$code,
                            'label'=>substr(str_replace('\n','<br>',(string)$cg->FeatureGroup[0]->Name[0]['Value']),0,128));
                Utils_RecordBrowserCommon::new_record('premium_ecommerce_param_group_labels',$parameter_group_label);
            }


            //parameters
            $parameter_labels_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameter_labels',array('language'=>$code),array('id','parameter'));
            $parameter_labels = array();
            foreach($parameter_labels_tmp as $rr)
                $parameter_labels[$rr['parameter']] = $rr['id'];

            if(!empty($obj->Product[0]->ProductFeature)) {
                foreach($obj->Product[0]->ProductFeature as $pf) {
                    $key = 'icecat_'.$pf->Feature[0]['ID'];
                    if(!isset($parameters[$key]))
                        $parameters[$key] = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameters',array('parameter_code'=>$key));
                    if(!isset($parameter_labels[$parameters[$key]])) {
                    $parameter_label = array('parameter'=>$parameters[$key],
                                'language'=>$code,
                                'label'=>substr(str_replace('\n','<br>',(string)$pf->Feature[0]->Name[0]['Value']),0,128));
                    $parameter_labels[$parameters[$key]] = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_labels',$parameter_label);
                    }
                    if((string)$pf->Feature[0]->Name[0]['Value']=='Weight') {
                        $weight = null;
                        switch((string)$pf->Feature[0]->Measure[0]->Signs[0]->Sign[0]) {
                            case 'g':
                                $weight = ((string)$pf['Value'])/1000;
                                break;
                            case 'kg':
                                $weight = ((string)$pf['Value']);
                                break;
                        }
                        if($weight!==null)
                            Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$item['id'],array('weight'=>$weight));
                    }
                    $item_params = array('item_name'=>$item['id'],
                            'parameter'=>$parameters[$key],
                            'group'=>$parameter_groups['icecat_'.$pf['CategoryFeatureGroup_ID']],
                            'language'=>$code,
                            'value'=>substr(str_replace('\n','<br />',(string)$pf['Presentation_Value']),0,256));
                    if(isset($item_parameters[$parameters[$key]])) {
                    Utils_RecordBrowserCommon::update_record('premium_ecommerce_products_parameters',$item_parameters[$parameters[$key]],$item_params);
                    unset($item_parameters[$parameters[$key]]);
                    } else
                    Utils_RecordBrowserCommon::new_record('premium_ecommerce_products_parameters',$item_params);
                }
                foreach($item_parameters as $pf=>$pf_id)
                    Utils_RecordBrowserCommon::delete_record('premium_ecommerce_products_parameters',$pf_id,true);
            }

            //picture
            $pic = array();
            if(isset($obj->Product[0]['HighPic']))
                $pic[] = $obj->Product[0]['HighPic'];
            elseif(isset($obj->Product[0]['LowPic']))
                $pic[] = $obj->Product[0]['LowPic'];
            if(isset($obj->Product[0]->ProductGallery->ProductPicture))
                foreach($obj->Product[0]->ProductGallery->ProductPicture as $pp) {
                    $pic[] = $pp['Pic'];
                }
            $old_pics = array();
            $old_ver_pics = array();
            $ooo = Utils_AttachmentCommon::get('Premium/Warehouse/eCommerce/Products/'.$item['id']);
            if(is_array($ooo))
                foreach($ooo as $oo) {
                    if(!$oo['text']) {
                        if(preg_match('/^ice_/',$oo['original']))
                            $old_pics[$oo['original']] = $oo['id'];
                        else
                            $old_ver_pics[$oo['original']] = $oo['id']; //collect all images
                    }
                }
            foreach($pic as $pp) {
                $base_pp = 'ice_'.basename($pp);
                if(isset($old_ver_pics[basename($pp)])) { //delete icecat image without 'ice_' prefix
                    Utils_AttachmentCommon::persistent_mass_delete('Premium/Warehouse/eCommerce/Products/'.$item['id'],false,array($old_ver_pics[basename($pp)]));
                    unset($old_ver_pics[basename($pp)]);
                }
                if(!isset($old_pics[$base_pp])) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$pp);
                $temp_file = DATA_DIR.'/Premium_Warehouse_eCommerce/'.md5(microtime(true));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//              $fp = fopen($temp_file, 'w');
  //                curl_setopt($ch, CURLOPT_FILE, $fp);
                $response = curl_exec ($ch);
                    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close ($ch);
//              fclose($fp);

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
        return $langs_ok;
    }	


    private function icecat_get($arr) {
        $url = 'http://data.icecat.biz/xml_s3/xml_server3.cgi?'.http_build_query($arr);
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_USERPWD,$this->user.':'.$this->pass);
        $httpHeader = array(
        "Content-Type: text/xml; charset=UTF-8",
            "Content-Encoding: UTF-8"
        );
        curl_setopt($c, CURLOPT_HTTPHEADER, $httpHeader);
        $output = curl_exec($c);
        $response_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);
        if($response_code==401) {
            Epesi::alert("Invalid icecat user or password");
            return false;
        }
        if(!$output) return false;
        $got_data = true;

        $obj = @simplexml_load_string($output);
        if($obj===false) {
            $obj = @simplexml_load_string(iconv('iso8859-1','utf-8',$output));
            if($obj===false) {
                return false;
            }
        }
        if(isset($obj->Product[0]['ErrorMessage'])) {
            return false;
        }
        return $obj;
    }


	public function check($parameters,$upc,$man,$mpn,$langs) {
        $this->user = $parameters['Login'];
        $this->pass = $parameters['Password'];

        $query_arr = array();
        $ret = false;
        if($upc) {
            $query_arr['ean_upc'] = $upc;
            $ret = $this->icecat_get($query_arr+array('lang'=>'en','output'=>'productxml'),'default');
        }
        if(!$ret) {
            $query_arr = array();
            if(!$mpn)
                return;
            if(!$man)
                return;
            $query_arr['prod_id'] = $mpn;
            $query_arr['vendor'] = $man;
            $ret = $this->icecat_get($query_arr+array('lang'=>'en','output'=>'productxml'),'default');
        }
        if(!$ret)
            return;
            
        $langs_ok = array();
        foreach($langs as $code) {
            $obj = $this->icecat_get($query_arr+array('lang'=>$code,'output'=>'productxml'));
            if(!$obj) continue;
            $langs_ok[] = $code;
        }
        return $langs_ok;
	}

}
?>
