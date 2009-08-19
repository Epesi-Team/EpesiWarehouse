<?php
/**
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
		$this->rb->set_default_order(array('transaction_date'=>'DESC','transaction_id'=>'DESC'));
		$me = CRM_ContactsCommon::get_my_record();
		$defaults = array(	'country'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_country'),
							'zone'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_state'),
							'transaction_date'=>date('Y-m-d'),
							'employee'=>$me['id'],
							'warehouse'=>Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse')
							);
		$this->rb->set_defaults(array(
			$this->t('Purchase')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'purchase.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>0,'terms'=>0))),
			$this->t('Sales Quote')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'sale.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>1,'terms'=>0))),
			$this->t('Sale')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'quick_sale.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>1, 'status'=>2, 'payment_type'=>0, 'shipment_type'=>0,'terms'=>0))),
			$this->t('Inv. Adjustment')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'inv_adj.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>2))),
//			$this->t('Rental')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'rental.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>3))),
			$this->t('Warehouse Transfer')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'warehouse_transfer.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>4)))
			), true);
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse');
		$opts = array('__NULL__'=>'---');
		foreach ($warehouses as $v)
			$opts[$v['id']] = $v['warehouse'];
		$this->rb->set_custom_filter('warehouse',array('type'=>'select','label'=>$this->t('Warehouse'),'args'=>$opts,'trans_callback'=>array($this, 'warehouse_filter')));
		$this->rb->set_filters_defaults(array('warehouse'=>$my_warehouse));

		$this->rb->set_header_properties(array(
			'terms'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'status'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'transaction_id'=>array('width'=>1, 'wrapmode'=>'nowrap', 'name'=>'Trans. ID'),
			'transaction_type'=>array('width'=>1, 'wrapmode'=>'nowrap', 'name'=>'Type'),
			'invoice_number'=>array('width'=>1, 'wrapmode'=>'nowrap', 'name'=>'Invoice'),
			'transaction_date'=>array('width'=>1, 'wrapmode'=>'nowrap', 'name'=>'Date')
		));
		$this->display_module($this->rb);
	}

	public function warehouse_filter($choice) {
		if ($choice=='__NULL__') return array();
		return array('(warehouse'=>$choice, '|target_warehouse'=>$choice);
	}	

	public function applet($conf,$opts) {
		$opts['go'] = true; // enable full screen
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$limit = null;
		$crits = array();
		$sorting = array();
		$cols = array(
							array('field'=>'transaction_id', 'width'=>1, 'label'=>'Trans. ID'),
							array('field'=>'transaction_type', 'width'=>10, 'label'=>'Trans. Type'),
							array('field'=>'status', 'width'=>10),
//							array('field'=>'operation', 'width'=>10),
//							array('field'=>'quantity', 'width'=>10)
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
		$order = array(array('item_name'=>$arg['id']), array('quantity_on_hand'=>false,'item_name'=>false,'description'=>false,'item_name'=>false), array('transaction_date'=>'DESC', 'transaction_id'=>'DESC'));
		$rb->set_button(false);
		$rb->set_defaults(array('item_name'=>$arg['id']));
		$rb->set_header_properties(array(
			'transaction_id'=>array('name'=>'Trans. ID', 'width'=>1, 'wrapmode'=>'nowrap'),
			'transaction_date'=>array('name'=>'Trans. Date', 'width'=>1, 'wrapmode'=>'nowrap'),
			'transaction_type'=>array('name'=>'Trans. Type', 'width'=>1, 'wrapmode'=>'nowrap'),
			'transaction_status'=>array('name'=>'Trans. Status', 'width'=>1, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function order_details_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$cols = array('transaction_id'=>false);
		$cols['transaction_type'] = false;			
		$cols['transaction_date'] = false;			
		$cols['transaction_status'] = false;			
		$cols['warehouse'] = false;			
		$header_prop = array(
			'item_name'=>array('width'=>25, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>70, 'wrapmode'=>'nowrap'),
			'gross_total'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'tax_value'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'tax_rate'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'sww'=>array('width'=>15, 'wrapmode'=>'nowrap'),
			'net_total'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'net_price'=>array('width'=>28, 'wrapmode'=>'nowrap'),
			'gross_price'=>array('width'=>28, 'wrapmode'=>'nowrap'),
			'debit'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'credit'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'quantity'=>array('name'=>'Qty', 'width'=>4, 'wrapmode'=>'nowrap'),
			'serial'=>array('width'=>40, 'wrapmode'=>'nowrap')
		);
		if ($arg['transaction_type']==0) {
			$header_prop['net_price'] = array('name'=>'Net Cost', 'width'=>28, 'wrapmode'=>'nowrap');
			$header_prop['gross_price'] = array('name'=>'Gross Cost', 'width'=>28, 'wrapmode'=>'nowrap');
//			if ($arg['status']!=20) {
//				$cols['serial'] = false;
//			}
		}
		if ($arg['transaction_type']==2 || $arg['transaction_type']==4) {
			$cols['tax_rate'] = false;
			$cols['net_total'] = false;
			$cols['net_price'] = false;			
			$cols['gross_price'] = false;			
			$cols['tax_value'] = false;			
			$cols['gross_total'] = false;
			$cols['quantity'] =  $arg['transaction_type']==4;			
			$cols['debit'] = $arg['transaction_type']!=4;			
			$cols['credit'] = $arg['transaction_type']!=4;			
			$header_prop['debit'] = array('width'=>20, 'wrapmode'=>'nowrap', 'name'=>'Debit (-)');
			$header_prop['credit'] = array('width'=>20, 'wrapmode'=>'nowrap', 'name'=>'Credit (+)');
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
	
	public function revise_items(&$po_form, $items, $trans) {
		$po_form->addElement('select', 'payment_type', $this->t('Payment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Payment_Types'));
		$po_form->addElement('text', 'payment_no', $this->t('Payment No'));
		$po_form->addElement('select', 'terms', $this->t('Terms'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Terms'));
		$po_form->addElement('select', 'shipment_type', $this->t('Shipment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Shipment_Types'));
		$po_form->setDefaults($trans);

		$taxes = Data_TaxRatesCommon::get_tax_rates();
		$po_form->addElement('static', 'item_header', '');
		$po_form->setDefaults(array('item_header'=>$this->t('Please revise items quantity, price and tax rate')));
		foreach ($items as $v) {
			$elements = array();
			$elements[] = $po_form->createElement('text', 'quantity', $this->t('Price'));
			$elements[] = $po_form->createElement('currency', 'net_price', $this->t('Price'));
			$elements[] = $po_form->createElement('select', 'tax_rate', $this->t('Tax'), $taxes);
			$po_form->addGroup($elements, 'item__'.$v['id'], Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
			$po_form->setDefaults(array('item__'.$v['id']=>array('quantity'=>$v['quantity'], 'net_price'=>$v['net_price'], 'tax_rate'=>$v['tax_rate'])));
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
	
	public function check_if_items_available($trans, $items) {
		$items_check = $this->init_module('Libs/QuickForm'); 
		$items_check->addElement('static', 'item_header', '', $this->t('The following items are unavailable'));
		$items_available = true;
		$quantities = array();
		foreach ($items as $v) {
			if (!isset($quantities[$v['item_name']])) $quantities[$v['item_name']]=0;
			$quantities[$v['item_name']] += $v['quantity'];
			$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku', 'warehouse'), array($v['item_name'], $trans['warehouse']));
			if (is_numeric($loc_id)) $qty = Utils_RecordBrowserCommon::get_value('premium_warehouse_location', $loc_id, 'quantity');
			else $qty = 0;
			if ($qty<$quantities[$v['item_name']] && Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')<2) {
				$items_check->addElement('static', 'item_'.$v['id'], Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true), '<span style="color:red;">'.$qty.' / '.$quantities[$v['item_name']].'</span>');
				$items_available = false;
			}
		}
		if (!$items_available) return $items_check;
		else return null;
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
					$po_form = $this->init_module('Libs_QuickForm'); 

					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($po_form, 'employee', 'Employee', 'add', null, $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					$po_form->addElement('select', 'warehouse', $this->t('Warehouse'), $warehouses);

					$this->revise_items($po_form, $items, $trans);

					$po_form->setDefaults(array('handling_cost'=>$trans['handling_cost'],'shipment_cost'=>$trans['shipment_cost']));
					if ($trans['warehouse']) $def_warehouse = $trans['warehouse']; 
					else $def_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
					if ($trans['employee']) {
						$def_employee = $trans['employee']; 
					} else {
						$me = CRM_ContactsCommon::get_my_record();
						$def_employee = $me['id'];
					}
					$po_form->setDefaults(array('employee'=>$def_employee,'warehouse'=>$def_warehouse));

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
					$po_form = $this->init_module('Libs_QuickForm'); 

					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($po_form, 'employee', 'Employee', 'add', null, $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					$po_form->addElement('select', 'warehouse', $this->t('Warehouse'), $warehouses);

					$this->revise_items($po_form, $items, $trans);

					$po_form->setDefaults(array('handling_cost'=>$trans['handling_cost'],'shipment_cost'=>$trans['shipment_cost']));
					if ($trans['warehouse']) $def_warehouse = $trans['warehouse']; 
					else $def_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
					if ($trans['employee']) {
						$def_employee = $trans['employee']; 
					} else {
						$me = CRM_ContactsCommon::get_my_record();
						$def_employee = $me['id'];
					}
					$po_form->setDefaults(array('employee'=>$def_employee,'warehouse'=>$def_warehouse));

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
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					if (empty($items)) {
						$this->href = Utils_TooltipCommon::open_tag_attrs(Base_LangCommon::ts('Premium_Warehouse_Items_Orders','No items were saved in this transaction, cannot proceed with processing.'),false);
						break;
					}
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
//					$serials->addElement('static', 'item_header', '');
//					$serials->setDefaults(array('item_header'=>$this->t('Please enter serial numbers for Serialized Items')));
					$any_item = false;
//					foreach ($items as $v) {
//						if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1) {
//							$any_item = true; 
//							for ($i=0;$i<$v['quantity'];$i++)
//								$serials->addElement('text', 'serial__'.$v['id'].'__'.$i, Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
//						}
//					}
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
									$location_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku','warehouse'), array($v['item_name'], $trans['warehouse']));
									for ($i=0;$i<$v['quantity'];$i++) {
										DB::Execute('INSERT INTO premium_warehouse_location_serial (location_id, serial) VALUES (%d, %s)', array($location_id, $vals['form']['serial__'.$v['id'].'__'.$i]));
										$id = DB::Insert_ID('premium_warehouse_location_serial','id');
										DB::Execute('INSERT INTO premium_warehouse_location_orders_serial (serial_id, order_details_id) VALUES (%d, %d)', array($id, $v['id']));
									}
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
		} elseif ($trans['transaction_type']==1) {
			switch ($status) {			
				case -1:
					$new_form = $this->init_module('Libs_QuickForm');
					$me = CRM_ContactsCommon::get_my_record();
					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($new_form, 'employee', 'Employee', 'add', $me['id'], $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					$new_form->addElement('select', 'warehouse', $this->t('Warehouse'), $warehouses);
					$new_form->setDefaults(array('employee'=>$me['id'],'warehouse'=>Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse')));
					$lp->add_option('new', $this->t('Order received'), null, $new_form);

					$this->display_module($lp, array($this->t('Recieve Online Order')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						else $vals['form']['status'] = 2;
						if (is_numeric($vals['form']['employee']) && is_numeric($vals['form']['warehouse'])) 
							Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case '':
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$so_form = $this->init_module('Libs_QuickForm');

					$so_form->addElement('currency', 'shipment_cost', $this->t('Shipment Cost'));
					$so_form->addElement('currency', 'handling_cost', $this->t('Handling Cost'));

					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($so_form, 'employee', 'Employee', 'add', null, $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					$so_form->addElement('select', 'warehouse', $this->t('Warehouse'), $warehouses);

					$this->revise_items($so_form, $items, $trans);

					$so_form->setDefaults(array('handling_cost'=>$trans['handling_cost'],'shipment_cost'=>$trans['shipment_cost']));
					if ($trans['warehouse']) $def_warehouse = $trans['warehouse']; 
					else $def_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
					if ($trans['employee']) {
						$def_employee = $trans['employee']; 
					} else {
						$me = CRM_ContactsCommon::get_my_record();
						$def_employee = $me['id'];
					}
					$so_form->setDefaults(array('employee'=>$def_employee,'warehouse'=>$def_warehouse));

					$lp->add_option('so', $this->t('Order received'), null, $so_form);

					$quote_form = $this->init_module('Libs/QuickForm');
					$quote_form->addElement('datepicker', 'expiration_date', $this->t('Expiration Date'));
					$quote_form->setDefaults(array('expiration_date'=>date('Y-m-d', strtotime('+7 days'))));
					$lp->add_option('quote', $this->t('Sale Quote'), null, $quote_form);

					$items_unavailable = $this->check_if_items_available($trans, $items);
					if ($trans['payment_type']==0 && $trans['shipment_type']==0) $lp->add_option('all_done', $this->t('Paid & Delievered'), null, $items_unavailable);
					
					$this->display_module($lp, array($this->t('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						switch ($vals['option']) {
							case 'all_done': if ($items_unavailable===null) $vals['form']['status'] = 20; break;
							case 'quote': $vals['form']['status']=1; break;
							case 'so': $vals['form']['status']=2; break;
						}
						if ($vals['option']=='so')
							foreach ($items as $v)
								Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], $vals['form']['item__'.$v['id']]);
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 1:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$so_form = $this->init_module('Libs_QuickForm'); 

					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($so_form, 'employee', 'Employee', 'add', null, $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					$so_form->addElement('select', 'warehouse', $this->t('Warehouse'), $warehouses);

					$this->revise_items($so_form, $items, $trans);

					$so_form->setDefaults(array('handling_cost'=>$trans['handling_cost'],'shipment_cost'=>$trans['shipment_cost']));
					if ($trans['warehouse']) $def_warehouse = $trans['warehouse']; 
					else $def_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
					if ($trans['employee']) {
						$def_employee = $trans['employee']; 
					} else {
						$me = CRM_ContactsCommon::get_my_record();
						$def_employee = $me['id'];
					}
					$so_form->setDefaults(array('employee'=>$def_employee,'warehouse'=>$def_warehouse));

					$lp->add_option('so', $this->t('SO'), null, $so_form);

					$lp->add_option('cancel', $this->t('Cancel'), null, null);
					
					$this->display_module($lp, array($this->t('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='so')?2:21; 
						if ($vals['option']=='so')
							foreach ($items as $v)
								Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], $vals['form']['item__'.$v['id']]);
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 2:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					if (empty($items)) {
						$this->href = Utils_TooltipCommon::open_tag_attrs(Base_LangCommon::ts('Premium_Warehouse_Items_Orders','No items were saved in this transaction, cannot proceed with processing.'),false);
						break;
					}
					
					$items_unavailable = $this->check_if_items_available($trans, $items);
					if ($trans['payment_type']==0 && $trans['shipment_type']==0) $lp->add_option('quick_delivery', $this->t('Paid & Delivered'), null, $items_unavailable);

					$lp->add_option('accepted', $this->t('Accepted'), null, null);
					$lp->add_option('onhold', $this->t('Put On Hold'), null, null);
					$this->display_module($lp, array($this->t('Funds available?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						switch ($vals['option']) {
							case 'accepted': $vals['form']['status'] = 3; break; 
							case 'on_hold': $vals['form']['status'] = 5; break; 
							case 'quick_delivery': if($items_unavailable===null)$vals['form']['status']=20; break; 
						}
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 3:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$items_unavailable = $this->check_if_items_available($trans, $items);
					$lp->add_option('available', $this->t('Items Available'), null, $items_unavailable);
					// check items qty
					$lp->add_option('onhold', $this->t('Put On Hold'), null, null);
					$this->display_module($lp, array($this->t('Items available?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='available')?4:5;
						// TODO: reduce the amount of items
						if ($items_unavailable!==null && $vals['form']['status']==4) break;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 4:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$items_unavailable = $this->check_if_items_available($trans, $items);

					$serials = $this->init_module('Libs/QuickForm');
//					$serials->addElement('static', 'item_header', '');
//					$serials->setDefaults(array('item_header'=>$this->t('Please select serial numbers for Serialized Items')));
					$any_item = false;
//					foreach ($items as $v) {
//						if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1) {
//							$any_item = true; 
//							$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku', 'warehouse'), array($v['item_name'], $trans['warehouse']));
//							if (!$loc_id) continue; // failsafe
//							$item_serials = array(''=>'---')+DB::GetAssoc('SELECT id, serial FROM premium_warehouse_location_serial WHERE active=1 AND location_id=%d', array($loc_id));
//							for ($i=0;$i<$v['quantity'];$i++)
//								$serials->addElement('select', 'serial__'.$v['id'].'__'.$i, Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true), $item_serials);
//						}
//					}

					$lp->add_option('available', $this->t('Items Available'), null, ($items_unavailable===null)?($any_item?$serials:null):$items_unavailable);
					// check items qty
					$lp->add_option('onhold', $this->t('Put On Hold'), null, null);
					$this->display_module($lp, array($this->t('Final Inspection: All items available?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='available')?6:5;
						if ($items_unavailable!==null && $vals['form']['status']==6) break;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						if ($vals['form']['status']==6) {
							foreach ($items as $v) {
								for ($i=0;$i<$v['quantity'];$i++)
									if (isset($vals['form']['serial__'.$v['id'].'__'.$i]) && is_numeric($vals['form']['serial__'.$v['id'].'__'.$i])) {
										DB::Execute('UPDATE premium_warehouse_location_serial SET active=0 WHERE id=%d', array($vals['form']['serial__'.$v['id'].'__'.$i]));
										// TODO: sign this transaction for those serials
									}
							}
						}
						location(array());
					}
					break;
				case 6:
					$ship_received = $this->init_module('Libs/QuickForm');
					$emps_ids = CRM_ContactsCommon::get_contacts(Premium_Warehouse_ItemsCommon::employee_crits(), array(), array('last_name'=>'ASC','first_name'=>'ASC'));
					$emps = array(''=>'---');
					$my_id = '';
					foreach ($emps_ids as $v) {
						if ($v['login']==Acl::get_user()) $my_id = $v['id'];
						$emps[$v['id']] = CRM_ContactsCommon::contact_format_no_company($v,true);
					}
					$ship_received->addElement('datepicker', 'shipment_date', $this->t('Shipment - send date'));
					$ship_received->addElement('select', 'shipment_employee', $this->t('Shipment - sent by'), $emps);
					$ship_received->addElement('datepicker', 'shipment_eta', $this->t('Shipment - ETA'));
					$ship_received->addElement('text', 'shipment_no', $this->t('Shipment No.'));
					$ship_received->addElement('text', 'tracking_info', $this->t('Shipment - Tracking Info'));
					if (!isset($trans['shipment_date'])) $trans['shipment_date'] = date('Y-m-d');
					if (!isset($trans['shipment_employee'])) $trans['shipment_employee'] = $my_id;
					$ship_received->setDefaults(array('shipment_date'=>$trans['shipment_date'], 'shipment_employee'=>$trans['shipment_date'], 'shipment_eta'=>$trans['shipment_eta'], 'shipment_no'=>$trans['shipment_no'], 'tracking_info'=>$trans['tracking_info']));

					$lp->add_option('pickup', $this->t('Pickup'), null, null);
					$lp->add_option('ship', $this->t('Ship'), null, $ship_received);
					$this->display_module($lp, array($this->t('Select Shipping method.')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						if ($vals['option']=='pickup') {
							$vals['form']['status'] = 20;
						} else {
							$vals['form']['status'] = 7;
						}
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 5:
					$lp->add_option('items_available', $this->t('Items Available'), null, null);
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$split = $this->split_items_form($items);
					$lp->add_option('partial_order', $this->t('Partial Order'), null, $split);
					$lp->add_option('cancel', $this->t('Cancel'), null, null);
					$this->display_module($lp, array($this->t('Items available?')));
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
				case 7:
					$lp->add_option('delivered', $this->t('Delivered'), null, null);
					$lp->add_option('missing', $this->t('Missing'), null, null);
					$this->display_module($lp, array($this->t('Was the shipment delivered?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='delivered')?20:22;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
			}
		} elseif ($trans['transaction_type']==4) {
			switch ($status) {			
				case '':
//					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
//					$so_form = $this->revise_items($items, $trans);
					$lp->add_option('so', $this->t('Transfer pending'), null, null);

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
//						if ($vals['option']=='po')
//							foreach ($items as $v)
//								Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], $vals['form']['item__'.$v['id']]);
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 1:
//					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
//					$so_form = $this->revise_items($items, $trans);
					$lp->add_option('so', $this->t('SO'), null, null);

					$lp->add_option('cancel', $this->t('Cancel'), null, null);
					
					$this->display_module($lp, array($this->t('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='po')?2:21; 
//						if ($vals['option']=='so')
//							foreach ($items as $v)
//								Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], $vals['form']['item__'.$v['id']]);
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 2:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					if (empty($items)) {
						$this->href = Utils_TooltipCommon::open_tag_attrs(Base_LangCommon::ts('Premium_Warehouse_Items_Orders','No items were saved in this transaction, cannot proceed with processing.'),false);
						break;
					}
					$items_unavailable = $this->check_if_items_available($trans, $items);
					$lp->add_option('available', $this->t('Items Available'), null, $items_unavailable);
					// check items qty
					$lp->add_option('onhold', $this->t('Put On Hold'), null, null);
					$this->display_module($lp, array($this->t('Items available?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='available')?3:4;
						if ($items_unavailable!==null && $vals['form']['status']==4) break;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 3:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$items_unavailable = $this->check_if_items_available($trans, $items);

					$serials = $this->init_module('Libs/QuickForm');
//					$serials->addElement('static', 'item_header', '');
//					$serials->setDefaults(array('item_header'=>$this->t('Please select serial numbers for Serialized Items')));
					$any_item = false;
//					foreach ($items as $v) {
//						if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1) {
//							$any_item = true; 
//							$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku', 'warehouse'), array($v['item_name'], $trans['warehouse']));
//							if (!$loc_id) continue; // failsafe
//							$item_serials = array(''=>'---')+DB::GetAssoc('SELECT id, serial FROM premium_warehouse_location_serial WHERE active=1 AND location_id=%d', array($loc_id));
//							for ($i=0;$i<$v['quantity'];$i++)
//								$serials->addElement('select', 'serial__'.$v['id'].'__'.$i, Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true), $item_serials);
//						}
//					}

					$lp->add_option('available', $this->t('Items Available'), null, ($items_unavailable===null)?($any_item?$serials:null):$items_unavailable);
					// check items qty
					$lp->add_option('onhold', $this->t('Put On Hold'), null, null);
					$this->display_module($lp, array($this->t('Final Inspection: All items available?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='available')?5:4;
						if ($items_unavailable!==null && $vals['form']['status']==5) break;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						if ($vals['form']['status']==5) {
							foreach ($items as $v) {
								for ($i=0;$i<$v['quantity'];$i++)
									if (isset($vals['form']['serial__'.$v['id'].'__'.$i]) && is_numeric($vals['form']['serial__'.$v['id'].'__'.$i])) {
										DB::Execute('UPDATE premium_warehouse_location_serial SET active=0 WHERE id=%d', array($vals['form']['serial__'.$v['id'].'__'.$i]));
										// TODO: sign this transaction for those serials
									}
							}
						}
						location(array());
					}
					break;
				case 5:
					$ship_received = $this->init_module('Libs/QuickForm');
					$emps_ids = CRM_ContactsCommon::get_contacts(Premium_Warehouse_ItemsCommon::employee_crits(), array(), array('last_name'=>'ASC','first_name'=>'ASC'));
					$emps = array(''=>'---');
					$my_id = '';
					foreach ($emps_ids as $v) {
						if ($v['login']==Acl::get_user()) $my_id = $v['id'];
						$emps[$v['id']] = CRM_ContactsCommon::contact_format_no_company($v,true);
					}
					$ship_received->addElement('datepicker', 'shipment_date', 'Shipment - send date');
					$ship_received->addElement('select', 'shipment_employee', 'Shipment - sent by', $emps);
					$ship_received->addElement('datepicker', 'shipment_eta', 'Shipment - ETA');
					$ship_received->addElement('text', 'tracking_info', 'Shipment - Tracking Info');
					$ship_received->setDefaults(array('shipment_date'=>date('Y-m-d'), 'shipment_employee'=>$my_id));

					$lp->add_option('pickup', $this->t('Pickup'), null, null);
					$lp->add_option('ship', $this->t('Ship'), null, $ship_received);
					$this->display_module($lp, array($this->t('Select Shipping method.')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						if ($vals['option']=='pickup') {
							$vals['form']['status'] = 20;
						} else {
							$vals['form']['status'] = 6;
						}
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 4:
					$lp->add_option('items_available', $this->t('Items Available'), null, null);
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$split = $this->split_items_form($items);
					$lp->add_option('partial_order', $this->t('Partial Order'), null, $split);
					$lp->add_option('cancel', $this->t('Cancel'), null, null);
					$this->display_module($lp, array($this->t('Items available?')));
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
				case 6:
					$lp->add_option('delivered', $this->t('Delivered'), null, null);
					$lp->add_option('missing', $this->t('Missing'), null, null);
					$this->display_module($lp, array($this->t('Was the shipment delivered?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='delivered')?20:22;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
			}
		} elseif ($trans['transaction_type']==2) {
			switch ($status) {			
				case '':
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					if (empty($items)) {
						$this->href = Utils_TooltipCommon::open_tag_attrs(Base_LangCommon::ts('Premium_Warehouse_Items_Orders','No items were saved in this transaction, cannot proceed with processing.'),false);
						break;
					}
					$lp->add_option('yes', $this->t('Yes'), null, null);
					$lp->add_option('no', $this->t('No'), null, null);
					
					$this->display_module($lp, array($this->t('Close Inv. Adjustment. Are you sure?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						if ($vals['option']=='yes')
							Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], array('status'=>20));
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