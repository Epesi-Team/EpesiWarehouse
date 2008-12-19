<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Location
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-location
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

	public function display_rental($r, $nolink = false){
		if (isset($r['rental_item']) && $r['rental_item']) $ret = 'Yes';
		else $ret = 'No';
		return Base_LangCommon::ts('Premium_Warehouse_Items_Location',$ret);
		//Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $r['id'], array('rental_item'=>$state?1:0));
		//return false;
	}

    public static function display_warehouse($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'Warehouse', $v['warehouse'], $nolink);
	}

    public static function mark_used($r) {
    	if ($r) return '* ';
    	else return '';
    }
    public static function display_serial($r, $nolink=false) {
		return self::mark_used($r['used']).$r['serial'];
	}

	public static function access_location($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse location');
			case 'view':	return true;
			case 'edit':	return true;
			case 'delete':	return false;
			case 'fields':	return array('item_sku'=>'read-only', 'quantity'=>'read-only', 'warehouse'=>'read-only');
		}
		return false;
    }
	
	public static function get_item_quantity_in_warehouse($r, $warehouse=null, $rental=false) {
		$my_quantity = 0;
		$crits = array('item_sku'=>$r['id']);
		if ($warehouse!==null) $crits['warehouse'] = $warehouse;
		if ($rental) $crits['rental_item']=1;
		else $crits['rental_item']=array(0,'');
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', $crits, array('quantity'));
		foreach ($recs as $v) {
			$my_quantity += $v['quantity'];
		}
		return $my_quantity;
	}
	public static function display_item_quantity($r, $nolink) {
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		return self::display_item_quantity_in_warehouse_and_total($r, $my_warehouse, $nolink);
	}
	
	public static function display_item_quantity_in_warehouse_and_total($r, $warehouse, $nolink=false, $custom_qty=null, $custom_label=null) {
		if ($r['item_type']>=2) return '---';
		if (!$warehouse) return $r['quantity_on_hand'];
		if ($custom_qty===null) $custom_qty = self::get_item_quantity_in_warehouse($r, $warehouse);
		$ret = $custom_qty.' / '.$r['quantity_on_hand'];
		if ($custom_label===null) $custom_label = array(
			'main'=>'Quantity on hand',
			'in_one'=>'In <b>%s</b> warehouse',
			'in_all'=>'In all warehouses',
		);
		if (!$nolink) $ret = Utils_TooltipCommon::create($ret, 
			'<b>'.
			Base_LangCommon::ts('Premium_Warehouse_Items_Location',$custom_label['main'].':').
			'</b>'.
			'<table border=0><tr><td style="width:5px;" /><td nowrap="1">'.
			Base_LangCommon::ts('Premium_Warehouse_Items_Location',$custom_label['in_one'], array(Utils_RecordBrowserCommon::get_value('premium_warehouse',$warehouse,'warehouse'))).
			'</td><td style="text-align:right;">'.
			$custom_qty.
			'</td></tr><tr><td style="width:5px;" /><td>'.
			Base_LangCommon::ts('Premium_Warehouse_Items_Location',$custom_label['in_all']).
			'</td><td style="text-align:right;">'.
			$r['quantity_on_hand'].
			'</td></tr></table>'
			,false);
		return $ret;
	}
	
	public static function location_addon_parameters($record) {
		if ($record['item_type']==2 || $record['item_type']==3) return array('show'=>false);
		return array('show'=>true, 'label'=>'Items Locations');
	}
}
?>
