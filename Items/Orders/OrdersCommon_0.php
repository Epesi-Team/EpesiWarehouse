<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Items Orders
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-orders
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_OrdersCommon extends ModuleCommon {
	public static $trans = null;
	private static $new_status = null;
	public static $key = null;
	private static $status_blocked = null;
	private static $order_details_id = null;
	
	public static function user_settings() {
		return array(	
			__('Transaction')=>array(
				array('name'=>'my_transaction','label'=>'','type'=>'hidden','default'=>'')
			),
			__('Inventory')=>array(
				array('name'=>'display_qty','label'=>__('Quantity Display'),'type'=>'select','values'=>array(0=>__('Available'), 1=>__('On Hand'), 2=>__('Both')),'default'=>2),
				array('name'=>'filter_by_my_warehouse','label'=>__('Filter items by my warehouse'),'type'=>'select','values'=>array(0=>__('No'), 1=>__('Yes')),'default'=>1)
			));
	}
	public static function attachment_addon_access() {
		return Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',Utils_RecordBrowser::$last_record);
	}
	public static function order_serial_addon_access() {
		return Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',Utils_RecordBrowser::$last_record);
	}
    public static function get_order($id) {
		return Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $id);
    }

	public static function get_orders($crits=array(),$cols=array()) {
    		return Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', $crits, $cols);
	}

	public static function items_crits() {
		return array();
	}

	public static function transactions_crits() {
		return array();
	}
	
	public static function related_transactions_crits() {
		return array();
	}

	public static function company_crits(){
		return array('_no_company_option'=>true);
	}
	
	public static function display_related_transaction($r, $nolink) {
		if ($r['related']) {
			$related = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $r['related']);
			return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items_orders', $r['related'], $nolink).$related['transaction_id'].': '.self::display_transaction_type_order($related).Utils_RecordBrowserCommon::record_link_close_tag();
		} else {
            eval_js("warehouse_orders_hide_field('related', true);");
			return '';
		}
	}

    public static function display_company_name($v, $nolink=false, $desc=null) {
		if ($v['company']<=0) return $v[$desc['id']];
		return Utils_RecordBrowserCommon::record_link_open_tag('company', $v['company'], $nolink).$v[$desc['id']].Utils_RecordBrowserCommon::record_link_close_tag();
	}

    public static function display_first_name($v, $nolink=false, $desc=null) {
		if (!$v['contact']) return $v[$desc['id']];
		return Utils_RecordBrowserCommon::record_link_open_tag('contact', $v['contact'], $nolink).$v[$desc['id']].Utils_RecordBrowserCommon::record_link_close_tag();
	}

    public static function display_last_name($v, $nolink=false, $desc=null) {
		if (!$v['contact']) return $v[$desc['id']];
		return Utils_RecordBrowserCommon::record_link_open_tag('contact', $v['contact'], $nolink).$v[$desc['id']].Utils_RecordBrowserCommon::record_link_close_tag();
	}

    public static function display_warehouse($v, $nolink=false, $desc=null) {
		$ret = Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'warehouse', $v[$desc['id']], $nolink);
		if (isset($v['target_warehouse']) && $v['target_warehouse']
		    && $desc['id']!='target_warehouse') {
			if (!$ret) $ret = '?';
			$ret .= '&nbsp;-> '.Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'warehouse', $v['target_warehouse'], $nolink);
		}
		return $ret;
	}

    public static function display_item_name($v, $nolink=false) {
		if (!$v['item_name'] && isset($v['item_sku'])) $v['item_name'] = $v['item_sku'];
    	$r = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v['item_name']);
		$ret = 	Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items', $v['item_name'], $nolink).
				$r['sku'].': '.$r['item_name'].
				Utils_RecordBrowserCommon::record_link_close_tag();

		if (!$nolink) {
			$ret = Utils_TooltipCommon::create($ret, htmlspecialchars($r['description']),false);
		}
		return $ret;
	}
	
    public static function display_item_sku($v, $nolink=false) {
    	if (!$v['item_name'] && isset($v['item_sku'])) $v['item_name'] = $v['item_sku'];
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items', 'sku', $v['item_name'], $nolink);
	}
	
	/**
	 * Calculates total value and tax value for given transaction, including Shipment and Handling cost
	 * 
	 * @param array transaction record, result of get_record() expected
	 * @param string either 'total' or 'tax' - selection of desired value
	 * @return array array where keys are currency IDs and values are result numbers 
	 */
	public static function calculate_tax_and_total_value($r, $arg) {
		static $res=array();
		if (isset($_REQUEST['__location'])) $res = array();
		if (isset($res[$r['id']][$arg])) return $res[$r['id']][$arg];
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$r['id']));
		$res[$r['id']]['tax'] = array();
		$res[$r['id']]['total'] = array();
		foreach($recs as $rr){
			$price = Utils_CurrencyFieldCommon::get_values($rr['net_price']);
			$net_total = round($price[0],Utils_CurrencyFieldCommon::get_precission($price[1]))*$rr['quantity'];
			if ($r['tax_calculation']==0)
				$tax_value = round(Data_TaxRatesCommon::get_tax_rate($rr['tax_rate'])*$price[0]/100, Utils_CurrencyFieldCommon::get_precission($price[1]))*$rr['quantity'];
			else
				$tax_value = round(Data_TaxRatesCommon::get_tax_rate($rr['tax_rate'])*$price[0]*$rr['quantity']/100, Utils_CurrencyFieldCommon::get_precission($price[1]));
			if (!isset($res[$r['id']]['tax'][$price[1]]) && $tax_value)
				$res[$r['id']]['tax'][$price[1]] = 0;
			if (!isset($res[$r['id']]['total'][$price[1]]) && $net_total)
				$res[$r['id']]['total'][$price[1]] = 0;
			if ($tax_value) $res[$r['id']]['tax'][$price[1]] += $tax_value;
			if ($net_total) $res[$r['id']]['total'][$price[1]] += $net_total+$tax_value;
		}
		$r['shipment_cost'] = Utils_CurrencyFieldCommon::get_values($r['shipment_cost']);
		$r['handling_cost'] = Utils_CurrencyFieldCommon::get_values($r['handling_cost']);
		if (!isset($res[$r['id']]['total'][$r['shipment_cost'][1]]))
			$res[$r['id']]['total'][$r['shipment_cost'][1]] = 0;
		if (!isset($res[$r['id']]['total'][$r['handling_cost'][1]]))
			$res[$r['id']]['total'][$r['handling_cost'][1]] = 0;
		$res[$r['id']]['total'][$r['shipment_cost'][1]] += $r['shipment_cost'][0];
		$res[$r['id']]['total'][$r['handling_cost'][1]] += $r['handling_cost'][0];
		return $res[$r['id']][$arg];
	}
	
	public static function display_total_value($r, $nolink=false) {
		if ($r['transaction_type']==4 || $r['transaction_type']==2 || ($r['transaction_type']==3 && !$r['payment']))
			return '---';
		$ret = array();
		$vals = self::calculate_tax_and_total_value($r, 'total');
		$failsafe = false;
		foreach ($vals as $k=>$v) {
			if (!$failsafe) $failsafe = Utils_CurrencyFieldCommon::format($v, $k);
			if (!$v) continue;
			$ret[] = Utils_CurrencyFieldCommon::format($v, $k);
		}
		if (empty($ret)) return $failsafe;
		return implode('; ',$ret);
	}
	
	public static function display_tax_value($r, $nolink=false) {
		if ($r['transaction_type']==4 || $r['transaction_type']==2 || ($r['transaction_type']==3 && !$r['payment']))
			return '---';
		$ret = array();
		$vals = self::calculate_tax_and_total_value($r, 'tax');
		foreach ($vals as $k=>$v)
			$ret[] = Utils_CurrencyFieldCommon::format($v, $k);
		return implode('; ',$ret);
	}
	
	public static function calculate_weight_and_volume($r, $arg) {
		static $res=array();
		if (isset($_REQUEST['__location'])) $res = array();
		if (isset($res[$r['id']][$arg])) return $res[$r['id']][$arg];
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$r['id']));
		$res[$r['id']]['volume'] = 0;
		$res[$r['id']]['weight'] = 0;
		foreach($recs as $rr){
			$i = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $rr['item_name']);
			$res[$r['id']]['volume'] += $i['volume']*$rr['quantity'];
			$res[$r['id']]['weight'] += $i['weight']*$rr['quantity'];
		}
		return $res[$r['id']][$arg];
	}

	public static function display_weight($r, $nolink) {
		$val = self::calculate_weight_and_volume($r, 'weight');
		if (!is_numeric($val)) return '--';
		return $val.' '.Variable::get('premium_warehouse_weight_units');
	}
	
	public static function display_volume($r, $nolink) {
		$val = self::calculate_weight_and_volume($r, 'volume');
		if (!is_numeric($val)) return '--';
		return $val.' '.Variable::get('premium_warehouse_volume_units');
	}	

	public static function display_transaction_type_order($r, $nolink=false) {
		if (!isset($r['payment']) || !$r['payment']) {
			if ($r['transaction_type']==0) return __('Check-in');
			if ($r['transaction_type']==1) return __('Check-out');
		}
        if ($r['transaction_type'] == 1 && isset($r['status']) && $r['status'] == '1')
            return __('Sales Quote');
		return Utils_CommonDataCommon::get_value('Premium_Items_Orders_Trans_Types/'.$r['transaction_type'],true);	
	}

	public static function display_transaction_id($r, $nolink) {
		$ret = Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items_orders', 'transaction_id', $r, $nolink);
		if (!$nolink)	
			$ret = '<span '.Utils_TooltipCommon::ajax_open_tag_attrs(array('Premium_Warehouse_Items_OrdersCommon','item_list_tooltip'), array($r), 700).'>'.$ret.'</span>';
		return $ret;
	}
	
	public static function item_list_tooltip($r) {
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$r['id']));
		if (empty($items)) return __('There are no items saved in this transaction');
		$theme = Base_ThemeCommon::init_smarty();
		foreach ($items as $k=>$v) {
			$net = Utils_CurrencyFieldCommon::get_values($v['net_price']);
			$v['gross_price'] = round((100+Data_TaxRatesCommon::get_tax_rate($v['tax_rate']))*$net[0]/100, Utils_CurrencyFieldCommon::get_precission($net[1]));
			$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v['item_name']);
			$items[$k]['sku'] = $item['sku'];
			$items[$k]['item_name'] = $item['item_name'];
			$items[$k]['net_price'] = Utils_CurrencyFieldCommon::format($v['net_price']);
			$items[$k]['gross_price'] = Utils_CurrencyFieldCommon::format($v['gross_price'], $net[1]);
			$items[$k]['tax'] = Data_TaxRatesCommon::get_tax_name($v['tax_rate']);
		}
		$header = array(
			'sku'=>__('SKU'),
			'item_name'=>__('Item Name'),
			'quantity'=>__('Qty')
			);
		if ($r['payment']) {
			$header = $header+array(
				'net_price'=>__('Net Price'),
				'tax'=>__('Tax'),
				'gross_price'=>__('Gross Price')
			);
		}
		$theme->assign('header', $header);	
		$theme->assign('items', $items);
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Items_Orders','item_list_tooltip');
	}
	
	public static function display_transaction_type($r, $nolink) {
		return self::display_transaction_type_order(Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $r['transaction_id']));
	}
	
	public static function display_transaction_status($r, $nolink) {
		$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $r['transaction_id']);
		return self::display_status($trans);	
	}
	
	public static function display_transaction_date($r, $nolink) {
		return Base_RegionalSettingsCommon::time2reg(Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $r['transaction_id'], 'transaction_date'), false);	
	}
	
	public static function display_transaction_warehouse($r, $nolink) {
		$ret = Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'warehouse', Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $r['transaction_id'], 'warehouse'), $nolink);
		$t_w = Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $r['transaction_id'], 'target_warehouse');
		if ($t_w) {
			if (!$ret) $ret = '?';
			$ret .= '&nbsp;-> '.Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'warehouse', $t_w, $nolink);
		}
		return $ret;
	}
	
	public static function display_transaction_id_in_details($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items_orders', 'transaction_id', $r['transaction_id'], $nolink);	
	}

	public static function display_order_details_total($r, $nolink) {
	    if($r['net_price']===null) return '---';
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$ret = $r['quantity']*$price[0];
		return Utils_CurrencyFieldCommon::format($ret, $price[1]);
	}

	public static function display_order_details_tax_value($r, $nolink) {
	    if($r['net_price']===null) return '---';
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$tax = Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $r['transaction_id'], 'tax_calculation');
		if ($tax==0)
			$ret = round(Data_TaxRatesCommon::get_tax_rate($r['tax_rate'])*$price[0]/100, Utils_CurrencyFieldCommon::get_precission($price[1]))*$r['quantity'];
		else
			$ret = round(Data_TaxRatesCommon::get_tax_rate($r['tax_rate'])*$price[0]*$r['quantity']/100, Utils_CurrencyFieldCommon::get_precission($price[1]));
		return Utils_CurrencyFieldCommon::format($ret, $price[1]);
	}

	public static function display_order_details_gross_price($r, $nolink) {
	    if($r['net_price']===null) return '---';
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$tax = Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $r['transaction_id'], 'tax_calculation');
		if ($tax==0)
			$ret = round((100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))*$price[0]/100, Utils_CurrencyFieldCommon::get_precission($price[1]))*$r['quantity'];
		else
			$ret = round((100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))*$price[0]*$r['quantity']/100, Utils_CurrencyFieldCommon::get_precission($price[1]));
		return Utils_CurrencyFieldCommon::format($ret, $price[1]);
	}
	
	public static function display_gross_price($r, $nolink) {
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$ret = round((100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))*$price[0]/100, Utils_CurrencyFieldCommon::get_precission($price[1]));
		return Utils_CurrencyFieldCommon::format($ret, $price[1]);
	}
	
	public static function get_reserved_qty($item_id) {
		$trans = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('status'=>array(-2,-1,2,3,4,5), 'transaction_type'=>1), array('id', 'warehouse'));
		$trans = $trans+Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('status'=>array(2,3), 'transaction_type'=>4), array('id', 'warehouse', 'target_warehouse'));
		$qty = 0;
		$ids = array();
		foreach ($trans as $k=>$t) {
			if (!isset($t['warehouse'])) $trans[$k]['warehouse'] = $t['target_warehouse'];
			$ids[] = $t['id'];
		}
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$ids, 'item_name'=>$item_id), array('quantity','transaction_id'));
		$reserved_qty = array();
		foreach ($items as $i) {
			$warehouse = $trans[$i['transaction_id']]['warehouse'];
			if (!$warehouse) $warehouse = -1;
			if (!isset($reserved_qty[$warehouse])) $reserved_qty[$warehouse] = 0;
			$reserved_qty[$warehouse] += $i['quantity'];
			$qty+=$i['quantity'];
		}
		return array('per_warehouse'=>$reserved_qty, 'total'=>$qty);
	}
	
	public static function display_reserved_qty($r, $nolink) {
		$qty = self::get_reserved_qty($r['id']);
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		$ret = Premium_Warehouse_Items_LocationCommon::display_item_quantity_in_warehouse_and_total($r,$my_warehouse,$nolink,$qty['per_warehouse'],array('main'=>__('Reserved Qty'), 'in_one'=>_M('In %s', array('%s')), 'in_all'=>__('Total')));
		return $ret;
	}
	
	public static function display_available_qty($r, $nolink) {
		$qty = self::get_reserved_qty($r['id']);
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse');
		$warehouses[-1] = array('id'=>-1);
		$i = Premium_Warehouse_Items_LocationCommon::Instance();
		$myrec = CRM_ContactsCommon::get_my_record();
		foreach ($warehouses as $w) {
			$l_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('warehouse','item_sku'), array($w['id'], $r['id']));
			if (isset($qty['per_warehouse'][$w['id']])) $minus = $qty['per_warehouse'][$w['id']];
			else $minus = 0;  
			if($l_id) {
			    if(Utils_RecordBrowserCommon::get_access('premium_warehouse_location','browse'))
        			$qty['per_warehouse'][$w['id']] = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $l_id, 'quantity') - $minus;
//        	    elseif(browse my location)
//        			$qty['per_warehouse'][$w['id']] = DB::GetOne('SELECT SUM(id) FROM premium_warehouse_location_serial WHERE owner = %d AND location_id=%d',array($myrec['id'],$l_id)) - $minus;
				else
					$qty['per_warehouse'][$w['id']] = -$minus;
			} else {
    			$qty['per_warehouse'][$w['id']] = -$minus;
			}
		}
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		$ret = Premium_Warehouse_Items_LocationCommon::display_item_quantity_in_warehouse_and_total($r,$my_warehouse,$nolink,$qty['per_warehouse'],array('main'=>__('Available Qty'), 'in_one'=>_M('In %s', array("%s")), 'in_all'=>__('Total')));
		return $ret;
	}
	
	public static function display_quantity_on_route($r, $nolink) {
		static $trans = null;
		if ($trans===null) {
			$trans = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('status'=>array(3,4), 'transaction_type'=>0), array('id', 'warehouse'));
			$trans = $trans+Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('status'=>array(5,6), 'transaction_type'=>4), array('id', 'target_warehouse'));
		}
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		$qty = 0;
		$ids = array();
		foreach ($trans as $k=>$t) {
			if (!isset($t['warehouse'])) $trans[$k]['warehouse'] = $t['target_warehouse'];
			$ids[] = $t['id'];
		}
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$ids, 'item_name'=>$r['id']), array('quantity','transaction_id'));
		$en_route_qty = array();
		foreach ($items as $i) {
			$warehouse = $trans[$i['transaction_id']]['warehouse'];
			if (!isset($en_route_qty[$warehouse])) $en_route_qty[$warehouse] = 0;
			$en_route_qty[$warehouse] += $i['quantity'];
			$qty+=$i['quantity'];
		}
		return Premium_Warehouse_Items_LocationCommon::display_item_quantity_in_warehouse_and_total($r,$my_warehouse,$nolink,$en_route_qty,array('main'=>__('Quantity En Route'), 'in_one'=>_M('to %s', array("%s")), 'in_all'=>__('Total')));
	}

    /**
     * @return array array of associative arrays ['crits' => crits_array, 'label' => type_label]
     */
    public static function get_possible_transaction_type_labels() {
        // 'Premium_Items_Orders_Trans_Types' array(0=>_M('Purchase'),1=>_M('Sale'),2=>_M('Inventory Adjustment'),3=>_M('Rental'),4=>_M('Transfer'))
        $types = array();
        $crits = array();
        $tt = 'transaction_type';
        $p = 'payment';
        $status = 'status';
        // check-in
        $crits[$p] = 0;
        $crits[$tt] = 0;
        $types[] = array('crits' => $crits, 'label' => self::display_transaction_type_order($crits));
        // check-out
        $crits[$tt] = 1;
        $types[] = array('crits' => $crits, 'label' => self::display_transaction_type_order($crits));
        // purchase
        $crits[$p] = 1;
        $crits[$tt] = 0;
        $types[] = array('crits' => $crits, 'label' => self::display_transaction_type_order($crits));
        // sale
        $crits[$tt] = 1;
        $crits["!$status"] = 1;
        $types[] = array('crits' => $crits, 'label' => self::display_transaction_type_order($crits));
        unset($crits["!$status"]);
        // sales quote
        $crits[$p] = 1;
        $crits[$tt] = 1;
        $crits[$status] = 1;
        $types[] = array('crits' => $crits, 'label' => self::display_transaction_type_order($crits));
        unset($crits[$status]);
        unset($crits[$p]);
        // inv adjustment
        $crits[$tt] = 2;
        $types[] = array('crits' => $crits, 'label' => self::display_transaction_type_order($crits));
        // rental - commented out also in Orders_0 body
//        $crits[$tt] = 3;
//        $types[] = array('crits' => $crits, 'label' => self::display_transaction_type_order($crits));
        // transfer
        $crits[$tt] = 4;
        $types[] = array('crits' => $crits, 'label' => self::display_transaction_type_order($crits));
        return $types;
    }

	public static function get_status_array($trans, $payment=null) {
		if ($payment==null) $payment = isset($trans['payment'])?$trans['payment']:null;
		if (!isset($trans['transaction_type'])) $trans['transaction_type'] = null;
		switch ($trans['transaction_type']) {
			// PURCHASE
			case 0: $opts = array(''=>__('New'), 1=>__('Purchase Quote'), 2=>__('Purchase Order'), 3=>__('New Shipment'), 4=>__('Shipment Received'), 5=>__('On Hold'), 20=>__('Delivered'), 21=>__('Canceled'));
					break;
			// SALE
			case 1: if (!$payment)
						$opts = array(''=>__('Check-out Received'), 4=>__('Check-out confirmed'), 5=>__('On Hold'), 6=>__('Ready to Ship'), 7=>__('Shipped'), 20=>__('Delivered'), 21=>__('Canceled'), 22=>__('Missing'));
					else {
						$payment_ack = __('Payment Confirmed');
						if (isset($trans['terms']) && $trans['terms']>0) $payment_ack = __('Payment Approved');
						$opts = array(''=>__('New'), -1=>__('New Online Order'), -2=>__('New Online Order (with payment)'), 1=>__('Sales Quote'), 2=>__('Order Received'), 3=>$payment_ack, 4=>__('Order Confirmed'), 5=>__('On Hold'), 6=>__('Order Ready to Ship'), 7=>__('Shipped'), 20=>__('Delivered'), 21=>__('Canceled'), 22=>__('Missing'));
					}
					break;
			// INV. ADJUSTMENT
			case 2: $opts = array(''=>__('Active'), 20=>__('Completed'));
					break;
			// RENTAL
			case 3: if ($payment===true || ($payment===null && isset($trans['payment']) && $trans['payment']))
						$opts = array(''=>__('Rental order'), 1=>__('Create picklist'), 2=>__('Check payment'), 3=>__('Process picklist'), 4=>__('Payment'), 5=>__('Items rented'), 6=>__('Partially returned'), 20=>__('Completed'), 21=>__('Completed (Items lost)'));
					else
						$opts = array(''=>__('Create picklist'), 1=>__('Items rented'), 2=>__('Partially returned'), 20=>__('Completed'), 21=>__('Completed (Items lost)'));
					break;
			// WAREHOUSE TRANSFER
			case 4: $opts = array(''=>__('New'), 1=>__('Transfer Quote'), 2=>__('Pending'), 3=>__('Order Fullfilment'), 4=>__('On Hold'), 5=>__('Ready to Ship'), 6=>__('Shipped'), 20=>__('Delivered'), 21=>__('Canceled'), 22=>__('Missing')); break;
			default:
				// FIXME
				$opts = array(''=>__('New'), 20=>__('Delivered'), 21=>__('Canceled'));
		}
		return $opts;
	}

	public static function display_status($r, $nolink=false){
		$opts = self::get_status_array($r);
		if (!isset($opts[$r['status']])) return '---';
		return $opts[$r['status']];
	}
	
	public static function check_no_empty_invoice($data) {
		if (isset($data['receipt']) && $data['receipt']) return true;
		if (Utils_RecordBrowser::$last_record['transaction_type']==4 || Utils_RecordBrowser::$last_record['transaction_type']==2) return true;
		$access = Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',Utils_RecordBrowser::$last_record);
		$ret = array();
		if (!isset($data['company_name']) || !$data['company_name']) {
			if ($access['last_name'] && (!isset($data['last_name']) || !$data['last_name'])) $ret['last_name'] = __('Field required for non-receipt transactions'); 
			if ($access['first_name'] && (!isset($data['first_name']) || !$data['first_name'])) $ret['first_name'] = __('Field required for non-receipt transactions'); 
		}
		if ($access['address_1'] && (!isset($data['address_1']) || !$data['address_1'])) $ret['address_1'] = __('Field required for non-receipt transactions'); 
		if ($access['city'] && (!isset($data['city']) || !$data['city'])) $ret['city'] = __('Field required for non-receipt transactions'); 
		if ($access['country'] && (!isset($data['country']) || !$data['country'])) $ret['country'] = __('Field required for non-receipt transactions'); 
		return empty($ret)?true:$ret;
	}

	public static function QFfield_receipt(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		if ($mode!='view') {
			$form->addElement('checkbox', $field, $label, null, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			if(isset($_SESSION['client']['order_add'])) {
				$rb_obj->switch_to_addon('Items');
				unset($_SESSION['client']['order_add']);
			}
			if ($default) {
				$form->addElement('checkbox', $field, $label);
				$form->freeze('checkbox');
				$form->setDefaults(array($field=>$default));
				eval_js('hide_rb_field=function(arg){if($("_"+arg+"__label"))$("_"+arg+"__label").parentNode.parentNode.style.display="none"}');
//				foreach(array('last_name','first_name','company_name','address_1','address_2','city','country','zone','postal_code','phone','tax_id') as $v)
//					eval_js('hide_rb_field("'.$v.'");');
			}
		}
	}
	
	public static function check_target_warehouse_required($data) {
		if (Utils_RecordBrowser::$last_record['transaction_type']!=4) return true;
		if ($data['target_warehouse']) return true;
		return array('target_warehouse'=>__('Field required'));
	}
	public static function QFfield_company_name(&$form, $field, $label, $mode, $default, $desc, $rb_obj){
		$form->addFormRule(array('Premium_Warehouse_Items_OrdersCommon','check_target_warehouse_required'));
		if ($mode!='view' && (Utils_RecordBrowser::$last_record['transaction_type']==0 || Utils_RecordBrowser::$last_record['transaction_type']==1)) {
			load_js('modules/Premium/Warehouse/Items/Orders/contractor_update.js');
			eval_js('new ContractorUpdate()');
		}
		if ($mode!='view') {
			if ($field=='company_name')$form->addFormRule(array('Premium_Warehouse_Items_OrdersCommon','check_no_empty_invoice'));
			$form->addElement('text', $field, $label, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label, self::display_company_name(Utils_RecordBrowser::$last_record, false, array('id'=>$field)));
		}
	}
	
	public static function check_if_warehouse_set($data) {
		if (!isset($data['status'])) return true;
		if (isset($data['warehouse']))
			$warehouse = $data['warehouse'];
		else
			$warehouse = utils_RecordBrowser::$last_record['warehouse'];
		
		if ($data['status']>=2 && !$warehouse)
			return array('warehouse'=>__('Unable to change status - select warehouse first'));
		if (isset($data['target_warehouse']) && $data['target_warehouse'] && $warehouse==$data['target_warehouse'])
			return array('warehouse'=>__('Source and target warehouses must be different'));
		return true;
	}

	public static function QFfield_status(&$form, $field, $label, $mode, $default, $desc, $rb_obj){
		$opts = self::get_status_array($rb_obj->record);
		if ($mode!='view') {
			$form->addElement('select', $field, $label, $opts, array('id'=>'status'));
			$form->setDefaults(array($field=>$default));
			$form->addFormRule(array('Premium_Warehouse_Items_OrdersCommon','check_if_warehouse_set'));
		} else {
		    $i = self::Instance();
		    if(Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',Utils_RecordBrowser::$last_record)) {
    			$obj = $rb_obj->init_module('Premium/Warehouse/Items/Orders');
	    		$rb_obj->display_module($obj, array(Utils_RecordBrowser::$last_record, $default), 'change_status_leightbox');
		    	$href = $obj->get_href();
		    } else $href=null;
			if ($href) $label2 = '<a '.$href.'>'.$opts[$default].'</a>';
			else $label2 = $opts[$default];
			$form->addElement('static', $field, $label, $label2);
		}
	}

    public static function QFfield_unit_price(&$form, $field, $label, $mode, $default, $desc, $rb_obj){
        if ($mode!=='view') {
            if ($default) {
                $default = explode('__',$default);
                $default = Utils_CurrencyFieldCommon::format_default($default[0], $default[1]);
            }
            $form->addElement('currency', $field, $label, array('id'=>$field));
            $form->setDefaults(array($field=>$default));
            $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('currency', $field, $label, array('id'=>$field));
            $form->setDefaults(array($field=>$default));
        }
    }

    public static function QFfield_discount_rate(&$form, $field, $label, $mode, $default, $desc, $rb_obj){
        if ($mode!=='view') {
            $form->addElement('text', $field, $label, array('id'=>$field));
            $form->setDefaults(array($field=>$default?$default:0));
            $form->addRule($field,__('Invalid markup/discount rate'),'regex','/^(-[0-9]{1,2}|[0-9]+)(\.[0-9]+)?$/');
            $curr_format = '-?[0-9]*\.?[0-9]*';
			eval_js('Event.observe(\''.$field.'\',\'keypress\',Utils_CurrencyField.validate.bindAsEventListener(Utils_CurrencyField,\''.Epesi::escapeJS($curr_format,false).'\'))');
			eval_js('Event.observe(\''.$field.'\',\'blur\',Utils_CurrencyField.validate_blur.bindAsEventListener(Utils_CurrencyField,\''.Epesi::escapeJS($curr_format,false).'\'))');
        } else {
            $form->addElement('text', $field, $label, array('id'=>$field));
            $form->setDefaults(array($field=>$default));
        }
    }

    public static function QFfield_net_price(&$form, $field, $label, $mode, $default, $desc, $rb_obj){
		if ($mode!=='view') {
			if ($default) {
				$default = explode('__',$default);
				$default = Utils_CurrencyFieldCommon::format_default($default[0], $default[1]);
			}
			Premium_Warehouse_ItemsCommon::init_net_gross_js_calculation($form, 'tax_rate', 'net_price', 'gross_price','unit_price','markup_discount_rate');
			$form->addElement('currency', $field, $label, array('id'=>$field));
			$form->setDefaults(array($field=>$default, 'use_net_price'=>1));
			if ($default) {
				$decp = Utils_CurrencyFieldCommon::get_decimal_point();
				eval_js('update_gross("'.$decp.'","net_price","gross_price","tax_rate",0);');
			}
		} else {
			$form->addElement('currency', $field, $label, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		}
	}
	
	public static function QFfield_gross_price(&$form, $field, $label, $mode, $default, $desc, $rb_obj){
		if ($mode!=='view') {
			$form->addElement('currency', $field, $label, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label, self::display_gross_price(Utils_RecordBrowser::$last_record, false));
		}
	}
	
	public static function display_debit($r, $nolink) {
		return $r['quantity']<0?-$r['quantity']:'';
	}
	
	public static function display_credit($r, $nolink) {
		return $r['quantity']>0?$r['quantity']:'';
	}
	
	public static function display_serial($r, $nolink){
		if (!is_numeric($r['serial'])) return $r['serial'];
		return Premium_Warehouse_Items_LocationCommon::mark_used(Utils_RecordBrowserCommon::get_value('premium_warehouse_location',$r['serial'],'used')).Utils_RecordBrowserCommon::get_value('premium_warehouse_location',$r['serial'],'serial');
	}
	
	public static function QFfield_return_date(&$form, $field, $label, $mode, $default, $args, $rb){
		$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$rb->record['transaction_id']);
//	        Epesi::alert($rec['transaction_type']);
                if($rec['transaction_type']!=3) return;
                $form->addElement('datepicker',$args['id'],$label,array('format'=>'d M Y', 'minYear'=>date('Y')-95,'maxYear'=>date('Y')+5, 'addEmptyOption'=>true, 'emptyOptionText'=>'--'));
		$form->setDefaults(array($field=>$default));
	}
	
	public static function display_return_date($r, $nolink) {
		if ($r['returned']) $icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Orders','return_date_returned.png');
		else {
			if ($r['return_date']<date('Y-m-d')) $icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Orders','return_date_overdue.png');
			elseif ($r['return_date']<date('Y-m-d',strtotime('+3 days'))) $icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Orders','return_date_nearing.png');
		}
		$ret = '';
		if (isset($icon)) $ret = '<img src="'.$icon.'" />';
		$ret .= $r['return_date'];
		return $ret;
	}
	
	public static function get_trans() {
		if (!intval(Utils_RecordBrowser::$last_record['transaction_id'])) self::$trans = Utils_RecordBrowser::$last_record;
		if (!self::$trans) self::$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',Utils_RecordBrowser::$last_record['transaction_id']);
	}
	
	public static function QFfield_serial(&$form, $field, $label, $mode, $default){
		self::get_trans();
		if ($mode=='view' || ($mode=='edit' && self::$trans['transaction_type']!=0 && self::$trans['transaction_type']!=2)) {
			$form->addElement('static', $field, $label);
//			if (is_numeric($default)) $form->setDefaults(array($field=>Utils_RecordBrowserCommon::get_value('premium_warehouse_location',$default,'serial')));
		} else {
			if (self::$trans['transaction_type']==1 || self::$trans['transaction_type']==3) {
				$form->addElement('select', $field, $label, array(), array('id'=>'serial'));
			} else {
				$form->addElement('text', $field, $label, array('id'=>'serial'));
//				if ($mode=='edit' && is_numeric($default)) $form->setDefaults(array($field=>Utils_RecordBrowserCommon::get_value('premium_warehouse_location',$default,'serial')));
			}
		}
	}
	
	public static function QFfield_details_tax_rate(&$form, $field, $label, $mode, $default){
		if ($mode=='add' || $mode=='edit') {
			$tax_rates = array(''=>'---')+Data_TaxRatesCommon::get_tax_rates();
			$form->addElement('select', $field, $label, $tax_rates, array('onkeypress'=>'var key=event.which || event.keyCode;if(key==13){'.$form->get_submit_form_js().'};', 'id'=>'tax_rate'));
			if ($mode=='edit') $form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>Data_TaxRatesCommon::get_tax_name($default)));
		}
	} 
	
	public static function check_sale_price($data) {
		if (!isset($data['item_name'])) {
			$item_id = Utils_RecordBrowser::$last_record['item_name'];
		} else {
			list($item_id) = explode('/',$data['item_name']);
			if (!is_numeric($item_id))
				$item_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'item_name', $data['item_name']);
		}
		if (!is_numeric($item_id)) return array('item_name'=>__( 'Item not found'));
		$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item_id);
		$item['last_purchase_price'] = Utils_CurrencyFieldCommon::get_values($item['last_purchase_price']);
		$sale_price = implode('.',explode(Utils_CurrencyFieldCommon::get_decimal_point(), $data['net_price']));
		if (!$item['last_purchase_price'][0]) return true;
		if ($item['last_purchase_price'][1]!=$data['__net_price__currency']) return true;
		if ($sale_price<$item['last_purchase_price'][0]) return array('net_price'=>__('Error! Price too low.'));
		return true;
	}
	
	public static function QFfield_credit(&$form, $field, $label, $mode, $default) {
		$attrs = array('onkeyup'=>'if(this.value)$("debit").style.display="none";else $("debit").style.display="inline";');
		$attrs['id'] = $field;
		$default = null;
		if (isset(Utils_RecordBrowser::$last_record['quantity'])) {
			$qty = Utils_RecordBrowser::$last_record['quantity'];
			if ($qty>0)
				$default = $qty;
		}
		$form->addElement('text',$field,$label,$attrs);
		$form->setDefaults(array($field=>$default));
		eval_js('if($("debit").value)$("credit").style.display="none";');
	}

	public static function QFfield_debit(&$form, $field, $label, $mode, $default) {
		$attrs = array('onkeyup'=>'if(this.value)$("credit").style.display="none";else $("credit").style.display="inline";');
		$attrs['id'] = $field;
		$default = null;
		if (isset(Utils_RecordBrowser::$last_record['quantity'])) {
			$qty = Utils_RecordBrowser::$last_record['quantity'];
			if ($qty<0)
				$default = -$qty;
		}
		$form->addElement('text',$field,$label,$attrs);
		$form->setDefaults(array($field=>$default));
		eval_js('if($("credit").value)$("debit").style.display="none";');
	}
	
	public static function QFfield_item_name(&$form, $field, $label, $mode, $default){
		self::get_trans();
		if ($mode=='add' || $mode=='edit') {
			if (self::$trans['transaction_type']==1 && self::$trans['payment']==1) {
				$decp = Utils_CurrencyFieldCommon::get_decimal_point();
				load_js('modules/Premium/Warehouse/Items/Orders/check_item_price_cost.js');
				$msg = __('Warning: Sale price is lower than the last purchase price.');
				$sell_with_loss = Base_AclCommon::check_permission('Inventory - Sell at loss');
				if (!$sell_with_loss) {
					$msg = __('Error: Sale price is lower than the last purchase price!');
					$form->addFormRule(array('Premium_Warehouse_Items_OrdersCommon','check_sale_price'));
				}
				$warning = $msg;
				$form->addElement('button', 'submit', __('Submit'), array('style'=>'width:auto;', 'onclick'=>'if(check_item_price_cost_difference("'.$decp.'","'.$warning.'","'.((int)(!$sell_with_loss)).'")){'.$form->get_submit_form_js().'};'));
				$form->addElement('hidden', 'last_item_price', '', array('id'=>'last_item_price'));
			}
			$crits = array();
			$callback = array('Premium_Warehouse_ItemsCommon','display_item_name');
			$el = $form->addElement('autoselect', $field, $label, array(), array(array('Premium_Warehouse_Items_OrdersCommon','autoselect_item_name_suggestbox'), array($crits, self::$trans)), $callback, array('id'=>$field, 'onchange'=>'warehouse_update_item_details('.self::$trans['id'].');'));
			$el->on_hide_js('warehouse_update_item_details('.self::$trans['id'].');');
			if (isset($default) && is_numeric($default)) $form->setDefaults(array($field=>$default));
			$form->addFormRule(array('Premium_Warehouse_Items_OrdersCommon','check_qty_on_hand'));
			load_js('modules/Premium/Warehouse/Items/Orders/item_autocomplete.js');
			eval_js('if($("item_name") && $("item_name").value=="")focus_by_id("item_name");');
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>self::display_item_name(array('item_name'=>$default), null, array('id'=>'item_name'))));
		}
	}

	public static function QFfield_quantity(&$form, $field, $label, $mode, $default){
		self::get_trans();
		if (self::$trans['transaction_type']==2) return;
		if ($mode=='add' || $mode=='edit') {
			$form->addElement('text', $field, $label, array('id'=>$field));
			if ($mode=='edit') $form->setDefaults(array($field=>$default));
            $curr_format = '-?[0-9]*\.?[0-9]*';
			eval_js('Event.observe(\''.$field.'\',\'keypress\',Utils_CurrencyField.validate.bindAsEventListener(Utils_CurrencyField,\''.Epesi::escapeJS($curr_format,false).'\'))');
			eval_js('Event.observe(\''.$field.'\',\'blur\',Utils_CurrencyField.validate_blur.bindAsEventListener(Utils_CurrencyField,\''.Epesi::escapeJS($curr_format,false).'\'))');
            eval_js('Event.observe("'.$field.'","keyup",function(){update_total(jq("#net_price").val(),jq("#gross_price").val(),"'.Utils_CurrencyFieldCommon::get_decimal_point().'");});');
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>$default));
		}
	}

    public static function allow_negative_qty($item_id)
    {
        $negative = Variable::get('premium_warehouse_negative_qty', false);
        if ($negative == 'all') {
            return true;
        } elseif ($negative == 'selected') {
            $item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item_id);
            if (isset($item['allow_negative_quantity']) && $item['allow_negative_quantity']) {
                return true;
            }
        }
        return false;
    }

	public static function check_qty_on_hand($data){
		self::get_trans();
		if (isset($data['quantity']) && intval($data['quantity'])!=$data['quantity']) return array('item_name'=>__( 'Invalid amount'));
		if (self::$trans['transaction_type']==0) return true;
		list($data['item_name']) = explode('/',$data['item_name']);
		if (isset(Utils_RecordBrowser::$last_record['quantity'])) {
			if (isset($data['quantity'])) {
				$ord_qty = $data['quantity'];
				$data['quantity'] -= Utils_RecordBrowser::$last_record['quantity'];
			}
			if (isset($data['debit']) && $data['debit']) {
				$ord_qty = $data['debit'];
				$data['debit'] += Utils_RecordBrowser::$last_record['quantity'];
			}
		} elseif (isset($data['quantity']))
			$ord_qty = $data['quantity'];
	    elseif (self::$trans['transaction_type']!=2)
	        return array('item_name'=>__( 'Field required'));
		if (!isset($data['item_name'])) {
			$data['item_name'] = Utils_RecordBrowser::$last_record['item_name'];
		} else { 
			if (!is_numeric($data['item_name']))
				$data['item_name'] = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'item_name', $data['item_name']);
			if (!is_numeric($data['item_name'])) {
				if (isset(Utils_RecordBrowser::$last_record['item_name']))
					$data['item_name'] = Utils_RecordBrowser::$last_record['item_name'];
				else
					return array('item_name'=>__( 'Item not found'));
			}
		}
		$item_type = Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$data['item_name'],'item_type');
		if ($item_type>=2) return true;
		if (self::$trans['transaction_type']==1) {
			if ($ord_qty<=0) return array('quantity'=>__( 'Invalid amount'));
			if (self::$trans['status']<=1) return true;
            if (self::allow_negative_qty($data['item_name'])) return true;
			$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$data['item_name'],'warehouse'=>self::$trans['warehouse']));
			$location_id = array_shift($location_id);
			if (!isset($location_id) || !$location_id) {
				return array('quantity'=>__( 'Amount not available'));
			}
			if ($data['quantity']>$location_id['quantity']) return array('quantity'=>__( 'Amount not available'));
		}
		if (self::$trans['transaction_type']==4) {
			if ($ord_qty<=0) return array('quantity'=>__( 'Invalid amount'));
			$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$data['item_name'],'warehouse'=>self::$trans['warehouse']));
			$location_id = array_shift($location_id);
			if (!isset($location_id) || !$location_id) {
				return array('quantity'=>__( 'Amount not available'));
			}
			if ($data['quantity']>$location_id['quantity']) return array('quantity'=>__( 'Amount not available'));
		}
		if (self::$trans['transaction_type']==2) {
			if (!isset($data['debit'])) return true;
			if ($data['debit']<0 ||
				$data['credit']<0) return array('debit'=>__( 'Invalid amount'));
//			if (!$data['debit']>0 &&
//				!$data['credit']>0) return array('debit'=>__( 'Non-zero amount must be entered'));
			if ($data['debit']>0) {
				$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$data['item_name'],'warehouse'=>self::$trans['warehouse']));
				$location_id = array_shift($location_id);
				if (!isset($location_id) || !$location_id) {
					return array('debit'=>__( 'Amount not available'));
				}
				if ($data['debit']>$location_id['quantity']) return array('debit'=>__( 'Amount not available'));
			}
		}
		return true;
	} 
	
    public static function menu() {
		if (Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','browse'))
			return array(_M('Inventory')=>array('__submenu__'=>1,_M('Items Transactions')=>array()));
/*		if (browse my orders)
			return array(_M('Orders')=>array());*/
		return array();
	}

	public static function applet_caption() {
		return __('Active Orders');
	}
	public static function applet_info() {
		return __('Active Orders');
	}

	public static function applet_info_format($r){
		$arr = array(
			__('Transaction ID')=>$r['transaction_id'],
			__('Status')=>self::display_status($r,true)
		);
		$ret = array('notes'=>Utils_TooltipCommon::format_info_tooltip($arr));
		return $ret;
	}

	public static function applet_settings() {
		$opts = array('all'=>'---', 604800=>__('1 week'), 1209600=>__('2 weeks'), 2419200=>__('4 weeks'));
		$types = array(0=>__('Purchase'), 1=>__('Sale'), 2=>__('Inv. Adjustment'), 4=>__('Transfer'));
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse');
		$wopts = array(''=>'---');
		foreach ($warehouses as $v)
			$wopts[$v['id']] = $v['warehouse'];
		return array_merge(Utils_RecordBrowserCommon::applet_settings(),
			array(
			array('name'=>'settings_header','label'=>__('Settings'),'type'=>'header'),
			array('name'=>'older','label'=>__('Transaction older then'),'type'=>'select','default'=>'all','rule'=>array(array('message'=>__('Field required'), 'type'=>'required')),'values'=>$opts),
			array('name'=>'my','label'=>__('Only mine and not assigned'),'type'=>'checkbox','default'=>0),
			array('name'=>'type','label'=>__('Transaction Type'),'type'=>'select','default'=>1,'rule'=>array(array('message'=>__('Field required'), 'type'=>'required')),'values'=>$types),
			array('name'=>'warehouse','label'=>__('Warehouse'),'type'=>'select','default'=>1,'values'=>$wopts),
			array('name'=>'company','label'=>__('Display company'),'type'=>'checkbox','default'=>1),
			array('name'=>'contact','label'=>__('Display contact'),'type'=>'checkbox','default'=>1),
			array('name'=>'total_value','label'=>__('Display total value'),'type'=>'checkbox','default'=>0),
			));
		}


	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse_items_orders',
				__('Orders'),
				$rid,
				$events,
				'transaction_id',
				$details
			);
	}
	
	public static function generate_id($id) {
		if (is_array($id)) $id = $id['id'];
		return '#'.str_pad($id, 6, '0', STR_PAD_LEFT);
	}

	/**
	 * Use these two methods only when actually changing serials
	 */
	// "Transaction type 1-4"-method
	public static function selected_serials($details, $trans, $serials, $update_location = true) {
		if (!Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $details['item_name'], 'item_type')==1) return;
		$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku', 'warehouse'), array($details['item_name'], $trans['warehouse']));
		if ($update_location) DB::Execute('UPDATE premium_warehouse_location_serial s SET location_id=%d WHERE EXISTS(SELECT * FROM premium_warehouse_location_orders_serial os WHERE os.serial_id=s.id AND os.order_details_id=%d)', array($loc_id, $details['id']));
		$old_serials = DB::GetAssoc('SELECT serial_id, serial_id FROM premium_warehouse_location_orders_serial WHERE order_details_id=%d', array($details['id']));
		DB::Execute('DELETE FROM premium_warehouse_location_orders_serial WHERE order_details_id=%d', array($details['id']));
		$target = null;
		if ($trans['transaction_type']==4 && $trans['status']==20) $target = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku', 'warehouse'), array($details['item_name'], $trans['target_warehouse']));
		foreach ($serials as $s) {
			if ($s===null) {
				$id = array_shift($old_serials);
				if (!$id)
					$id = DB::GetOne('SELECT id FROM premium_warehouse_location_serial WHERE location_id=%d', array($loc_id));
			} elseif ($s=="NULL") {
				$id = DB::GetOne('SELECT id FROM premium_warehouse_location_serial WHERE serial="" AND location_id=%d', array($loc_id));
			} else
				$id = $s;
			if (!$id) continue;
			if ($update_location) DB::Execute('UPDATE premium_warehouse_location_serial SET location_id=%d WHERE id=%d', array($target, $id));
			DB::Execute('INSERT INTO premium_warehouse_location_orders_serial (serial_id, order_details_id) VALUES (%d, %d)', array($id, $details['id']));
		}
	}
	// "Transaction type 0/2"-method
	public static function set_serials($details, $trans, $serials = array()) {
		if (!Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $details['item_name'], 'item_type')==1) return;
		$details_id = $details['id'];
		$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku','warehouse'), array($details['item_name'], $trans['warehouse']));
		$qty = $details['quantity'];
		$owner = $trans['payment']?null:($trans['contact']?'P:'.$trans['contact']:($trans['company']?'C:'.$trans['company']:null));
		$ret = DB::Execute('SELECT * FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial s ON s.id=os.serial_id WHERE os.order_details_id=%d ORDER BY s.location_id ASC, s.id ASC', array($details_id));
		for ($i=0;$i<$qty;$i++) {
			$row = $ret->FetchRow();
			if (empty($serials)) $el = '';
			else $el = array_shift($serials);
			if (is_array($el) && isset($el['note'])) $note = $el['note'];
			else $note = null;
			if (is_array($el) && isset($el['shelf'])) $shelf = $el['shelf'];
			else $shelf = null;
			if (is_array($el)) $el = $el['serial'];
			if ($row) {
				if ($el) DB::Execute('UPDATE premium_warehouse_location_serial SET serial=%s, notes=%s,shelf=%s WHERE id=%d', array($el, $note, $shelf, $row['id']));
			} else {
				DB::Execute('INSERT INTO premium_warehouse_location_serial (location_id, serial, owner, notes, shelf) VALUES (%d, %s, %s, %s, %s)', array($loc_id, $el, $owner, $note, $shelf));
				$id = DB::Insert_ID('premium_warehouse_location_serial','id');
				DB::Execute('INSERT INTO premium_warehouse_location_orders_serial (serial_id, order_details_id) VALUES (%d, %d)', array($id, $details_id));
			}
		}
		while ($row = $ret->FetchRow()) {
			DB::Execute('DELETE FROM premium_warehouse_location_serial WHERE id=%d', array($row['id']));
			DB::Execute('DELETE FROM premium_warehouse_location_orders_serial WHERE serial_id=%d', array($row['id']));
		}
	}
	/**
	 * end of serials-changing methods
	 */

	public static function change_quantity($item_id, $warehouse, $quantity) {
		if (!$warehouse) return;
		$location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($item_id,$warehouse));
		if ($location_id===false || $location_id===null) {
			$location_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$warehouse, 'quantity'=>$quantity));
		} else {
			$new_qty = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $location_id, 'quantity')+$quantity;
			Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $location_id, array('quantity'=>$new_qty));
		}
		return $location_id;
	}
	
	public static function set_owner($trans, $details) {
		if ($trans['contact']) $own = 'P:'.$trans['contact'];
		elseif ($trans['company']) $own = 'C:'.$trans['company'];
		else return;
		DB::Execute('UPDATE premium_warehouse_location_serial SET owner=%s WHERE id IN (SELECT serial_id FROM premium_warehouse_location_orders_serial WHERE order_details_id=%d)', array($own, $details['id']));
	}
	
	public static function remove_transaction($trans, $details) {
		$item_id = $details['item_name'];
		$serialized = (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $item_id, 'item_type')==1);
		$transaction_type = $trans['transaction_type'];
		$status = $trans['status']; 
		$warehouse = $trans['warehouse'];
		
		$quantity = $details['quantity'];
		
		if ($transaction_type==0) {
			if ($status==20) {
				$loc_id = self::change_quantity($item_id, $warehouse, -$quantity);
			}
		}
		
		if ($transaction_type==1) {
			if ($status>=6 && $status<=20) {
				$loc_id = self::change_quantity($item_id, $warehouse, $quantity);
			}
		}
		
		if ($transaction_type==2) {
			$loc_id = self::change_quantity($item_id, $warehouse, -$quantity);
		}
		
		if ($transaction_type==4) {
			if ($status==20) {
				$loc_id = self::change_quantity($item_id, $trans['target_warehouse'], -$quantity);
			}
			if ($status>=5 && $status<=20) {
				$loc_id = self::change_quantity($item_id, $warehouse, $quantity);
			}
		}
	}
	
	public static function add_transaction($trans, $details) {
		$item_id = $details['item_name'];
		$serialized = (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $item_id, 'item_type')==1);
		$transaction_type = $trans['transaction_type'];
		$status = $trans['status']; 
		$warehouse = $trans['warehouse'];
		
		$quantity = $details['quantity'];
		
		if ($transaction_type==0) {
			if ($status==20) {
				$loc_id = self::change_quantity($item_id, $warehouse, $quantity);
			}
		}
		
		if ($transaction_type==1) {
			if ($status>=6 && $status<=20) {
				$loc_id = self::change_quantity($item_id, $warehouse, -$quantity);
			}
		}
		
		if ($transaction_type==2) {
			$loc_id = self::change_quantity($item_id, $warehouse, $quantity);
		}
		
		if ($transaction_type==4) {
			if ($status>=5 && $status<=20) {
				$loc_id = self::change_quantity($item_id, $warehouse, -$quantity);
				if ($serialized) DB::Execute('UPDATE premium_warehouse_location_serial SET location_id=NULL WHERE id IN (SELECT serial_id FROM premium_warehouse_location_orders_serial WHERE order_details_id=%d)', array($details['id']));
			}
			if ($status==20) {
				$loc_id = self::change_quantity($item_id, $trans['target_warehouse'], $quantity);
				if ($serialized) DB::Execute('UPDATE premium_warehouse_location_serial SET location_id=%d WHERE id IN (SELECT serial_id FROM premium_warehouse_location_orders_serial WHERE order_details_id=%d)', array($loc_id, $details['id']));
			}
		}
	}

	public static function cleanup_serials($trans, $details) {
		$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($details['item_name'],$trans['warehouse']));
		if ($trans['transaction_type']==0) {
			if ($trans['status']==20 && Utils_RecordBrowserCommon::is_active('premium_warehouse_items_orders_details',$details['id'])) {
				// Make sure amount of serials matches quantity
				self::set_serials($details, $trans);
			} else {
				self::remove_serials($trans, $details);
			}
		}
		if ($trans['transaction_type']==1) {
			if ($trans['status']>=6 && $trans['status']!=21 && Utils_RecordBrowserCommon::is_active('premium_warehouse_items_orders_details',$details['id']) && $details['quantity']>0) {
				// Make sure amount of serials matches quantity
				$serials = array_fill(1, $details['quantity'], null);
				self::selected_serials($details, $trans, $serials, false);
			} else {
				self::remove_serials($trans, $details);
			}
		}
	}

	public static function remove_serials($trans, $details) {
		if ($trans['transaction_type']==1) {
			$location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($details['item_name'],$trans['warehouse']));
			$ret = DB::Execute('SELECT * FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial s ON s.id=os.serial_id WHERE os.order_details_id=%d ORDER BY s.location_id ASC, s.id ASC', array($details['id']));
			while ($row=$ret->FetchRow()) DB::Execute('UPDATE premium_warehouse_location_serial SET location_id=%d WHERE id=%d', array($location_id, $row['id']));
		}
		if ($trans['transaction_type']==0) {
			$ret = DB::Execute('SELECT * FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial s ON s.id=os.serial_id WHERE os.order_details_id=%d ORDER BY s.location_id ASC, s.id ASC', array($details['id']));
			while ($row=$ret->FetchRow()) DB::Execute('UPDATE premium_warehouse_location_serial SET location_id=NULL WHERE id=%d', array($row['id']));
		}
	}

	public static function add_serials($trans, $details) {
		if ($trans['transaction_type']==0) {
			$location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($details['item_name'],$trans['warehouse']));
			$ret = DB::Execute('SELECT * FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial s ON s.id=os.serial_id WHERE os.order_details_id=%d ORDER BY s.location_id ASC, s.id ASC', array($details['id']));
			while ($row=$ret->FetchRow()) DB::Execute('UPDATE premium_warehouse_location_serial SET location_id=%d WHERE id=%d', array($location_id, $row['id']));
		}
		if ($trans['transaction_type']==1) {
			$ret = DB::Execute('SELECT * FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial s ON s.id=os.serial_id WHERE os.order_details_id=%d ORDER BY s.location_id ASC, s.id ASC', array($details['id']));
			while ($row=$ret->FetchRow()) DB::Execute('UPDATE premium_warehouse_location_serial SET location_id=NULL WHERE id=%d', array($row['id']));
		}
	}
	
	public static function submit_order($values, $mode) {
		if (in_array($mode, array('view', 'editing', 'adding'))) {
			load_js('modules/Premium/Warehouse/Items/Orders/field_control.js');
			eval_js('warehouse_order_mode = "'.str_replace('ing', '', $mode).'";');
			eval_js('warehouse_orders_hide_fields('.$values['transaction_type'].', '.($values['status']?$values['status']:0).', "'.($values['shipment_type']?$values['shipment_type']:0).'", "'.($values['payment_type']?$values['payment_type']:0).'");');
		}

		switch ($mode) {
			case 'cloned':
				$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details',array('transaction_id'=>$values['original']));
				foreach ($recs as $r) {
					$r['transaction_id'] = $values['clone'];
					Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details',$r);
				}
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$values['clone'],array('status'=>''));
				return $values['clone'];
			case 'adding':
				if ($mode!='view' && ($values['transaction_type']==0 || $values['transaction_type']==1)) {
					load_js('modules/Premium/Warehouse/Items/Orders/contractor_update.js');
					eval_js('new ContractorUpdate()');
				}
			case 'editing':
				if ($values['transaction_type']==2 || $values['transaction_type']==4) {
					Utils_RecordBrowser::$rb_obj->hide_tab('Contact Details');
					Utils_RecordBrowser::$rb_obj->hide_tab('Shipping Address');
				}
				eval_js('if($("transaction_type"))Event.observe("transaction_type","change",function(){warehouse_orders_hide_fields();});');
				eval_js('if($("status"))Event.observe("status","change",function(){warehouse_orders_hide_fields();});');
				eval_js('if($("shipment_type"))Event.observe("shipment_type","change",function(){warehouse_orders_hide_fields();});');
				eval_js('if($("payment_type"))Event.observe("payment_type","change",function(){warehouse_orders_hide_fields();});');
				if ($values['transaction_type']==3) {
					$opts_pay = self::get_status_array($values,true);
					$opts_no_pay = self::get_status_array($values,false);
					eval_js(
					'trans_rental_disable = function(){'.
						'arg=!$(\'payment\').checked;'.
						'if ($(\'paid\')) $(\'paid\').disabled = arg;'.
						'$(\'payment_type\').disabled = arg;'.
						'$(\'payment_no\').disabled = arg;'.
						'$(\'shipment_type\').disabled = arg;'.
						'$(\'shipment_no\').disabled = arg;'.
						'$(\'terms\').disabled = arg;'.
						'if($(\'status\')){'.
							'if(arg)'.
								'new_opts = '.json_encode($opts_no_pay).';'.
							'else '.
								'new_opts = '.json_encode($opts_pay).';'.
							'var obj=$(\'status\');'.
							'var opts=obj.options;'.
							'opts.length=0;'.
							'for(y in new_opts) {'.
								'opts[opts.length] = new Option(new_opts[y],y);'.
							'}'.
						'}'.
					'};'.
					'trans_rental_disable();'.
					'Event.observe(\'payment\', \'change\', trans_rental_disable)');
				}
				break;
			case 'delete':
				$det = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$values['id']));
				foreach ($det as $d)
					Utils_RecordBrowserCommon::delete_record('premium_warehouse_items_orders_details', $d['id']);
				return;
			case 'restore':
				$det = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$values['id']), array(), array(), array(), true);
				foreach ($det as $d)
					Utils_RecordBrowserCommon::restore_record('premium_warehouse_items_orders_details', $d['id']);
				return;
			case 'view':
				if ($values['transaction_type']==2 || $values['transaction_type']==4 && isset(Utils_RecordBrowser::$rb_obj)) {
					Utils_RecordBrowser::$rb_obj->hide_tab('Contact Details');
					Utils_RecordBrowser::$rb_obj->hide_tab('Shipping Address');
				}
				if (self::$status_blocked)
					print('<b>'.__('Warning: status change impossible - select warehouse first.').'</b>');
				if ($values['transaction_type']==1 && $values['status']>20 && !$values['related']) {
					Base_ActionBarCommon::add('attach',__('Corrective Transaction'),Module::create_href(array('premium_warehouse_correct'=>$values['id'])));
					if (isset($_REQUEST['premium_warehouse_correct']) && $_REQUEST['premium_warehouse_correct']==$values['id']) {
						$vals = $values;
						$vals['related'] = $values['id'];
						unset($vals['invoice_number']);
						unset($vals['invoice_print_date']);
						unset($vals['id']);
						$new_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders', $vals);
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $values['id'],array('related'=>$new_id));
						$det = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$values['id']));
						foreach ($det as $d) {
							$d['transaction_id']=$new_id;
							$d['quantity'] = -$d['quantity'];
							unset($d['id']);
							Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details', $d);
						}
						unset($_REQUEST['premium_warehouse_correct']);
						location(array());
					}
				}
				if (Base_AclCommon::i_am_admin() && $values['transaction_type']==2) {
					$debts = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$values['id'], '<quantity'=>0));
					if (empty($debts)) {
						Base_ActionBarCommon::add('attach',__('Turn into Purchase'),Module::create_href(array('premium_warehouse_turn_into_purchase'=>$values['id'])));
						if (isset($_REQUEST['premium_warehouse_turn_into_purchase']) && $_REQUEST['premium_warehouse_turn_into_purchase']==$values['id']) {
							Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $values['id'], array('transaction_type'=>0, 'status'=>20, 'receipt'=>1, 'payment'=>1));
							unset($_REQUEST['premium_warehouse_turn_into_purchase']);
							location(array());
						}
					}
				}

				$active = (Base_User_SettingsCommon::get('Premium_Warehouse_Items_Orders','my_transaction')==$values['id']);
				if (!Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',$values)) {
					if ($active) Base_User_SettingsCommon::save('Premium_Warehouse_Items_Orders','my_transaction','');
					return $values;
				}
				if (isset($_REQUEST['premium_warehouse_change_active_order']) && $_REQUEST['premium_warehouse_change_active_order']==$values['id']) {
					Base_User_SettingsCommon::save('Premium_Warehouse_Items_Orders','my_transaction',$active?'':$values['id']);
					unset($_REQUEST['premium_warehouse_change_active_order']);
					$active = !$active;
				}
				if ($active) {
					$icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Orders','deactivate.png');
					$label = __('Leave this trans.');
				} else {
					$icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Orders','activate.png');
					$label = __('Use this Trans.');
				}
				Base_ActionBarCommon::add($icon,$label,Module::create_href(array('premium_warehouse_change_active_order'=>$values['id'])));
				if (isset($_REQUEST['premium_warehouse_add_bill']) && $_REQUEST['premium_warehouse_add_bill']==$values['id']) {
					$bill = $values;
					$bill['transaction_type'] = 1;
					$bill['transaction_date'] = date('Y-m-d');
					$bill['status'] = '';
					$bill['payment'] = true;
					$bill['related'] = $values['id'];
					$id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders', $bill);
					Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $values['id'], array('related'=>$id));
					location(array());
				} else {
					if (!$values['payment'] && !$values['related'] && ($values['transaction_type'] == 0 || $values['transaction_type'] == 1)) 
						Base_ActionBarCommon::add('clone', __('Create Bill'), Module::create_href(array('premium_warehouse_add_bill'=>$values['id']))); // temporary name
				}
				return $values;
			case 'clone':
			case 'add':
				$_SESSION['client']['order_add']=1;
			case 'edit':
				if ($values['status']!=21) {
					if ($values['company']==0 && $values['company_name']) {
						$values['company'] = Utils_RecordBrowserCommon::get_id('company', 'company_name', $values['company_name']);
						if (!$values['company']) $values['company'] = Utils_RecordBrowserCommon::new_record('company',
							array(
								'company_name'=>$values['company_name'],
								'permission'=>0,
								'address_1'=>$values['address_1'],
								'address_2'=>$values['address_2'],
								'city'=>$values['city'],
								'country'=>$values['country'],
								'zone'=>$values['zone'],
								'postal_code'=>$values['postal_code'],
								'phone'=>$values['phone'],
								'tax_id'=>$values['tax_id']
							));
					}
					if ($values['contact']==0 && trim($values['last_name']) && trim($values['first_name'])) {
						if ($values['company']==-1) $values['company']='';
						$values['contact'] = Utils_RecordBrowserCommon::get_id('contact', array('first_name','last_name'), array($values['first_name'],$values['last_name']));
						if (!$values['contact']) $values['contact'] = Utils_RecordBrowserCommon::new_record('contact',
							array(
								'first_name'=>$values['first_name'],
								'last_name'=>$values['last_name'],
								'company_name'=>$values['company'],
								'permission'=>0,
								'address_1'=>$values['address_1'],
								'address_2'=>$values['address_2'],
								'city'=>$values['city'],
								'country'=>$values['country'],
								'zone'=>$values['zone'],
								'postal_code'=>$values['postal_code'],
								'work_phone'=>$values['phone']
							));
					}
				}
				if(!isset($values['id'])) break;
				if (!$values['warehouse'] && $values['status']>=2) {
					self::$status_blocked = true;
					$values['status'] = Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $values['id'], 'status');
				}
				$values['transaction_id'] = self::generate_id($values['id']);
				$old_values = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $values['id']);
				$det = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$values['id']));
				foreach ($det as $d) {
					self::remove_transaction($old_values, $d);
					self::add_transaction($values, $d);
					self::cleanup_serials($values, $d);
				}
                self::update_last_used_prices($values, $det);
				break;
			case 'added':
				if (isset($values['online_order']) && $values['online_order']) {
					$users = Base_User_SettingsCommon::get_users_settings('Premium_Warehouse', 'new_online_order_auto_subs');
					foreach ($users as $u=>$v)
						if ($v==1) Utils_WatchdogCommon::user_subscribe($u, 'premium_warehouse_items_orders', $values['id']);
				}
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$values['id'],array('transaction_id'=>self::generate_id($values['id'])), false, null, true);
				Base_User_SettingsCommon::save('Premium_Warehouse_Items_Orders','my_transaction',$values['id']);
		}
		$ret = ModuleManager::call_common_methods('submit_warehouse_order',false,array($values,$mode));
		foreach($ret as $r) {
			if($r && is_array($r)) {
				$values = array_merge($values,$r);
			}
		}
		return $values;
	}

    public static function update_last_used_prices($transaction, $items_array)
    {
        $transaction_type = $transaction['transaction_type'];
        $status = $transaction['status'];

        $update = false;
        if ($transaction_type == 0) { //purchase
            if ($status == 3 || $status == 4 || $status == 20) { // new shipment, shipment received, delivered
                $update = true;
            }
        }
        if ($transaction_type == 1) { //sale
            if ($status == 4 || $status == 6 || $status == 7 || $status == 20) { // confirmed, ready to ship, shipped, delivered
                $update = true;
            }
        }

        if ($update) {
            $field = $transaction_type == 0 ? 'last_purchase_price' : 'last_sale_price';
            foreach ($items_array as $det) {
                Utils_RecordBrowserCommon::update_record('premium_warehouse_items', $det['item_name'], array($field => $det['net_price']));
            }
        }
    }

	public static function display_last_price($r, $nolink=false, $desc=null) {
		$price = Utils_CurrencyFieldCommon::get_values($r[$desc['id']]);
		$ret = Utils_CurrencyFieldCommon::format($r[$desc['id']]);
		if (!$nolink) {
			$htmlinfo = array();
			if ($desc['id']=='last_sale_price') {
				$label = __('Last Sale Price');
			} else {
				$label = __('Last Purchase Price');
			}
			$htmlinfo[$label] = Utils_CurrencyFieldCommon::format($r[$desc['id']]);
			$htmlinfo[__('Tax')] = Data_TaxRatesCommon::get_tax_name($r['tax_rate']);
			$htmlinfo[__('Tax Rate')] = Data_TaxRatesCommon::get_tax_rate($r['tax_rate']).'%';
			$htmlinfo[__('Tax Value')] = Utils_CurrencyFieldCommon::format(($price[0]*Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))/100, $price[1]);
			$htmlinfo[$label.' ('.__('Gross').')'] = Utils_CurrencyFieldCommon::format(($price[0]*(100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate'])))/100, $price[1]);
			$ret = Utils_TooltipCommon::create($ret, Utils_TooltipCommon::format_info_tooltip($htmlinfo), false);
		}
		return $ret;
	}

	public static function submit_order_details($values, $mode) {
		if ($mode=='edit_changes') return $values;
		static $notice='';
   		if($mode!='adding') unset($_SESSION['client']['warehouse_transaction_new_item_id']);
		if ($notice!=='' && ($mode=='browse' || $mode=='display')) {
		    print($notice);
		    $notice = '';
		}
		if (isset($values['item_name'])) {
		    list($values['item_name']) = explode('/',$values['item_name']);
		    if(!is_numeric($values['item_name']))
			$values['item_name'] = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'item_name', $values['item_name']);
		}
		if (isset($values['item_name'])) $item_type = Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $values['item_name'], 'item_type');
		$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $values['transaction_id']);
		if (in_array($mode, array('view', 'editing', 'adding'))) {
			load_js('modules/Premium/Warehouse/Items/Orders/field_control.js');
			eval_js('order_details_trans_type = '.$trans['transaction_type'].';');
			if ($trans['payment']) eval_js('order_details_trans_payment = '.$trans['payment'].';');
			eval_js('warehouse_order_details_hide_fields();');
		}
        if($mode=='add' || $mode=='edit') {
            if(Utils_CurrencyFieldCommon::is_empty($values['gross_price']) && !Utils_CurrencyFieldCommon::is_empty($values['net_price'])) {
                $net = Utils_CurrencyFieldCommon::get_values($values['net_price']);
                $tax_rate = Data_TaxRatesCommon::get_tax_rate($values['tax_rate']);
                $values['gross_price'] = Utils_CurrencyFieldCommon::format_default($net[0]*(100+$tax_rate)/100,$net[1]);
            } elseif(!Utils_CurrencyFieldCommon::is_empty($values['gross_price']) && Utils_CurrencyFieldCommon::is_empty($values['net_price'])) {
                $gross = Utils_CurrencyFieldCommon::get_values($values['gross_price']);
                $tax_rate = Data_TaxRatesCommon::get_tax_rate($values['tax_rate']);
                $values['unit_price'] = $values['net_price'] = Utils_CurrencyFieldCommon::format_default($gross[0]*100/(100+$tax_rate),$gross[1]);
            } elseif(Utils_CurrencyFieldCommon::is_empty($values['gross_price']) && Utils_CurrencyFieldCommon::is_empty($values['net_price'])) {
                $values['unit_price'] = $values['gross_price'] = $values['net_price'] = '';
            }
            if(!Utils_CurrencyFieldCommon::is_empty($values['net_price'])) {
                if(Utils_CurrencyFieldCommon::is_empty($values['unit_price']) || $values['markup_discount_rate']==='' || !isset($values['markup_discount_rate'])) {
                    $values['unit_price'] = $values['net_price'];
                    $values['markup_discount_rate'] = 0;
                }
            } elseif($values['markup_discount_rate']==='') {
                $values['markup_discount_rate']=0;
            }
        }
		switch ($mode) {
			case 'adding':
				return $values;
			case 'delete':
				self::remove_transaction($trans, $values);
				self::remove_serials($trans, $values);
				location(array());
				return;
			case 'restore':
				self::add_transaction($trans, $values);
				self::add_serials($trans, $values);
				return;
			case 'add':
				if ($trans['transaction_type']==2) {
					if ($values['debit']) $values['quantity']=-$values['debit'];
					else $values['quantity']=$values['credit'];
				}
				unset($values['credit']);
				unset($values['debit']);
				break;
			case 'clone':
			case 'added':
				$net = Utils_CurrencyFieldCommon::get_values($values['net_price']);
				$gross = Utils_CurrencyFieldCommon::get_values($values['gross_price']);
				$tax_rate = Data_TaxRatesCommon::get_tax_rate($values['tax_rate']);
				if (round($net[0]*(100+$tax_rate)/100,2)!=$gross[0]) {
					$values['gross_price'] = round($net[0]*(100+$tax_rate)/100,2).'__'.$net[1];
					$new_gross = Utils_CurrencyFieldCommon::get_values($values['gross_price']);
					$notice .= '<font color="red"><b>'.__('Notice').':</b></font> '.
							__('No gross price is worth %s including %s%% tax.', array(Utils_CurrencyFieldCommon::format($gross[0], $gross[1]),$tax_rate)).'<br />'.
							__('Gross price was adjusted to %s, based on net price', array(Utils_CurrencyFieldCommon::format($new_gross[0], $new_gross[1])));
				}
				if ($trans['transaction_type']<2) {
    				$item=Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $values['item_name']);
    				$item_net = Utils_CurrencyFieldCommon::get_values($item['net_price']);
    				if ($trans['transaction_type']==0 && $trans['payment'] && $item_net[0] && $item_net[0]<$net[0] && $item_net[1]==$net[1]) {//if buy price is greater than suggested sell price
    				    Epesi::alert(__('Warning! Purchase price is greater than sell price.'));
		    		}
    				$item_last_purchase = Utils_CurrencyFieldCommon::get_values($item['last_purchase_price']);
    				if ($trans['transaction_type']==1 && $trans['payment'] && $item_last_purchase[0] && $item_last_purchase[0]>$net[0] && $item_net[1]==$net[1]) {//if buy price is greater than suggested sell price
    				    Epesi::alert(__('Warning! Transaction sell price is lower than last purchase price.'));
		    		}
				}
				self::add_transaction($trans, $values);
				if (isset($_REQUEST['serial_1']) && $item_type==1)
					self::process_sent_serials($values, $trans);
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $values['id'], $values);
				if ($mode=='added') location(array());
				return $values;
			case 'view':
				return $values;
			case 'edit':
				
				if ($trans['transaction_type']==2) {
					if ($values['debit']) $values['quantity']=-$values['debit'];
					elseif ($values['credit']) $values['quantity']=$values['credit'];
					unset($values['credit']);
					unset($values['debit']);
				}
				$old_values = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders_details', $values['id']);
				self::remove_transaction($trans, $old_values);
				self::add_transaction($trans, $values);
				if (isset($_REQUEST['serial_1']) && $item_type==1)
					self::process_sent_serials($values, $trans);
				return $values;
		}
		return $values;
	}

	public static function process_sent_serials($values, $trans) {
		if ($trans['transaction_type']==0) {
			$serials = array();
			for ($i=1; $i<=$values['quantity']; $i++) 
				$serials[] = array('serial'=>$_REQUEST['serial_'.$i],'note'=>$_REQUEST['note_'.$i],'shelf'=>$_REQUEST['shelf_'.$i]);
			self::set_serials($values, $trans, $serials);
		}
		if ($trans['transaction_type']==1 || $trans['transaction_type']==4) {
			$serials = array();
			for ($i=1; $i<=$values['quantity']; $i++) {
				$serials[] = $_REQUEST['serial__1__'.$i];
				DB::Execute('UPDATE premium_warehouse_location_serial SET note=%s, shelf=%s WHERE id=%d', array($_REQUEST['note_'.$i],$_REQUEST['shelf_'.$i],$_REQUEST['serial__1__'.$i]));
			}
			self::selected_serials($values, $trans, $serials);
		}
	}
	
	public static function submit_new_item_from_order($values, $mode) {
	    if(!isset($_SESSION['client']['warehouse_transaction_id'])) return $values;
	    
		switch ($mode) {
			case 'added':
	    		$_SESSION['client']['warehouse_transaction_new_item_id'] = $values['id'];
		}
		return $values;
	}

	public static function search_format($id) {
		if(!Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','browse')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items_orders', $row['id']).__( 'Item order (attachment) #%d, %s %s', array($row['transaction_id'], $row['transaction_type'], $row['warehouse'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}

	public static function contact_orders_label($r) {
		if (!isset($r['id'])) return array();
	    if(!isset(self::$orders_rec)) {
		    $ret = Utils_RecordBrowserCommon::get_records_count('premium_warehouse_items_orders',array('contact'=>$r['id']));
		    if(!$ret)
			    return array('show'=>false);
	    }
	    return array('show'=>true, 'label'=>__('Warehouse Orders'));
	}

	public static function company_orders_label($r) {
		if (!isset($r['id'])) return array();
		if(!isset(self::$orders_rec)) {
			$ret = Utils_RecordBrowserCommon::get_records_count('premium_warehouse_items_orders',array('company'=>$r['id']));
			if(!$ret)
				return array('show'=>false);
		}
		return array('show'=>true, 'label'=>__('Warehouse Orders'));
	}

   	public static function display_notes($r) {
   		return Utils_AttachmentCommon::count('premium_warehouse_items_orders/'.$r['id']);
   	}

	public static function check_if_no_sold_serial_is_removed($data) {
		$ret = DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial s ON s.id=os.serial_id WHERE os.order_details_id=%d AND s.location_id IS NULL', array(self::$order_details_id));
		if ($data['quantity']<$ret) return array('quantity'=>'You can\'t remove already sold items');
		return true;
	}
	
	public static function display_shipment_no($r, $nolink=false) {
		if ($nolink) return $r['shipment_no'];
		return '<a target="_blank" href="http://www.packagetrackr.com/track/'.$r['shipment_no'].'">'.$r['shipment_no'].'</a>';
	}
	
	public static function display_serials(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		if (isset($rb_obj->record)) $record = $rb_obj->record;
		else $record = $rb_obj;
		if ($mode=='view' || !isset($record['item_name']) || !isset($record['id'])) {
			return;
		}
		self::$order_details_id = $record['id'];
		$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $record['item_name']);
		if ($item['item_type']!=1) return;

		$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $record['transaction_id']);
		switch (true) {
			case $trans['transaction_type']==0 && ($trans['status']!=20 && $trans['payment']): return false;
			case $trans['transaction_type']==1 && ($trans['status']<6 || $trans['status']==21): return false;
			case $trans['transaction_type']==4 && $trans['status']<5: return false;
		}

		print('<span style="display:none;" id="serial_form_main_label">'.__('Serial #').'</span>');
		print('<span style="display:none;" id="serial_form_note_label">'.__('Note for serial #').'</span>');
		print('<span style="display:none;" id="serial_form_shelf_label">'.__('Shelf for serial #').'</span>');
		$form->addElement('text', 'serial', __('Serial #'));
		if ($trans['transaction_type']==0) {
			$form->addFormRule(array('Premium_Warehouse_Items_OrdersCommon','check_if_no_sold_serial_is_removed'));
		}
		load_js('modules/Premium/Warehouse/Items/Orders/js/serials.js');
		eval_js('set_serials_based_on_quantity('.$record['id'].');');
		if ($trans['transaction_type']!=2)
			eval_js('Event.observe("quantity","keyup",function(){set_serials_based_on_quantity('.$record['id'].')});');
		else {
			eval_js('Event.observe("debit","keyup",function(){set_serials_based_on_quantity('.$record['id'].')});');
			eval_js('Event.observe("credit","keyup",function(){set_serials_based_on_quantity('.$record['id'].')});');
		}
	}
	
	public static function autoselect_item_name_suggestbox($str, $crits, $trans) {
		$trans_type = $trans['transaction_type'];
		$qry = array();
		$vals = array();
		$words = explode(' ', $str);
        $negative_qty = Variable::get('premium_warehouse_negative_qty', false);
		if ($negative_qty != 'all' && ($trans_type==1 || $trans_type==4) && $trans['status']>=2 && $trans['warehouse']) {
            $negative_query = ($negative_qty == 'selected') ? 'OR pwi.f_allow_negative_quantity = 1 ' : '';
			$qry[] = '(pwi.f_item_type>=%s ' . $negative_query . 'OR EXISTS (SELECT id FROM premium_warehouse_location_data_1 AS pwl WHERE pwl.f_quantity!=0 AND pwl.f_item_sku=pwi.id AND pwl.f_warehouse=%d))';
			$vals[] = 2;
			$vals[] = $trans['warehouse'];
		}
		foreach ($words as $w) {
			$str = DB::Concat(DB::qstr('%'), '%s', DB::qstr('%'));
            $fields_to_search = array('item_name', 'sku', 'product_code', 'manufacturer_part_number');
            foreach ($fields_to_search as & $field_name) {
                $field_name = "pwi.f_{$field_name} " . DB::like() . ' ' . $str;  // make f_name LIKE %%s% stmt
                $vals[] = $w;  // put value for every %s - yes we know that it's the same for all
            }
			$qry[] = '(' . implode(' OR ', $fields_to_search) . ')';  // merge with OR stmts
		}

		$my_warehouse = $trans['warehouse'];
		if (!$my_warehouse) $my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');

		$ret = DB::SelectLimit('SELECT * FROM premium_warehouse_items_data_1 AS pwi WHERE '.implode(' AND ',$qry).' AND active=1', 10, 0, $vals);

        $use_last_sale_or_purchase_price = Variable::get('premium_warehouse_use_last_price', false);
        $table_width = ($use_last_sale_or_purchase_price ? "900" : "800") . "px;";
		$header = '<table style="width: ' . $table_width . '" class="informal">'.
				'<tr>'.
					'<th width="300px;" align="center">'.
						__('Item Name').
					'</th>'.
					'<th  width="80px;" align="center">'.
						__('Item SKU').
					'</th>'.
                    '<th  width="80px;" align="center">'.
                        __('Man.Part.No.').
                    '</th>'.
					'<th width="80px;" align="center">'.
						__('Product Code').
					'</th>'.
					'<th width="60px;" align="center">'.
						__('QoH').
					'</th>'.
					'<th width="60px;" align="center">'.
						__('Qty. Res.').
					'</th>';
			
		if ($trans_type==0 || $trans_type==1) {
			$header .= 	'<th width="90px;" align="center">'.
						($trans_type==0?__('Cost'):__('Net Price')).
					'</th>';
            if ($use_last_sale_or_purchase_price) {
                $header .= '<th width="90px;" align="center">' .
                    ($trans_type == 0 ? __('Last Purchase Price') : __('Last Sale Price')) .
                    '</th>';
            }
        }
		$header .= 	'</tr>'.
			'</table>';

		$empty = true;
        $result = array();
		while ($row = $ret->FetchRow()) {
			if ($empty) $result[''] = $header;
			$qty = Premium_Warehouse_Items_LocationCommon::display_item_quantity_in_warehouse_and_total(array('id'=>$row['id'], 'item_type'=>$row['f_item_type']), $my_warehouse, true);
			$rqty = Premium_Warehouse_Items_OrdersCommon::display_reserved_qty(array('id'=>$row['id'], 'item_type'=>$row['f_item_type']), true);
			$price_col = '';
			$l = '<span style="display:none;">'.$row['id'].'__'.$row['f_item_name'].'</span><table style="width: ' . $table_width . '" class="informal">'.
				'<tr>'.
				'<td width="300px;">'.
					$row['f_item_name'].
				'</td>'.
				'<td width="80px;">'.
					$row['f_sku'].
				'</td>'.
                '<td width="80px;">'.
                    $row['f_manufacturer_part_number'].
                '</td>'.
				'<td width="80px;">'.
					$row['f_product_code'].
				'</td>'.
				'<td width="60px;" align="right">'.
					$qty.
				'</td>'.
				'<td width="60px;" align="right">'.
					$rqty.
				'</td>';
						
			if ($trans_type==0 || $trans_type==1) {
				$l .= 	'<td width="90px;" align="right">'.
							($price_col = Utils_CurrencyFieldCommon::format($trans_type==0?$row['f_cost']:$row['f_net_price'])).
						'</td>';
                if ($use_last_sale_or_purchase_price) {
                    $l .= '<td width="90px;" align="right">' .
                        Utils_CurrencyFieldCommon::format($trans_type == 0 ? $row['f_last_purchase_price'] : $row['f_last_sale_price']) .
                        '</td>';
                }
            }
				$l .= '</tr>'.
					'</table>';
			$empty = false;
			$result[$row['id'].'/0'] = $l;
                        if(ModuleManager::is_installed('Premium/Warehouse/DrupalCommerce')>=0 && $trans_type==1) {
                            $prices = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$row['id']));
                            foreach($prices as $price) {
                                $tax_rate = Data_TaxRatesCommon::get_tax_rate($price['tax_rate']);
                                $eprice_col = Utils_CurrencyFieldCommon::format($price['gross_price']*100/(100+$tax_rate),$price['currency']);
                                if($eprice_col == $price_col) continue;
                                $l = '<span style="display:none;">'.$row['id'].'/'.$price['id'].'__'.$row['f_item_name'].'</span><table style="width: ' . $table_width . '" class="informal">'.
				'<tr>'.
				'<td width="300px;">'.
					$row['f_item_name'].
				'</td>'.
				'<td width="80px;">'.
					$row['f_sku'].
				'</td>'.
                                '<td width="80px;">'.
                                    $row['f_manufacturer_part_number'].
                                '</td>'.
				'<td width="80px;">'.
					$row['f_product_code'].
				'</td>'.
				'<td width="60px;" align="right">'.
					$qty.
				'</td>'.
				'<td width="60px;" align="right">'.
					$rqty.
				'</td>';
				$l .= 	'<td width="90px;" align="right">'.
							$eprice_col.
						'</td>';
                                if ($use_last_sale_or_purchase_price) {
                                    $l .= '<td width="90px;" align="right">' .
                                    Utils_CurrencyFieldCommon::format($row['f_last_sale_price']) .
                                    '</td>';
                                }
				$l .= '</tr>'.
					'</table>';
	        		$result[$row['id'].'/'.$price['id']] = $l;
                            }
                        }
		}
		if (!empty($result))
			$result = '<ul style="width: ' . $table_width . '"><li>'.implode('</li><li>',$result).'</li></ul>';
        return $result;
    }
    
    public static function create_shipping_map_href($r) {
        return 'href="http://maps.'.(IPHONE?'apple.com/':'google.com/maps').'?'.http_build_query(array('q'=>$r['shipping_address_1'].' '.$r['shipping_address_2'].', '.$r['shipping_city'].', '.$r['shipping_postal_code'].', '.Utils_CommonDataCommon::get_value('Countries/'.$r['shipping_country']))).'" target="_blank"';
    }

    public static function shipping_maplink($r,$nolink,$desc) {
        if (!$nolink) return Utils_TooltipCommon::create('<a '.self::create_shipping_map_href($r).'>'.$r[$desc['id']].'</a>',__('Click here to search this location using google maps'));
        return $r[$desc['id']];
    }

    public static function display_negative_qty($record, $nolink, $desc)
    {
        $allow_negative = Variable::get('premium_warehouse_negative_qty', false);
        if (!$allow_negative) {
            return __('Not allowed');
        }
        if ($allow_negative == 'all') {
            $ret = true;
        } else {
            $ret = $record[$desc['id']];
        }
        return $ret = $ret ? __('Yes') : __('No');
    }

    public static function QFfield_negative_qty(&$form, $field, $label, $mode, $default, $desc, $rb_obj)
    {
        $allow_negative = Variable::get('premium_warehouse_negative_qty', false);
        if (!$allow_negative) {
            return;
        }
        if ($mode == 'edit' || $mode == 'add') {
            Utils_RecordBrowserCommon::QFfield_checkbox($form, $field, $label, $mode, $default, $desc, $rb_obj);
            if ($allow_negative == 'all') {
                $form->freeze($field);
                $form->setDefaults(array($field => 1));
            }
        } else {
            if ($allow_negative == 'all') {
                $default = 1;
            }
            Utils_RecordBrowserCommon::QFfield_checkbox($form, $field, $label, $mode, $default, $desc, $rb_obj);
        }
    }

    public static function QFfield_split_transaction(&$form, $field, $label, $mode, $default, $desc, $rb_obj)
    {
        if ($default) {
            $callback = Utils_RecordBrowserCommon::get_default_QFfield_callback($desc['type']);
            call_user_func_array($callback, array(&$form, $field, $label, $mode, $default, $desc, $rb_obj));
        }
    }

}
?>
