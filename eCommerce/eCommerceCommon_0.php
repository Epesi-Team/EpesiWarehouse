<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * eCommerce
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerceCommon extends ModuleCommon {
	private static $curr_opts;

	public static function access_parameters($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse ecommerce');
			case 'view':	if($i->acl_check('view ecommerce')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit ecommerce');
			case 'delete':	return $i->acl_check('delete ecommerce');
			case 'fields':	return array('position'=>'hide');
		}
		return false;
    }

	public function display_item_name($r, $nolink, $desc) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items','item_name',$r['item_name'],$nolink);
	}
	
	public function QFfield_description_language(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		$opts = array(''=>'---')+Utils_CommonDataCommon::get_translated_array('Premium/Warehouse/eCommerce/Languages');
		if ($mode!='view') {
			$form->addElement('select', $field, $label, $opts, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label, $opts[$default]);
		}
	}
	
	public function display_parameter_label($r, $nolink, $desc) {
		$lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
		$id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_parameter_labels', array('parameter', 'language'), array($r['id'], $lang_code));
		if (!is_numeric($id)) {
			$lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
			return Base_LangCommon::ts('Premium_eCommerce','Description in <b>%s</b> missing', array($lan?$lan:$lang_code));
		}
		return Utils_RecordBrowserCommon::get_value('premium_ecommerce_parameter_labels',$id,'label');
	}
	
	public function display_description($r, $nolink, $desc) {
		$lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
		$id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_descriptions', array('item_name', 'language'), array($r['item_name'], $lang_code));
		if (!is_numeric($id)) {
			$lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
			return Base_LangCommon::ts('Premium_eCommerce','Description in <b>%s</b> missing', array($lan?$lan:$lang_code));
		}
		return Utils_RecordBrowserCommon::get_value('premium_ecommerce_descriptions',$id,'short_description');
	}
	
	public function display_product_name($r, $nolink, $desc) {
		$lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
		$id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_descriptions', array('item_name', 'language'), array($r['item_name'], $lang_code));
		if (!is_numeric($id)) {
			$lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
			return Base_LangCommon::ts('Premium_eCommerce','Product name in <b>%s</b> missing', array($lan?$lan:$lang_code));
		}
		return 	Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$r['id'],$nolink).
				Utils_RecordBrowserCommon::get_value('premium_ecommerce_descriptions',$id,'display_name').
				Utils_RecordBrowserCommon::record_link_close_tag();
	}
	
	public function items_crits() {
		Utils_RecordBrowserCommon::get_records();
		return array();
	}

  	public static function QFfield_page_type(&$form, $field, $label, $mode, $default) {
		$opts = array(''=>'---','1'=>'Top menu above logo','2'=>'Top menu under logo','5'=>'Hidden');
		if ($mode=='add' || $mode=='edit') {
			$form->addElement('select', $field, $label, $opts, array('id'=>$field));
			if ($mode=='edit') $form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>$opts[$default]));
		}
	}

  	public static function display_page_type($r, $nolink=false) {
		$opts = array(''=>'---','1'=>'Top menu above logo','2'=>'Top menu under logo','5'=>'Hidden');
		return $opts[$r['type']];
	}

  	public static function parent_page_crits($v, $rec) {
		if(!$rec)
			return array();
		return array('!id'=>$rec['id']);
	}

  	public static function QFfield_currency(&$form, $field, $label, $mode, $default) {
		self::init_currency();
		if ($mode=='add' || $mode=='edit') {
			$form->addElement('select', $field, $label, self::$curr_opts, array('id'=>$field));
			if ($mode=='edit') $form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>self::$curr_opts[$default]));
		}
	}

  	public static function display_currency($r, $nolink=false) {
		self::init_currency();
		return self::$curr_opts[$r['currency']];
	}

	public static function init_currency() {
		if(!isset(self::$curr_opts))
			self::$curr_opts = DB::GetAssoc('SELECT id, code FROM utils_currency');
	}
	
	public static function get_currencies() {
		self::init_currency();
		return self::$curr_opts;
	}
	
	public static function QFfield_fckeditor(&$form, $field, $label, $mode, $default) {
		if ($mode=='add' || $mode=='edit') {
			$fck = $form->addElement('fckeditor', $field, $label);
			$fck->setFCKProps('800','300',true);
			if ($mode=='edit') $form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>html_entity_decode($default)));
		}	
	}

  	public static function display_fckeditor($r, $nolink=false, $desc=null) {
		return html_entity_decode(html_entity_decode($r[$desc['id']]));
	}
	
    public static function menu() {
		return array('Warehouse'=>array(
			'__submenu__'=>1,
			'eCommerce'=>array(
				'__submenu__'=>1, 
				'Products'=>array('recordset'=>'products'), 
				'Settings'=>array('__submenu__'=>1,
					'Parameters'=>array('recordset'=>'parameters'),
					'Parameter Groups'=>array('recordset'=>'parameter_groups'),
					'Availability'=>array('recordset'=>'availability'),
					'Payments & Carriers'=>array('recordset'=>'payments_carriers'),
					'Pages'=>array('recordset'=>'pages'),
					'Start page'=>array('__function__'=>'start_page'),
					'Icecat'=>array('__function__'=>'icecat'),
					'Rules and policies'=>array('__function__'=>'rules_page'),
					'QuickCart settings'=>array('__function__'=>'QC_dirs')
				)
			)
		));
	}
	
	public static function copy_attachment($id,$rev,$file,$original) {
		static $qcs;
		if(!isset($qcs))
    		    $qcs = DB::GetRow('SELECT path FROM premium_ecommerce_quickcart');
		$ext = strrchr($original,'.');
		if(eregi('^\.(jpg|jpeg|gif|png|bmp)$',$ext)) {
    		    $th1 = Utils_ImageCommon::create_thumb($file,100,100);
		    $th2 = Utils_ImageCommon::create_thumb($file,200,200);
		}
		foreach($qcs as $q) {
		    copy($file,$q.'/files/epesi/'.$id.'_'.$rev.$ext);
		    if(isset($th1)) {
    			copy($th1['thumb'],$q.'/files/100/epesi/'.$id.'_'.$rev.$ext);
    			copy($th2['thumb'],$q.'/files/200/epesi/'.$id.'_'.$rev.$ext);
		    }
		}
	}
	
	public static function icecat_sync($item_id) {
    		$user = Variable::get('icecat_user');
    		$pass = Variable::get('icecat_pass');
		if(!$user || !$pass)
			return;
			
		$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$item_id);
		$query_arr = array();
		if($item['upc']) {
		    $query_arr['ean_upc'] = $item['upc'];
		} else {
    		    $prod_id = $item['manufacturer_part_number'];
		    if(!$prod_id)
    			$prod_id = $item['product_code'];
		    if(!$prod_id) {
			Epesi::alert("Missing product code or manufacturer part number.");
			return false;		
		    }
		    if(!$item['vendor']) {
			Epesi::alert("Missing product vendor.");
			return false;		
		    }
		    $vendor = CRM_ContactsCommon::get_company($item['vendor']);
		    $query_arr['prod_id'] = $prod_id;
		    $query_arr['vendor'] = $vendor['company_name'];
		}
		
		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');

		//descriptions in all langs		
		$descriptions_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$item_id),array('id','language'));
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


		set_time_limit(count($langs)*60);
		foreach($langs as $code=>$name) {
		    $url = 'http://data.icecat.biz/xml_s3/xml_server3.cgi?'.http_build_query($query_arr+array('lang'=>$code,'output'=>'productxml'));
		    $c = curl_init();
		    curl_setopt($c, CURLOPT_URL, $url);
		    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($c, CURLOPT_USERPWD,$user.':'.$pass);
		    $output = curl_exec($c);
		    $response_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
		    curl_close($c);
	    	    if($response_code==401) {
			Epesi::alert("Invalid user or password");
			return false;
		    }
		    if($output) {
			$obj = simplexml_load_string($output);
			
			//description
			$product_desc = array('item_name'=>$item_id,
						'language'=>$code,
						'display_name'=>(string)$obj->Product[0]['Name'],
						'short_description'=>(string)$obj->Product[0]->ProductDescription[0]);
			if(isset($descriptions[$code]))
			    Utils_RecordBrowserCommon::update_record('premium_ecommerce_descriptions',$descriptions[$code],$product_desc);
			else
			    $descriptions[$code] = Utils_RecordBrowserCommon::new_record('premium_ecommerce_descriptions',$product_desc);

			//parameters
			$item_parameters_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters',array('item_name'=>$item_id,'language'=>$code),array('id','parameter'));
			$item_parameters = array();
			foreach($item_parameters_tmp as $rr)
			    $item_parameters[$rr['parameter']] = $rr['id'];
			unset($item_parameters_tmp);
			
			//parameter groups
			$parameter_group_labels_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameter_group_labels',array('language'=>$code),array('id','group'));
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
							'label'=>(string)$cg->FeatureGroup[0]->Name[0]['Value']);
				Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_group_labels',$parameter_group_label);
			}
			
			
			//parameters
			$parameter_labels_tmp = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameter_labels',array('language'=>$code),array('id','parameter'));
			$parameter_labels = array();
			foreach($parameter_labels_tmp as $rr)
			    $parameter_labels[$rr['parameter']] = $rr['id'];
			
			foreach($obj->Product[0]->ProductFeature as $pf) {
			    $key = 'icecat_'.$pf->Feature[0]['ID'];
			    if(!isset($parameters[$key]))
			        $parameters[$key] = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameters',array('parameter_code'=>$key));
			    if(!isset($parameter_labels[$parameters[$key]])) {
				$parameter_label = array('parameter'=>$parameters[$key],
							'language'=>$code,
							'label'=>(string)$pf->Feature[0]->Name[0]['Value']);
				$parameter_labels[$parameters[$key]] = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_labels',$parameter_label);
			    }
			    $item_params = array('item_name'=>$item_id,
						'parameter'=>$parameters[$key],
						'group'=>$parameter_groups['icecat_'.$pf['CategoryFeatureGroup_ID']],
						'language'=>$code,
						'value'=>(string)$pf['Presentation_Value']);
			    if(isset($item_parameters[$parameters[$key]]))
				Utils_RecordBrowserCommon::update_record('premium_ecommerce_products_parameters',$item_parameters[$parameters[$key]],$item_params);
			    else
				$item_parameters[$parameters[$key]] = Utils_RecordBrowserCommon::new_record('premium_ecommerce_products_parameters',$item_params);
			}

			//picture
			$pic = null;
			if(isset($obj->Product[0]['HighPic']))
			    $pic = $obj->Product[0]['HighPic'];
			elseif(isset($obj->Product[0]['LowPic']))
			    $pic = $obj->Product[0]['LowPic'];
			if($pic) {
			    $num_of_pics = Utils_AttachmentCommon::count('Premium/Warehouse/eCommerce/ProductsDesc/'.$code.'/'.$item_id);
			    if(!$num_of_pics) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$pic);
				$temp_file = tempnam(sys_get_temp_dir(), 'icecatpic');
				$fp = fopen($temp_file, 'w');
    				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_exec ($ch);
		    		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close ($ch);
				fclose($fp);

				if($response_code==200)
				    Utils_AttachmentCommon::add('Premium/Warehouse/eCommerce/ProductsDesc/'.$code.'/'.$item_id,
							    0,Acl::get_user(),'Icecat product picture',basename($pic),$temp_file,null,null,array('Premium_Warehouse_eCommerceCommon','copy_attachment'));

				@unlink($temp_file);
			    }
			}
		    }
		}
	}
	
	public static function icecat_addon_parameters($r) {
	    $user = Variable::get('icecat_user');
	    $pass = Variable::get('icecat_pass');
	    if($user && $pass)
        	    Base_ActionBarCommon::add('add','Icecat',Module::create_href(array('icecat_sync'=>1),'Getting data from icecat - please wait.'));
	    if(isset($_REQUEST['icecat_sync'])) {
		self::icecat_sync($r['item_name']);
	    }
	    return false;
	}


	public static function submit_position($values, $mode) {
		switch ($mode) {
			case 'add':
			    $values['position'] = 0;
			    break;
		}
		return $values;
	}


}
?>
