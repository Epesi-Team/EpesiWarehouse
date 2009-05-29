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
	
	public static function user_settings() {
		return array(	'Transaction'=>array(
				array('name'=>'my_transaction','label'=>'None','type'=>'hidden','default'=>'')
			),
						'Warehouse'=>array(
				array('name'=>'display_qty','label'=>'Quantity Display','type'=>'select','values'=>array(0=>'Availble', 1=>'On Hand', 2=>'Both'),'default'=>2)
			),
						'Warehouse'=>array(
				array('name'=>'filter_by_my_warehouse','label'=>'Filter items by my warehouse','type'=>'select','values'=>array(0=>'No', 1=>'Yes'),'default'=>1)
			));
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

	public static function company_crits(){
		return array('_no_company_option'=>true);
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
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'Warehouse', $v[$desc['id']], $nolink);
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
			$net_total = $price[0]*$rr['quantity'];
			$tax_value = round(Data_TaxRatesCommon::get_tax_rate($rr['tax_rate'])*$price[0]/100, Utils_CurrencyFieldCommon::get_precission($price[1]))*$rr['quantity'];
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
		if ($r['transaction_type']==2 || ($r['transaction_type']==3 && !$r['payment']))
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
		if ($r['transaction_type']==2 || ($r['transaction_type']==3 && !$r['payment']))
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

	public static function display_transaction_id($r, $nolink) {
		$ret = Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items_orders', 'transaction_id', $r, $nolink);
		if (!$nolink)	
			$ret = '<span '.Utils_TooltipCommon::ajax_open_tag_attrs(array('Premium_Warehouse_Items_OrdersCommon','item_list_tooltip'), $r).'>'.$ret.'</span>';
		return $ret;
	}
	
	public static function item_list_tooltip($r) {
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$r['id']));
		if (empty($items)) return Base_LangCommon::ts('Premium_Warehouse_Items_Orders','There are no items saved in this transaction');
		$theme = Base_ThemeCommon::init_smarty();
		foreach ($items as $k=>$v) {
			$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $v['item_name']);
			$items[$k]['sku'] = $item['sku'];
			$items[$k]['item_name'] = $item['item_name'];
			$items[$k]['net_price'] = Utils_CurrencyFieldCommon::format($v['net_price']);
			$items[$k]['gross_price'] = Utils_CurrencyFieldCommon::format($v['gross_price']);
			$items[$k]['tax'] = Data_TaxRatesCommon::get_tax_name($v['tax_rate']);
		}
		$theme->assign('header', array(
			'sku'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders','SKU'),
			'item_name'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Item Name'),
			'quantity'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Qty'),
			'net_price'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Net Price'),
			'tax'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Tax'),
			'gross_price'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Gross Price')
			));
		$theme->assign('items', $items);
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Items_Orders','item_list_tooltip');
	}
	
	public static function display_transaction_type($r, $nolink) {
		return Utils_CommonDataCommon::get_value('Premium_Items_Orders_Trans_Types/'.Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $r['transaction_id'], 'transaction_type'),true);	
	}
	
	public static function display_transaction_status($r, $nolink) {
		$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $r['transaction_id']);
		return self::display_status($trans);	
	}
	
	public static function display_transaction_date($r, $nolink) {
		return Base_RegionalSettingsCommon::time2reg(Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $r['transaction_id'], 'transaction_date'), false);	
	}
	
	public static function display_transaction_warehouse($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'warehouse', Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $r['transaction_id'], 'warehouse'), $nolink);	
	}
	
	public static function display_transaction_id_in_details($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items_orders', 'transaction_id', $r['transaction_id'], $nolink);	
	}

	public static function display_order_details_total($r, $nolink) {
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$ret = $r['quantity']*$price[0];
		return Utils_CurrencyFieldCommon::format($ret, $price[1]);
	}

	public static function display_order_details_tax_value($r, $nolink) {
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$ret = round(Data_TaxRatesCommon::get_tax_rate($r['tax_rate'])*$price[0]/100, Utils_CurrencyFieldCommon::get_precission($price[1]))*$r['quantity'];
		return Utils_CurrencyFieldCommon::format($ret, $price[1]);
	}

	public static function display_order_details_gross_price($r, $nolink) {
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$ret = round((100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))*$price[0]/100, Utils_CurrencyFieldCommon::get_precission($price[1]))*$r['quantity'];
		return Utils_CurrencyFieldCommon::format($ret, $price[1]);
	}
	
	public static function display_gross_price($r, $nolink) {
		$price = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		$ret = round((100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))*$price[0]/100, Utils_CurrencyFieldCommon::get_precission($price[1]));
		return Utils_CurrencyFieldCommon::format($ret, $price[1]);
	}
	
	public static function get_reserved_qty($item_id) {
		$trans = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('status'=>array(2,3,4,5), 'transaction_type'=>1), array('id', 'warehouse'));
		$trans = $trans+Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('status'=>array(2,3), 'transaction_type'=>4), array('id', 'target_warehouse'));
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
			if (!$warehouse) continue;
			if (!isset($reserved_qty[$warehouse])) $reserved_qty[$warehouse] = 0;
			$reserved_qty[$warehouse] += $i['quantity'];
			$qty+=$i['quantity'];
		}
		return array('per_warehouse'=>$reserved_qty, 'total'=>$qty);
	}
	
	public static function display_reserved_qty($r, $nolink) {
		$qty = self::get_reserved_qty($r['id']);
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		$ret = Premium_Warehouse_Items_LocationCommon::display_item_quantity_in_warehouse_and_total($r,$my_warehouse,$nolink,$qty['per_warehouse'],array('main'=>'Reserved Qty', 'in_one'=>'In %s', 'in_all'=>'Total'));
		return $ret;
	}
	
	public static function display_available_qty($r, $nolink) {
		$qty = self::get_reserved_qty($r['id']);
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse');
		foreach ($warehouses as $w) {
			$l_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('warehouse','item_sku'), array($w['id'], $r['id']));
			if (isset($qty['per_warehouse'][$w['id']])) $minus = $qty['per_warehouse'][$w['id']];
			else $minus = 0;  
			$qty['per_warehouse'][$w['id']] = $l_id?(Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $l_id, 'quantity') - $minus):-$minus;
		}
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		$ret = Premium_Warehouse_Items_LocationCommon::display_item_quantity_in_warehouse_and_total($r,$my_warehouse,$nolink,$qty['per_warehouse'],array('main'=>'Available Qty', 'in_one'=>'In %s', 'in_all'=>'Total'));
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
		return Premium_Warehouse_Items_LocationCommon::display_item_quantity_in_warehouse_and_total($r,$my_warehouse,$nolink,$en_route_qty,array('main'=>'Quantity En Route', 'in_one'=>'to %s', 'in_all'=>'Total'));
	}

	public static function get_status_array($trans, $payment=null) {
		switch ($trans['transaction_type']) {
			// PURCHASE
			case 0: $opts = array(''=>'New', 1=>'Purchase Quote', 2=>'Purchase Order', 3=>'New Shipment', 4=>'Shipment Received', 5=>'On Hold', 20=>'Delivered', 21=>'Canceled'); break;
			// SALE
			case 1: $payment_ack = 'Payment Confirmed';
					if (isset($trans['terms']) && $trans['terms']>0) $payment_ack = 'Payment Approved';
					$opts = array(''=>'New', -1=>'New Online Order', 1=>'Sales Quote', 2=>'Order Received', 3=>$payment_ack, 4=>'Order Confirmed', 5=>'On Hold', 6=>'Order Ready to Ship', 7=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing'); break;
			// INV. ADJUSTMENT
			case 2: $opts = array(''=>'Active', 20=>'Completed'); break;
			// RENTAL
			case 3: if ($payment===true || ($payment===null && isset($trans['payment']) && $trans['payment']))
						$opts = array(''=>'Rental order', 1=>'Create picklist', 2=>'Check payment', 3=>'Process picklist', 4=>'Payment', 5=>'Items rented', 6=>'Partially returned', 20=>'Completed', 21=>'Completed (Items lost)');
					else
						$opts = array(''=>'Create picklist', 1=>'Items rented', 2=>'Partially returned', 20=>'Completed', 21=>'Completed (Items lost)');
					break;
			// WAREHOUSE TRANSFER
			case 4: $opts = array(''=>'New', 1=>'Transfer Quote', 2=>'Pending', 3=>'Order Fullfilment', 4=>'On Hold', 5=>'Ready to Ship', 6=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing'); break;
		}
		foreach ($opts as $k=>$v)
			$opts[$k] = Base_LangCommon::ts('Premium_Warehouse_Items_Orders',$v);
		return $opts;
	}

	public static function display_status($r, $nolink=false){
		$opts = self::get_status_array($r);
		return $opts[$r['status']];
	}

	public static function QFfield_status(&$form, $field, $label, $mode, $default, $desc, $rb_obj){
		if ($mode!='view' && (Utils_RecordBrowser::$last_record['transaction_type']==0 || Utils_RecordBrowser::$last_record['transaction_type']==1)) {
			load_js('modules/Premium/Warehouse/Items/Orders/contractor_update.js');
			eval_js('new ContractorUpdate()');
		}
		$opts = self::get_status_array($rb_obj->record);
		if ($mode=='edit') {
			$form->addElement('select', $field, $label, $opts, array('id'=>'status'));
			$form->setDefaults(array($field=>$default));
		} else {
			$obj = $rb_obj->init_module('Premium/Warehouse/Items/Orders');
			$rb_obj->display_module($obj, array(Utils_RecordBrowser::$last_record, $default), 'change_status_leightbox');
			$href = $obj->get_href();
			if ($href) $label2 = '<a '.$href.'>'.$opts[$default].'</a>';
			else $label2 = $opts[$default];
			$form->addElement('static', $field, $label, $label2);
		}
	}
	
	public static function QFfield_net_price(&$form, $field, $label, $mode, $default, $desc, $rb_obj){
		if ($mode!=='view') {
			Premium_Warehouse_ItemsCommon::init_net_gross_js_calculation($form, 'tax_rate', 'net_price', 'gross_price');
			$form->addElement('currency', $field, $label, array('id'=>$field));
			$form->setDefaults(array($field=>$default, 'use_net_price'=>1));
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
			$form->addElement('currency', $field, $label, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
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
		$item_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'item_name', $data['item_name']);
		if (!is_numeric($item_id)) return array('item_name'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Item not found'));
		$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item_id);
		$item['last_purchase_price'] = Utils_CurrencyFieldCommon::get_values($item['last_purchase_price']);
		$sale_price = implode('.',explode(Utils_CurrencyFieldCommon::get_decimal_point(), $data['net_price']));
		if (!$item['last_purchase_price'][0]) return true;
		if ($item['last_purchase_price'][1]!=$data['__net_price__currency']) return true;
		if ($sale_price<$item['last_purchase_price'][0]) return array('net_price'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Error! Price too low.'));
		return true;
	}
	
	public static function QFfield_item_name(&$form, $field, $label, $mode, $default){
		self::get_trans();
		if (self::$trans['transaction_type']==2) {
			eval_js('$("'.Utils_RecordBrowserCommon::get_calculated_id('premium_warehouse_items_orders_details', 'debit', null).'").innerHTML="";');
			eval_js('$("'.Utils_RecordBrowserCommon::get_calculated_id('premium_warehouse_items_orders_details', 'credit', null).'").innerHTML="";');
			$form->addElement('text','order_details_credit_or_debit','None');
			$form->addElement('text','order_details_debit','None');
			$form->addElement('text','order_details_credit','None');
		}
		if ($mode=='add' || $mode=='edit') {
			if (self::$trans['transaction_type']==1) {
				$decp = Utils_CurrencyFieldCommon::get_decimal_point();
				load_js('modules/Premium/Warehouse/Items/Orders/check_item_price_cost.js');
				$msg = 'Warning';
				$sell_with_loss = self::Instance()->acl_check('sell with loss');
				if (!$sell_with_loss) {
					$msg = 'Error';
					$form->addFormRule(array('Premium_Warehouse_Items_OrdersCommon','check_sale_price'));
				}
				$warning = Base_LangCommon::ts('Premium_Warehouse_Items_Orders',$msg.': Sale price is lower than the last purchase price!');
				$form->addElement('button', 'submit', Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Submit'), array('onclick'=>'if(check_item_price_cost_difference("'.$decp.'","'.$warning.'","'.((int)(!$sell_with_loss)).'")){'.$form->get_submit_form_js().'};'));
				$form->addElement('hidden', 'last_item_price', '', array('id'=>'last_item_price'));
			}
			$form->addElement('text', $field, $label, array('id'=>$field));
			print('<div id="'.$field.'_suggestbox" class="autocomplete">&nbsp;</div>');
			load_js('modules/Premium/Warehouse/Items/Orders/item_autocomplete.js');
			eval_js('var item_autocompleter = new warehouse_itemAutocompleter(\''.$field.'\', \''.$field.'_suggestbox\', \'modules/Premium/Warehouse/Items/Orders/item_name_autocomplete.php?'.http_build_query(array('cid'=>CID, 'transaction_id'=>self::$trans['id'])).'\', \'\', '.self::$trans['id'].');');
			if (isset($default) && is_numeric($default)) $form->setDefaults(array($field=>Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$default,'item_name')));
			$form->addFormRule(array('Premium_Warehouse_Items_OrdersCommon','check_qty_on_hand'));
			eval_js('if($("item_name").value=="")focus_by_id("item_name");');
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>self::display_item_name(array('item_name'=>$default), null, array('id'=>'item_name'))));
		}
	}

	public static function QFfield_company_name(&$form, $field, $label, $mode, $default){
		if ($mode=='add' || $mode=='edit') {
			$form->addElement('text', $field, $label);
			if ($mode=='edit') $form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>self::display_company_name(array('company_name'=>$default), null, array('id'=>'company_name'))));
		}
	}

	public static function QFfield_quantity(&$form, $field, $label, $mode, $default){
		if ($mode=='add' || $mode=='edit') {
			$form->addElement('text', $field, $label, array('id'=>$field));
			if ($mode=='edit') $form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>$default));
		}
	}

	public static function check_qty_on_hand($data){
		self::get_trans();
		if (isset($data['quantity']) && intval($data['quantity'])!=$data['quantity']) return array('item_name'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Invallid amount.'));
		if (self::$trans['transaction_type']==0) return true;
		if (!isset($data['item_name'])) {
			$data['item_name'] = Utils_RecordBrowser::$last_record['item_name'];
		} else { 
			if (!is_numeric($data['item_name']))
				$data['item_name'] = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'item_name', $data['item_name']);
			if (!is_numeric($data['item_name'])) return array('item_name'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Item not found'));
		}
		$item_type = Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$data['item_name'],'item_type');
		if ($item_type>=2) return true;
//		if (self::$trans['transaction_type']==1) {
//			if ($data['quantity']<=0) return array('quantity'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Invallid amount.'));
//			$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$data['item_name'],'warehouse'=>self::$trans['warehouse'],'!quantity'=>0));
//			$location_id = array_shift($location_id);
//			if (!isset($location_id) || !$location_id) {
//				return array('quantity'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Error. Please contact system administrator.'));
//			}
//			if ($data['quantity']>$location_id['quantity']) return array('quantity'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Amount not available'));
//		}
		if (self::$trans['transaction_type']==2) {
			if (!isset($data['order_details_debit'])) return true;
			if ($data['order_details_debit']<0 ||
				$data['order_details_credit']<0 ||
				($data['order_details_debit']==0 && $data['order_details_credit']==0)) return array('item_name'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Invallid amount.'));
			if ($data['order_details_debit']>0) {
				$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$data['item_name'],'warehouse'=>self::$trans['warehouse'],'!quantity'=>0));
				$location_id = array_shift($location_id);
				if (!isset($location_id) || !$location_id) {
					return array('item_name'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Error. Please contact system administrator.'));
				}
				if ($data['order_details_debit']>$location_id['quantity']) return array('item_name'=>Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Amount not available'));
			}
		}
			return true;
	} 
	
	public static function access_order_details($action, $param, $action_details=null){
		$i = self::Instance();
		switch ($action) {
			case 'browse':	return $i->acl_check('browse orders');
			case 'view':	if($i->acl_check('view orders')) return true;
							return false;
			case 'add':
			case 'edit':	return ($i->acl_check('edit orders') && self::access_orders('edit', Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $param['transaction_id'])));
			case 'delete':	if (Acl::get_user()==$param['created_by']) return true;
							return $i->acl_check('delete orders');
			case 'fields':	$ret = array();
/*							if ($action_details=='new' && isset($param['item_sku'])) 
								$ret = array(	'transaction_id'=>'read-only', 
												'item_sku'=>'read-only',
												'transaction_type'=>'hide',
												'transaction_date'=>'hide',
												'warehouse'=>'hide',
												'net_total'=>'hide',
												'tax_value'=>'hide',
												'gross_total'=>'hide',
												'quantity_on_hand'=>'hide',
												'quantity'=>$param['single_pieces']?'read-only':'full');*/
							if (isset($param['transaction_id']) && is_numeric($param['transaction_id']))
								$trans_id = $param['transaction_id'];
							elseif (isset($action_details['transaction_id']) && is_numeric($action_details['transaction_id']))
								$trans_id = $action_details['transaction_id'];
							if (!isset($trans_id)) return array();
							$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $trans_id);
							if (is_array($param)) {
								$ret = array('item_name'=>'read-only','transaction_id'=>'read-only');
							}
							if ($trans['transaction_type']!=3) {
								$ret['return_date'] = 'hide';
								$ret['returned'] = 'hide';
							}
							if ($trans['transaction_type']==3) {
								$ret['transaction_date'] = 'hide';
								$ret['transaction_type'] = 'hide';
								$ret['warehouse'] = 'hide';
								$ret['debit'] = 'hide';
								$ret['credit'] = 'hide';
								$ret['net_price'] = 'hide';
								$ret['net_total'] = 'hide';
								$ret['tax_rate'] = 'hide';
								$ret['tax_value'] = 'hide';
								$ret['gross_total'] = 'hide';
								$ret['quantity_on_hand'] = 'hide';
								$ret['returned'] = 'read-only';
							}
							if ($trans['transaction_type']!=2) {
								$ret['credit'] = 'hide';
								$ret['debit'] = 'hide';
							} else {
								$ret['quantity'] = 'hide';
							}
							return $ret;
		}
		return false;
	}
	public static function access_orders($action, $param, $action_details=null){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse orders');
			case 'view':	if($i->acl_check('view orders')) return true;
							return false;
			case 'edit':	if (!Base_AclCommon::i_am_admin() &&
								($param['status']>=20
								|| ($param['status']>=2 && $param['transaction_type']==0)
								|| ($param['status']>=3 && $param['transaction_type']==1)
								) &&
								(time()-strtotime($param['transaction_date']) > 60*60*24*7 ||
								Acl::get_user()!=$param['created_by']))
								return false;
							return $i->acl_check('edit orders');
			case 'delete':	if (Acl::get_user()==$param['created_by']) return true;
							return $i->acl_check('delete orders');
			case 'fields':	$ret = array();
							$ret['status'] = 'read-only';
							$tt = $param['transaction_type'];
							if (is_array($param)) {
								if (($tt!=0 && $tt!=1) || (isset($param['status']) && $param['status']>=2)) $ret = array('warehouse'=>'read-only');
								if ($action_details=='view') {
									$ret['company'] = 'hide';
									$ret['contact'] = 'hide';
								}
							}
							if ($action_details=='new')
								$ret = array('status'=>'hide');
							$ret['transaction_type'] = 'read-only';
							if ($tt==3 && $action_details!='view') {
								$opts_pay = self::get_status_array($param,true);
								$opts_no_pay = self::get_status_array($param,false);
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
							if ($param!='browse') {
								if ($tt!=3) {
									$ret['payment'] = 'hide';
									$ret['return_date'] = 'hide';
								}
								if ($tt==3 && isset($param['payment']) && !$param['payment'] && $action_details=='view') {
									$ret['payment_type'] = 'hide';
									$ret['payment_no'] = 'hide';
									$ret['shipment_type'] = 'hide';
									$ret['shipment_no'] = 'hide';
									$ret['terms'] = 'hide';
									$ret['total_value'] = 'hide';
									$ret['tax_value'] = 'hide';
								}
								if ($tt==2 || $tt==4) {
									$ret['company'] = 'hide';
									$ret['contact'] = 'hide';
									$ret['tax_id'] = 'hide';
									$ret['company_name'] = 'hide';
									$ret['first_name'] = 'hide';
									$ret['last_name'] = 'hide';
									$ret['address_1'] = 'hide';
									$ret['address_2'] = 'hide';
									$ret['city'] = 'hide';
									$ret['country'] = 'hide';
									$ret['zone'] = 'hide';
									$ret['postal_code'] = 'hide';
									$ret['phone'] = 'hide';
									$ret['payment_type'] = 'hide';
									$ret['payment_no'] = 'hide';
									$ret['terms'] = 'hide';
									$ret['total_value'] = 'hide';
									$ret['tax_value'] = 'hide';
								}
								if ($tt!=4) {
									$ret['target_warehouse'] = 'hide';
								}
								if ($tt==4) {
									$ret['tax_value'] = 'hide';
								}
								if (isset($param['shipment_type']) && $param['shipment_type']==0) {
									$ret['shipment_date'] = 'hide';
									$ret['shipment_no'] = 'hide';
									$ret['shipment_employee'] = 'hide';
									$ret['shipment_eta'] = 'hide';
									$ret['shipment_cost'] = 'hide';
									$ret['tracking_info'] = 'hide';
								}
								if (isset($param['payment_type']) && $param['payment_type']==0) {
									$ret['payment_no'] = 'hide';
								}
								if ($tt==0 && isset($param['status'])) {
									if ($param['status']<4) {
										$ret['shipment_date'] = 'hide';
										$ret['shipment_no'] = 'hide';
										$ret['shipment_employee'] = 'hide';
										$ret['shipment_eta'] = 'hide';
										$ret['shipment_cost'] = 'hide';
										$ret['handling_cost'] = 'hide';
										$ret['tracking_info'] = 'hide';
									}
									if ($param['status']<2) {
										$ret['payment_no'] = 'hide';
										$ret['payment_type'] = 'hide';
										$ret['shipment_type'] = 'hide';
										$ret['terms'] = 'hide';
										$ret['total_value'] = 'hide';
										$ret['tax_value'] = 'hide';
									} elseif ($param['status']<20) { 
										$ret['shipment_no'] = 'hide';
									}
									if ($param['status']!=1) $ret['expiration_date'] = 'hide';
								}
								if ($tt==1 && isset($param['status'])) {
									if ($param['status']<7) {
										$ret['shipment_date'] = 'hide';
										$ret['shipment_employee'] = 'hide';
										$ret['shipment_no'] = 'hide';
									}
									if ($param['status']<2) {
										$ret['payment_no'] = 'hide';
										$ret['payment_type'] = 'hide';
										$ret['shipment_type'] = 'hide';
										$ret['terms'] = 'hide';
										$ret['total_value'] = 'hide';
										$ret['tax_value'] = 'hide';
									}
									if ($param['status']!=1) $ret['expiration_date'] = 'hide';
								}
							}
							return $ret;
		}
		return false;
    }

	public static function access_items($action, $param, $defaults){
		$i = Premium_Warehouse_ItemsCommon::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse items');
			case 'view':	if($i->acl_check('view items')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit items');
			case 'delete':	return $i->acl_check('delete items');
			case 'fields':	if ($param['item_type']==2 || $param['item_type']==3) return array('reorder_point'=>'hide','quantity_on_hand'=>'hide','item_type'=>'read-only','upc'=>'hide','manufacturer_part_number'=>'hide', 'quantity_en_route'=>'hide');
							return array('quantity_on_hand'=>'read-only','item_type'=>'read-only','quantity_sold'=>'hide');
		}
		return false;
    }

    public static function menu() {
		return array('Warehouse'=>array('__submenu__'=>1,'Items: Transactions'=>array()));
	}

	public static function applet_caption() {
		return 'Items Orders';
	}
	public static function applet_info() {
		return 'List of Orders on Items';
	}

	public static function applet_info_format($r){
		return
			'Item: '.$r['item'].'<HR>'.
			'Operation: '.$r['operation'].'<br>'.
			'Quantity: '.$r['quantity'].'<br>'.
			'Description: '.$r['description'];
	}

	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse_items_orders',
				Base_LangCommon::ts('Premium_Warehouse_Items_Orders','Orders'),
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

	public static function change_total_qty(& $details, $action=null, $force_change=false) {
		$item_id = $details['item_name'];
		$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $details['transaction_id']);
		if ($order['transaction_type']==3 && $details['returned'] && ($action=='delete' || $action=='restore')) return;
		$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item_id);
