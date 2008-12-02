<?php
/**
 * Warehouse - Items
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.9
 * @package premium-warehouse
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
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items', 'Item Name', $v, $nolink);
	}
	
	public static function display_sku($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items', 'SKU', $r, $nolink);
	}
	
	public static function display_gross_price($r, $nolink) {
		return Utils_CurrencyFieldCommon::format(($r['net_price']*(100+$r['tax_rate']))/100);
	}
	
	public static function display_quantity_sold($r, $nolink) {
		return -$r['quantity_on_hand'];
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

    public static function menu() {
		return array('Warehouse'=>array('__submenu__'=>1,'Items'=>array()));
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
			'Quantity: '.$r['quantity_on_hand'].'<br>'.
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
							'item_sku'=>$values['id'],
							'quantity'=>1,
							'item_name'=>Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $values['id'], 'item_name'),
							'net_price'=>Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $values['id'], 'net_price'),
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
		if($this->acl_check('browse items')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items', $row['id']).Base_LangCommon::ts('Premium_Warehouse_Items', 'Item in warehouse (attachment) #%d, %s %s', array($row['sku'], $row['item_type'], $row['item_name'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}
}
?>
