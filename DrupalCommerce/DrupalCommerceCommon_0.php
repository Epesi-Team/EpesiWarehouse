<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * DrupalCommerce
 *
 * @author Paweł Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2013, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_DrupalCommerceCommon extends ModuleCommon {
	public static $plugin_path = 'modules/Premium/Warehouse/DrupalCommerce/3rdp_plugins/';
    private static $curr_opts;
    private static $curr_opts_active;

    public static function display_item_name($r, $nolink, $desc) {
        return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items','item_name',$r['item_name'],$nolink);
    }

    public static function QFfield_description_language(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
        $opts = array(''=>'---')+Utils_CommonDataCommon::get_translated_array('Premium/Warehouse/eCommerce/Languages');
        if ($mode!='view') {
            $form->addElement('select', $field, $label, $opts, array('id'=>$field));
            $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label, $opts[$default]);
        }
    }

    public static function display_parameter_label($r, $nolink, $desc) {
        $lang_code = Base_LangCommon::get_lang_code();
        $id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_parameter_labels', array('parameter', 'language'), array($r['id'], $lang_code));
        if (!is_numeric($id)) {
            $lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
            return __('Description in %s is missing', array('<b>'.($lan?$lan:$lang_code).'</b>'));
        }
        return Utils_RecordBrowserCommon::get_value('premium_ecommerce_parameter_labels',$id,'label');
    }

    public static function display_parameter_group_label($r, $nolink, $desc) {
        $lang_code = Base_LangCommon::get_lang_code();
        $id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_param_group_labels', array('group', 'language'), array($r['id'], $lang_code));
        if (!is_numeric($id)) {
            $lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
            return __('Description in %s is missing', array('<b>'.($lan?$lan:$lang_code).'</b>'));
        }
        return Utils_RecordBrowserCommon::get_value('premium_ecommerce_param_group_labels',$id,'label');
    }

    public static function display_description($r, $nolink, $desc) {
        $lang_code = Base_LangCommon::get_lang_code();
        $id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_descriptions', array('item_name', 'language'), array($r['item_name'], $lang_code));
        if (!is_numeric($id)) {
            $lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
            return __('Description in %s is missing', array('<b>'.($lan?$lan:$lang_code).'</b>'));
        }
        return Utils_RecordBrowserCommon::get_value('premium_ecommerce_descriptions',$id,'short_description');
    }

    public static function display_product_name($r, $nolink, $desc) {
        $lang_code = Base_LangCommon::get_lang_code();
        $id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_descriptions', array('item_name', 'language'), array($r['item_name'], $lang_code));
        if (!is_numeric($id)) {
            $lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
            return __('Product name in %s is missing', array('<b>'.($lan?$lan:$lang_code).'</b>'));
        }
        return  Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$r['id'],$nolink).
                Utils_RecordBrowserCommon::get_value('premium_ecommerce_descriptions',$id,'display_name').
                Utils_RecordBrowserCommon::record_link_close_tag();
    }

    public static function display_sku($r, $nolink, $desc) {
        return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items',$r['item_name'],$nolink).
                Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$r['item_name'],'sku').
                Utils_RecordBrowserCommon::record_link_close_tag();
    }

    public static function items_crits() {
        return array();
    }

    public static function customer_crits() {
        return array('group'=>'custm');
    }

    public static function QFfield_password(&$form, $field, $label, $mode, $default) {
        if ($mode=='add' || $mode=='edit') {
            $form->addElement('password', $field, $label);
            $form->addRule($field, __('Field required'), 'required');
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>'*****'));
        }
    }

    public static function display_password($r, $nolink=false) {
        return '*****';
    }
    
    public static function product_exists($id) {
        static $cache;
        if(!isset($cache)) $cache = array();
        if(isset($cache[$id])) return $cache[$id];
        $cache[$id] = Utils_RecordBrowserCommon::get_records_count('premium_ecommerce_products',array('item_name'=>$id))>0;
        return $cache[$id];
    }

    public static function prices_addon_parameters($r) {
        if(!Variable::get('ecommerce_item_prices'))
            return array('show'=>false);
        return array('show'=>true, 'label'=>__('Prices'));
    }
    public static function prices_addon_item_parameters($r) {
        if(!isset($r['id'])) return array('show'=>false);
        $product_exists = self::product_exists($r['id']);
        if(!Variable::get('ecommerce_item_prices') || !$product_exists)
            return array('show'=>false);
        return array('show'=>true, 'label'=>__('eCommerce').'#'.__('Prices'));
    }
    public static function parameters_addon_parameters($r) {
        if(!Variable::get('ecommerce_item_parameters'))
            return array('show'=>false);
        return array('show'=>true, 'label'=>__('Parameters'));
    }
    public static function parameters_addon_item_parameters($r) {
        if(!isset($r['id'])) return array('show'=>false);
        $product_exists = self::product_exists($r['id']);
        if(!Variable::get('ecommerce_item_parameters') || !$product_exists)
            return array('show'=>false);
        return array('show'=>true, 'label'=>__('eCommerce').'#'.__('Parameters'));
    }
    public static function descriptions_addon_parameters($r) {
        if(!Variable::get('ecommerce_item_descriptions'))
            return array('show'=>false);
        return array('show'=>true, 'label'=>__('Descriptions'));
    }
    public static function descriptions_addon_item_parameters($r) {
        if(!isset($r['id'])) return array('show'=>false);
        $product_exists = self::product_exists($r['id']);
        if(!Variable::get('ecommerce_item_descriptions') || !$product_exists)
            return array('show'=>false);
        return array('show'=>true, 'label'=>__('eCommerce').'#'.__('Descriptions'));
    }
    public static function attachment_product_addon_item_parameters($r) {
        if(!isset($r['id'])) return array('show'=>false);
        $product_exists = self::product_exists($r['id']);
        if(!$product_exists)
            return array('show'=>false);
        return array('show'=>true, 'label'=>__('eCommerce').'#'.__('Pictures'));
    }

    public static $order_statuses;

    public static function QFfield_order_status(&$form, $field, $label, $mode, $default) {
        if ($mode=='add' || $mode=='edit') {
            $form->addElement('select', $field, $label, self::$order_statuses, array('id'=>$field));
            $form->addRule($field,'Field required','required');
            if ($mode=='edit') $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>self::$order_statuses[$default]));
        }
    }

    public static function display_order_status($r, $nolink=false) {
        return self::$order_statuses[$r['send_on_status']];
    }

    public static function QFfield_currency(&$form, $field, $label, $mode, $default) {
        self::init_currency();
        if ($mode=='add' || $mode=='edit') {
            $curr = self::$curr_opts_active;
            if(!isset($curr[$default])) $curr[$default] = self::$curr_opts[$default];
            $form->addElement('select', $field, $label, $curr, array('id'=>$field));
            if ($mode=='edit') $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>isset(self::$curr_opts[$default])?self::$curr_opts[$default]:''));
        }
    }

    public static function display_currency($r, $nolink=false) {
        self::init_currency();
        if(isset(self::$curr_opts[$r['currency']]))
        	return self::$curr_opts[$r['currency']];
       	return '';
    }

    public static function init_currency() {
        if(!isset(self::$curr_opts)) {
            self::$curr_opts = DB::GetAssoc('SELECT id, code FROM utils_currency');
            self::$curr_opts_active = DB::GetAssoc('SELECT id, code FROM utils_currency WHERE active=1');
        }
    }

    public static function get_currencies() {
        self::init_currency();
        return self::$curr_opts;
    }

    public static function menu() {
		$m = array('__submenu__'=>1);
		if (Utils_RecordBrowserCommon::get_access('premium_ecommerce_products', 'add')) {
			$m[_M('Express publish')] = array('__function__'=>'fast_fill');
            $m[_M('Update categories and products now')] = array('__function__'=>'reset_cron');
        }
		if (Utils_RecordBrowserCommon::get_access('premium_ecommerce_products', 'browse'))
			$m[_M('Products')] = array();
		return array(_M('Inventory')=>array(
			'__submenu__'=>1,
			_M('eCommerce')=>$m));
    }

    public static $images;
    public static function copy_attachment($id,$file,$original,$arr) {
        $drupal_id = $arr[0];
        $ext = strrchr($original,'.');
        if(preg_match('/^\.(jpg|jpeg|gif|png|bmp)$/i',$ext)) {
//           print($id.' '.$file.' '.$original.' '.print_r($arr,true)."\n");
           $files = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'views/epesi_product_images_search_by_filename.json?'.http_build_query(array('display_id'=>'services_1','args'=>array('epesi_'.$id.$ext,''))));
           if(isset($files[0]['fid'])) {
             $ret['fid'] = $files[0]['fid'];
           } else {
             $file_arr = array(
               'filesize' => filesize($file),
               'filename' => 'epesi_'.$id.$ext,
               'file' => base64_encode(file_get_contents($file)),
               'filepath'=>"public://epesi/epesi_".$id.$ext
             );
             $ret = self::drupal_post($drupal_id,'file',$file_arr);
           }
           if(isset($ret['fid'])) {
             $lang = $arr[1]?'und':basename(dirname(dirname($file)));
             self::$images[$lang][] = $ret['fid'];
           }
        }
    }

	public static function get_plugin($arg) {
		static $plugins = array();
		if (isset($plugins[$arg])) return $plugins[$arg];

		static $interface_included = false;
		if (!$interface_included)
			require_once('modules/Premium/Warehouse/DrupalCommerce/interface.php');

		if (is_numeric($arg)) {
			$id = $arg;
			$filename = DB::GetOne('SELECT filename FROM premium_ecommerce_3rdp_plugin WHERE id=%d', array($arg));
		} else {
			$filename = $arg;
			$id = DB::GetOne('SELECT id FROM premium_ecommerce_3rdp_plugin WHERE filename=%s', array($arg));
		}
		if (is_file(self::$plugin_path.basename($filename).'.php')) {
			require_once(self::$plugin_path.basename($filename).'.php');
			$class = 'Premium_Warehouse_DrupalCommerce_3rdp__Plugin_'.$filename;
			if (!class_exists($class))
				trigger_error('Warning: invalid plugin in file '.$filename.'.php<br>', E_USER_ERROR);
			return $plugins[$id] = $plugins[$filename] = new $class();
		}
		return null;
	}

	public static function submit_3rdp_info($values, $mode) {
		if (isset($values['plugin']) && is_numeric($values['plugin'])) {
			$plugin = self::get_plugin($values['plugin']);
			$params = $plugin->get_parameters();
		} else $params = array();

		switch ($mode) {
			case 'edit':
				$i = 1;
				foreach ($params as $k=>$v) {
					if ($values['param'.$i]=='[_password_dummy_]' && $v=='password') unset($values['param'.$i]);
					$i++;
				}
				break;
		}

		$i = 1;
		foreach ($params as $k=>$v) {
			if ($v=='password' && isset($values['param'.$i]) && $values['param'.$i]) switch ($mode) {
				case 'adding':
					$values['param'.$i] = '[_password_dummy_]';
				    break;
				case 'editing':
					$values['param'.$i] = '[_password_dummy_]';
				    break;
				case 'view':
					$values['param'.$i] = str_pad('', strlen($values['param'.$i]), '*');
				    break;
			}
			$i++;
		}
        return self::submit_position($values, $mode, 'premium_ecommerce_3rdp_info');
	}
	
	public static function get_change_parameters_labels_js($id) {
		$i = 1;
		$js = '';
		if (is_numeric($id)) {
			$plugin = self::get_plugin($id);
			$params = $plugin->get_parameters();
			foreach ($params as $k=>$v) {
				$js .= 	'if($("param'.$i.'"))$("param'.$i.'").type="'.$v.'";'.
						'if($("_param'.$i.'__label")){'.
							'$("_param'.$i.'__label").innerHTML="'.$k.'";'.
							'$("_param'.$i.'__label").parentNode.parentNode.style.display="";'.
						'}';
				$i++;
			}
		}
		while($i<=6) {
			$js .= 'if($("_param'.$i.'__label"))$("_param'.$i.'__label").parentNode.parentNode.style.display="none";';
			$i++;
		}
		return $js;
	}

	public static function change_parameters_labels($id) {
		eval_js(self::get_change_parameters_labels_js($id));
	}

	public static function QFfield_3rdp_plugin(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		self::change_parameters_labels($default);
		if ($mode!='view') {
			if (is_numeric($default)) {
				$vals = array($default);
				$where=' OR id=%d';
			} else {
				$vals = array();
				$where='';
			}
			$opts = array(''=>'---');
			$opts = $opts+DB::GetAssoc('SELECT id, name FROM premium_ecommerce_3rdp_plugin WHERE active=1'.$where,$vals);
			load_js('modules/Premium/Warehouse/DrupalCommerce/adjust_parameters.js');
			eval_js('Event.observe("plugin","change",adjust_parameters)');
			$form->addElement('select', $field, $label, $opts, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label, DB::GetOne('SELECT name FROM premium_ecommerce_3rdp_plugin WHERE id=%d', array($default)));
		}
	}
    public static function display_3rdp_name($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_ecommerce_3rdp_info', 'Name', $v, $nolink);
	}

	public static function scan_for_3rdp_info_plugins() {
		$dir = scandir(self::$plugin_path);
		DB::Execute('UPDATE premium_ecommerce_3rdp_plugin SET active=2');
		foreach ($dir as $file) {
			if ($file=='..' || $file=='.' || !preg_match('/\.php$/i',$file)) continue;
			$filename = basename($file, '.php');
			$plugin = self::get_plugin($filename);
			if ($plugin) {
				$name = $plugin->get_name();
				$id = DB::GetOne('SELECT id FROM premium_ecommerce_3rdp_plugin WHERE filename=%s', array($filename));
				if ($id===false || $id==null) {
					DB::Execute('INSERT INTO premium_ecommerce_3rdp_plugin (name, filename, active) VALUES (%s, %s, 1)', array($name, $filename));
				} else {
					DB::Execute('UPDATE premium_ecommerce_3rdp_plugin SET active=1, name=%s WHERE id=%d', array($name, $id));
				}
			}
		}
		DB::Execute('UPDATE premium_ecommerce_3rdp_plugin SET active=0 WHERE active=2');
		return false;
	}

    public static function get_3rd_party_item_data($item_id,$verbose=true) {
        $item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$item_id);
        $plugins = Utils_RecordBrowserCommon::get_records('premium_ecommerce_3rdp_info',array(),array(),array('position'=>'ASC'));
        if(!$plugins) return;
        $langs = array_keys(Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages'));
        $langs_ok = array();
        foreach($plugins as $plugin) {
            if(!$langs) break;
            $pl = self::get_plugin($plugin['plugin']);
    		$params = $pl->get_parameters();
	    	$i = 1;
		    foreach ($params as $k=>$v) {
			    $params[$k] = $plugin['param'.$i];
    			$i++;
	    	}
            $ret = $pl->download($params,$item,$langs,$verbose); //TODO wprowadzic pozycje pluginow (priorytet)
            if(is_array($ret)) {
                $langs_ok = array_merge($langs_ok,$ret);
                $langs = array_diff($langs,$ret);
            } elseif($ret) {
                $langs_ok = array_merge($langs_ok,$langs);
                break;
            }
        }
        if(!empty($langs_ok)) {
            return Epesi::alert("Successfully downloaded product data for languages: ".implode(', ',$langs_ok).".");
        }
        Epesi::alert("There is no data about this item on 3rd party servers.");
    }

    public static function check_3rd_party_item_data($upc,$man,$mpn) {
        $plugins = Utils_RecordBrowserCommon::get_records('premium_ecommerce_3rdp_info',array(),array(),array('position'=>'ASC'));
        $langs = array_keys(Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages'));
        $ret = array();
        foreach($plugins as $plugin) {
            if(!$langs) break;
            $pl = self::get_plugin($plugin['plugin']);
    		$params = $pl->get_parameters();
	    	$i = 1;
		    foreach ($params as $k=>$v) {
			    $params[$k] = $plugin['param'.$i];
    			$i++;
	    	}
            $ret_check = $pl->check($params,$upc,$man,$mpn,$langs); //TODO wprowadzic pozycje pluginow (priorytet)
            if(is_array($ret_check)) {
                $ret[$plugin['name']] = $ret_check;
                $langs = array_diff($langs,$ret_check);
            } elseif($ret) {
                $ret[$plugin['name']] = $langs;
                break;
            }
        }
        return $ret;
    }

    public static function get_3rd_party_info_addon_parameters($r) {
        if(DB::GetOne('SELECT 1 FROM premium_ecommerce_3rdp_plugin WHERE active=1')) {
            Base_ActionBarCommon::add('add',__('3rd party'),Module::create_href(array('get_3rd_party_item_data'=>1),'Getting data from 3rd party servers - please wait.'));
            if(isset($_REQUEST['get_3rd_party_item_data'])) {
                self::get_3rd_party_item_data($r['item_name']);
                unset($_REQUEST['get_3rd_party_item_data']);
            }
        }
        return array('show'=>false);
    }
    
    private static $orders_rec;

    public static function orders_get_record() {
        return self::$orders_rec['id'];
    }

    public static function orders_addon_parameters($r) {
        if(!isset(self::$orders_rec) && isset($r['id'])) {
            $ret = Utils_RecordBrowserCommon::get_records('premium_ecommerce_orders',array('transaction_id'=>$r['id']));
            if(!$ret)
                return array('show'=>false);
            self::$orders_rec = array_pop($ret);
        }
        return array('show'=>true, 'label'=>__('eCommerce'));
    }

    public static function submit_products_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_products');
    }
    public static function submit_parameters_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_parameters');
    }
    public static function submit_parameter_groups_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_parameter_groups');
    }

    public static function submit_position($values, $mode, $recordset) {
        switch ($mode) {
            case 'add':
            case 'restore':
                $values['position'] = Utils_RecordBrowserCommon::get_records_count($recordset);
                break;
            case 'delete':
                DB::Execute('UPDATE '.$recordset.'_data_1 SET f_position=f_position-1 WHERE f_position>%d',array($values['position']));
                break;
        }
        return $values;
    }

    public static function toggle_publish($id,$v) {
        Utils_RecordBrowserCommon::update_record('premium_ecommerce_products',$id,array('publish'=>$v?1:0));
        location(array());
    }

    public static function toggle_recommended($id,$v) {
        Utils_RecordBrowserCommon::update_record('premium_ecommerce_products',$id,array('recommended'=>$v?1:0));
        location(array());
    }

    public static function toggle_always_in_stock($id,$v) {
        Utils_RecordBrowserCommon::update_record('premium_ecommerce_products',$id,array('always_in_stock'=>$v?1:0));
        location(array());
    }

    public static function publish_warehouse_item($id,$icecat=true) {
        Utils_RecordBrowserCommon::new_record('premium_ecommerce_products',array('item_name'=>$id,'publish'=>1,'available'=>1));
        if($icecat)
                Premium_Warehouse_DrupalCommerceCommon::get_3rd_party_item_data($id,false);
    }


    public static function warehouse_item_actions($r, $gb_row) {
        if(isset($_REQUEST['publish_warehouse_item']) && $r['id']==$_REQUEST['publish_warehouse_item']) {
            self::publish_warehouse_item($r['id']);
            unset($_REQUEST['publish_warehouse_item']);
        }

        $tip = '<table>';
        $icon = 'available.png';
        $action = '';

        $on = '<span class="checkbox_on" />';
        $off = '<span class="checkbox_off" />';

        $recs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('item_name'=>$r['id']));
        $quantity = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$r['id'],'>quantity'=>0));
        if(empty($recs)) {
            $icon = 'notavailable.png';
                $tip .= '<tr><td colspan=2>'.__('eCommerce item doesn\'t exist.').'</td></tr>';
            $action = Module::create_href(array('publish_warehouse_item'=>$r['id']));
        } else {
            $rec = array_pop($recs);

            if(isset($_REQUEST['toggle_publish']) && $rec['id']==$_REQUEST['toggle_publish'] && ($_REQUEST['publish_value']==0 || $_REQUEST['publish_value']==1)) {
            $rec['publish'] = $_REQUEST['publish_value'];
            self::toggle_publish($rec['id'],$rec['publish']);
            unset($_REQUEST['toggle_publish']);
            }

            if(!$rec['publish']) {
                $icon = 'notpublished.png';
            } elseif(empty($quantity) || !$r['category'])
            $icon = 'published.png';
            $action = Module::create_href(array('toggle_publish'=>$rec['id'],'publish_value'=>$rec['publish']?0:1));
                $tip .= '<tr><td>'.__('Published').'</td><td>'.($rec['publish']?$on:$off).'</td></tr>';
        }

        $tip .= '<tr><td>'.__('Assigned category').'</td><td>'.($r['category']?$on:$off).'</td></tr>';
        $tip .= '<tr><td>'.__('Available in warehouse').'</td><td>'.(empty($quantity)?$off:$on).'</td></tr>';
        if(ModuleManager::is_installed('Premium_Warehouse_DrupalCommerce_CompareUpdatePrices')>=0) {
            $cs = DB::GetCol('SELECT f_plugin FROM premium_ecommerce_compare_prices_data_1 WHERE f_item_name=%d AND active=1',array($r['id']));
            $tip .= '<tr><td>'.__('Compare Service').'</td><td>'.($cs?implode(', ',$cs):$off).'</td></tr>';
        }
	if(ModuleManager::is_installed('Premium_Warehouse_DrupalCommerce_Allegro')>=0) {
		$cs = DB::GetOne('SELECT count(*) FROM premium_ecommerce_allegro_auctions WHERE item_id=%d AND active=1',array($r['id']));
	        $tip .= '<tr><td>'.__('Allegro').'</td><td>'.($cs?$cs:$off).'</td></tr>';
	}
        $tip .= '</table>';


        $gb_row->add_action($action,'',$tip,Base_ThemeCommon::get_template_file('Premium_Warehouse_DrupalCommerce',$icon));
    }

    private static function get_payment_channel($sys,$chn) {
        static $aPay;
        if(!isset($aPay)) {
        $aPay = array();
        $aPay[1] = array();
        $aPay[1][0] = 'Credit card';
        $aPay[1][1] = 'mTransfer (mBank)';
        $aPay[1][2] = 'Płacę z Inteligo (PKO BP Inteligo)';
        $aPay[1][3] = 'Multitransfer (MultiBank)';
        $aPay[1][4] = 'DotPay Transfer (DotPay.pl)';
        $aPay[1][6] = 'Przelew24 (Bank Zachodni WBK)';
        $aPay[1][7] = 'ING OnLine (ING Bank Śląski)';
        $aPay[1][8] = 'Sez@m (Bank Przemysłowo-Handlowy S.A.)';
        $aPay[1][9] = 'Pekao24 (Bank Pekao S.A.)';
        $aPay[1][10] = 'MilleNet (Millennium Bank)';
        $aPay[1][12] = 'PayPal';
        $aPay[1][13] = 'Deutsche Bank PBC S.A.';
        $aPay[1][14] = 'Kredyt Bank S.A. - KB24 Bankowość Elektroniczna';
        $aPay[1][15] = 'PKO BP (konto Inteligo)';
        $aPay[1][16] = 'Lukas Bank';
        $aPay[1][17] = 'Nordea Bank Polska';
        $aPay[1][18] = 'Bank BPH (usługa Przelew z BPH)';
        $aPay[1][19] = 'Citibank Handlowy';
        $aPay[4] = array();
        $aPay[4]['m'] = 'mTransfer - mBank';
        $aPay[4]['n'] = 'MultiTransfer - MultiBank';
        $aPay[4]['w'] = 'BZWBK - Przelew24';
        $aPay[4]['o'] = 'Pekao24Przelew - BankPekao';
        $aPay[4]['i'] = 'Płace z Inteligo';
        $aPay[4]['d'] = 'Płac z Nordea';
        $aPay[4]['p'] = 'Płac z PKO BP';
        $aPay[4]['h'] = 'Płac z BPH';
        $aPay[4]['g'] = 'Płac z ING';
        $aPay[4]['c'] = 'Credit card';
        }
        if(!isset($aPay[$sys][$chn])) return '---';
        return $aPay[$sys][$chn];
    }

    public static function display_payment_channel($r) {
        $r2 = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$r['transaction_id']);
        return self::get_payment_channel($r2['payment_type'],$r['payment_channel']);
    }

    public static function QFfield_payment_channel(&$form, $field, $label, $mode, $default, $desc, $parent_rb) {
        $ord = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$parent_rb->record['transaction_id']);
        $form->addElement('static', $field, $label, self::get_payment_channel($ord['payment_type'],$default));
    }

    public static function display_promotion_shipment_discount($r) {
        if(!$r['promotion_shipment_discount']) return '---';
        $r2 = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$r['transaction_id']);
        list($cc,$curr) = Utils_CurrencyFieldCommon::get_values($r2['shipment_cost']);
        return Utils_CurrencyFieldCommon::format($r['promotion_shipment_discount'],$curr).' ('.__('"Shipment Cost" is already discounted').')';
    }

    public static function QFfield_promotion_shipment_discount(&$form, $field, $label, $mode, $default,$desc,$parent_rb) {
        $form->addElement('static', $field, $label, self::display_promotion_shipment_discount($parent_rb->record));
    }

    public static function display_payment_realized($r) {
        return $r['payment_realized']?__('Yes'):__('No');
    }

    public static function QFfield_payment_realized(&$form, $field, $label, $mode, $default,$args) {
        if(isset($_REQUEST['payment_realized'])) {
            $id = self::orders_get_record();
            if($_REQUEST['payment_realized']) $val=1;
            else $val=0;
            Utils_RecordBrowserCommon::update_record('premium_ecommerce_orders',$id,array('payment_realized'=>$val));
            unset($_REQUEST['payment_realized']);
            $default = $val;
        }
        $form->addElement('static', $field, $label, $default?'<a '.Module::create_confirm_href(__('Mark this record as not paid?'),array('payment_realized'=>0)).'><span class="checkbox_on" /></a>':'<a '.Module::create_href(array('payment_realized'=>1)).'><span '.Utils_TooltipCommon::open_tag_attrs('Click to mark as paid').' class="checkbox_off" /></a>');
    }

    public static function display_product_name_short($r) {
        $rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$r['item_name']);
        return $rec['item_name'];
    }

