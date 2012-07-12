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

	public static function display_rental($r, $nolink = false){
		if (isset($_REQUEST['warehouse_change_rental_status']) &&
			$_REQUEST['warehouse_change_rental_status']==$r['id']) {
			unset($_REQUEST['warehouse_change_rental_status']);
			$r['rental_item'] = $r['rental_item']?0:1;
			Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $r['id'], array('rental_item'=>$r['rental_item']));
		}
		if (isset($r['rental_item']) && $r['rental_item']) $ret = 'Yes';
		else $ret = 'No';
		return '<a '.Module::create_href(array('warehouse_change_rental_status'=>$r['id'])).'>'._V($ret).'</a>';
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

    public static function display_quantity($r,$nolink=false) {
		$i = self::Instance();
		if(Utils_RecordBrowserCommon::get_access('premium_warehouse_location','browse'))
		    return $r['quantity'];
        $myrec = CRM_ContactsCommon::get_my_record();
		return DB::GetOne('SELECT SUM(id) FROM premium_warehouse_location_serial WHERE owner = %d AND location_id=%d',array($myrec['id'],$r['id']));
    }
	
	public static function get_item_quantity_in_warehouse($item_id, $warehouse=null, $rental=false) {
		$my_quantity = 0;
		$crits = array('item_sku'=>$item_id);
		if ($warehouse!==null) $crits['warehouse'] = $warehouse;
//		if ($rental) $crits['rental_item']=1;
//		else $crits['rental_item']=array(0,'');
		$i = self::Instance();
		if(Utils_RecordBrowserCommon::get_access('premium_warehouse_location','browse')) {
    		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', $crits, array('quantity'));
	    	foreach ($recs as $v) {
		    	$my_quantity += $v['quantity'];
    		}
        } else {
            $myrec = CRM_ContactsCommon::get_my_record();
    		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', $crits, array('id'));
            $l_ids = array();
            foreach($recs as $r)
                $l_ids[] = $r['id'];
            $my_quantity = DB::GetOne('SELECT SUM(id) FROM premium_warehouse_location_serial WHERE owner = %d AND location_id IN '.implode(',',$l_ids),array($myrec['id']));
        }
		return $my_quantity;
	}

	public static function display_item_quantity($r, $nolink=false) {
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		return self::display_item_quantity_in_warehouse_and_total($r, $my_warehouse, $nolink);
	}
	
	public static function QFfield_item_quantity(&$form, $field, $label, $mode, $default) {
		$form->addElement('static', $field, $label);
		$form->setDefaults(array($field=>'<div class="static_field">'.self::display_item_quantity(Utils_RecordBrowser::$last_record, false)));
	}
	
	public static function display_item_quantity_in_warehouse_and_total($r, $warehouse, $nolink=false, $enroute=null, $custom_label=null) {
		if ($r['item_type']==2 || $r['item_type']==3) return '---';
		if (!isset($r['id'])) return '0';
		if ($custom_label===null) $custom_label = array(
			'main'=>'Quantity on hand',
			'in_one'=>'%s',
			'in_all'=>'Total',
		);
		if (!$nolink)
			$tooltip = '<b>'.
				_V($custom_label['main'].':').
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
			$i = self::Instance();
			foreach ($locations as $v) {
/*        		if(!browse my location)) {
                    $myrec = CRM_ContactsCommon::get_my_record();
                    $v['quantity'] = DB::GetOne('SELECT SUM(id) FROM premium_warehouse_location_serial WHERE owner = %d AND location_id=%d',array($myrec['id'],$v['id']));
                }*/
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
					$v['quantity'] = self::get_item_quantity_in_warehouse($r['id'], $warehouse);
					if ($v['quantity']==0) continue;
				}
				$max_shown--;
				$quantities[$v['warehouse']] = $v['quantity'];
				$warehouse_label = isset($warehouses[$v['warehouse']])?$warehouses[$v['warehouse']]:'---';
				if ($v['warehouse']==$warehouse) $warehouse_label = '<b>'.$warehouse_label.'</b>';
				if ($quantities[$v['warehouse']])
					$tooltip .= '<tr><td>'.
						_V($custom_label['in_one'], array($warehouse_label)).
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
					_V($custom_label['in_all']).
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
					$warehouse_label = '---';
				if ($k==$warehouse) $warehouse_label = '<b>'.$warehouse_label.'</b>';
				$tooltip .= '<tr><td>'.
					_V($custom_label['in_one'], array($warehouse_label)).
					'</td><td bgcolor="#FFFFFF" WIDTH=50 style="text-align:right;">'.
					$quantities[$k].
					'</td></tr>';
			}
			if (!$nolink)
				$tooltip .= '<tr><td><b>'.
					_V($custom_label['in_all']).
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
		if ($record['item_type']=='0' || $record['item_type']=='1')
		    return array('show'=>true, 'label'=>__('Items Locations'));
		 return array('show'=>false);
	}

	public static function location_serial_addon_parameters($record) {
		if ($record['item_type']!=1) return array('show'=>false);
		return array('show'=>true, 'label'=>__('Items Serials'));
	}

	public static function company_items_addon_parameters($record) {
		if (!isset($record['id'])) return array();
	    $items = DB::GetOne('SELECT count(location_id) FROM premium_warehouse_location_serial WHERE owner=%s', array('C:'.$record['id']));
		if (!$items) return array('show'=>false);
		return array('show'=>true, 'label'=>__('Warehouse Items'));
	}
}

?>
