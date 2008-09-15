<?php
/**
 * Warehouse - Inventory Items Orders
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_InventoryItems_OrdersCommon extends ModuleCommon {
    public static function get_order($id) {
		return Utils_RecordBrowserCommon::get_record('premium_inventoryitems_orders', $id);
    }

	public static function get_orders($crits=array(),$cols=array()) {
    		return Utils_RecordBrowserCommon::get_records('premium_inventoryitems_orders', $crits, $cols);
	}

    public static function display_item_name($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_inventoryitems', 'Item Name', $v['item'], $nolink);
	}

	public static function display_order_id($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_inventoryitems_orders', 'order_id', $r, $nolink);	
	}

	public static function access_orders($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse orders');
			case 'view':	if($i->acl_check('view orders')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit orders');
			case 'delete':	return $i->acl_check('delete orders');
			case 'fields':	return array('item'=>'read-only');
		}
		return false;
    }

	public static function access_inventoryitems($action, $param){
		$i = Premium_Warehouse_InventoryItemsCommon::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse inventoryitems');
			case 'view':	if($i->acl_check('view inventoryitems')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit inventoryitems');
			case 'delete':	return $i->acl_check('delete inventoryitems');
			case 'fields':	return array('quantity'=>'read-only');
		}
		return false;
    }

    public static function menu() {
		return array('Warehouse'=>array('__submenu__'=>1,'Inventory Items: Orders'=>array()));
	}

	public static function applet_caption() {
		return 'Inventory Items Orders';
	}
	public static function applet_info() {
		return 'List of Orders on Inventory Items';
	}

	public static function applet_info_format($r){
		return
			'Item: '.$r['item'].'<HR>'.
			'Operation: '.$r['operation'].'<br>'.
			'Quantity: '.$r['quantity'].'<br>'.
			'Description: '.$r['description'];
	}

	public static function items_crits() {
		return array();
	}

	public static function generate_id($id) {
		if (is_array($id)) $id = $id['id'];
		return '#'.str_pad($id, 5, '0', STR_PAD_LEFT);
	}

	public static function watchdog_label($rid = null, $events = array()) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_inventoryitems_orders',
				Base_LangCommon::ts('Premium_Warehouse_InventoryItems_Orders','Orders'),
				$rid,
				$events,
				'item'
			);
	}
	
	public static function change_total_qty($order, $action=null) {
		$item_id = $order['item'];
		$new_qty = Utils_RecordBrowserCommon::get_value('premium_inventoryitems', $item_id, 'quantity');
		if ($action!=='add' && $action!=='restore') {
			$original_order = Utils_RecordBrowserCommon::get_record('premium_inventoryitems_orders', $order['id']);
			if ($original_order['operation']==1) $mult = -1;
			else $mult = 1;
			$new_qty = $new_qty-$original_order['quantity']*$mult;
		}
		if ($order['operation']==1) $mult = -1;
		else $mult = 1;
		if ($action!=='delete') $new_qty = $new_qty+$order['quantity']*$mult;
		Utils_RecordBrowserCommon::update_record('premium_inventoryitems', $item_id, array('quantity'=>$new_qty));
	}
	
	public static function submit_order($values, $mode) {
		switch ($mode) {
			case 'delete':
				self::change_total_qty($values, 'delete');
				return;
			case 'restore':
				self::change_total_qty($values, 'restore');
				return;
			case 'add':
				self::change_total_qty($values, 'add');
				return $values;
			case 'view':
				return;
			case 'edit':
				self::change_total_qty($values);
			case 'added':
				$values['order_id'] = self::generate_id($values['id']);
				Utils_RecordBrowserCommon::update_record('premium_inventoryitems_orders',$values['id'],array('order_id'=>$values['order_id']), false, null, true);
		}
		return $values;
	}
}
?>