/*    public static function adv_related_products_params() {
        return array('cols'=>array(),
            'format_callback'=>array('Premium_Warehouse_DrupalCommerceCommon','display_product_name_short'));
    }

    public static function related_products_crits($arg, $r){
        if (isset($r['id']))
            return array('!id'=>$r['id']);
        return array();
    }

    public static function display_related_product_name($r, $nolink=true) {
        $ret = array();
        if(isset($r['related_products']))
        foreach($r['related_products'] as $p) {
            $rr = Utils_RecordBrowserCommon::get_record('premium_ecommerce_products',$p);
            $name = self::display_product_name_short($rr);
            if($nolink)
                $ret[] = $name;
            else
                $ret[] = Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$p).$name.Utils_RecordBrowserCommon::record_link_close_tag();
        }
        return implode($ret,', ');
    }

    public static function display_popup_product_name($r, $nolink=true) {
        $ret = array();
        if(isset($r['popup_products']))
        foreach($r['popup_products'] as $p) {
            $rr = Utils_RecordBrowserCommon::get_record('premium_ecommerce_products',$p);
            $name = self::display_product_name_short($rr);
            if($nolink)
                $ret[] = $name;
            else
                $ret[] = Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$p).$name.Utils_RecordBrowserCommon::record_link_close_tag();
        }
        return implode($ret,', ');
    }*/

    public static function display_category_available_languages($r, $nolink) {
        $rr = Utils_RecordBrowserCommon::get_records('premium_ecommerce_cat_descriptions',array('category'=>$r['id']),array('language'));
        $ret = array();
        foreach($rr as $r) {
            $ret[] = $r['language'];
        }
        sort($ret);
        return implode(', ',$ret);
    }

    public static function QFfield_online_order(&$form, $field, $label, $mode, $default) {
        $form->addElement('checkbox', $field, $label)->freeze();
        $form->setDefaults(array($field=>$default));
    }

    public static function admin_caption() {
		return array('label'=>__('eCommerce'), 'section'=>__('Features Configuration'));
    }

    public static function applet_caption() {
        return __('eCommerce - Orders');
    }

    public static function applet_info() {
        return __('Displays eCommerce orders.');
    }

    public static function applet_settings() {
		$opts = Premium_Warehouse_Items_OrdersCommon::get_status_array(array('transaction_type'=>1, 'payment'=>1));
		$opts = $opts + array('active'=>'['.__('Active').']');
//        $opts = array(-1=>'New Online Order', -2=>'New Online Order (with payment)', 2=>'Order Received', 3=>'Payment Confirmed', 4=>'Order Confirmed', 5=>'On Hold', 6=>'Order Ready to Ship', 7=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing');
        return array_merge(Utils_RecordBrowserCommon::applet_settings(),
            array(
                array('name'=>'settings_header','label'=>__('Settings'),'type'=>'header'),
                array('name'=>'status','label'=>__('Transaction Status'),'type'=>'select','default'=>-1,'rule'=>array(array('message'=>__('Field required'), 'type'=>'required')),'values'=>$opts),
                array('name'=>'my','label'=>__('Only my and not assigned'),'type'=>'checkbox','default'=>0)
                ));
    }

    public static function applet_info_format($r){
        return Utils_TooltipCommon::format_info_tooltip(array(__('Contact')=>$r['first_name'].' '.$r['last_name'],
                    __('Company')=>$r['company_name'],'Phone'=>$r['phone']));
    }
    
    public static function submit_ecommerce_order($values,$mode) {
    	if($mode=='add') {
    		self::submit_warehouse_order(Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $values['transaction_id']),'edit',$values);
    	}
    	return null;
    }

    public static function submit_payment($values,$mode) {
    	if($mode=='add' && $values['record_type']=='premium_warehouse_items_orders') {
    		$ord = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $values['record_id']);
    		if($ord && $ord['status']==-1)
    			Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $values['record_id'], array('status'=>-2));
    	}
    	return null;
    }
    
    public static function submit_warehouse_order($values, $mode,$erec = null) {
        if ($mode=='edit' && $values['transaction_type']==1 && $values['online_order'] && 
        	(isset($erec) || $values['status']!=DB::GetOne('SELECT f_status FROM premium_warehouse_items_orders_data_1 WHERE id=%d',array($values['id'])))) {
        	
        	//send messages from epesi
            $txt = '';
            
            $orec = $values;
            $orec['shipment_type'] = self::display_shipment_type($values);
            $orec['payment_type'] = Utils_CommonDataCommon::get_value('Premium_Items_Orders_Payment_Types/'.$values['payment_type']);
            $h_cost = Utils_CurrencyFieldCommon::get_values($values['handling_cost']);
            $sh_cost = Utils_CurrencyFieldCommon::get_values($values['shipment_cost']);
            if($h_cost[1]==$sh_cost[1])
                $orec['shipment_handling_cost'] = Utils_CurrencyFieldCommon::format($h_cost[0]+$sh_cost[0],$h_cost[1]);
            else
                $orec['shipment_handling_cost'] = Utils_CurrencyFieldCommon::format($values['handling_cost']).' + '.Utils_CurrencyFieldCommon::format($values['shipment_cost']);
            $orec['total_value'] = Premium_Warehouse_Items_OrdersCommon::display_total_value($values,true);

            if(!isset($erec)) {
            	$erec = Utils_RecordBrowserCommon::get_records('premium_ecommerce_orders',array('transaction_id'=>$values['id']));
            	if($erec && is_array($erec) && count($erec)==1) {
	                $erec = array_shift($erec);
            	}
            }
            if(isset($erec) && is_array($erec) && $erec['language'] && $values['status']!=-1) {
                $emails = Utils_RecordBrowserCommon::get_records('premium_ecommerce_emails',array('send_on_status'=>$values['status'],'language'=>$erec['language']));
                if(!$emails)
                    $emails = Utils_RecordBrowserCommon::get_records('premium_ecommerce_emails',array('send_on_status'=>$values['status'],'language'=>''));
                if($emails) {
                    $email = array_shift($emails);
                    $txt = $email['content'];
                    $title = $email['subject'];
                    foreach($orec as $name=>$val) {
                	if(!is_string($name) || !is_string($val)) continue;
                        $txt = str_replace('__'.strtoupper($name).'__',$val,$txt);
                    }
                    foreach($erec as $name=>$val) {
                	if(!is_string($name) || !is_string($val)) continue;
                        $txt = str_replace('__'.strtoupper($name).'__',$val,$txt);
                    }
                }
            }
            if($txt) {
                $it_tmp = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details',array('transaction_id'=>$values['id']));
                $items = '<ul>';
                foreach($it_tmp as & $it) {
	            $it['gross_total'] = Premium_Warehouse_Items_OrdersCommon::display_order_details_gross_price($it,true);
	            $it['gross_price'] = Premium_Warehouse_Items_OrdersCommon::display_gross_price($it,true);
                    $itt = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$it['item_name']);
                    $it['item_name'] = $itt['item_name'];
                    $items .= '<li>'.$itt['item_name'].'</li>';
                }
                $items .= '</ul>';
                $txt = str_replace('__ITEMS__',$items,$txt);

                $sm = Base_ThemeCommon::init_smarty();
                $sm->assign('txt',$txt);
                $sm->assign('order',$orec);
                $sm->assign('ecommerce',$erec);
                $sm->assign('items',$it_tmp);

                $sm->assign('contact_us_title',__('Contact us'));
                if($erec) {
                    $contactus = Variable::get('ecommerce_contactus_'.$erec['language'],false);
                    if(!$contactus)
                        $contactus = Variable::get('ecommerce_contactus');
                    $email = $erec['email'];
                } else {
                    $contactus = Variable::get('ecommerce_contactus');
                    if(is_numeric($values['contact'])) {
                        $contact = CRM_ContactsCommon::get_contact($values['contact']);
                        if(isset($contact['email']) && $contact['email'])
                            $email = $contact['email'];
                        elseif(is_numeric($values['company'])) {
                            $company = CRM_ContactsCommon::get_company($values['company']);
                            if(isset($company['email']) && $company['email'])
                                $email = $company['email'];
                        }
                    }
                }
                if(!isset($email)) return null;

                $sm->assign('contact_us',$contactus);
                ob_start();
                Base_ThemeCommon::display_smarty($sm, 'Premium_Warehouse_DrupalCommerce','mail');
                $mail = ob_get_clean();

                $title .= ' - id '.$values['id'];
				
                Base_MailCommon::send($email,$title,$mail,null,null,true);
            }
            
        	//update status
        	if(isset($erec) && is_array($erec) && $erec['drupal'] && $erec['drupal_order_id'] && $values['status']!=-1) {
        		$drupal_id = $erec['drupal'];
        		$drupal_order_id = $erec['drupal_order_id'];
//        		$drupal_order = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'order/'.$drupal_order_id);
//        		$drupal_order['status'] = $values['status']>0 && $values['status']<20?'processing':($values['status']==20?'completed':'canceled');
				$drupal_status = $values['status']>0 && $values['status']<20?'processing':($values['status']==20?'completed':'canceled');
				Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'order/'.$drupal_order_id,array('status'=>$drupal_status));
//				Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'order/'.$drupal_order_id,$drupal_order);
			}
        }
        return null;//don't modify values
    }

