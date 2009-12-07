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
		if (isset($_REQUEST['warehouse_change_rental_status']) &&
			$_REQUEST['warehouse_change_rental_status']==$r['id']) {
			unset($_REQUEST['warehouse_change_rental_status']);
			$r['rental_item'] = $r['rental_item']?0:1;
			Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $r['id'], array('rental_item'=>$r['rental_item']));
		}
		if (isset($r['rental_item']) && $r['rental_item']) $ret = 'Yes';
		else $ret = 'No';
		return '<a '.Module::create_href(array('warehouse_change_rental_status'=>$r['id'])).'>'.Base_LangCommon::ts('Premium_Warehouse_Items_Location',$ret).'</a>';
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
			case 'browse_crits':	return $i->acl_check('browse location');
			case 'browse':	return $i->acl_check('browse location');
			case 'view':	return true;
			case 'clone':
			case 'add':
			case 'edit':	$ret = array('item_sku'=>false, 'warehouse'=>false);
							if (!Base_AclCommon::i_am_sa()) $ret['quantity'] = false;
							return $ret;
			case 'delete':	return false;
		}
		return false;
    }
	
	public static function get_item_quantity_in_warehouse($r, $warehouse=null, $rental=false) {
		$my_quantity = 0;
		$crits = array('item_sku'=>$r['id']);
		if ($warehouse!==null) $crits['warehouse'] = $warehouse;
//		if ($rental) $crits['rental_item']=1;
//		else $crits['rental_item']=array(0,'');
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
	
	public static function display_item_quantity_in_warehouse_and_total($r, $warehouse, $nolink=false, $enroute=null, $custom_label=null) {
		if ($r['item_type']>=2) return '---';
		if ($custom_label===null) $custom_label = array(
			'main'=>'Quantity on hand',
			'in_one'=>'%s',
			'in_all'=>'Total',
		);
		if (!$nolink)
			$tooltip = '<b>'.
				Base_LangCommon::ts('Premium_Warehouse_Items_Location',$custom_label['main'].':').
				'</b><HR>'.
				'<table border=0>';
				
		static $warehouses = array();
		if (empty($warehouses)) {
			$warehouses_records = Utils_RecordBrowserCommon::get_records('premium_warehouse', array(), array(), array('warehouse'=>'ASC'));
			foreach ($warehouses_records as $v)
				$warehouses[$v['id']] = $v['warehouse'];
		}
		$total = 0;
		if ($enroute===null) {
			$locations = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', array('item_sku'=>$r['id']), array(), array('quantity'=>'DESC'));
			$max_shown = 5;
			$quantities = array();
			foreach ($locations as $v) {
				$total += $v['quantity'];
				if ($nolink) {
					$quantities[$v['warehouse']] = $v['quantity'];
					continue;
				}
				if ($max_shown<0) continue;
				if ($max_shown==0) {
					$tooltip .= '<tr><td></td><td>...</td></tr>';
					if (isset($quantities[$warehouse])) continue;
					$v['warehouse'] = $warehouse;
					$v['quantity'] = self::get_item_quantity_in_warehouse($r, $warehouse);
					if ($v['quantity']==0) continue;
				}
				$max_shown--;
				$quantities[$v['warehouse']] = $v['quantity'];
				$warehouse_label = $warehouses[$v['warehouse']];
				if ($v['warehouse']==$warehouse) $warehouse_label = '<b>'.$warehouse_label.'</b>';
				if ($quantities[$v['warehouse']])
					$tooltip .= '<tr><td>'.
						Base_LangCommon::ts('Premium_Warehouse_Items_Location',$custom_label['in_one'], array($warehouse_label)).
						'</td><td bgcolor="#FFFFFF" WIDTH=50 style="text-align:right;">'.
						$quantities[$v['warehouse']].
						'</td></tr>';
				if ($max_shown<=0) {
					$tooltip .= '<tr><td></td><td>...</td></tr>';
					continue;
				}
			}
			if (!$nolink)
				$tooltip .= '<tr><td><b>'.
					Base_LangCommon::ts('Premium_Warehouse_Items_Location',$custom_label['in_all']).
					'</b></td><td bgcolor="#FFFFCC" style="text-align:right;"><b>'.
					$total.
					'</b></td></tr></table>';
		} else {
			arsort($enroute);
			$quantities = array();
			foreach ($enroute as $k=>$v) {
				if (!$v) continue;
				$total += $v;
				$quantities[$k] = $v;
				if ($nolink) continue;
				if ($k!=-1)
					$warehouse_label = $warehouses[$k];
				else
					$warehouse_label = Base_LangCommon::ts('Premium_Warehouse_Items_Location','--');
				if ($k==$warehouse) $warehouse_label = '<b>'.$warehouse_label.'</b>';
				$tooltip .= '<tr><td>'.
					Base_LangCommon::ts('Premium_Warehouse_Items_Location',$custom_label['in_one'], array($warehouse_label)).
					'</td><td bgcolor="#FFFFFF" WIDTH=50 style="text-align:right;">'.
					$quantities[$k].
					'</td></tr>';
			}
			if (!$nolink)
				$tooltip .= '<tr><td><b>'.
					Base_LangCommon::ts('Premium_Warehouse_Items_Location',$custom_label['in_all']).
					'</b></td><td bgcolor="#FFFFCC" style="text-align:right;"><b>'.
					$total.
					'</b></td></tr></table>';
		}
		if (!isset($quantities[$warehouse])) $quantities[$warehouse] = 0;
		
		if (!$warehouse) $ret = $total;
		else $ret = $quantities[$warehouse].' / '.$total;
		if (!$nolink)
			$ret = Utils_TooltipCommon::create($ret, $tooltip, false);
		return $ret;
	}
	
	public static function location_addon_parameters($record) {
		if ($record['item_type']==2 || $record['item_type']==3) return array('show'=>false);
		return array('show'=>true, 'label'=>'Items Locations');
	}

	public static function location_serial_addon_parameters($record) {
		if ($record['item_type']!=1) return array('show'=>false);
		return array('show'=>true, 'label'=>'Items Serials');
	}
}
?>
