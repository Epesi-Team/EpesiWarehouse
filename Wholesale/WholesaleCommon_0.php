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
		require_once(self::$plugin_path.basename($filename).'.php');
		$class = 'Premium_Warehouse_Wholesale__Plugin_'.$filename;
		if (!class_exists($class))
			trigger_error('Warning: invalid plugin in file '.$filename.'.php<br>', E_USER_ERROR);
		return $plugins[$id] = $plugins[$filename] = new $class();
	}
	
	public static function scan_for_plugins() {
		$dir = scandir(self::$plugin_path);
		DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=2');
		foreach ($dir as $file) {
			if ($file=='..' || $file=='.' || $file=='interface.php') continue;
			$filename = basename($file, '.php');
			$plugin = self::get_plugin($filename);
			$name = $plugin->get_name();
			$id = DB::GetOne('SELECT id FROM premium_warehouse_wholesale_plugin WHERE filename=%s', array($filename));
			if ($id===false || $id==null) {
				DB::Execute('INSERT INTO premium_warehouse_wholesale_plugin (name, filename, active) VALUES (%s, %s, 1)', array($name, $filename));
			} else {
				DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=1, name=%s WHERE id=%d', array($name, $id));
			}
		}
		DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=0 WHERE active=2');
		return false;
	}

    public static function display_distributor($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_distributor', 'Name', $v, $nolink);
	}
	
	public static function access_distributor($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse distributors');
			case 'view':	if($i->acl_check('view distributors')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit distributors');
			case 'delete':	return $i->acl_check('delete distributors');
			case 'fields':	return array('last_update'=>'read-only');
		}
		return false;
    }
    
	public static function scan_file_processing($data) {
		self::$current_plugin->update_from_file($data);
    }
    
    public static function scan_file_leightbox($rb) {
    	$form = $rb->init_module('Utils_FileUpload');
		$form->add_upload_element();
		$form->addElement('button',null,$rb->t('Upload'),$form->get_submit_form_href());
		ob_start();
		$rb->display_module($form, array(array('Premium_Warehouse_WholesaleCommon','scan_file_processing')));
    	$form_html = ob_get_clean();
		Libs_LeightboxCommon::display('wholesale_scan_file',$form_html,Base_LangCommon::ts('Premium_Warehouse_Wholesale','Upload a file for a scan'));
    	
		Base_ActionBarCommon::add('folder', 'Scan new file', 'class="lbOn" rel="wholesale_scan_file"');
    }

	public static function submit_distributor($values, $mode, $recordset) {
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
					if ($plugin->is_auto_update()) {
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
	
	public static function auto_update($dist) {
		print('Starting...<br>');
		$plugin = self::get_plugin($dist['plugin']);
		$params = $plugin->get_parameters();
		$i = 1;
		foreach ($params as $k=>$v) {
			$params[$k] = $dist['param'.$i];
			$i++;
		}
		$plugin->auto_update($params);
		print('Update sucessful<br>');
		return false;
	}
	
	public static function get_change_parameters_labels_js($id) {
		$i = 1;
		$js = '';
		if (is_numeric($id)) {
			$plugin = self::get_plugin($id);
			$params = $plugin->get_parameters();
			foreach ($params as $k=>$v) {
				$js .= '$("_param'.$i.'__label").innerHTML="'.Base_LangCommon::ts('Premium_Warehouse_Wholesale',$k).'";';
				$js .= 'if($("param'.$i.'"))$("param'.$i.'").type="'.$v.'";';
				$js .= '$("_param'.$i.'__label").parentNode.parentNode.style.display="";';
				$i++;
			}
		}
		while($i<=6) {
			$js .= '$("_param'.$i.'__label").parentNode.parentNode.style.display="none";';
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
}
?>