/*    public static function QFfield_related_products(&$form, $field, $label, $mode, $default,$y,$x) {
        if ($mode=='edit' || $mode=='add') {
            $el = $form->addElement('automulti', $field, $label, array('Premium_Warehouse_DrupalCommerceCommon', 'automulti_search'), array($x->record), array('Premium_Warehouse_DrupalCommerceCommon','automulti_format'));
            $form->setDefaults(array($field=>$default));

    		$opts = Premium_Warehouse_DrupalCommerceCommon::get_categories();
			$rp = $x->init_module('Utils/RecordBrowser/RecordPicker',array());
    		$x->display_module($rp, array('premium_ecommerce_products',$field,array('Premium_Warehouse_DrupalCommerceCommon','automulti_format'),isset($x->record['id'])?array('!id'=>$x->record['id']):array(),array(),array(),array(),array(),array('item_name'=>array('type'=>'select','label'=>__('Category'),'args'=>$opts,'trans_callback'=>array('Premium_Warehouse_DrupalCommerceCommon', 'category_filter')))));
			$el->set_search_button('<a '.$rp->create_open_href().' '.Utils_TooltipCommon::open_tag_attrs(__('Advanced Selection')).' href="javascript:void(0);"><img border="0" src="'.Base_ThemeCommon::get_template_file('Utils_RecordBrowser','icon_zoom.png').'"></a>');
        } else {
            $form->addElement('static', $field, $label, self::display_related_product_name(array($field=>$default),false));
        }
    }

    public static function QFfield_popup_products(&$form, $field, $label, $mode, $default,$y,$x) {
        if ($mode=='edit' || $mode=='add') {
            $el = $form->addElement('automulti', $field, $label, array('Premium_Warehouse_DrupalCommerceCommon', 'automulti_search'), array($x->record), array('Premium_Warehouse_DrupalCommerceCommon','automulti_format'));
            $form->setDefaults(array($field=>$default));
            $form->addRule($field,__('You can select up to 6 items'),'callback',array('Premium_Warehouse_DrupalCommerceCommon','check_related'));

    		$opts = Premium_Warehouse_DrupalCommerceCommon::get_categories();
			$rp = $x->init_module('Utils/RecordBrowser/RecordPicker',array());
			if (isset($x->record['id'])) $crits = array('!id'=>$x->record['id']);
			else $crits = array();
    		$x->display_module($rp, array('premium_ecommerce_products',$field,array('Premium_Warehouse_DrupalCommerceCommon','automulti_format'),$crits,array(),array(),array(),array(),array('item_name'=>array('type'=>'select','label'=>__('Category'),'args'=>$opts,'trans_callback'=>array('Premium_Warehouse_DrupalCommerceCommon', 'category_filter')))));
			$el->set_search_button('<a '.$rp->create_open_href().' '.Utils_TooltipCommon::open_tag_attrs(__('Advanced Selection')).' href="javascript:void(0);"><img border="0" src="'.Base_ThemeCommon::get_template_file('Utils_RecordBrowser','icon_zoom.png').'"></a>');
        } else {
            $form->addElement('static', $field, $label, self::display_popup_product_name(array($field=>$default),false));
        }
    }
    
    public static function check_related($el) {
	return count(array_filter(explode('__SEP__',$el)))<=6;
    }

    public static function automulti_search($arg,$r) {
        $ret = DB::GetAssoc('SELECT ep.id, wp.f_item_name FROM premium_ecommerce_products_data_1 ep INNER JOIN premium_warehouse_items_data_1 wp ON ep.f_item_name=wp.id WHERE ep.active=1 AND wp.active=1 AND (wp.f_item_name '.DB::like().' CONCAT("%%",%s,"%%") OR wp.f_sku '.DB::like().' CONCAT("%%",%s,"%%"))'.(isset($r['id'])?' AND ep.id!='.$r['id']:'').' ORDER BY wp.f_item_name LIMIT 10',array($arg,$arg));
        return $ret;
    }

    public static function automulti_format($id) {
        if(is_array($id)) return DB::GetOne('SELECT f_item_name FROM premium_warehouse_items_data_1 WHERE id=%d',array($id['item_name']));
        return DB::GetOne('SELECT wp.f_item_name FROM premium_ecommerce_products_data_1 ep INNER JOIN premium_warehouse_items_data_1 wp ON ep.f_item_name=wp.id WHERE ep.id=%d',array($id));
    }*/
    
    public static function get_categories() {
        $categories = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_categories',array(),array(),array('position'=>'ASC'));
		$opts = array('__NULL__'=>'---');
   		$ch = array();
   		foreach ($categories as $v) {
    	    if(!$v['parent_category'])
   		    	$opts[$v['id']] = $v['category_name'];
   		    else {
   		        if(!isset($ch[$v['parent_category']])) $ch[$v['parent_category']] = array();
   		        $ch[$v['parent_category']][$v['id']] = $v['category_name'];
   		    }
        }
        $i=2;
        while(!empty($ch)) {
            $opts2 = array();
            foreach($opts as $k=>$v) {
                $opts2[$k] = $v;
                if(isset($ch[$k])) {
                    foreach($ch[$k] as $kk=>$vv)
                        $opts2[$kk] = str_pad('',$i*6,'&nbsp;').$vv;
                    unset($ch[$k]);
                }
            }
            $opts = $opts2;
            $i+=2;
            if($i>10) break;
        }
        return $opts;
    }

	public static function category_filter($choice) {
		if ($choice=='__NULL__') return array();
		$ids = DB::GetCol('SELECT id FROM premium_warehouse_items_data_1 WHERE (f_category '.DB::like().' CONCAT("%%\_\_",%d,"\_\_%%") OR f_category '.DB::like().' CONCAT("%%\_\_",%d,"\/%%") OR f_category '.DB::like().' CONCAT("%%\/",%d,"\_\_%%") OR f_category '.DB::like().' CONCAT("%%\/",%d,"\/%%")) AND active=1',array($choice,$choice,$choice,$choice));
		return array('item_name'=>$ids);
	}
	
	public static function QFfield_shipment_service_type(&$form, $field, $label, $mode, $default, $desc) {
		$param = explode('::',$desc['param']['array_id']);
		foreach ($param as $k=>$v) if ($k!==0) $param[$k] = strtolower(str_replace(' ','_',$v));
		$form->addElement('commondata', $field, $label, $param, array('empty_option'=>false), array('id'=>$field));
		if ($mode!=='add') $form->setDefaults(array($field=>$default));
	}
	
	public static function QFfield_shipment_type(&$form, $field, $label, $mode, $default, $args,$rb) {
	    if(($mode=='edit' || $mode=='add') && strpos($default,'#')===false) {
		$param = explode('::',$args['param']['array_id']);
                foreach ($param as $k=>$v) if ($k!=0) $param[$k] = preg_replace('/[^a-z0-9]/','_',strtolower($v));
			$label = Utils_RecordBrowserCommon::get_field_tooltip($label, $args['type'], $args['param']['array_id']);
                $form->addElement('commondata', $field, $label, $param, array('empty_option'=>true, 'id'=>$args['id'], 'order_by_key'=>$args['param']['order_by_key']));
                if ($mode!=='add') $form->setDefaults(array($args['id']=>$default));
            } else {
        	$form->addElement('static',$args['id'],$label,self::display_shipment_type($rb->record));
            }
	}

	public static function display_shipment_type($r,$nolink=false) {
		$shi = explode('#',$r['shipment_type']);
		if(count($shi)==1)
			return Utils_CommonDataCommon::get_value('Premium_Items_Orders_Shipment_Types/'.$shi[0]);
		return Utils_CommonDataCommon::get_value('Premium_Items_Orders_Shipment_Types/'.$shi[0]).' ('.Utils_CommonDataCommon::get_value('Premium_Items_Orders_Shipment_Types/'.$shi[0].'/'.$shi[1]).')';
	}
	
	public static function cron() {
        return array('cron_orders'=>3,'cron_categories'=>6*60);
    }

    public static function cron_orders() {
	    $drupals = Utils_RecordBrowserCommon::get_records('premium_ecommerce_drupal');
	    foreach($drupals as $drupal_row) {
	        $drupal_id = $drupal_row['id'];
	        
//	        print("1\n");

			//create new orders
			$taxes = DB::GetAssoc('SELECT f_percentage, id FROM data_tax_rates_data_1 WHERE active=1');
			$drupal_orders_tmp = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'order',array('filter'=>array('status'=>'pending'),'limit'=>999999999999999999));
			foreach($drupal_orders_tmp as $ord) {
			  if(!Utils_RecordBrowserCommon::get_records_count('premium_ecommerce_orders',array('drupal'=>$drupal_id,'drupal_order_id'=>$ord['order_id']))) {
			    $billing = array_shift($ord['commerce_customer_billing_entities']);
			    $billing = $billing['commerce_customer_address'];
			    if(!$billing['last_name'] && $billing['name_line'])
			      @list($billing['first_name'],$billing['last_name']) = explode(' ',$billing['name_line'],2);
			    if(isset($ord['commerce_customer_shipping_entities'])) {
			      $shipping = array_shift($ord['commerce_customer_shipping_entities']);
			      $shipping = $shipping['commerce_customer_address'];
			      if(!$shipping['last_name'] && $shipping['name_line'])
			        @list($shipping['first_name'],$shipping['last_name']) = explode(' ',$shipping['name_line'],2);
			    } else {
			      $shipping = array('organisation_name'=>'','last_name'=>'','first_name'=>'','thoroughfare'=>'','locality'=>'','postal_code'=>'','country'=>'');
			    }
			      
			    $products = array();
			    //products
			    foreach($ord['commerce_line_items_entities'] as $line_item) {
			      if($line_item['type']!='product') continue;
			      $node = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'product/'.$line_item['commerce_product']);
			      if($node['type']!='epesi_products') continue;
			      $products[$line_item['commerce_product']] = $node;
			    }
			    if(!$products) continue;
			      
			    $memo = '';
			    if(isset($ord['data']['payment_method']) && $ord['data']['payment_method']) {
			      list($payment_method) = explode('|',$ord['data']['payment_method']);
			      $memo .= _('Payment').': '.$payment_method."\n";
			    }
			    
			    //shipping
			    $shipping_cost = 0;
			    $handling_cost = 0;
			    $currency_id = 0;
			    $currency_precission = 0;
			    foreach($ord['commerce_line_items_entities'] as $line_item) {
			      if(!$currency_id && isset($line_item['commerce_unit_price']['currency_code'])) {
			        $currency_id = Utils_CurrencyFieldCommon::get_id_by_code($line_item['commerce_unit_price']['currency_code']);
			        $currency_precission = pow(10,Utils_CurrencyFieldCommon::get_precission($currency_id));
			      }
			      if($line_item['type']=='shipping') {
			        $memo .= __('Shipping').': '.$line_item['line_item_label']."\n";
			        $shipping_cost += $line_item['commerce_unit_price']['amount'];
			      } elseif($line_item['type']!='product' && isset($line_item['commerce_unit_price']['amount']) && $line_item['commerce_unit_price']['amount']) {
			        $handling_cost += $line_item['commerce_unit_price']['amount'];
			        $memo .= _('Handling').': '.$line_item['line_item_label'].' '.($line_item['commerce_unit_price']['amount']/$currency_precission).' '.$line_item['commerce_unit_price']['currency_code']."\n";
			      }
			    }
			    $shipping_cost/=$currency_precission;
			    $handling_cost/=$currency_precission;
			    
			    //contact & company
			    $contact = DB::GetOne('SELECT id FROM contact_data_1 WHERE f_email=%s AND active=1',array($ord['mail']));
			    $company = DB::GetOne('SELECT id FROM company_data_1 WHERE f_email=%s AND active=1',array($ord['mail']));
			    if(!$company && $billing['organisation_name']) {
			        $company = Utils_RecordBrowserCommon::new_record('company',array(
			          'company_name'=>$billing['organisation_name'],
			          'address_1'=>$billing['thoroughfare'],
			          'city'=>$billing['locality'],
			          'postal_code'=>$billing['postal_code'],
			          'phone'=>isset($billing['phone'])?$billing['phone']:'',
			          'country'=>$billing['country'],
			          'email'=>$ord['mail'],
			          'group'=>'customer',
			          'permission'=>0
			        ));
			    }
			    if(!$contact) {
			      $arr = array(
			        'last_name'=>$billing['last_name'],
			        'first_name'=>$billing['first_name'],
			        'address_1'=>$billing['thoroughfare'],
			        'city'=>$billing['locality'],
			        'postal_code'=>$billing['postal_code'],
			        'work_phone'=>isset($billing['phone'])?$billing['phone']:'',
			        'country'=>$billing['country'],
			        'email'=>$ord['mail'],
			        'group'=>'custm',
			        'permission'=>0
			      );
			      if($company)
			        $arr['company_name']=$company;
			      $contact = Utils_RecordBrowserCommon::new_record('contact',$arr);
			    } elseif($company) {
			      $ccc = Utils_RecordBrowserCommon::get_record('contact',$contact);
			      if($ccc['company_name'])
			        $ccc['related_companies'][] = $company;
			      else
			        $ccc['company_name'] = $company;
			      Utils_RecordBrowserCommon::update_record('contact',$contact,$ccc);
			    }
			    
			    $id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders',array(
			      'transaction_type'=>1,
			      'transaction_date'=>$ord['created'],
			      'company_name'=>$billing['organisation_name'],
			      'last_name'=>$billing['last_name'],
			      'first_name'=>$billing['first_name'],
			      'address_1'=>$billing['thoroughfare'],
			      'city'=>$billing['locality'],
			      'postal_code'=>$billing['postal_code'],
			      'phone'=>isset($billing['phone'])?$billing['phone']:'',
			      'country'=>$billing['country'],
			//	    'zone'=>$aForm['sState'],
			      'created_on'=>$ord['created'],
			      'shipment_type'=>'Drupal',
			      'shipment_cost'=>$shipping_cost.'__'.$currency_id,
			      'payment'=>1,
			      'payment_type'=>'Drupal',
			      'memo'=>$memo,
			//	    'tax_id'=>$aForm['sNip'],
			      'tax_calculation'=> Variable::get('premium_warehouse_def_tax_calc', false),
			//	    'warehouse'=>$carrier==0?$aForm['iPickupShop']:null,
			      'online_order'=>1,
			      'status'=>-1,
			      'contact'=>$contact,
			      'company'=>$company,
			//	    'terms'=>$order_terms,
			//	    'receipt'=>$aForm['iInvoice']?0:1,
			      'handling_cost'=>$handling_cost.'__'.$currency_id,
			      'shipping_company_name'=>$shipping['organisation_name'],
			      'shipping_last_name'=>$shipping['last_name'],
			      'shipping_first_name'=>$shipping['first_name'],
			      'shipping_address_1'=>$shipping['thoroughfare'],
			      'shipping_city'=>$shipping['locality'],
			      'shipping_postal_code'=>$shipping['postal_code'],
			      'shipping_phone'=>isset($shipping['phone'])?$shipping['phone']:'',
			      'shipping_country'=>$shipping['country'],
			      'shipping_contact'=>$contact,
			      'shipping_company'=>$company
			    ));
			    
			    $drupal_user = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'user/'.$ord['uid']);
			    
			    Utils_RecordBrowserCommon::new_record('premium_ecommerce_orders',array(
			      'drupal'=>$drupal_id,
			      'drupal_order_id'=>$ord['order_id'],
			      'transaction_id'=>$id,
			      'email'=>$ord['mail'],
			      'ip'=>isset($ord['hostname'])?$ord['hostname']:(isset($ord['revision_hostname'])?$ord['revision_hostname']:''),
			      'language'=>$drupal_user['language']
			    ));
			
			    //products
			    foreach($ord['commerce_line_items_entities'] as $line_item) {
			      if($line_item['type']!='product') continue;
			      $tax_amount = 0;
			      $tax = 0;
			      $tax_type = null;
			      foreach($line_item['commerce_unit_price']['data']['components'] as $pr) {
			        if(isset($pr['price']['data']['tax_rate'])) {
			          $tax_amount+=$pr['price']['amount'];
			          if(isset($pr['price']['data']['tax_rate']['type'])) {
    			        $tax_type = $pr['price']['data']['tax_rate']['type'];
    			      }
			          if(isset($taxes[(string)($pr['price']['data']['tax_rate']['rate']*100)])) {
			            $tax = $taxes[(string)($pr['price']['data']['tax_rate']['rate']*100)];
			          }
			        }
			      }
			      if($tax_type=='sales_tax') {
	    		      $net = $line_item['commerce_unit_price']['amount']/$currency_precission;
    			      $gross = ($line_item['commerce_unit_price']['amount']+$tax_amount)/$currency_precission;
			      } else {
	    		      $net = ($line_item['commerce_unit_price']['amount']-$tax_amount)/$currency_precission;
    			      $gross = $line_item['commerce_unit_price']['amount']/$currency_precission;
			      }
			      $node = $products[$line_item['commerce_product']];
			      $sku = explode(' ',$node['sku'],2);
			      $product_id = ltrim($sku[0],'#0');
			      ob_start();
			      Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details',array('transaction_id'=>$id,'item_name'=>$product_id,'quantity'=>$line_item['quantity'],'description'=>(isset($sku[1])?$sku[1].' | ':'').$line_item['line_item_label'].' '.$line_item['line_item_title'],'tax_rate'=>$tax,'net_price'=>$net.'__'.$currency_id,'gross_price'=>$gross.'__'.$currency_id));
			      ob_clean();
			    }
			    
				//TODO: skasowanie niepotrzebnych pol z ecommerce_orders
			  }
			}

			if(ModuleManager::is_installed('Premium_Payments')>=0) {
				$payments = Utils_RecordBrowserCommon::get_records('premium_payments',array('record_id'=>''));
				foreach($payments as $payment) {
					if(preg_match('/^drupal:([0-9]+):([0-9]+)$/',$payment['record_hash'],$match)) {
						$ecomm_order = Utils_RecordBrowserCommon::get_records('premium_ecommerce_orders',array('drupal'=>$match[1],'drupal_order_id'=>$match[2]));
						if(!$ecomm_order) continue;
						$ecomm_order = array_shift($ecomm_order);
						Utils_RecordBrowserCommon::update_record('premium_payments',$payment['id'],array('record_id'=>$ecomm_order['transaction_id']));
					}
				}
			}
		}
	}
	
	public static function cron_categories() {
	    $drupals = Utils_RecordBrowserCommon::get_records('premium_ecommerce_drupal');
	    $default_lang = Base_LangCommon::get_lang_code();
	    $log = array();
	    foreach($drupals as $drupal_row) {
	        $drupal_id = $drupal_row['id'];

            //look for epesi vocabulary
            $voc = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'entity_taxonomy_vocabulary',array('pagesize'=>9999999999));
            $epesi_vocabulary = null;
            $epesi_manufacturer_vocabulary = null;
            foreach($voc as $v) {
              if($v['machine_name']=='epesi_category')
                $epesi_vocabulary = $v['vid'];
              if($v['machine_name']=='epesi_manufacturer')
                $epesi_manufacturer_vocabulary = $v['vid'];
              if($epesi_vocabulary && $epesi_manufacturer_vocabulary)
                break;
            }
            if(!$epesi_vocabulary || !$epesi_manufacturer_vocabulary) continue;
            
            //get terms from epesi vocabulary
            $category_exists = array();
            $category_mapping = array();
            try {
              $terms = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_taxonomy_vocabulary/getTree',array('vid'=>$epesi_vocabulary,'load_entities'=>1));
              foreach($terms as $term_data) {
                $category_exists[$term_data['tid']] = 1;
                $category_mapping[$term_data['field_epesi_category_id']['und'][0]['value']] = $term_data['tid'];
              }
            } catch(Exception $e) {}
            //print_r($terms);
            
            //get local epesi categories
            $epesi_categories_temp = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_categories');
            $epesi_category_names = array();
            $epesi_category_parents = array();
            $epesi_category_weight = array();
            foreach($epesi_categories_temp as $c) {
              if(isset($category_mapping[$c['id']])) {
                $category_exists[$category_mapping[$c['id']]] = 2;
              }
              $epesi_category_names[$c['id']] = html_entity_decode($c['category_name']);
              $epesi_category_parents[$c['id']] = $c['parent_category'];
              $epesi_category_weight[$c['id']] = $c['position'];
            }
            //print_r($category_mapping);
            
            do {
		      $old_count_epesi_category_names = count($epesi_category_names);
              //TODO: use or remove meta tags from descriptions from ecommerce recordsets
              foreach($epesi_category_names as $id=>$name) {
		       // print("4 ".$name."\n");
                if($epesi_category_parents[$id] && !isset($category_mapping[$epesi_category_parents[$id]])) continue;
                $term = array();
                $term['name'] = $name;
			    $term['vocabulary_machine_name'] = 'epesi_category';
                $term['name_original'] = $term['name_field']['und'][0]['value'] = $term['name_field']['en'][0]['value'] =  $name;
                $term['vid'] = $epesi_vocabulary;
                $term['field_epesi_category_id']['und'][0]['value']=$id;
                $term['description_field'] = array();
                $term['description_original'] = '';
                $term['format'] = 'full_html';
                $term['translations']['original']='en';
                $term['weight'] = $epesi_category_weight[$id];
                $term['field_images'] = array('und'=>array());
                
                if($epesi_category_parents[$id])
                  $term['parent'] = $category_mapping[$epesi_category_parents[$id]];

			    //get images
			    Premium_Warehouse_DrupalCommerceCommon::$images = array();
			    Utils_AttachmentCommon::call_user_func_on_file('premium_warehouse_items_categories/'.$id,array('Premium_Warehouse_DrupalCommerceCommon','copy_attachment'),false,array($drupal_id,1));
			    $desc_langs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_cat_descriptions',array('category'=>$id),array('language'));
			    foreach($desc_langs as $desc_lang)
			      Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_cat_descriptions/'.$desc_lang['language'].'/'.$id,array('Premium_Warehouse_DrupalCommerceCommon','copy_attachment'),false,array($drupal_id,0));
			    $field_images = array();
			    foreach(Premium_Warehouse_DrupalCommerceCommon::$images as $lang=>$fids) {
			      foreach($fids as $fid) {
			        if($lang=='und') $term['field_images']['und'][]['fid'] = $fid;
			        else $field_images[$lang][]['fid'] = $fid;
			      }
			    }
			    
			    //update each language... if there is no field_images translation, default/random language images are displayed
			    foreach(Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages') as $lang=>$lang_name)
			      if(!isset($field_images[$lang])) $field_images[$lang] = array();
			      
                //sync/create categories
                if(isset($category_mapping[$id])) {
                  $term['tid'] = $category_mapping[$id];
                  try {
                    Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'entity_taxonomy_term/'.$category_mapping[$id],$term);
                  } catch(Exception $e) {
                    $log[] = 'DRUPAL #'.$drupal_id.' Error updating category: '.$e->getMessage().' '.print_r($term,true);
                    continue;
                  }
                } else {
                  try {
                    Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_taxonomy_term',$term);
                  } catch(Exception $e) {
                    $log[] = 'DRUPAL #'.$drupal_id.' Error adding category: '.$e->getMessage().' '.print_r($term,true);
                    continue;
                  }
                  $all_terms = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_taxonomy_vocabulary/getTree',array('vid'=>$epesi_vocabulary,'maxdepth'=>99));
                  foreach($all_terms as $t) {
                    if(!isset($category_exists[$t['tid']])) {
                      $term_data = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'entity_taxonomy_term/'.$t['tid']);
                      if($term_data['field_epesi_category_id']['und'][0]['value']==$id) {
                        $category_exists[$t['tid']] = 2;
                        $category_mapping[$term_data['field_epesi_category_id']['und'][0]['value']] = $t['tid'];
                        break;
                      }
                    }
                  }
                }
                
                //sync translations
                $translations = Utils_RecordBrowserCommon::get_records('premium_ecommerce_cat_descriptions',array('category'=>$id));
                foreach($translations as $translation) {
                  $values = array();
                  $values['name_field'][$translation['language']][0]['value'] = $translation['display_name']?html_entity_decode($translation['display_name']):$name;
                  $values['description_field'][$translation['language']][0]['value'] = $translation['long_description'];
                  $values['description_field'][$translation['language']][0]['format'] = 'full_html';
                  $values['description_field'][$translation['language']][0]['summary'] = $translation['short_description'];
			      $values['field_images'][$translation['language']] = isset($field_images[$translation['language']])?array_merge($term['field_images'],$field_images[$translation['language']]):$term['field_images'];
                  $info = array(
                    'language'=>$translation['language'],
                    'source'=>$translation['language']=='en'?'':'en',
                    'status'=>1,
                    'translate'=>0,
                  );
                  try {
                    Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_translation/translate',array('entity_type'=>'taxonomy_term','entity_id'=>$category_mapping[$id],'translation'=>$info,'values'=>$values));
                  } catch(Exception $e) {
                    $log[] = 'DRUPAL #'.$drupal_id.' Error translating to '.$translation['language'].' category '.$category_mapping[$id].': '.$e->getMessage().' '.print_r($values,true);
                  }
                }

                unset($epesi_category_names[$id]);
              }
            } while(!empty($epesi_category_names) && $old_count_epesi_category_names!=count($epesi_category_names));
            
            //remove elements with invalid epesi_category field
            foreach($category_exists as $tid=>$val) {
              if($val===1)  try {
                  Premium_Warehouse_DrupalCommerceCommon::drupal_delete($drupal_id,'entity_taxonomy_term/'.$tid);
              } catch(Exception $e) {}
            }

            //update manufacturers
            
            //get terms from epesi manufacturer vocabulary
            $manufacturer_exists = array();
            $manufacturer_mapping = array();
            try {
              $terms = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_taxonomy_vocabulary/getTree',array('vid'=>$epesi_manufacturer_vocabulary,'load_entities'=>1));
              foreach($terms as $term_data) {
                $manufacturer_exists[$term_data['tid']] = 1;
//                $term_data = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'taxonomy_term/'.$t['tid']);
                $manufacturer_mapping[$term_data['field_epesi_manufacturer_id']['und'][0]['value']] = $term_data['tid'];
              }
            } catch(Exception $e) {}
            
            //get local epesi manufacturers
            $epesi_manufacturers_temp = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('!manufacturer'=>''),array('manufacturer'));
            $epesi_manufacturer_names = array();
            foreach($epesi_manufacturers_temp as $c) {
              if(isset($manufacturer_mapping[$c['manufacturer']])) {
                $manufacturer_exists[$manufacturer_mapping[$c['manufacturer']]] = 2;
              }
              $manufacturer = CRM_ContactsCommon::get_company($c['manufacturer']);
              $epesi_manufacturer_names[$c['manufacturer']] = $manufacturer['company_name'];
            }
            
            foreach($epesi_manufacturer_names as $id=>$name) {
		        //print("5 ".$name."\n");
              $term = array();
              $term['name'] = $name;
              $term['name_original'] = $name;
              $term['vid'] = $epesi_manufacturer_vocabulary;
              $term['field_epesi_manufacturer_id']['und'][0]['value']=$id;
              $term['name_field'] = array();
              $term['description_field'] = array();
              $term['description_original'] = '';
              $term['format'] = 'full_html';
              $term['translations']['original']='en';
              
              //sync/create categories
              if(isset($manufacturer_mapping[$id])) {
                $term['tid'] = $manufacturer_mapping[$id];
                try {
                  Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'entity_taxonomy_term/'.$manufacturer_mapping[$id],$term);
                } catch(Exception $e) {
                  $log[] = 'DRUPAL #'.$drupal_id.' Error updating manufacturer: '.$e->getMessage().' '.print_r($term,true);
                  continue;
                }
              } else {
                try {
                  Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_taxonomy_term',$term);
                } catch(Exception $e) {
                  $log[] = 'DRUPAL #'.$drupal_id.' Error adding manufacturer: '.$e->getMessage().' '.print_r($term,true);
                  continue;
                }
                $all_terms = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_taxonomy_vocabulary/getTree',array('vid'=>$epesi_manufacturer_vocabulary,'maxdepth'=>99));
                foreach($all_terms as $t) {
                  if(!isset($manufacturer_exists[$t['tid']])) {
                    $term_data = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'entity_taxonomy_term/'.$t['tid']);
                    if($term_data['field_epesi_manufacturer_id']['und'][0]['value']==$id) {
                      $manufacturer_exists[$t['tid']] = 2;
                      $manufacturer_mapping[$term_data['field_epesi_manufacturer_id']['und'][0]['value']] = $t['tid'];
                      break;
                    }
                  }
                }
              }
            }
            unset($epesi_manufacturer_names);
            
            //remove elements with invalid epesi_category field
            foreach($manufacturer_exists as $tid=>$val) {
              if($val===1) Premium_Warehouse_DrupalCommerceCommon::drupal_delete($drupal_id,'entity_taxonomy_term/'.$tid);
            }

			$manufacturers = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('!manufacturer'=>''),array('manufacturer'));
			
			//update products
			//get fields
			$product_fields = array_merge(Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'epesi_commerce/get_product_fields'),array('sku','title','type'));
			$node_fields = array_merge(Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'epesi_commerce/get_node_fields'),array('type','field_title','title','promote','sticky','uid'));
			
			//get old products
			$drupal_products_tmp = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'product',array('fields'=>'product_id,sku','filter'=>array('type'=>'epesi_products'),'sort_by'=>'sku','limit'=>999999999999999999));
			$drupal_products = array();
			$drupal_products_done = array();
			$drupal_nodes_done = array();
			foreach($drupal_products_tmp as $row) {
			  $drupal_products[$row['sku']] = $row['product_id'];
			}
			unset($drupal_products_tmp);
			
			$currencies = DB::GetAssoc('SELECT id,code,decimals FROM utils_currency WHERE active=1');
			$taxes = DB::GetAssoc('SELECT id, f_percentage FROM data_tax_rates_data_1 WHERE active=1');
			$export_net_price = $drupal_row['export_net_price'];
			
			$products = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('publish'=>1),array(),array('item_name'=>'ASC'));
			foreach($products as $row) {
			  if(isset($drupal_products_done[$row['sku']])) continue;
			  
			  $ecommerce_product_id = $row['id'];
			  $row = array_merge($row,Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$row['item_name']));
			  if(!$row['category'] || !$row[':active']) continue;
			  
			  $row['item_name'] = html_entity_decode($row['item_name']);
			  
			  //product
			  $data = array('sku'=>$row['sku'],'title'=>$row['item_name'],'type'=>'epesi_products');
			  if($row['weight']) $data['field_weight'] = array('weight'=>$row['weight'],'unit'=>Variable::get('premium_warehouse_weight_units','lb'));
			  if($row['volume']) $data['field_dimensions'] = array('length'=>$row['volume'],'width'=>1,'height'=>1,'unit'=>preg_replace('/[^a-z]/','',strip_tags(Variable::get('premium_warehouse_volume_units','in'))));

			  
			  //set quantity
			  $quantity = Premium_Warehouse_Items_LocationCommon::get_item_quantity_in_warehouse($row['id']) - DB::GetOne('SELECT SUM(d.f_quantity) FROM premium_warehouse_items_orders_details_data_1 d INNER JOIN premium_warehouse_items_orders_data_1 o ON (o.id=d.f_transaction_id) WHERE ((o.f_transaction_type=1 AND o.f_status in (-1,2,3,4,5)) OR (o.f_transaction_type=4 AND o.f_status in (2,3))) AND d.active=1 AND o.active=1 AND d.f_item_name=%d',array($row['id']));
			  if($quantity<=0) {
			    if($row['always_in_stock']) {
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
			  Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products/'.$row['id'],array('Premium_Warehouse_DrupalCommerceCommon','copy_attachment'),false,array($drupal_id,1));
			  $desc_langs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$row['id']),array('language'));
			  foreach($desc_langs as $desc_lang)
			    Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions/'.$desc_lang['language'].'/'.$row['item_name'],array('Premium_Warehouse_DrupalCommerceCommon','copy_attachment'),false,array($drupal_id,0));
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
			    

			  $products_queue = array();
			  //set prices
			  $prices = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$row['id']),array(),array('gross_price'=>'ASC'));
			  if($prices) {
			    foreach($prices as $price) {
			      if(!isset($currencies[$price['currency']])) continue;
			      if(isset($products_queue[$price['model']])) $tmp_data = $products_queue[$price['model']];
			      else {
			        $tmp_data = $data;
			        $tmp_data['title'] = $price['model']?$price['model']:'---';
			        $tmp_data['sku'] .= $price['model']?' '.$price['model']:'';
			      }
			      $currency = $currencies[$price['currency']];
			      $tmp_data['commerce_price_'.strtolower($currency['code'])]=array('amount'=>round($export_net_price?($price['gross_price']*100/(100+($price['tax_rate']?$taxes[$price['tax_rate']]:0))):$price['gross_price'],$currency['decimals'])*pow(10,$currency['decimals']),
			                    'currency_code'=>$currency['code']);
			      if(!isset($data['commerce_price']))
			        $tmp_data['commerce_price'] = $tmp_data['commerce_price_'.strtolower($currency['code'])];
			      $products_queue[$price['model']] = $tmp_data;
			    }
			  } elseif($row['net_price']) {
			    $item_price = Utils_CurrencyFieldCommon::get_values($row['net_price']);
			    if($item_price[0] && isset($currencies[$item_price[1]])) {
			      $currency = $currencies[$item_price[1]];
			      $data['commerce_price']=array('amount'=>round($export_net_price?(float)$item_price[0]:((float)$item_price[0])*(100+($row['tax_rate']?$taxes[$row['tax_rate']]:0))/100,$currency['decimals'])*pow(10,$currency['decimals']),
			                    'currency_code'=>$currency['code']);
			      if(!isset($data['commerce_price_'.strtolower($currency['code'])]))
			        $data['commerce_price_'.strtolower($currency['code'])] = $data['commerce_price'];
			      $products_queue[] = $data;
			    }
			  }
			  if(!$products_queue) continue;
			  
			  //update product
			  $drupal_product_ids = array();
			  foreach($products_queue as $data) {
			    //filter out invalid fields
			    foreach($data as $key=>$value) {
			      if(!in_array($key,$product_fields)) {
			        //print('Invalid product field: '.$key."\n");
			        unset($data[$key]);
			      }
			    }
			    //check all required prices are set in product
			    foreach($product_fields as $key) {
			      if(preg_match('/^commerce_price_/',$key)) {
			        if(!isset($data[$key])) continue 2; //skip product
			      }
			    }
			    
			    //check if product exists in drupal
			    $drupal_product_id = 0;
			    $nid = 0;
			    if(isset($drupal_products[$data['sku']])) {
			      //check product
			      $drupal_data = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'product/'.$drupal_products[$data['sku']]);
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
			      if($update) {
			        try {
			          Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'product/'.$drupal_products[$data['sku']],$data);
			        } catch(Exception $e) {
			          $log[] = 'DRUPAL #'.$drupal_id.' Error updating product: '.$e->getMessage().' '.print_r($data,true);
			          continue;
			        }
			      }
			      $drupal_product_id = $drupal_products[$data['sku']];
			      $nodes = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'views/epesi_products_search_by_product_id.json?'.http_build_query(array('display_id'=>'services_1','args'=>array($drupal_product_id,''))));
			      $nid = isset($nodes[0]['nid'])?$nodes[0]['nid']:0;
