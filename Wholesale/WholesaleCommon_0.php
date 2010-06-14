<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-wholesale
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_WholesaleCommon extends ModuleCommon {
	public static $plugin_path = 'modules/Premium/Warehouse/Wholesale/plugins/';
	public static $current_plugin = '';
	
	public static function get_plugin($arg) {
		static $plugins = array();
		if (isset($plugins[$arg])) return $plugins[$arg];

		static $interface_included = false;
		if (!$interface_included)
			require_once('modules/Premium/Warehouse/Wholesale/interface.php');

		if (is_numeric($arg)) {
			$id = $arg;
			$filename = DB::GetOne('SELECT filename FROM premium_warehouse_wholesale_plugin WHERE id=%d', array($arg));
		} else {
			$filename = $arg;
			$id = DB::GetOne('SELECT id FROM premium_warehouse_wholesale_plugin WHERE filename=%s', array($arg));
		}
		if (is_file(self::$plugin_path.basename($filename).'.php')) {
			require_once(self::$plugin_path.basename($filename).'.php');
			$class = 'Premium_Warehouse_Wholesale__Plugin_'.$filename;
			if (!class_exists($class))
				trigger_error('Warning: invalid plugin in file '.$filename.'.php<br>', E_USER_ERROR);
			return $plugins[$id] = $plugins[$filename] = new $class();
		}
		return null;
	}
	
	public static function scan_for_plugins() {
		$dir = scandir(self::$plugin_path);
		DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=2');
		foreach ($dir as $file) {
			if ($file=='..' || $file=='.' || !preg_match('/\.php$/i',$file)) continue;
			$filename = basename($file, '.php');
			$plugin = self::get_plugin($filename);
			if ($plugin) {
				$name = $plugin->get_name();
				$id = DB::GetOne('SELECT id FROM premium_warehouse_wholesale_plugin WHERE filename=%s', array($filename));
				if ($id===false || $id==null) {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_plugin (name, filename, active) VALUES (%s, %s, 1)', array($name, $filename));
				} else {
					DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=1, name=%s WHERE id=%d', array($name, $id));
				}
			}
		}
		DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=0 WHERE active=2');
		return false;
	}

    public static function display_distributor($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_distributor', 'Name', $v, $nolink);
	}
	
    public static function display_distributor_qty($v, $nolink=false) {
    	$row = DB::GetRow('SELECT SUM(quantity) AS qty, MAX(quantity_info) AS qty_info FROM premium_warehouse_wholesale_items WHERE item_id=%d', array($v['id']));
    	if (!$row['qty'] && !$row['qty_info']) return 0;
		return '<span '.Utils_TooltipCommon::ajax_open_tag_attrs(array('Premium_Warehouse_WholesaleCommon','dist_qty_tooltip'), array($v['id']),500).'>'.$row['qty'].($row['qty_info']?'*':'').'</span>';
	}
	
	public static function dist_qty_tooltip($item_id) {
    	$ret = DB::Execute('SELECT * FROM premium_warehouse_wholesale_items WHERE item_id=%d ORDER BY quantity', array($item_id));
		$theme = Base_ThemeCommon::init_smarty();
		$theme->assign('header', array(
			'distributor'=>Base_LangCommon::ts('Premium_Warehouse_Wholesale','Distributor'),
			'quantity'=>Base_LangCommon::ts('Premium_Warehouse_Wholesale','Quantity'),
			'quantity_info'=>Base_LangCommon::ts('Premium_Warehouse_Wholesale','Qty Info'),
			'price'=>Base_LangCommon::ts('Premium_Warehouse_Wholesale','Price')
			));    	
		$distros = array();
    	while ($row = $ret->FetchRow()) {
			$dist = Utils_RecordBrowserCommon::get_record('premium_warehouse_distributor', $row['distributor_id']);
    		$distros[] = array(
    			'distributor_name'=>$dist['name'],
    			'quantity'=>$row['quantity'],
    			'quantity_info'=>$row['quantity_info'],
				'price'=>Utils_CurrencyFieldCommon::format($row['price'],$row['price_currency'])
    		);
    	}
		$theme->assign('distros', $distros);
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Wholesale','dist_qty_tooltip');
	}
	
	public static function access_distributor($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'browse_crits':	return $i->acl_check('browse distributors');
			case 'browse':	return true;
			case 'view':	return $i->acl_check('view distributors');
			case 'clone':
			case 'add':
			case 'edit':	if(!$i->acl_check('edit distributors')) return false;
							return array('last_update'=>false);
			case 'delete':	return $i->acl_check('delete distributors');
		}
		return false;
    }
    
    private static function get_processing_message_js($str, $type=0, $hide_details=false) {
    	$class = 'notification';
    	if ($type==1) $class = 'success';
    	if ($type==2) $class = 'error';
    	$det_disp = ($hide_details?'none':'block');
    	return 'wholesale_processing_message("'.$str.'","'.$det_disp.'","'.$class.'");';
    }
    
    /**
     * Displays a message in file scan legihtbox
     * Use this method in plugnis to inform the user or progress or errors encountered
     * Notice: use this method during downloading process, for file scan process use file_scan_message() instead 
     * 
     * @param string message text (must be already translated)
     * @param integer type of the message, 0 - notification, 1 - success announcement, 2 - error
     * @param bool true to hide progress details (numbers), false to show them
     */
    public static function file_download_message($str, $type=0, $hide_details=false) {
    	eval_js(self::get_processing_message_js($str, $type, $hide_details));
    }

    /**
     * Displays a message in file scan legihtbox
     * Use this method in plugnis to inform the user or progress or errors encountered
     * Notice: use this method during file scan process, for download process use file_download_message() instead 
     * 
     * @param string message text (must be already translated)
     * @param integer type of the message, 0 - notification, 1 - success announcement, 2 - error
     * @param bool true to hide progress details (numbers), false to show them
     */
    public static function file_scan_message($str, $type=0, $hide_details=false) {
    	print('<script>parent.'.self::get_processing_message_js($str, $type, $hide_details).'</script>');
    	flush();
    	@ob_flush();
    }
    
	public static function scan_file_processing($data) {
		eval_js('wholesale_leightbox_switch_to_info();');
	    $time = time();	    
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');
		$filename = $dir.'current_scan_'.$time.'.tmp';
		@copy($data, $filename);
		@unlink($data);
		eval_js('wholesale_create_iframe('.Utils_RecordBrowser::$last_record['id'].',"'.$filename.'");');
    }
    
    public static function scan_file_leightbox($rb) {
    	$form = $rb->init_module('Utils_FileUpload');
		$form->add_upload_element();
		$form->addElement('button',null,$rb->t('Upload'),$form->get_submit_form_href());
		ob_start();
		$rb->display_module($form, array(array('Premium_Warehouse_WholesaleCommon','scan_file_processing')));
    	$form_html = ob_get_clean();
    	
		$theme = Base_ThemeCommon::init_smarty();
		$fields = array(
			'total'=>'Items in file:',
			'scanned'=>'Items scanned:',
			'available'=>'Items available:',
			'item_exist'=>'Items found in the system:',
			'link_exist'=>'Items scanned in the past:',
			'new_items_added'=>'New items:',
			'new_categories_added'=>'New categories:',
			'unknown'=>'Unknown'
		);
		foreach ($fields as $k=>$v) 
			$theme->assign($k, Base_LangCommon::ts('Premium_Warehouse_Wholesale', $v));
		
		load_js('modules/Premium/Warehouse/Wholesale/scan_file_progress_reporting.js');
		load_js('modules/Premium/Warehouse/Wholesale/process_file.js');

		ob_start();
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Wholesale','scan_status');
		$html = ob_get_clean();

		Libs_LeightboxCommon::display('wholesale_scan_file','<div id="wholesale_scan_file_progress" style="display:none;">'.$html.'</div><div id="wholesale_scan_file_form">'.$form_html.'</div>',Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scan a file'));
    	
		Base_ActionBarCommon::add('folder', 'File scan', 'class="lbOn" rel="wholesale_scan_file" onmouseup="wholesale_leightbox_switch_to_form();"');
    }
    
    public static function update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items_added, $new_categories_added) {
		static $time = 0;
		$new_time = microtime(true);
		if ($new_time-$time>1.5 || $total==$scanned) {
			$time = $new_time;
			if ($total===null) $total='"'.Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unknown').'"';
			echo('<script>parent.update_wholesale_scan_status('.$total.','.$scanned.','.$available.','.$item_exist.','.$link_exist.','.$new_items_added.','.$new_categories_added.');</script>');
			flush();
			@ob_flush();
		}
    }

	public static function auto_update($dist) {
		$plugin = self::get_plugin($dist['plugin']);
		$params = $plugin->get_parameters();
		$i = 1;
		foreach ($params as $k=>$v) {
			$params[$k] = $dist['param'.$i];
			$i++;
		}
		$filename = $plugin->download_file($params, $dist);

		eval_js('leightbox_activate(\'wholesale_scan_file\');');

		if ($filename!==false) self::scan_file_processing($filename);
		return false;
	}
	
	public static function submit_distributor($values, $mode) {
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
			case 'view':
				if (isset($plugin)) {
					self::$current_plugin = $plugin;
					if ($plugin->is_auto_download()) {
						if (isset($_REQUEST['wholesale_module_auto_update']) && $_REQUEST['wholesale_module_auto_update']=$values['id'])
							self::auto_update($values);
						Base_ActionBarCommon::add('search','Auto-update', Module::create_href(array('wholesale_module_auto_update'=>$values['id'])));
					}
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
		return $values;
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
							'$("_param'.$i.'__label").innerHTML="'.Base_LangCommon::ts('Premium_Warehouse_Wholesale',$k).'";'.
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

	public static function QFfield_plugin(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
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
			$opts = $opts+DB::GetAssoc('SELECT id, name FROM premium_warehouse_wholesale_plugin WHERE active=1'.$where,$vals);
			load_js('modules/Premium/Warehouse/Wholesale/adjust_parameters.js');
			eval_js('Event.observe("plugin","change",adjust_parameters)');
			$form->addElement('select', $field, $label, $opts, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			self::scan_file_leightbox($rb_obj);
			$form->addElement('static', $field, $label, DB::GetOne('SELECT name FROM premium_warehouse_wholesale_plugin WHERE id=%d', array($default)));
		}
	}

	public function item_match_autocomplete($str) {
		$ret = '<ul>';
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array('(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($str),DB::qstr('%')), '|~"sku'=>DB::Concat(DB::qstr('%'),DB::qstr($str),DB::qstr('%'))), array(), array('item_name'=>'ASC'), 10);
		foreach ($items as $k=>$v) {
			$ret .= '<li>';
			$ret .= '<span style="display:none;">'.$v['sku'].'</span>';
			$ret .= '<span class="informal">'.$v['sku'].': '.$v['item_name'].'</span>';
			$ret .= '</li>';
		}
		$ret .= '</ul>';
		return $ret;
	}
	
	public static function add_dest_qty_info($r, $str) {
		static $calculated = array();
		if (isset($calculated[$r['id']])) return $str;
		$calculated[$r['id']] = true;
		$d_qty = DB::GetAll('SELECT * FROM premium_warehouse_wholesale_items WHERE item_id=%d AND (quantity!=0 OR quantity_info!=%s)', array($r['id'], ''));
		if (empty($d_qty)) return $str;
		$tip = '<hr><table border=0 width="100%">';
		foreach ($d_qty as $v) {
			$dist_name = Utils_RecordBrowserCommon::get_value('premium_warehouse_distributor', $v['distributor_id'], 'name');
			$tip .= '<tr><td>'.$dist_name.'</td>'.
					'<td bgcolor="#FFFFFF" WIDTH=50 style="text-align:right;">'.
					$v['quantity'].($v['quantity_info']?' ('.$v['quantity_info'].')':'').
					'</td></tr>';
		}
		$str = preg_replace('/(tip=\".*?)(\")/', '$1'.htmlspecialchars($tip).'$2', $str);
		return '* '.$str;
	}
	
	public static function display_item_quantity($r, $nolink=false) {
		$res = Premium_Warehouse_Items_LocationCommon::display_item_quantity($r, $nolink);
		return self::add_dest_qty_info($r, $res);
	}
	
	public static function display_available_qty($r, $nolink=false) {
		$res = Premium_Warehouse_Items_OrdersCommon::display_available_qty($r, $nolink);
		return self::add_dest_qty_info($r, $res);
	}
	
    public static function menu() {
		return array('Warehouse'=>array('__submenu__'=>1,'Distributors'=>array()));
	}

	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse_distributor',
				Base_LangCommon::ts('Premium_Warehouse_Wholesale','Distributor'),
				$rid,
				$events,
				'name',
				$details
			);
	}
	
	public static function search_format($id) {
		if(Acl::check('Premium_Warehouse','browse distributors')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse_distributor',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_distributor', $row['id']).Base_LangCommon::ts('Premium_Warehouse_Wholesale', 'Distributor (attachment) #%d, %s', array($row['id'], $row['name'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}
	
	public static function access_distributor_categories($action, $param=null){
		$i = self::Instance();
		switch ($action) {
			case 'browse_crits':	return $i->acl_check('browse distributors');
			case 'browse':	return true;
			case 'view':	return $i->acl_check('view distributors');
			case 'clone':
			case 'add':	return false;
			case 'edit':	if(!$i->acl_check('edit distributors')) return false;
							return array('last_update'=>false);
			case 'delete':	return $i->acl_check('delete distributors');
		}
		return false;
    }
	public static function QFfield_category_name(&$form, $field, $label, $mode, $default) {
		$form->addElement('text', $field, $label)->freeze();
		$form->setDefaults(array($field=>$default));
	}

	public static function QFfield_distributor_name(&$form, $field, $label, $mode, $default) {
		$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_distributor', $default);
		$form->addElement('select', $field, $label, array($default=>$rec['name']))->freeze();
		$form->setDefaults(array($field=>$default));
	}
	
	public static function display_epesi_cat_name($v, $nolink=false) {
		$ret = array();
		foreach($v['epesi_category'] as $c) {
			$cc = explode('/',$c);
			$ret2 = array();
			foreach($cc as $ccc) {
				$cat = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_categories',$ccc);
				$ret2[] = $cat['category_name'];
			}
			$ret[] = implode(' / ',$ret2);
		}
		return implode(', ',$ret);
	}
	
	public static function cron() {
		$dists = Utils_RecordBrowserCommon::get_records('premium_warehouse_distributor',array('<last_update'=>date('Y-m-d 8:00:00',time()-3600*23)));
		$ret = '';
		foreach($dists as $dist) {
			$plugin = self::get_plugin($dist['plugin']);
			$params = $plugin->get_parameters();
			$i = 1;
			foreach ($params as $k=>$v) {
				$params[$k] = $dist['param'.$i];
				$i++;
			}
			ob_start();
			$filename = @$plugin->download_file($params, $dist);
			if(!$filename) continue;
			$res = @$plugin->update_from_file($filename, $dist);
			@unlink($filename);
			ob_end_clean();
			if ($res===true) { 
				$ret .= 'updated '.$dist['name'].'<br>';
				$time = time();
				Utils_RecordBrowserCommon::update_record('premium_warehouse_distributor', $dist['id'], array('last_update'=>$time));
			}
		}
    	$r2 = DB::Execute('SELECT id,upc,manufacturer,manufacturer_part_number FROM premium_warehouse_wholesale_items WHERE 3rdp is null OR 3rdp=\'\'');
    	while($row = $r2->FetchRow()) {
    	    $r3 = Premium_Warehouse_eCommerceCommon::check_3rd_party_item_data(isset($row['upc'])?$row['upc']:null,isset($row['manufacturer'])?$row['manufacturer']:null,isset($row['manufacturer_part_number'])?$row['manufacturer_part_number']:null);
    	    $val = array();
            if(!$r3)
                $val[] = '<i>no data available</i>';
            foreach($r3 as $name=>$langs) {
                $val[] = '<b>'.$name.'</b> - <i>'.implode(', ',$langs).'</i>';
            }
            DB::Execute('UPDATE premium_warehouse_wholesale_items SET 3rdp=%s WHERE id=%d',array(implode('<br/>',$val),$row['id']));
    	}
		return $ret;
	}
}
?>