//		$new_qty = $item['quantity_on_hand'];

		if ($order['transaction_type']==0 && $order['status']!=20 && $action!='change_delivered' && !$force_change) return;
		if ($order['transaction_type']==1 && ($order['status']>20 || $order['status']<6) && $action!='change_delivered' && !$force_change) return;

		if ($order['transaction_type']==0 && $action=='change_delivered') $action=($order['status']!=20?'add':'delete'); 
		if ($order['transaction_type']==1 && $action=='change_delivered') $action=(in_array($order['status'], array(6,7,20,22))?'delete':'add');			
					
		if ($action!=='add') $old_details = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders_details', $details['id']);
		if ($action!=='add' && $action!=='restore') {
			$location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($item_id,$order['warehouse']));
			if ($order['transaction_type']==4) {
				if ($action!='change_delivered') {
					if ($order['status']!=21 && $order['status']>=5) {
						if ($location_id===false || $location_id===null)
							$location_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$order['warehouse'], 'quantity'=>$old_details['quantity']));
						else {
							$new_loc_qty = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $location_id, 'quantity')+$old_details['quantity'];
							Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $location_id, array('quantity'=>$new_loc_qty));
						}
//						$new_qty = $new_qty+$old_details['quantity'];
					}
					if ($order['status']==20) { 
						$target_location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($item_id,$order['target_warehouse']));
						if ($target_location_id===false || $target_location_id===null)
							$target_location_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$order['target_warehouse'], 'quantity'=>-$old_details['quantity']));
						else {
							$new_loc_qty = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $target_location_id, 'quantity')-$old_details['quantity'];
							Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $target_location_id, array('quantity'=>$new_loc_qty));
						}