//			      $product = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'product/'.$drupal_product_id);
			    } else {
			      try {
			        $product = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'product',$data);
			        $drupal_product_id = $product['product_id'];
			      } catch(Exception $e) {
			        $log[] = 'DRUPAL #'.$drupal_id.' Error adding product: '.$e->getMessage().' '.print_r($data,true);
			        continue;
			      }
			    }
			  
			    if(!isset($data['field_images']) || !is_array($data['field_images'])) $data['field_images'] = array();
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
			        //error_log($drupal_product_id.' trans_prod '.var_export($values,true).' '.var_export($info,true)."\n",3,DATA_DIR.'/aaaa.log');
			        try {
			          Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_translation/translate',array('entity_type'=>'commerce_product','entity_id'=>$drupal_product_id,'translation'=>$info,'values'=>$values));
			        } catch(Exception $e) {
			          $log[] = 'DRUPAL #'.$drupal_id.' Error translating to '.$lang.' product '.$drupal_product_id.': '.$e->getMessage().' '.print_r($values,true);
			        }
			      }
			      $drupal_product_ids[] = array('product_id'=>$drupal_product_id);
			      $drupal_products_done[$data['sku']] = 1;
			    }
			  }
			    
			    if(!$drupal_product_ids) continue;
			
			    //update node of product
			    //print_r($row);
			    //if($row['recommended']) print('RECOMMENDED!!!'."\n");
			    $node = array();
			    $node['type']='epesi_products';
                $node['uid'] = $_SESSION['drupal_uid'];
			    $node['title']=$node['title_field']['en'][0]['value']=$node['title_field']['und'][0]['value']=$node['field_title']=trim($row['item_name']);
			    $node['body']['en'][0]['value']=$row['description'];
			    $node['body']['en'][0]['format'] = 'full_html';
