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

class Premium_Warehouse_Items_Orders extends Module {
	private $rb;
	private $href = '';

	public function body() {
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$this->rb->set_default_order(array('transaction_date'=>'DESC'));
		$this->rb->set_button(false);
		$me = CRM_ContactsCommon::get_my_record();
		$defaults = array(	'country'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_country'),
							'zone'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_state'),
							'transaction_date'=>date('Y-m-d'),
							'employee'=>$me['id'],
							'warehouse'=>Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse'),
							'terms'=>0);
		$this->rb->set_defaults(array(
			$this->t('Purchase')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'purchase.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>0))),
			$this->t('Sale')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'sale.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>1))),
			$this->t('Inv. Adjustment')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'inv_adj.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>2))),
			$this->t('Rental')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'rental.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>3)))
			), true);
		$warehouse = Base_User_SettingsCommon::get('Premium_Warehouse', 'my_warehouse');
		if ($warehouse) $this->rb->set_additional_caption(' - '.Utils_RecordBrowserCommon::get_value('premium_warehouse', $warehouse,'warehouse'));
		$this->rb->set_header_properties(array('terms'=>array('width'=>1, 'wrapmode'=>'nowrap')));
		$this->display_module($this->rb);
	}

	public function applet($conf,$opts) {
		$opts['go'] = true; // enable full screen
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$limit = null;
		$crits = array();
		$sorting = array('item_name'=>'ASC');
		$cols = array(
							array('field'=>'item', 'width'=>10, 'cut'=>18),
							array('field'=>'operation', 'width'=>10),
							array('field'=>'quantity', 'width'=>10)
										);

		$conds = array(
									$cols,
									$crits,
									$sorting,
									array('Premium_Warehouse_Items_OrdersCommon','applet_info_format'),
									$limit,
									$conf,
									& $opts
				);
		$opts['actions'][] = Utils_RecordBrowserCommon::applet_new_record_button('premium_warehouse_items_orders',array());
		$this->display_module($rb, $conds, 'mini_view');
	}
	
	public function transaction_history_addon($arg){
		// TODO: service?
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$order = array(array('item_name'=>$arg['id']), array('quantity_on_hand'=>false,'item_name'=>false,'description'=>false,'item_name'=>false, ($arg['item_type']==1)?'quantity':'serial'=>false), array('transaction_id'=>'DESC'));
		$rb->set_button(false);
		$rb->set_defaults(array('item_name'=>$arg['id']));
		$rb->set_header_properties(array(
			'transaction_id'=>array('name'=>'Trans. ID', 'width'=>1, 'wrapmode'=>'nowrap'),
			'transaction_date'=>array('name'=>'Trans. Date', 'width'=>1, 'wrapmode'=>'nowrap'),
			'transaction_type'=>array('name'=>'Trans. Type', 'width'=>1),
			'transaction_status'=>array('name'=>'Trans. Status', 'width'=>1)
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function order_details_addon($arg){
		// TODO: leightbox do wybierania przedmiotow do select'a (sic! ^^)
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$cols = array('transaction_id'=>false);
		$cols['transaction_type'] = false;			
		$cols['transaction_date'] = false;			
		$cols['transaction_status'] = false;			
		$cols['warehouse'] = false;			
		$header_prop = array(
			'item_name'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'gross_total'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'tax_value'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'tax_rate'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'net_total'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'net_price'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'debit'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'credit'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'quantity'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'serial'=>array('width'=>40, 'wrapmode'=>'nowrap')
		);
		if ($arg['transaction_type']==0) {
			$header_prop['net_price'] = array('name'=>'Net Cost', 'width'=>14, 'wrapmode'=>'nowrap');
			$header_prop['gross_price'] = array('name'=>'Gross Cost', 'width'=>1, 'wrapmode'=>'nowrap');
			if ($arg['status']!=20) {
				$cols['serial'] = false;
			}
		}
		if ($arg['transaction_type']==2) {
			$cols['tax_rate'] = false;
			$cols['net_total'] = false;
			$cols['net_price'] = false;			
			$cols['tax_value'] = false;			
			$cols['gross_total'] = false;			
			$cols['debit'] = true;			
			$cols['credit'] = true;			
		}
		if ($arg['transaction_type']==3) {
			if (!$arg['payment']) {
				$cols['tax_rate'] = false;
				$cols['net_total'] = false;
				$cols['net_price'] = false;			
				$cols['tax_value'] = false;			
				$cols['gross_total'] = false;
			}			
			$cols['quantity'] = false;
			$cols['debit'] = false;
			$cols['credit'] = false;
			$cols['return_date'] = true;
			$rb->set_defaults(array('return_date'=>$arg['return_date']));
			$rb->set_additional_actions_method($this, 'actions_for_order_details');
		}
		$order = array(array('transaction_id'=>$arg['id']), $cols, array());
		$rb->set_button(false);
		$rb->set_defaults(array('transaction_id'=>$arg['id']));
		$rb->enable_quick_new_records();
		$rb->set_cut_lengths(array('description'=>50));
		$rb->set_header_properties($header_prop);
		$this->display_module($rb,$order,'show_data');
	}
	
	public function mark_as_returned($r) {
		$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $r['transaction_id']);
		Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $r['id'], array('returned'=>1));
		Utils_RecordBrowserCommon::restore_record('premium_warehouse_location', $r['serial']);
		return false;
	}
	
	public function actions_for_order_details($r, & $gb_row) {
		if (!$r['returned']) $gb_row->add_action($this->create_callback_href(array($this,'mark_as_returned'),array($r)),'Restore', 'Mark as returned');
	}

	public function attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Warehouse/Items/Orders/'.$arg['id']));
		$a->set_view_func(array('Premium_Warehouse_Items_OrdersCommon','search_format'),array($arg['id']));
		$a->additional_header('Transaction ID: '.$arg['transaction_id']);
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$this->display_module($a);
	}
	