//						$new_qty = $new_qty-$old_details['quantity'];
					}
				}
			} else {
				if ($order['transaction_type']==1 || $order['transaction_type']==3) $mult = -1;
				else $mult = 1;
//				$new_qty = $new_qty-$old_details['quantity']*$mult;
				if ($location_id===false || $location_id===null)
					$location_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$order['warehouse'], 'quantity'=>-$old_details['quantity']*$mult));
				else {
					$new_loc_qty = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $location_id, 'quantity')-$old_details['quantity']*$mult;
					Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $location_id, array('quantity'=>$new_loc_qty));
				}
			}
		}
		if ($order['transaction_type']==1 || $order['transaction_type']==3) $mult = -1;
		else $mult = 1;
		if ($action!=='delete') {
			$location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($item_id,$order['warehouse']));
			if ($order['transaction_type']==4) {
				if (($order['status']!=21 && $order['status']>=5 && $action!='change_delivered') || ($order['status']<5 && self::$new_status>=5 && self::$new_status!=21)) {
					if ($location_id===false || $location_id===null)
						$location_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$order['warehouse'], 'quantity'=>-$details['quantity']));
					else {
						$new_loc_qty = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $location_id, 'quantity')-$details['quantity'];
						Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $location_id, array('quantity'=>$new_loc_qty));
					}