//			    $node['field_product']['und'][0]['product_id'] = $drupal_product_id;
			    $node['field_product']['und'] = $drupal_product_ids;
			    $node['promote']=$row['recommended']?1:null; //TODO: doesn't work
			    $node['sticky']=$row['recommended']?1:null;
			    foreach($row['category'] as $ccc) {
			      $category_id = array_pop(explode('/',$ccc));
			      if(isset($category_mapping[$category_id])) $node['field_epesi_category']['und'][]['tid'] = $category_mapping[$category_id];
			    }
			    if(!isset($node['field_epesi_category'])) continue;
			    
			    if($row['manufacturer'] && isset($manufacturer_mapping[$row['manufacturer']]))
			      $node['field_manufacturer']['und'][0]['tid'] = $manufacturer_mapping[$row['manufacturer']];
			    $translations = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$row['id'],'language'=>'en'));
			    if($translations) {
			      $translations = array_shift($translations);
			      if($translations['display_name']) $node['title']=$node['title_field']['en'][0]['value'] = $node['title_field']['und'][0]['value']=$node['field_title']=$translations['display_name'];
			      $node['body']['en'][0]['value']=$translations['long_description'];
			      $node['body']['en'][0]['summary']=$translations['short_description'];
			    }

			    //features / parameters
		        $parameters = array();
			    $ret2 = DB::Execute('SELECT pp.f_item_name, pp.f_value,
									p.f_parameter_code as parameter_code,
									pl.f_label as parameter_label,
									g.f_group_code as group_code,
									gl.f_label as group_label,
									pp.f_language as language
						FROM premium_ecommerce_products_parameters_data_1 pp
						INNER JOIN (premium_ecommerce_parameters_data_1 p,premium_ecommerce_parameter_groups_data_1 g) ON (p.id=pp.f_parameter AND g.id=pp.f_group)
						LEFT JOIN premium_ecommerce_parameter_labels_data_1 pl ON (pl.f_parameter=p.id AND pl.f_language=pp.f_language AND pl.active=1)
						LEFT JOIN premium_ecommerce_param_group_labels_data_1 gl ON (gl.f_group=g.id AND gl.f_language=pp.f_language AND gl.active=1)
						WHERE pp.active=1 AND pp.f_item_name=%d ORDER BY pp.f_language,g.f_position,gl.f_label,g.f_group_code,p.f_position,pl.f_label,p.f_parameter_code',array($row['id']));
	            while($bExp = $ret2->FetchRow()) {
	                if(!isset($parameters[$bExp['language']])) {
	                    $parameters[$bExp['language']] = array();
            			$last_group = null;
            		}
		    		$parameters[$bExp['language']][] = array('sGroup'=>($last_group!=$bExp['group_code']?($bExp['group_label']?$bExp['group_label']:$bExp['group_code']):''), 'sName'=>($bExp['parameter_label']?$bExp['parameter_label']:$bExp['parameter_code']), 'sValue'=>($bExp['f_value']=='Y'?'<span class="yes">Yes</span>':($bExp['f_value']=='N'?'<span class="no">No</span>':$bExp['f_value'])));
			    	if($last_group != $bExp['group_code']) {
    					$last_group = $bExp['group_code'];
				    }
			    }
			    Base_LangCommon::load('en');
			    $parameters['en'][] = array('sGroup'=>__('Codes'),'sName'=>'SKU','sValue'=>$row['sku']);
			    if($row['upc']) $parameters['en'][] = array('sGroup'=>'','sName'=>__('UPC'),'sValue'=>$row['upc']);
			    if($row['product_code']) $parameters['en'][] = array('sGroup'=>'','sName'=>__('Product code'),'sValue'=>$row['product_code']);
			    Base_LangCommon::load($default_lang);

			    $features = '<table id="features" cellspacing="1"><tbody>';
			    $i2=0;
			    foreach($parameters['en'] as $aData) {
				    $aData['iStyle'] = ( $i2 % 2 ) ? 0: 1;
    				$features .= '<tr class="l'.$aData['iStyle'].'"><th>'.$aData['sGroup'].'</th><th>'.$aData['sName'].'</th><td>'.$aData['sValue'].'</td></tr>';
	    			$i2++;
		    	} // end for
			    $features .= '</tbody></table>';
                $node['body']['en'][0]['value'] .= $features;
                
                foreach($node as $key=>$value) {
			      if(!in_array($key,$node_fields)) {
			        print('Invalid node field: '.$key."\n");
			        unset($node[$key]);
			      }
			    }

//			    print(var_export($node));
			    if($nid) {
//			      print('nid='.$nid."\n");
                  $node['nid']=$nid;
			      //error_log($nid.' upd_node '.var_export($node,true)."\n",3,DATA_DIR.'/aaaa.log');
			      try {
			        Premium_Warehouse_DrupalCommerceCommon::drupal_put($drupal_id,'entity_node/'.$nid,$node);
			      } catch(Exception $e) {
			        $log[] = 'DRUPAL #'.$drupal_id.' Error updating node: '.$e->getMessage().' '.print_r($node,true);
			        continue;
			      }
			    } else {
			      try {
			        $tmp = Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_node',$node);
			        $nid = $tmp['nid'];
			      } catch(Exception $e) {
			        $log[] = 'DRUPAL #'.$drupal_id.' Error adding node: '.$e->getMessage().' '.print_r($node,true);
			        continue;
			      }
			    }
			    $drupal_nodes_done[$nid] = 1;
			    
			    $translations = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$row['id']));
			    foreach($translations as $translation) {
			      //features
			      Base_LangCommon::load($translation['language']);
			      $parameters[$translation['language']][] = array('sGroup'=>__('Codes'),'sName'=>'SKU','sValue'=>$row['sku']);
			      if($row['upc']) $parameters[$translation['language']][] = array('sGroup'=>'','sName'=>__('UPC'),'sValue'=>$row['upc']);
			      if($row['product_code']) $parameters[$translation['language']][] = array('sGroup'=>'','sName'=>__('Product Code'),'sValue'=>$row['product_code']);
			      Base_LangCommon::load($default_lang);

			      $features = '<table id="features" cellspacing="1"><tbody>';
			      $i2=0;
			      foreach($parameters[$translation['language']] as $aData) {
				    $aData['iStyle'] = ( $i2 % 2 ) ? 0: 1;
    				$features .= '<tr class="l'.$aData['iStyle'].'"><th>'.$aData['sGroup'].'</th><th>'.$aData['sName'].'</th><td>'.$aData['sValue'].'</td></tr>';
	    			$i2++;
		    	  } // end for
			      $features .= '</tbody></table>';

			      $values = array();
			      $display_name = trim($translation['display_name']);
			      $values['title_field'][$translation['language']][0]['value'] = $display_name?$display_name:$node['title'];
			      $values['body'][$translation['language']][0]['value'] = $translation['long_description'].$features;
			      $values['body'][$translation['language']][0]['format'] = 'full_html';
			      $values['body'][$translation['language']][0]['summary'] = $translation['short_description'];
			    //  $values['field_epesi_category'][$translation['language']] = $node['field_epesi_category']['und'];
			      $info = array(
			        'language'=>$translation['language'],
			        'source'=>$translation['language']=='en'?'':'en',
			        'status'=>1,
			        'translate'=>0,
			      );
			      //error_log($nid.' trans '.var_export($values,true).' '.var_export($info,true)."\n",3,DATA_DIR.'/aaaa.log');
			        try {
			          Premium_Warehouse_DrupalCommerceCommon::drupal_post($drupal_id,'entity_translation/translate',array('entity_type'=>'node','entity_id'=>$nid,'translation'=>$info,'values'=>$values));
			        } catch(Exception $e) {
			          $log[] = 'DRUPAL #'.$drupal_id.' Error translating to '.$translation['language'].' node '.$nid.': '.$e->getMessage().' '.print_r($values,true);
			        }
			    }
			    
			}

            //print("7\n");
            //print_r($drupal_products_done);
			foreach($drupal_products as $sku=>$id) {
			  if(!isset($drupal_products_done[$sku])) {
			    try {
			        $nodes = Premium_Warehouse_DrupalCommerceCommon::drupal_get($drupal_id,'views/epesi_products_search_by_product_id.json?'.http_build_query(array('display_id'=>'services_1','args'=>array($id,''))));
			    } catch(Exception $e) {
			        $log[] = 'DRUPAL #'.$drupal_id.' Error getting product node id '.$id.': '.$e->getMessage();
			        continue;
			    }

			    try {
			        Premium_Warehouse_DrupalCommerceCommon::drupal_delete($drupal_id,'product/'.$id);
			    } catch(Exception $e) {
			        if(!preg_match('/(Product not found|Product cannot be deleted)/i',$e->getMessage()))
			            $log[] = 'DRUPAL #'.$drupal_id.' Error deleting product '.$id.': '.$e->getMessage();
			    }

			    try {
			        $nid = isset($nodes[0]['nid'])?$nodes[0]['nid']:0;
			        if($nid && !isset($drupal_nodes_done[$nid])) Premium_Warehouse_DrupalCommerceCommon::drupal_delete($drupal_id,'entity_node/'.$nid);
			    } catch(Exception $e) {
			        $log[] = 'DRUPAL #'.$drupal_id.' Error deleting node '.$nid.', product '.$id.': '.$e->getMessage();
			    }
			  }
			}
        }
        return implode("\n",$log);
	}
	
	public static function drupal_connection($drupal) {
	    static $conn;
	    if(!isset($conn)) $conn = array();
	    
	    if(!isset($conn[$drupal])) {
		  require_once(self::Instance()->get_module_dir().'guzzle.phar');
	      $drupal_record = Utils_RecordBrowserCommon::get_record('premium_ecommerce_drupal',$drupal);
	      
	      $endpoint = rtrim($drupal_record['url'],'/')."/".$drupal_record['endpoint'];
	      $conn[$drupal] = $client = new \Guzzle\Http\Client($endpoint);

	      if(isset($_SESSION['drupal_cookies']) && isset($_SESSION['drupal_csrf_token']) && isset($_SESSION['drupal_uid'])) {
	        $cookiePlugin = new \Guzzle\Plugin\Cookie\CookiePlugin(unserialize($_SESSION['drupal_cookies']));
	        $client->addSubscriber($cookiePlugin); 
	      } else {
	        $cookieJar = new \Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar();
	        $cookiePlugin = new \Guzzle\Plugin\Cookie\CookiePlugin($cookieJar);
	        $client->addSubscriber($cookiePlugin); 
	        $user = $client->post('user/login.json')->setBody(json_encode(array('username'=>$drupal_record['login'],'password'=>$drupal_record['password'])),'application/json')->send()->json();
	        if(!isset($user['user']['uid']) || !$user['user']['uid']) {
	          throw new Exception("Drupal login failed.");
	        }
	        $csrf_token=$client->post('user/token.json')->setBody(json_encode(array()),'application/json')->send()->json();
	        if(!isset($csrf_token['token']) || !$csrf_token['token']) {
	          throw new Exception("Drupal getting csrf token failed.");
	        }
	        $_SESSION['drupal_csrf_token'] = $csrf_token['token'];
	        $_SESSION['drupal_cookies'] = serialize($cookieJar);
	        $_SESSION['drupal_uid'] = $user['user']['uid'];
	      }
	      $client->setDefaultOption('headers', array('X-CSRF-Token' => $_SESSION['drupal_csrf_token']));
	    }
	    return $conn[$drupal];
	}
	
	public static function drupal_get($drupal,$op,$args=array()) {
	    $client = self::drupal_connection($drupal);
   	    return $client->get($op.'.json?'.http_build_query($args))->send()->json();
	}

	public static function drupal_put($drupal,$op,$args=array()) {
	    $client = self::drupal_connection($drupal);
   	    return $client->put($op.'.json')->setBody(json_encode($args),'application/json')->send()->json();
	}

	public static function drupal_delete($drupal,$op,$args=array()) {
	    $client = self::drupal_connection($drupal);
   	    return $client->delete($op.'.json?'.http_build_query($args))->send()->json();
	}

	public static function drupal_post($drupal,$op,$args=array()) {
	    $client = self::drupal_connection($drupal);
   	    return $client->post($op.'.json')->setBody(json_encode($args),'application/json')->send()->json();
	}
}

Premium_Warehouse_DrupalCommerceCommon::$order_statuses = Premium_Warehouse_Items_OrdersCommon::get_status_array(array('transaction_type'=>1),true);

?>
