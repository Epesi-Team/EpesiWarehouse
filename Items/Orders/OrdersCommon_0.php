<?php
/**
 * Warehouse - Items Orders
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_OrdersCommon extends ModuleCommon {
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
		return Utils_RecordBrowserCommon::record_link_open_tag('company', $v['company'], $nolink).$v[$desc['id']].Utils_RecordBrowserCommon::record_link_close_tag();
	}

    public static function display_first_name($v, $nolink=false, $desc=null) {
		return Utils_RecordBrowserCommon::record_link_open_tag('contact', $v['contact'], $nolink).$v[$desc['id']].Utils_RecordBrowserCommon::record_link_close_tag();
	}

    public static function display_last_name($v, $nolink=false, $desc=null) {
		return Utils_RecordBrowserCommon::record_link_open_tag('contact', $v['contact'], $nolink).$v[$desc['id']].Utils_RecordBrowserCommon::record_link_close_tag();
	}

    public static function display_warehouse($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'Warehouse', $v['warehouse'], $nolink);
	}

    public static function display_item_name($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items', 'SKU', $v['item_sku'], $nolink);
	}

	public static function display_transaction_id($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_items_orders', 'transaction_id', $r, $nolink);	
	}
	
	public static function display_transaction_id_in_details($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items_orders', 'transaction_id', $r['transaction_id'], $nolink);	
	}

	public static function display_order_details_tax($r, $nolink) {
		return Utils_CommonDataCommon::get_value('Premium_Warehouse_Items_Tax/'.Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$r['item_sku'],'tax'),true);
	}
	
	public static function display_order_details_total($r, $nolink) {
		$ret = $r['quantity']*$r['price'];
		$ret *= (100+Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$r['item_sku'],'tax'));
		$ret /= 100;
		return Utils_CurrencyFieldCommon::format($ret);
	}

	public static function display_order_details_tax_value($r, $nolink) {
		$ret = $r['quantity']*$r['price'];
		$ret *= (100+Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$r['item_sku'],'tax'));
		$ret /= 100;
		return Utils_CurrencyFieldCommon::format($ret);
	}

	public static function display_order_details_gross_price($r, $nolink) {
		$ret = $r['quantity']*$r['price'];
		$ret *= (100+Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$r['item_sku'],'tax'));
		$ret /= 100;
		return Utils_CurrencyFieldCommon::format($ret);
	}

	public static function display_order_details_qty($r, $nolink) {
		return Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$r['item_sku'],'quantity');
	}
	
	public static function QFfield_company_name(&$form, $field, $label, $mode, $default){
		if ($mode=='add' || $mode=='edit') {
			$form->addElement('text', $field, $label);
			if ($mode=='edit') $form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label);
			$form->setDefaults(array($field=>self::display_company_name(array('company_name'=>$default), null, array('id'=>'email'))));
		}
	}

	public static function access_order_details($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse orders');
			case 'view':	if($i->acl_check('view orders')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit orders');
			case 'delete':	return $i->acl_check('delete orders');
			case 'fields':	if (is_array($param)) {
								$sp = (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $param['item_sku'], 'item_type')==1);
								return array($sp?'quantity':'serial'=>'hide', 'item_sku'=>'read-only','transaction_id'=>'read-only');
							}
							return array();
		}
		return false;
	}
	public static function access_orders($action, $param, $defaults){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse orders');
			case 'view':	if($i->acl_check('view orders')) return true;
							return false;
			case 'edit':	if ((isset($param['paid']) && $param['paid']) ||
								(isset($param['delivered']) && $param['delivered']))
								return false;
							return $i->acl_check('edit orders');
			case 'delete':	return $i->acl_check('delete orders');
			case 'fields':	$ret = array();
							if (is_array($param)) {
								$ret = array('transaction_type'=>'read-only','warehouse'=>'read-only','company'=>'hide','contact'=>'hide');
								$tt = $param['transaction_type'];
							}
							if ($param=='new') {
								$ret = array('transaction_type'=>'read-only','paid'=>'hide','delivered'=>'hide');
								if (!isset($defaults['transaction_type'])) trigger_error(serialize($defaults));
								$tt = $defaults['transaction_type'];
							}
							if (isset($tt) && $tt==2) {
								$ret['company'] = 'hide';
								$ret['contact'] = 'hide';
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
								$ret['paid'] = 'hide';
							}
							return $ret;
		}
		return false;
    }

	public static function access_items($action, $param){
		$i = Premium_Warehouse_ItemsCommon::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse items');
			case 'view':	if($i->acl_check('view items')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit items');
			case 'delete':	return $i->acl_check('delete items');
			case 'fields':	if ($param=='new') return array('quantity'=>'read-only');
							else return array('quantity'=>'read-only','item_type'=>'read-only');
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
				'item',
				$details
			);
	}
	
	public static function generate_id($id) {
		if (is_array($id)) $id = $id['id'];
		return '#'.str_pad($id, 6, '0', STR_PAD_LEFT);
	}

	public static function change_total_qty($details, $action=null, $force_change=false) {
		$item_id = $details['item_sku'];
		$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $details['transaction_id']);
		if ($order['transaction_type']==0 && $order['delivered']==0 && !$force_change) return;
		$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item_id);
		$new_qty = $item['quantity'];
		$sp = ($item['item_type']==1);
		if ($action!=='add') $old_details = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders_details', $details['id']);
		if (!$sp) $location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse'),array($item_id,$order['warehouse']));
		else {
			if ($action=='add') $location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse','serial'),array($item_id,$order['warehouse'],$details['serial']));
			else $location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse','serial'),array($item_id,$order['warehouse'],$old_details['serial']));
		}
		if ($action!=='add' && $action!=='restore') {
			if ($order['transaction_type']==1) $mult = -1;
			else $mult = 1;
			$new_qty = $new_qty-$old_details['quantity']*$mult;
			if ($location_id===false ||$location_id===null)
				$location_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$order['warehouse'], 'quantity'=>-$old_details['quantity']*$mult, 'serial'=>$old_details['serial']));
			else
				Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $location_id, array('quantity'=>Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $location_id, 'quantity')-$old_details['quantity']*$mult, 'serial'=>$old_details['serial']));
		}
		if ($order['transaction_type']==1) $mult = -1;
		else $mult = 1;
		if ($sp && $action!=='add') $location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location',array('item_sku','warehouse','serial'),array($item_id,$order['warehouse'],$details['serial']));
		if ($action!=='delete') {
			$new_qty = $new_qty+$details['quantity']*$mult;
			if ($location_id===false ||$location_id===null)
				Utils_RecordBrowserCommon::new_record('premium_warehouse_location', array('item_sku'=>$item_id, 'warehouse'=>$order['warehouse'], 'quantity'=>+$details['quantity']*$mult, 'serial'=>$details['serial']));
			else
				Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $location_id, array('quantity'=>Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $location_id, 'quantity')+$details['quantity']*$mult, 'serial'=>$details['serial']));
		}
		Utils_RecordBrowserCommon::update_record('premium_warehouse_items', $item_id, array('quantity'=>$new_qty));
	}
	
	public static function submit_order($values, $mode) {
		switch ($mode) {
			case 'adding':
			case 'editing':
				if ($values['transaction_type']!=2) {
					load_js('modules\Premium\Warehouse\Items\Orders\contractor_update.js');
					eval_js('new ContractorUpdate()');
				}
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
			case 'add':
				return $values;
			case 'view':
				return;
			case 'edit':
				$values['transaction_id'] = self::generate_id($values['id']);
				$old_values = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $values['id']);
				if ($old_values['delivered']!=$values['delivered']) {
					$det = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$values['id']));
					foreach ($det as $d)
						self::change_total_qty($d, $values['delivered']?'add':'delete', true);
				}
				break;
			case 'added':
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$values['id'],array('transaction_id'=>self::generate_id($values['id'])), false, null, true);
		}
		return $values;
	}

	public static function submit_order_details($values, $mode) {
		switch ($mode) {
			case 'adding':
				load_js('modules\Premium\Warehouse\Items\Orders\item_details_update.js');
				eval_js('new ItemDetailsUpdate()');
				return;
			case 'delete':
				self::change_total_qty($values, 'delete');
				return;
			case 'restore':
				self::change_total_qty($values, 'restore');
				return;
			case 'add':
				// TODO: prevent more than 1 piece of single items (html injection)
				self::change_total_qty($values, 'add');
				return $values;
			case 'view':
				return;
			case 'edit':
				self::change_total_qty($values);
			case 'added':
		}
		return $values;
	}
}
?>
