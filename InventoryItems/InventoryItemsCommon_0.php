<?php
/**
 * Warehouse - Inventory Items
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_InventoryItemsCommon extends ModuleCommon {
    public static function get_inventoryitem($id) {
		return Utils_RecordBrowserCommon::get_record('premium_inventoryitems', $id);
    }

	public static function get_inventoryitems($crits=array(),$cols=array()) {
    		return Utils_RecordBrowserCommon::get_records('premium_inventoryitems', $crits, $cols);
	}

    public static function display_item_name($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_inventoryitems', 'Item Name', $v, $nolink);
	}

	public static function access_inventoryitems($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse inventoryitems');
			case 'view':	if($i->acl_check('view inventoryitems')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit inventoryitems');
			case 'delete':	return $i->acl_check('delete inventoryitems');
		}
		return false;
    }

    public static function menu() {
		return array('Warehouse'=>array('__submenu__'=>1,'Inventory Items'=>array()));
	}

	public static function applet_caption() {
		return 'Inventory Items';
	}
	public static function applet_info() {
		return 'List of Inventory Items';
	}

	public static function applet_info_format($r){
		return
			'Item Name: '.$r['item_name'].'<HR>'.
			'Quantity: '.$r['quantity'].'<br>'.
			'Description: '.$r['description'];
	}

	public static function watchdog_label($rid = null, $events = array()) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_inventoryitems',
				Base_LangCommon::ts('Premium_Warehouse_InventoryItems','Inv. Items'),
				$rid,
				$events,
				'item_name'
			);
	}
	
}
?>