	public function revise_items($items, $trans) {
		$po_form = $this->init_module('Libs/QuickForm');
		$po_form->addElement('select', 'payment_type', $this->t('Payment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Payment_Types'));
		$po_form->addElement('text', 'payment_no', $this->t('Payment No'));
		$po_form->addElement('select', 'terms', $this->t('Terms'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Terms'));
		$po_form->addElement('select', 'shipment_type', $this->t('Shipment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Shipment_Types'));
		$po_form->setDefaults($trans);

		$taxes = Utils_CommonDataCommon::get_array('Premium_Warehouse_Items_Tax');
		$po_form->addElement('static', 'item_header', '');
		$po_form->setDefaults(array('item_header'=>$this->t('Please revise items quantity, price and tax rate')));
		foreach ($items as $v) {
			$elements = array();
			$elements[] = $po_form->createElement('text', 'quantity', $this->t('Price'));
			$elements[] = $po_form->createElement('text', 'net_price', $this->t('Price'));
			$elements[] = $po_form->createElement('select', 'tax_rate', $this->t('Tax'), $taxes);
			$po_form->addGroup($elements, 'item__'.$v['id'], Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
			$po_form->setDefaults(array('item__'.$v['id']=>array('quantity'=>$v['quantity'], 'net_price'=>number_format($v['net_price'],2), 'tax_rate'=>$v['tax_rate'])));
		}
		return $po_form;
	}
	
	public function split_items_form($items) {
		$split = $this->init_module('Libs/QuickForm');
		$split->addElement('static', 'header', '');
		$split->setDefaults(array('header'=>'Enter items quantity for original (first field) and new (second field) transaction'));
		foreach ($items as $v) {
			$elements = array();
			$elements[] = $split->createElement('text', 'original_qty', $this->t('Price'), array('id'=>'original_item__'.$v['id'] ,'onkeyup'=>'$(\'total_item__'.$v['id'].'\').innerHTML=parseInt(this.value)+parseInt($(\'new_item__'.$v['id'].'\').value);'));
			$elements[] = $split->createElement('text', 'new_qty', $this->t('Price'), array('id'=>'new_item__'.$v['id'] ,'onkeyup'=>'$(\'total_item__'.$v['id'].'\').innerHTML=parseInt(this.value)+parseInt($(\'original_item__'.$v['id'].'\').value);'));
			$elements[] = $split->createElement('static', 'total', $this->t('Total'));
			$split->addGroup($elements, 'item__'.$v['id'], Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
			$split->setDefaults(array('item__'.$v['id']=>array('original_qty'=>$v['quantity'], 'new_qty'=>0, 'total'=>'<b>'.$this->t('Total').':</b> <span id="total_item__'.$v['id'].'">'.$v['quantity'].'</span>')));
		}
		return $split;
	}
	
	public function split_items_process($items, $trans, $vals) {
		$id = null;
		foreach ($items as $v)
			if (intval($vals['item__'.$v['id']]['new_qty'])>0) {
				if ($id===null) {
					$id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders', $trans);
					$this->set_module_variable('split_transaction_id', $id);
				}
				$old = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders_details', $v['id']);
				if (intval($vals['item__'.$v['id']]['original_qty'])!=0) Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], array('quantity'=>intval($vals['item__'.$v['id']]['original_qty'])));
				else Utils_RecordBrowserCommon::delete_record('premium_warehouse_items_orders_details', $v['id']);
				$old['transaction_id'] = $id;
				$old['quantity'] = intval($vals['item__'.$v['id']]['new_qty']);
				Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details', $old);
			}
	}
	
	public function change_status_leightbox($trans, $status) {
		if ($this->isset_module_variable('split_transaction_id')) {
			$id = $this->get_module_variable('split_transaction_id');
			$this->unset_module_variable('split_transaction_id');
			print($this->t('<b>The transaction was split succesfully.<br>The ID of newly created transaction is: %s', array(Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items_orders', 'transaction_id', $id))));
		}
		$lp = $this->init_module('Utils/LeightboxPrompt');
		if ($trans['transaction_type']==0) {
			switch ($status) {			
				case '':
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$po_form = $this->revise_items($items, $trans);
					$lp->add_option('po', $this->t('PO'), null, $po_form);

					$quote_form = $this->init_module('Libs/QuickForm');
					$quote_form->addElement('datepicker', 'expiration_date', $this->t('Expiration Date'));
					$quote_form->setDefaults(array('expiration_date'=>date('Y-m-d', strtotime('+7 days'))));
					$lp->add_option('quote', $this->t('Quote'), null, $quote_form);
					
					$this->display_module($lp, array($this->t('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='quote')?1:2; 
						if ($vals['option']=='po')
							foreach ($items as $v)
								Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], $vals['form']['item__'.$v['id']]);
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 1:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$po_form = $this->revise_items($items);
					$lp->add_option('po', $this->t('PO'), null, $po_form);

					$lp->add_option('cancel', $this->t('Cancel'), null, null);
					
					$this->display_module($lp, array($this->t('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='po')?2:21; 
						if ($vals['option']=='po')
							foreach ($items as $v)
								Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], $vals['form']['item__'.$v['id']]);
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 2:
					$lp->add_option('ship', $this->t('Accepted'), null, null);
					$lp->add_option('onhold', $this->t('On Hold'), null, null);
					$this->display_module($lp, array($this->t('Purchase Order accepted?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='ship')?3:5;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 3:
					$ship_received = $this->init_module('Libs/QuickForm');
					$emps_ids = CRM_ContactsCommon::get_contacts(Premium_Warehouse_ItemsCommon::employee_crits(), array(), array('last_name'=>'ASC','first_name'=>'ASC'));
					$emps = array(''=>'---');
					$my_id = '';
					foreach ($emps_ids as $v) {
						if ($v['login']==Acl::get_user()) $my_id = $v['id'];
						$emps[$v['id']] = CRM_ContactsCommon::contact_format_no_company($v,true);
					}
					$ship_received->addElement('datepicker', 'shipment_date', 'Shipment - receive date');
					$ship_received->addElement('select', 'shipment_employee', 'Shipment - received by', $emps);
					$ship_received->setDefaults(array('shipment_date'=>date('Y-m-d'), 'shipment_employee'=>$my_id));
					$lp->add_option('received', $this->t('Items Received'), null, $ship_received);
					$lp->add_option('onhold', $this->t('Put On Hold'), null, null);
					$this->display_module($lp, array($this->t('Shipment Received?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='received')?4:5;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 4:
					$serials = $this->init_module('Libs/QuickForm');
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$serials->addElement('static', 'item_header', '');
					$serials->setDefaults(array('item_header'=>$this->t('Please enter serial numbers for Serialized Items')));
					$any_item = false;
					foreach ($items as $v) {
						if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1) {
							$any_item = true; 
							for ($i=0;$i<$v['quantity'];$i++)
								$serials->addElement('text', 'serial__'.$v['id'].'__'.$i, Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
						}
					}
					$split = $this->split_items_form($items);

					$lp->add_option('received', $this->t('All items delivered'), null, $any_item?$serials:null);
					$lp->add_option('incomplete', $this->t('Incomplete Shipment'), null, $split);
					$lp->add_option('onhold', $this->t('Put On Hold'), null, null);
					$this->display_module($lp, array($this->t('Final Inspection. All items received?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						if ($vals['option']=='onhold') {
							$vals['form']['status'] = 5;
						} elseif ($vals['option']=='received') {
							$vals['form']['status'] = 20;
						}
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						if ($vals['option']=='incomplete') {
							$this->split_items_process($items, $trans, $vals['form']);
						}
						if ($any_item && $vals['option']=='received') {
							foreach ($items as $v) {
								if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1) {
									$copy = $v;
									for ($i=0;$i<$v['quantity'];$i++) {
										$copy['quantity'] = 1;
										$copy['serial'] = $vals['form']['serial__'.$v['id'].'__'.$i];
										Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details', $copy);
									}
									Utils_RecordBrowserCommon::delete_record('premium_warehouse_items_orders_details', $v['id'], true);
								}
							}
						}
						location(array());
					}
					break;
				case 5:
					$lp->add_option('items_available', $this->t('Items Available'), null, null);
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$split = $this->split_items_form($items);
					$lp->add_option('partial_order', $this->t('Partial Order'), null, $split);
					$lp->add_option('cancel', $this->t('Cancel'), null, null);
					$this->display_module($lp, array($this->t('Final Inspection. All items received?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$up_vals = array();
						if ($vals['option']=='items_available') {
							$up_vals['status'] = 2;
						} elseif ($vals['option']=='partial_order') {
							$this->split_items_process($items, $trans, $vals['form']);
							break;		
						} else {
							$up_vals['status'] = 21;
						} 
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $up_vals);
						location(array());
					}
					break;
			}
		}
	}
	
	public function get_href() {
		return $this->href;
	}

	public function caption(){
		return $this->rb->caption();
	}
}

?>