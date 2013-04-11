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
	
	public static function manufacturer_crits() {
		return array('group'=>'manufacturer');
	}
	
	public static function employee_crits() {
		return array('(company_name'=>CRM_ContactsCommon::get_main_company(),'|related_companies'=>array(CRM_ContactsCommon::get_main_company()));
	}

    public static function display_item_name($v, $nolink=false) {
		if (!is_array($v)) $v = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v);
		$ret = Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items', 'Item Name', $v, $nolink);
		if (!$nolink) {
			$ret = Utils_TooltipCommon::create($ret, $v['description'],false);
		}
		return $ret;
	}
	
	public static function display_sku($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items', 'SKU', $r, $nolink);
	}
	
	public static function display_volume($r, $nolink) {
		if (!is_numeric($r['volume'])) return '--';
		return $r['volume'].' '.Variable::get('premium_warehouse_volume_units');
	}
	
	public static function display_weight($r, $nolink,$x) {
		if (!is_numeric($r[$x['id']])) return '--';
		return $r[$x['id']].' '.Variable::get('premium_warehouse_weight_units');
	}
	
	public static function display_gross_price($r, $nolink) {
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$ret = Utils_CurrencyFieldCommon::format(($price[0]*(100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate'])))/100, $price[1]);
		if (!$nolink) {
			$htmlinfo = array();
			$htmlinfo[__('Net Price')] = Utils_CurrencyFieldCommon::format($r['net_price']);
			$htmlinfo[__('Tax')] = Data_TaxRatesCommon::get_tax_name($r['tax_rate']);
			$htmlinfo[__('Tax Rate')] = Data_TaxRatesCommon::get_tax_rate($r['tax_rate']).'%';
			$htmlinfo[__('Tax Value')] = Utils_CurrencyFieldCommon::format(($price[0]*Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))/100, $price[1]);
			$htmlinfo[__('Gross Price')] = $ret;
			$ret = Utils_TooltipCommon::create($ret, Utils_TooltipCommon::format_info_tooltip($htmlinfo), false);
		}
		return $ret;
	}
	
	public static function display_serials() {}
	
	public static function display_quantity_sold($r, $nolink = false) {
        static $transactions_ids = null;
        if (ModuleManager::is_installed('Premium/Warehouse/Items/Orders') < 0)
            return '--'; // transactions module not installed

        if ($transactions_ids === null) { // cache transaction ids for browse mode - it will be called every row
            $transactions = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',
                array('transaction_type' => 1, 'status' => 20), array('id')); // sale transactions with status = delivered
            $transactions_ids = array_keys($transactions);
        }
        $crits = array('item_name' => $r['id'], 'transaction_id' => $transactions_ids);
        $details = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', $crits, array('quantity'));
        $qty = 0;
        foreach ($details as $det) {
            $qty += $det['quantity'];
        }
		return $qty;
	}

    public static function QFfield_quantity_sold($form, $field, $label, $mode, $default, $params, $rb_obj) {
        if ($mode == 'view') {
            $form->addElement('static', $field, $label, self::display_quantity_sold($rb_obj->record));
        }
    }
	
    public static function build_category_tree(&$opts, $root='', $prefix='', $count=0) {
		$cats = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_categories', array('parent_category'=>$root),array('category_name'),array('position'=>'ASC'));
		foreach($cats as $v) {
			$opts[$prefix.$v['id']] = str_pad('',$count*2,'* ').$v['category_name'];
			self::build_category_tree($opts, $v['id'], $prefix.$v['id'].'/', $count+1);
		}
    }

    public static function build_category_tree_flat(&$opts, $root='', $count=0) {
		$cats = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_categories', array('parent_category'=>$root),array('category_name'),array('position'=>'ASC'));
		foreach($cats as $v) {
			$opts[$v['id']] = str_pad('',$count*2,'* ').$v['category_name'];
			self::build_category_tree_flat($opts, $v['id'], $count+1);
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
		return trim($next);
    }
    
    public static function QFfield_gross_price(&$form, $field, $label, $mode, $default) {
		if ($mode=='edit' || $mode=='add') {
			$form->addElement('currency', $field, $label, array('id'=>$field));
			$r=Utils_RecordBrowser::$last_record;
			Premium_Warehouse_ItemsCommon::init_net_gross_js_calculation($form, 'tax_rate', 'net_price', 'gross_price');
			if (isset($r['net_price'])) {
				$net_price=Utils_CurrencyFieldCommon::get_values($r['net_price']);
				$form->setDefaults(array($field=>Utils_CurrencyFieldCommon::format_default($net_price[0]*(100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))/100, $net_price[1])));
			}
		} else {
			$form->addElement('static', $field, $label, self::display_gross_price(Utils_RecordBrowser::$last_record, false));
		}
    }
    
    public static function init_net_gross_js_calculation(&$form, $tax_rate_field, $net_field, $gross_field, $unit_field='', $discount_field='') {
		$tax_rates = Data_TaxRatesCommon::get_tax_details();
		$js = 'var tax_values=new Array();';
		foreach ($tax_rates as $k=>$v)
			$js .= 'tax_values['.$k.']='.$v['percentage'].';';
		eval_js($js);
		// TODO: call only once
		$decp = Utils_CurrencyFieldCommon::get_decimal_point();
		$switch_field = 'switch_net_gross_'.md5($form->getAttribute('name').$tax_rate_field.$net_field.$gross_field);
		load_js('modules/Premium/Warehouse/Items/net_gross.js');
        eval_js('Event.observe("'.$net_field.'","keyup",function(){update_gross("'.$decp.'","'.$net_field.'","'.$gross_field.'","'.$tax_rate_field.'","'.$switch_field.'");});');
        eval_js('Event.observe("'.$gross_field.'","keyup",function(){update_net("'.$decp.'","'.$net_field.'","'.$gross_field.'","'.$tax_rate_field.'","'.$switch_field.'");});');
		eval_js('Event.observe("'.$tax_rate_field.'","change",function(){if($("'.$switch_field.'").value==1)update_gross("'.$decp.'","'.$net_field.'","'.$gross_field.'","'.$tax_rate_field.'","'.$switch_field.'");else update_net("'.$decp.'","'.$net_field.'","'.$gross_field.'","'.$tax_rate_field.'","'.$switch_field.'");});');
		eval_js('Event.observe("__'.$gross_field.'__currency","change",function(){switch_currencies($("__'.$gross_field.'__currency").selectedIndex,"'.$net_field.'","'.$gross_field.'","'.$unit_field.'");});');
		eval_js('Event.observe("__'.$net_field.'__currency","change",function(){switch_currencies($("__'.$net_field.'__currency").selectedIndex,"'.$net_field.'","'.$gross_field.'","'.$unit_field.'");});');
        if($unit_field && $discount_field) {
            eval_js('Event.observe("'.$unit_field.'","keyup",function(){update_net_discount("'.$decp.'","'.$unit_field.'","'.$net_field.'","'.$gross_field.'","'.$discount_field.'");update_gross("'.$decp.'","'.$net_field.'","'.$gross_field.'","tax_rate")});');
            eval_js('Event.observe("'.$net_field.'","keyup",function(){update_unit("'.$decp.'","'.$unit_field.'","'.$net_field.'","'.$discount_field.'");});');
            eval_js('Event.observe("'.$gross_field.'","keyup",function(){update_unit("'.$decp.'","'.$unit_field.'","'.$net_field.'","'.$discount_field.'");});');
            eval_js('Event.observe("'.$discount_field.'","keyup",function(){update_net_discount("'.$decp.'","'.$unit_field.'","'.$net_field.'","'.$gross_field.'","'.$discount_field.'");update_gross("'.$decp.'","'.$net_field.'","'.$gross_field.'","tax_rate")});');
            eval_js('Event.observe("__'.$unit_field.'__currency","change",function(){switch_currencies($("__'.$unit_field.'__currency").selectedIndex,"'.$net_field.'","'.$gross_field.'");});');
            eval_js('Event.observe("'.$tax_rate_field.'","change",function(){if($("'.$switch_field.'").value==0) update_unit("'.$decp.'","'.$unit_field.'","'.$net_field.'","'.$discount_field.'","'.$switch_field.'");});');
        }
		$form->addElement('hidden', $switch_field, '', array('id'=>$switch_field));
		$form->setDefaults(array($switch_field=>1));
    }
    
    public static function QFfield_item_category(&$form, $field, $label, $mode, $default) {
		if ($mode=='edit' || $mode=='add') {
//			$opts = array();
//			self::build_category_tree($opts);
			$form->addElement('automulti', $field, $label, array('Premium_Warehouse_ItemsCommon', 'automulti_search'), array(), array('Premium_Warehouse_ItemsCommon', 'automulti_format'));
			$form->setDefaults(array($field=>$default));
		} else {
			$def = array();
			foreach ($default as $d) {
				$next = self::automulti_format($d);
				if($next)
					$def[] = $next;
			}
			$form->addElement('static', $field, $label, implode('<br/>',$def));
		}
    }
    
    public static function resolve_category($id, &$i, &$n) {
    	$i = $id.'/'.$i;
    	$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_categories', $id);
    	$n = $rec['category_name'].'/'.$n;
    	if(isset($rec['parent_category'])) self::resolve_category($rec['parent_category'],$i,$n);
    }
    
    public static function automulti_search($arg) {
	$arg = DB::Concat(DB::qstr('%'),DB::qstr($arg),DB::qstr('%'));
	$cats = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_categories', array('~"category_name'=>$arg),array('category_name','parent_category'),array('position'=>'ASC'),10);
	$ret = array();
    	foreach($cats as $c) {
    		self::resolve_category($c['parent_category'], $c['id'], $c['category_name']);
    		$name = ltrim($c['category_name'],'/');
    		$r = explode('/',$name);
    		if(count($r)>3) {
    			$name = $r[0].'/../'.$r[count($r)-2].'/'.$r[count($r)-1];
    		}
    		$ret[ltrim($c['id'],'/')] = $name;
    	}
    	return $ret;
    }
    
    public static function automulti_format($id) {
	$keys = explode('/',$id);
	if (!is_numeric($keys[0])) return; // TODO: it's just a fail-safe
	$next = self::get_category_name($keys[0]);
	if (count($keys)>1) {
		if (count($keys)>3) $next .= '/../';
		else $next .= '/';
		$next .= (count($keys)>2?self::get_category_name($keys[count($keys)-2]).'/':'').self::get_category_name($keys[count($keys)-1]);
	}
    	return $next;
    }

    public static function QFfield_category_parent(&$form, $field, $label, $mode, $default,$x,$y) {
		if ($mode=='edit' || $mode=='add') {
			$opts = array(''=>'---');
			self::build_category_tree_flat($opts);
			$form->addElement('select', $field, $label, $opts, array('id'=>$field));
			$form->registerRule('unique_category','callback','check_parent_category','Premium_Warehouse_ItemsCommon');
			if(isset($y->record['id']))
				$form->addRule($field,'You cannot choose the same category as edited one','unique_category',$y->record['id']);
			$form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label, $default!==''?Utils_RecordBrowserCommon::get_value('premium_warehouse_items_categories',$default,'category_name'):$default);
		}
    }
    
    public static function check_parent_category($v,$op) {
    		return $v!=$op;
    }

    public static function menu() {
		$m = array();
		if (Utils_RecordBrowserCommon::get_access('premium_warehouse_items','browse'))
			$m[_M('Items')] = array();
		if (Utils_RecordBrowserCommon::get_access('premium_warehouse_items_categories','browse'))
			$m[_M('Items Categories')] = array('recordset'=>'categories');
		if (empty($m)) return $m;
		$m['__submenu__'] = 1;
		return array(_M('Inventory')=>$m);
	}

	public static function generate_id($id) {
		if (is_array($id)) $id = $id['id'];
		return '#'.str_pad($id, 6, '0', STR_PAD_LEFT);
	}

	public static function applet_caption() {
		return __('Not sold items');
	}
	public static function applet_info() {
		return __('List of not sold items');
	}

	public static function applet_info_format($r){
		$arr = array(
			__('Item Name')=>$r['item_name'],
			__('Description')=>htmlspecialchars($r['description'])
		);
		$ret = array('notes'=>Utils_TooltipCommon::format_info_tooltip($arr));
		return $ret;
	}

	public static function applet_settings() {
		$opts = array(1209600=>__('2 weeks'), 2419200=>__('4 weeks'), 4838400=>__('2 months'), 10281600=>__('4 months'));
		return array_merge(Utils_RecordBrowserCommon::applet_settings(),
			array(
				array('name'=>'settings_header','label'=>__('Settings'),'type'=>'header'),
				array('name'=>'older','label'=>__('Items not sold for'),'type'=>'select','default'=>2419200,'rule'=>array(array('message'=>__('Field required'), 'type'=>'required')),'values'=>$opts)
				));
	}

	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse_items',
				__('Items'),
				$rid,
				$events,
				'item_name',
				$details
			);
	}
	
	public static function submit_item($values, $mode) {
		if(isset($values['gross_price']))unset($values['gross_price']);
		switch ($mode) {
			case 'cloned':
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$values['clone'],array('quantity_on_hand'=>0));
				return;
			case 'delete':
				return;
			case 'restore':
				return;
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
						$label = __('Add to my Trans.');
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
			case 'adding':
			case 'editing':
				load_js('modules/Premium/Warehouse/Items/field_control.js');
				eval_js('warehouse_items_hide_fields('.$values['item_type'].');');
				return $values;
			case 'edit':
				$values['sku'] = self::generate_id($values['id']);
			case 'add':
				if (!isset($values['reorder_point']) || !$values['reorder_point']) $values['reorder_point']=0;
				if (!isset($values['weight']) || !$values['weight']) $values['weight']=1;
				break;
			case 'added':
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$values['id'],array('sku'=>self::generate_id($values['id'])), false, null, true);
		}
		return $values;
	}
	
	public static function search_format($id) {
		if(!Utils_RecordBrowserCommon::get_access('premium_warehouse_items','browse')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items', $row['id']).__( 'Item in warehouse (attachment) #%d, %s %s', array($row['sku'], $row['item_type'], $row['item_name'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}

	public static function submit_position($values, $mode) {
		$recordset = 'premium_warehouse_items_categories';
		switch ($mode) {
			case 'add':
			case 'restore':
			    $values['position'] = Utils_RecordBrowserCommon::get_records_count($recordset,array('parent_category'=>$values['parent_category']));
			    break;
			case 'delete':
				if($values['parent_category']!=='')
				  	DB::Execute('UPDATE '.$recordset.'_data_1 SET f_position=f_position-1 WHERE f_position>%d and f_parent_category=%d',array($values['position'],$values['parent_category']));
				else
				  	DB::Execute('UPDATE '.$recordset.'_data_1 SET f_position=f_position-1 WHERE f_position>%d and f_parent_category is null',array($values['position']));
			    break;
			case 'edit':
				$old = Utils_RecordBrowserCommon::get_record($recordset,$values['id']);
				if($old['parent_category']!=$values['parent_category']) {
					if($old['parent_category']!=='')
					  	DB::Execute('UPDATE '.$recordset.'_data_1 SET f_position=f_position-1 WHERE f_position>%d and f_parent_category=%d',array($old['position'],$old['parent_category']));
					else
					  	DB::Execute('UPDATE '.$recordset.'_data_1 SET f_position=f_position-1 WHERE f_position>%d and f_parent_category is null',array($old['position']));
					$values['position'] = Utils_RecordBrowserCommon::get_records_count($recordset,array('parent_category'=>$values['parent_category']));
				}
				break;
		}
		return $values;
	}

}

?>
