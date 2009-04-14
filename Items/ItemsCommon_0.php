<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Items
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_ItemsCommon extends ModuleCommon {
    public static function get_item($id) {
		return Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $id);
    }

	public static function get_items($crits=array(),$cols=array()) {
    		return Utils_RecordBrowserCommon::get_records('premium_warehouse_items', $crits, $cols);
	}

	public static function vendors_crits() {
		return array('group'=>'vendor');
	}
	
	public static function employee_crits() {
		return array('group'=>'office');
	}

    public static function display_item_name($v, $nolink=false) {
		$ret = Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items', 'Item Name', $v, $nolink);
		if (!$nolink) {
			$ret = Utils_TooltipCommon::create($ret, $v['description'],false);
		}
		return $ret;
	}
	
	public static function display_sku($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items', 'SKU', $r, $nolink);
	}
	
	public static function display_gross_price($r, $nolink) {
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		return Utils_CurrencyFieldCommon::format(($price[0]*(100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate'])))/100, $price[1]);
	}
	
	public static function display_quantity_sold($r, $nolink) {
		return '--';
	}
	
	public static function access_items($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse items');
			case 'view':	if($i->acl_check('view items')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit items');
			case 'delete':	return $i->acl_check('delete items');
			case 'fields':	return array('item_type'=>'read-only');
		}
		return false;
    }

	public static function access_items_categories($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse items');
			case 'view':	if($i->acl_check('view items')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit items');
			case 'delete':	return $i->acl_check('delete items');
			case 'fields':	return array('position'=>'hide','parent_category'=>'hide');
		}
		return false;
    }
    
    public static function build_category_tree(&$opts, $root='', $prefix='', $count=0) {
		$cats = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_categories', array('parent_category'=>$root));
		foreach($cats as $v) {
			$opts[$prefix.$v['id']] = str_pad('',$count*2,'* ').$v['category_name'];
			self::build_category_tree($opts, $v['id'], $prefix.$v['id'].'/', $count+1);
		}
    }
    
    public static function get_category_name($c_id) {
    	static $ecommerce_on;
    	static $lang;
		if (!isset($ecommerce_on)) {
			$ecommerce_on = ModuleManager::is_installed('Premium_Warehouse_eCommerce')!=-1;
			if ($ecommerce_on) $lang = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
		}
		$next = false;
		if ($ecommerce_on) {
			$id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_cat_descriptions', array('language','category'), array($lang,$c_id));
			if ($id) $next = Utils_RecordBrowserCommon::get_value('premium_ecommerce_cat_descriptions',$id,'display_name');
		}
		if (!$ecommerce_on || !$next) $next = Utils_RecordBrowserCommon::get_value('premium_warehouse_items_categories',$c_id,'category_name');
		return $next;
    }
    
    public static function QFfield_item_category(&$form, $field, $label, $mode, $default) {
		if ($mode=='edit' || $mode=='add') {
			$opts = array();
			self::build_category_tree($opts);
			$form->addElement('multiselect', $field, $label, $opts, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label, array('id'=>'status'));
			$def = array();
			foreach ($default as $d) {
				$keys = explode('/',$d);
				if (!is_numeric($keys[0])) return; // TODO: it's just a fail-safe
				$next = self::get_category_name($keys[0]);
				if (count($keys)>1) {
					if (count($keys)>2) $next .= '/.../';
					else $next .= '/';
					$next .= self::get_category_name($keys[count($keys)-1]);
				}
				$def[] = $next;
			}
			$form->setDefaults(array($field=>implode('<br/>',$def)));
		}
    }

    public static function menu() {
		return array('Warehouse'=>array('__submenu__'=>1,'Items'=>array(), 'Items: Categories'=>array('recordset'=>'categories')));
	}

	public static function generate_id($id) {
		if (is_array($id)) $id = $id['id'];
		return '#'.str_pad($id, 6, '0', STR_PAD_LEFT);
	}

	public static function applet_caption() {
		return 'Items';
	}
	public static function applet_info() {
		return 'List of Items';
	}

	public static function applet_info_format($r){
		return
			'Item Name: '.$r['item_name'].'<HR>'.
			'Description: '.$r['description'];
	}

	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse_items',
				Base_LangCommon::ts('Premium_Warehouse_Items','Items'),
				$rid,
				$events,
				'item_name',
				$details
			);
	}
	
	public static function submit_item($values, $mode) {
		switch ($mode) {
			case 'cloned':
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$values['clone'],array('quantity_on_hand'=>0));
				return;
			case 'delete':
				return;
			case 'restore':
				return;
			case 'add':
				return $values;
			case 'view':
				if (ModuleManager::is_installed('Premium_Warehouse_Items_Orders')>=0) {
					$my_trans = Base_User_SettingsCommon::get('Premium_Warehouse_Items_Orders','my_transaction');
					if ($my_trans) {
						$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $my_trans);
						if (!$trans) return;
						if (!Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',$trans)) {
							Base_User_SettingsCommon::save('Premium_Warehouse_Items_Orders','my_transaction','');
							return;
						}
						$icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Orders','deactivate.png');
						$label = Base_LangCommon::ts('Utils_Watchdog','Add to my Trans.');
						$defaults = array(
							'transaction_id'=>$my_trans,
							'quantity'=>1,
							'item_name'=>$values['id'],
							'net_price'=>Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $values['id'], 'last_'.($trans['transaction_type']==0?'purchase':'sale').'_price'),
							'tax_rate'=>Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $values['id'], 'tax_rate'),
							'single_pieces'=>Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $values['id'], 'item_type')==1
						);
						Base_ActionBarCommon::add($icon,$label,Utils_RecordBrowserCommon::create_new_record_href('premium_warehouse_items_orders_details', $defaults,'add_to_order'));
					}
				}
				return;
			case 'edit':
				$values['sku'] = self::generate_id($values['id']);
				break;
			case 'added':
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$values['id'],array('sku'=>self::generate_id($values['id'])), false, null, true);
		}
		return $values;
	}
	
	public static function search_format($id) {
		if(Acl::check('Premium_Warehouse_Items','browse items')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items', $row['id']).Base_LangCommon::ts('Premium_Warehouse_Items', 'Item in warehouse (attachment) #%d, %s %s', array($row['sku'], $row['item_type'], $row['item_name'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}
}
?>