//					$new_qty = $new_qty-$details['quantity'];
				}
				if (($order['status']==20 && $action!='change_delivered') || ($order['status']!=20 && self::$new_status==20)) { 
					$target_location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($item_id,$order['target_warehouse']));
					if ($target_location_id===false || $target_location_id===null)
						$target_location_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$order['target_warehouse'], 'quantity'=>$details['quantity']));
					else {
						$new_loc_qty = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $target_location_id, 'quantity')+$details['quantity'];
						Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $target_location_id, array('quantity'=>$new_loc_qty));
					}
//					$new_qty = $new_qty+$details['quantity'];
				}
			} else {
//				$new_qty = $new_qty+$details['quantity']*$mult;
				if ($location_id===false ||$location_id===null)
					Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$order['warehouse'], 'quantity'=>+$details['quantity']*$mult));
				else {
					$new_loc_qty = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $location_id, 'quantity')+$details['quantity']*$mult;
					Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $location_id, array('quantity'=>$new_loc_qty));
				}
			}
		}
//		Utils_RecordBrowserCommon::update_record('premium_warehouse_items', $item_id, array('quantity_on_hand'=>$new_qty));
	}
	
	public static function submit_order($values, $mode) {
		switch ($mode) {
			case 'cloned':
				$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details',array('transaction_id'=>$values['original']));
				foreach ($recs as $r) {
					$r['transaction_id'] = $values['clone'];
					Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details',$r);
				}
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$values['clone'],array('status'=>''));
				return;
			case 'adding':
				if ($mode!='view' && ($values['transaction_type']==0 || $values['transaction_type']==1)) {
					load_js('modules/Premium/Warehouse/Items/Orders/contractor_update.js');
					eval_js('new ContractorUpdate()');
				}
			case 'editing':
				return array('transaction_type'=>$values['transaction_type']);
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
				$active = (Base_User_SettingsCommon::get('Premium_Warehouse_Items_Orders','my_transaction')==$values['id']);
				if (!Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',$values)) {
					if ($active) Base_User_SettingsCommon::save('Premium_Warehouse_Items_Orders','my_transaction','');
					return;
				}
				if (isset($_REQUEST['premium_warehouse_change_active_order']) && $_REQUEST['premium_warehouse_change_active_order']===$values['id']) {
					Base_User_SettingsCommon::save('Premium_Warehouse_Items_Orders','my_transaction',$active?'':$values['id']);
					$active = !$active;
				}
				if ($active) {
					$icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Orders','deactivate.png');
					$label = Base_LangCommon::ts('Utils_Watchdog','Leave this trans.');
				} else {
					$icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Orders','activate.png');
					$label = Base_LangCommon::ts('Utils_Watchdog','Use this Trans.');
				}
				Base_ActionBarCommon::add($icon,$label,Module::create_href(array('premium_warehouse_change_active_order'=>$values['id'])));
				return;
			case 'add':
				return $values;
			case 'edit':
				$access = self::access_orders('fields', $values);
				if (isset($access['warehouse']) && $access['warehouse']=='read-only' && !$values['warehouse']) $values['status'] = Utils_RecordBrowserCommon::get_value('premium_warehouse_items_orders', $values['id'], 'status');
				$values['transaction_id'] = self::generate_id($values['id']);
				$old_values = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $values['id']);
				self::$new_status = $values['status']; 
				if (($values['status']-$old_values['status']!=0 && (20-$values['status'])*(20-$old_values['status'])==0 && $values['transaction_type']==0) ||
					((in_array($values['status'], array(6,7,20,22)) xor in_array($old_values['status'], array(6,7,20,22))) && $values['transaction_type']==1) ||
					($values['transaction_type']==4)) {
					$det = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$values['id']));
					foreach ($det as $d)
						self::change_total_qty($d, 'change_delivered', true);
				}
				break;
			case 'added':
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$values['id'],array('transaction_id'=>self::generate_id($values['id'])), false, null, true);
				Base_User_SettingsCommon::save('Premium_Warehouse_Items_Orders','my_transaction',$values['id']);
		}
		return $values;
	}

	public static function submit_order_details($values, $mode) {
		static $notice='';
		if ($notice!=='') print($notice);
		if (isset($values['item_name']) && !is_numeric($values['item_name']))
			$values['item_name'] = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'item_name', $values['item_name']);
		switch ($mode) {
			case 'adding':
				self::$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $values['transaction_id']);
				return;
			case 'delete':
				self::change_total_qty($values, 'delete');
				location(array());
				return;
			case 'restore':
				self::change_total_qty($values, 'restore');
				return;
			case 'add':
				$net = Utils_CurrencyFieldCommon::get_values($values['net_price']);
				$gross = Utils_CurrencyFieldCommon::get_values($values['gross_price']);
				$tax_rate = Data_TaxRatesCommon::get_tax_rate($values['tax_rate']);
				if (round($net[0]*(100+$tax_rate)/100,2)!=$gross[0]) {
					$values['gross_price'] = round($net[0]*(100+$tax_rate)/100,2).'__'.$net[1];
					$new_gross = Utils_CurrencyFieldCommon::get_values($values['gross_price']);
					$notice = Base_LangCommon::ts('Premium_Warehouse_Items_Orders', '<font color="red"><b>Notice:</b></font> No gross price is worth %s including %s%% tax.<br />Gross price was adjusted to %s, based on net price', array(
						Utils_CurrencyFieldCommon::format($gross[0], $gross[1]),
						$tax_rate,
						Utils_CurrencyFieldCommon::format($new_gross[0], $new_gross[1]),
						));
				}
				$item_type=Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $values['item_name'], 'item_type');
				if (self::$trans['transaction_type']==3) {
//					Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $values['serial'], array('used'=>1));
				}
				if (self::$trans['transaction_type']==2) {
					if ($values['order_details_debit']) $values['quantity']=-$values['order_details_debit'];
					else $values['quantity']=$values['order_details_credit'];
				}
				if (self::$trans['transaction_type']<2) {
					Utils_RecordBrowserCommon::update_record('premium_warehouse_items', $values['item_name'], array(self::$trans['transaction_type']==0?'last_purchase_price':'last_sale_price'=>$values['net_price']));
				}
				self::change_total_qty($values, 'add');
				return $values;
			case 'view':
				return;
			case 'edit':
				self::change_total_qty($values, 'edit');
				return $values;
			case 'added':
				location(array());
		}
		return $values;
	}

	public static function search_format($id) {
		if(Acl::check('Premium_Warehouse_Items_Orders','browse orders')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items_orders', $row['id']).Base_LangCommon::ts('Premium_Warehouse_Items_Orders', 'Item order (attachment) #%d, %s %s', array($row['transaction_id'], $row['transaction_type'], $row['warehouse'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}
}
?>
