<?php
/**
 * Warehouse - Location
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_LocationCommon extends ModuleCommon {
    public static function get_location($id) {
		return Utils_RecordBrowserCommon::get_record('premium_warehouse_location', $id);
    }

	public static function get_locations($crits=array(),$cols=array()) {
    		return Utils_RecordBrowserCommon::get_records('premium_warehouse_location', $crits, $cols);
	}

	public static function items_crits() {
		return array();
	}

	public static function transactions_crits() {
		return array();
	}

    public static function display_warehouse($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'Warehouse', $v['warehouse'], $nolink);
	}

	public static function access_location($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse location');
			case 'view':	if($i->acl_check('view location')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit location');
			case 'delete':	return $i->acl_check('delete location');
			case 'fields':	return array();
		}
		return false;
    }
	
	public static function display_item_quantity($r, $nolink) {
		$my_quantity = 0;
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		if (!$my_warehouse) return $r['quantity'];
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', array('item_sku'=>$r['id'], 'warehouse'=>$my_warehouse), array('quantity'));
		foreach ($recs as $v) {
			$my_quantity += $v['quantity'];
		}
		return $my_quantity.' / '.$r['quantity'];
	}
	
	public static function location_addon_parameters($record) {
		if ($record['item_type']==2) return array('show'=>false);
		return array('show'=>true, 'label'=>'Items Locations');
	}
}
?>
