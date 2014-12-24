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
							'warehouse'=>Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse'),
							'tax_calculation'=> Variable::get('premium_warehouse_def_tax_calc', false)
							);
		if(Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','add')) {

			$disabled = Variable::get('premium_warehouse_trans_types', false);
			if (!$disabled) $disabled = array();
			else $disabled = array_flip($disabled);
			$defaults2 = $defaults;
			$defaults = array();
    		if (!isset($disabled['disable_purchase'])) $defaults[__('Purchase')] = array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'purchase.png'), 'defaults'=>array_merge($defaults2,array('transaction_type'=>0,'terms'=>0,'payment'=>1,'transaction_date'=>date('Y-m-d'))));
		    if (!isset($disabled['disable_sales_quote'])) $defaults[__('Sales Quote')] = array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'sale.png'), 'defaults'=>array_merge($defaults2,array('transaction_type'=>1,'terms'=>0,'payment'=>1,'transaction_date'=>date('Y-m-d'))));
			if (!isset($disabled['disable_sale'])) $defaults[__('Sale')] = array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'quick_sale.png'), 'defaults'=>array_merge($defaults2,array('transaction_type'=>1, 'status'=>2,'payment'=>1, 'payment_type'=>0, 'shipment_type'=>0,'terms'=>0,'transaction_date'=>date('Y-m-d'))));
    		if (!isset($disabled['disable_inv_adj'])) $defaults[__('Inv. Adjustment')] = array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'inv_adj.png'), 'defaults'=>array_merge($defaults2,array('transaction_type'=>2,'transaction_date'=>date('Y-m-d'))));
//	    	if (!isset($disabled['disable_rental'])) $defaults[__('Rental')] = array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'rental.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>3,'transaction_date'=>date('Y-m-d'))));
		    if (!isset($disabled['disable_transfer'])) $defaults[__('Warehouse Transfer')] = array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'warehouse_transfer.png'), 'defaults'=>array_merge($defaults2,array('transaction_type'=>4,'transaction_date'=>date('Y-m-d'))));
    		if (!isset($disabled['disable_checkin'])) $defaults[__('Check-in')] = array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'purchase.png'), 'defaults'=>array_merge($defaults2,array('transaction_type'=>0,'terms'=>0,'payment'=>0,'transaction_date'=>date('Y-m-d'))));
	    	if (!isset($disabled['disable_checkout'])) $defaults[__('Check-out')] = array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'sale.png'), 'defaults'=>array_merge($defaults2,array('transaction_type'=>1,'terms'=>0,'payment'=>0,'transaction_date'=>date('Y-m-d'))));
			$this->rb->set_defaults($defaults, true);
		} else {
		    $defaults['transaction_type'] = 1;
		    $defaults['terms'] = 0;
		    $defaults['payment'] = 0;
		    $defaults['contact'] = $me['id'];
		    $defaults['company'] = $me['company_name'];
		    $defaults['last_name'] = $me['last_name'];
		    $defaults['first_name'] = $me['first_name'];
		    $company = CRM_ContactsCommon::get_company($me['company_name']);
		    $defaults['company_name'] = $company['company_name'];
		    $defaults['address_1'] = $company['address_1'];
		    $defaults['address_2'] = $company['address_2'];
		    $defaults['city'] = $company['city'];
		    $defaults['country'] = $company['country'];
		    $defaults['state'] = $company['zone'];
		    $defaults['postal_code'] = $company['postal_code'];
		    $defaults['phone'] = $company['phone'];
		    $defaults['tax_id'] = $company['tax_id'];
		    unset($defaults['employee']);
		    $this->rb->set_defaults($defaults);
		}
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse');
		$opts = array('__NULL__'=>'---');
		foreach ($warehouses as $v)
			$opts[$v['id']] = $v['warehouse'];
		$this->rb->set_custom_filter('warehouse',array('type'=>'select','label'=>__('Warehouse'),'args'=>$opts,'trans_callback'=>array($this, 'warehouse_filter')));
		$this->rb->set_filters_defaults(array('warehouse'=>$my_warehouse));
		$this->rb->set_additional_actions_method(array($this,'orders_actions'));

		$this->rb->set_header_properties(array(
			'terms'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'status'=>array('width'=>15, 'wrapmode'=>'nowrap'),
			'transaction_id'=>array('width'=>10, 'wrapmode'=>'nowrap', 'name'=>__('Trans. ID')),
			'transaction_type'=>array('width'=>10, 'wrapmode'=>'nowrap', 'name'=>__('Type')),
			'invoice_number'=>array('width'=>10, 'wrapmode'=>'nowrap', 'name'=>__('Invoice')),
			'transaction_date'=>array('width'=>10, 'wrapmode'=>'nowrap', 'name'=>__('Date'))
		));

        $possible_types_labels = Premium_Warehouse_Items_OrdersCommon::get_possible_transaction_type_labels();
        $transaction_type_filter_opts = array('__NULL__' => '---');
        foreach ($possible_types_labels as $val) {
            $transaction_type_filter_opts[serialize($val['crits'])] = $val['label'];
        }
        $this->rb->set_custom_filter('transaction_type',
            array(
                'type' => 'select',
                'label' => __('Transaction Type'),
                'args' => $transaction_type_filter_opts,
                'args_2' => array('id'=>'filter__transaction_type'),
                'trans_callback' => array($this, 'transaction_type_filter')
            )
        );

        $this->display_module($this->rb);
	}

    // callback for custom status filter
    public function browse_mode_details(& $form, & $external_filters, & $vals, & $crits, & $next_dont_hide, $rb_obj) {
        $transaction_type = isset($vals['filter__transaction_type']) ? $vals['filter__transaction_type'] : false;
        if ($transaction_type == '__NULL__')
            $transaction_type = false;

        if ($transaction_type === false)
            return;

        $transaction_type_crits = @unserialize($transaction_type);
        if (isset($transaction_type_crits['status']))
            return;

        $last_submited_defaults = $rb_obj->get_module_variable('def_filter', array());

        $args = array('__NULL__' => '---') + Premium_Warehouse_Items_OrdersCommon::get_status_array($transaction_type_crits);
        $form->addElement('select', 'filter__status', __('Transaction Status'), $args, array('id' => 'filter__status'));
        $external_filters[] = 'status';

        if (isset($last_submited_defaults['filter__transaction_type'])
            && $last_submited_defaults['filter__transaction_type'] == $transaction_type
        ) {
            $form->setDefaults($last_submited_defaults);

            $status_selected = $form->exportValue('filter__status');
            $vals['filter__status'] = $status_selected;
            if ($status_selected != '__NULL__' && $status_selected != null) {
                $crits = Utils_RecordBrowserCommon::merge_crits($crits, array('status' => $status_selected));
            }
        } else {
            $form->setDefaults(array('filter__status' => '__NULL__'));
        }
        eval_js('jq("#filter__transaction_type").change(function(){jq("#filter__status option").remove();})');
    }
	
	public function orders_actions($r, $gb_row) {
		
		switch ($r['transaction_type']) {
			case 0: $bg_color = '#D5FFD5'; break;
			case 1: $bg_color = '#FFFFD5'; break;
			case 2: $bg_color = '#FFD5D5'; break;
			case 4: $bg_color = '#D5D5FF'; break;
			default: $bg_color = '#D5D5D5';
		}
		$gb_row->set_attrs('style="background:'.$bg_color.';"');
	}

    public function transaction_type_filter($choice) {
        if ($choice == '__NULL__')
            return array();
        return @unserialize($choice);
    }

	public function warehouse_filter($choice) {
		if ($choice=='__NULL__') return array();
		return array('(warehouse'=>array($choice,''), '|target_warehouse'=>$choice);
	}	

	public function applet($conf, & $opts) {
		$opts['go'] = true; // enable full screen
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$limit = 15;
		switch($conf['type']) {
			// PURCHASE
			case 0: $status = array(1, 2, 3, 4, 5); 
				break;
			// SALE
			case 1: 
			// RENTAL
			case 3: 
			// WAREHOUSE TRANSFER
			case 4: 
				$status = array(1,2,3,4,5,6);
				break;
			// INV. ADJUSTMENT
			case 2: $status = array('');
				break;
			default:
				print(__('Invalid transaction type, please go to applet options and select different one.'));
				return;
		}
		$crits = array('transaction_type'=>$conf['type'], 'status'=>$status);
		if($conf['older']!='all')
			$crits['<=:Created_on'] = date('Y-m-d H:i:s',time()-$conf['older']);

		$me = CRM_ContactsCommon::get_my_record();
		if($conf['my']) {
			$crits['employee'] = array('',$me['id']);
		}

		if($conf['warehouse']) {
			$crits['(warehouse'] = array($conf['warehouse'],'');
			$crits['|target_warehouse'] = $conf['warehouse'];
		}

		
		$sorting = array();
		$cols = array(
							array('field'=>'transaction_id', 'width'=>10, 'label'=>__('Trans. ID')),
							array('field'=>'transaction_type', 'width'=>10, 'label'=>__('Trans. Type')),
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
		$new_def = array(	'country'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_country'),
							'zone'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_state'),
							'transaction_date'=>date('Y-m-d'),
							'employee'=>$me['id'],
							'warehouse'=>Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse'),
							'tax_calculation'=> Variable::get('premium_warehouse_def_tax_calc', false)
							);
		switch($conf['type']) {
		    case 0: $new_def = array_merge($new_def,array('transaction_type'=>0,'terms'=>0,'payment'=>1,'transaction_date'=>date('Y-m-d')));
		        break;
		    case 1: $new_def = array_merge($new_def,array('transaction_type'=>1, 'status'=>2,'payment'=>1, 'payment_type'=>0, 'shipment_type'=>0,'terms'=>0,'transaction_date'=>date('Y-m-d')));
		        break;
		    case 2: $new_def = array_merge($new_def,array('transaction_type'=>2,'transaction_date'=>date('Y-m-d')));
		        break;
		    case 4: $new_def = array_merge($new_def,array('transaction_type'=>4,'transaction_date'=>date('Y-m-d')));
		        break;
		}
		$opts['actions'][] = Utils_RecordBrowserCommon::applet_new_record_button('premium_warehouse_items_orders',$new_def);
		$this->display_module($rb, $conds, 'mini_view');
	}
	
	public function order_serial_addon($arg) {
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$arg['id']),array('id','item_name'));
		$gb = $this->init_module('Utils/GenericBrowser','premium_warehouse_order_serials','premium_warehouse_order_serials');
		$gb->set_table_columns(array(
			array('name'=>'Item'),
			array('name'=>'Serial'),
			array('name'=>'Owner'),
			array('name'=>'Notes'),
			array('name'=>'Shelf')
								));
		foreach ($items as $i) {
			$ret = DB::Execute('SELECT ls.serial, notes, owner, shelf FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial ls ON os.serial_id=ls.id WHERE os.order_details_id=%d ORDER BY ls.serial', array($i['id']));
			while ($row = $ret->FetchRow()) {
				if (!$row['serial']) {
					$row['serial'] = 'n/a';
					$row['notes'] = '';
					$row['owner'] = '';
				}
				$gb->add_row(
					Premium_Warehouse_Items_OrdersCommon::display_item_name($i),
					$row['serial'],
					CRM_ContactsCommon::autoselect_company_contact_format($row['owner']),
					$row['notes'],
					$row['shelf']
				);
			}
		}
		$this->display_module($gb);
	}
	
	public function transaction_history_addon($arg){
		// TODO: service?
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$order = array(array('item_name'=>$arg['id']), array('quantity_on_hand'=>false,'description'=>false)+('item_name'=='item_name'?array('item_name'=>false):array()), array('transaction_date'=>'DESC', 'transaction_id'=>'DESC'));
		$rb->set_button(false);
		$rb->set_defaults(array('item_name'=>$arg['id']));
		$rb->set_header_properties(array(
			'transaction_id'=>array('name'=>__('Trans. ID'), 'width'=>10, 'wrapmode'=>'nowrap'),
			'transaction_date'=>array('name'=>__('Trans. Date'), 'width'=>10, 'wrapmode'=>'nowrap'),
			'transaction_type'=>array('name'=>__('Trans. Type'), 'width'=>10, 'wrapmode'=>'nowrap'),
			'transaction_status'=>array('name'=>__('Trans. Status'), 'width'=>10, 'wrapmode'=>'nowrap')
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
			'item_name'=>array('width'=>'150px', 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>30, 'wrapmode'=>'nowrap'),
			'gross_total'=>array('width'=>'80px', 'wrapmode'=>'nowrap'),
			'tax_value'=>array('width'=>'70px', 'wrapmode'=>'nowrap'),
			'tax_rate'=>array('width'=>'60px', 'wrapmode'=>'nowrap'),
			'sww'=>array('width'=>4, 'wrapmode'=>'nowrap'),
			'markup_discount_rate'=>array('name'=>__('Markup'),'width'=>'80px', 'wrapmode'=>'nowrap'),
			'net_total'=>array('width'=>'80px', 'wrapmode'=>'nowrap'),
			'net_price'=>array('width'=>'100px', 'wrapmode'=>'nowrap'),
			'unit_price'=>array('width'=>'100px', 'wrapmode'=>'nowrap'),
			'gross_price'=>array('width'=>'100px', 'wrapmode'=>'nowrap'),
			'debit'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'credit'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'quantity'=>array('name'=>__('Qty'), 'width'=>'45px', 'wrapmode'=>'nowrap'),
			'serial'=>array('width'=>40, 'wrapmode'=>'nowrap')
		);
		if ($arg['transaction_type']==0) {
			$header_prop['net_price'] = array('name'=>__('Net Cost'), 'width'=>'100px', 'wrapmode'=>'nowrap');
			$header_prop['gross_price'] = array('name'=>__('Gross Cost'), 'width'=>'100px', 'wrapmode'=>'nowrap');
//			if ($arg['status']!=20) {
//				$cols['serial'] = false;
//			}
		}
		if ($arg['tax_calculation']==1) $header_prop['gross_price'] = array('width'=>'0px');
		if ($arg['payment']!=1) {
			$cols['tax_rate'] = false;
			$cols['net_total'] = false;
			$cols['net_price'] = false;
            $cols['unit_price'] = false;
            $cols['markup_discount_rate'] = false;
            $cols['sww'] = false;
			$cols['gross_price'] = false;
			$cols['tax_value'] = false;			
			$cols['gross_total'] = false;
			$cols['quantity'] =  $arg['transaction_type']!=2;
			$cols['debit'] = $arg['transaction_type']==2;			
			$cols['credit'] = $arg['transaction_type']==2;			
			$header_prop['debit'] = array('width'=>20, 'wrapmode'=>'nowrap', 'name'=>__('Debit').' (-)');
			$header_prop['credit'] = array('width'=>20, 'wrapmode'=>'nowrap', 'name'=>__('Credit').' (+)');
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
			$rb->set_additional_actions_method(array($this, 'actions_for_order_details'));
		}
		$order = array(array('transaction_id'=>$arg['id']), $cols, array());
		
        $defaults = array();
        $new_item_types = array(
			__('Inv. Item')=>array('icon'=>Base_ThemeCommon::get_template_file('Premium_Warehouse_Items','inv_item.png'), 'defaults'=>array_merge($defaults,array('item_type'=>0))),
			__('Serialized Item')=>array('icon'=>Base_ThemeCommon::get_template_file('Premium_Warehouse_Items','serialized.png'), 'defaults'=>array_merge($defaults,array('item_type'=>1))),
			__('Non-Inv. Items')=>array('icon'=>Base_ThemeCommon::get_template_file('Premium_Warehouse_Items','non-inv.png'), 'defaults'=>array_merge($defaults,array('item_type'=>2))),
			__('Service')=>array('icon'=>Base_ThemeCommon::get_template_file('Premium_Warehouse_Items','service.png'), 'defaults'=>array_merge($defaults,array('item_type'=>3)))
			);
		
		if(!isset($_SESSION['client']['warehouse_transaction_id']) || $_SESSION['client']['warehouse_transaction_id'] != $arg['id']) {
    		$_SESSION['client']['warehouse_transaction_id'] = $arg['id'];
	    	unset($_SESSION['client']['warehouse_transaction_new_item_id']);
	    }
	    if(isset($_SESSION['client']['warehouse_transaction_new_item_id'])) {
    		$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$_SESSION['client']['warehouse_transaction_new_item_id']);
            $defaults = array('item_name'=>$rec['item_name'],'description'=>$rec['description']);
            if ($arg['transaction_type']<2) {
            	$defaults["last_item_price"] = $rec['last_purchase_price'];
            	$defaults["tax_rate"] = $rec['tax_rate'];
            	$price = ($arg['transaction_type']==0?(isset($rec['last_purchase_price'])&&$rec['last_purchase_price']?$rec['last_purchase_price']:$rec['cost']):(isset($rec['last_sale_price'])&&$rec['last_sale_price']?$rec['last_sale_price']:$rec['net_price']));
            	$price2 = Utils_CurrencyFieldCommon::get_values($price);
            	$gross_price = Utils_CurrencyFieldCommon::format_default($price2[0]*(100+Data_TaxRatesCommon::get_tax_rate($rec['tax_rate']))/100, $price2[1]);
				$defaults["net_price"] = $price;
				$defaults["gross_price"] = $gross_price;
				$defaults['quantity'] = 1;
            }
    		$rb->set_defaults($defaults);
    		eval_js('$(\'add_in_table_row\').style.display=\'\';warehouse_itemAutocompleter.hide();focus_by_id(\'quantity\')');
        }
        if(Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit',$arg) && $arg['transaction_type']==0 && !$arg['payment'])
    		$rb->set_button($this->create_callback_href(array($this, 'jump_to_check_in_new_item'), array($arg)));
		else
			$rb->enable_quick_new_records();
		$rb->set_defaults(array('transaction_id'=>$arg['id']));
		$rb->set_header_properties($header_prop);
		$rb->disable_pdf();
		$this->display_module($rb,$order,'show_data');
	}
	
	public function jump_to_check_in_new_item($trans) {
		$box = ModuleManager::get_instance('/Base_Box|0');
        $box->push_main('Premium_Warehouse_Items_Orders','check_in_new_item',array($trans));
		return false;
	}
	
	public function check_in_new_item($trans) {
		$box = ModuleManager::get_instance('/Base_Box|0');
		if ($this->is_back()) {
			$box->pop_main();
			return false;
		}
		
		load_js('modules/Premium/Warehouse/Items/Orders/js/check_in_item.js');
		
		$form = $this->init_module('Libs_QuickForm');
		$theme = $this->init_module('Base_Theme');
		
		$manufacturers = array(''=>'---');
		$rec = CRM_ContactsCommon::get_companies(array('group'=>'manufacturer'));
		foreach ($rec as $r) $manufacturers[$r['id']] = $r['company_name'];
		
		$item_types = Utils_CommonDataCommon::get_array('Premium_Warehouse_Items_Type', true);
		
		/** new item source checkbox **/
		$form->addElement('checkbox', 'brand_new', __('Create new item'), '', array('onchange'=>'item_source_changed(this.checked);', 'id'=>'brand_new'));
		eval_js('item_source_changed($("brand_new").checked);');
		
		$form->setDefaults(array('brand_new'=>true));
		
		/** item **/
		$theme->assign('item_icon', Base_ThemeCommon::get_template_filename('Premium_Warehouse_Items','icon.png'));
		$theme->assign('item_caption', __('Item'));
		
		/** new item **/
		$form->addElement('select', 'item_type', __('Item Type'), $item_types, array('onchange'=>'item_type_changed(this.value);', 'id'=>'item_type'));
		eval_js('item_type_changed($("item_type").value);');
		$form->addElement('text', 'new_item_name', __('New Item name'));
		$form->addElement('text', 'product_code', __('Product Code'));
		$form->addElement('text', 'upc', __('UPC'));
		$form->addElement('text', 'weight', __('Weight'));
		Premium_Warehouse_ItemsCommon::QFfield_item_category($form, 'category', __('Category'), 'edit', '');
		$form->addElement('text', 'volume', __('Volume'));
		$form->addElement('text', 'manufacturer_part_number', __('Manufacturer Part Number'));
		$form->addElement('select', 'manufacturer', __('Manufacturer'), $manufacturers);
		$form->addElement('textarea', 'item_description', __('Item Description'));
		
		$theme->assign('brand_new_section_id', 'brand_new_section');
		$theme->assign('brand_new_long_section_id', 'brand_new_long_section');
		
		/** existing item **/
		Premium_Warehouse_Items_OrdersCommon::QFfield_item_name($form, 'item_name', __('Item Name'), 'edit', '', array(), array('id'=>-1, 'transaction_id'=>$trans['id']));
		
		$theme->assign('existing_item_section_id', 'existing_item_section');
		
		/** order details fields **/
		$theme->assign('order_detail_icon', Base_ThemeCommon::get_template_filename('Premium_Warehouse_Items_Orders','icon.png'));
		$theme->assign('order_detail_caption', __('Order Details'));
		
		$form->addElement('text', 'quantity', __('Quantity'), array('id'=>'quantity'));
		$form->addElement('text', 'description', __('Description'), array('id'=>'description'));
		
		/** serials **/
		$theme->assign('serials_icon', Base_ThemeCommon::get_template_filename('Premium_Warehouse_Items_Location','icon.png'));
		$theme->assign('serials_caption', __('Serials'));
		
		Premium_Warehouse_Items_OrdersCommon::display_serials($form, '', '', 'edit', '', array(), array('id'=>-1, 'transaction_id'=>$trans['id']));

		$theme->assign('serials_section_id', 'serials_section');
		
		if ($form->validate()) {
			$vals = $form->exportValues();
			if (isset($vals['brand_new']) && $vals['brand_new']) {
				$item = array();
				foreach (array(
					'new_item_name' => 'item_name',
					'item_type' => 'item_type',
					'product_code' => 'product_code',
					'upc' => 'upc',
					'weight' => 'weight',
					'category' => 'category',
					'volume' => 'volume',
					'manufacturer_part_number' => 'manufacturer_part_number',
					'manufacturer' => 'manufacturer',
					'item_description' => 'description'
				) as $k=>$v) $item[$v] = $vals[$k];
				$item_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items', $item);
			} else {
				$item_id = $vals['item_name'];
				$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item_id);
			}
			$files = Premium_Warehouse_Items_WebcamCommon::get_photos('Premium_Warehouse_check_in');
			foreach ($files as $f)
				Utils_AttachmentCommon::add('premium_warehouse_items/'.$item_id,0,Acl::get_user(),'Webcam photo','image.jpg',$f,array('Premium_Warehouse_ItemsCommon','search_format'),array($item_id));

			$order_details = array(
				'transaction_id'=>$trans['id'],
				'item_name'=>$item_id,
				'description'=>$vals['description'],
				'quantity'=>$vals['quantity']
			);
			Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details', $order_details);
			$box->pop_main();
			return false;
		}

		Base_ActionBarCommon::add('delete', __('Cancel'), $this->create_back_href());
		Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href());
		if (ModuleManager::is_installed('Premium_Warehouse_Items_Webcam')>=0)
			Premium_Warehouse_Items_WebcamCommon::attach_webcam_button('Premium_Warehouse_check_in', 'view');
		
		$form->assign_theme('form', $theme);
		$theme->display('check_in_item');
		return true;
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

	public function revise_items(&$po_form, $items, $trans) {
		$po_form->addElement('select', 'payment_type', __('Payment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Payment_Types'));
		$po_form->addElement('text', 'payment_no', __('Payment No'));
		$po_form->addElement('select', 'terms', __('Terms'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Terms'));
		$po_form->addElement('select', 'shipment_type', __('Shipment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Shipment_Types'));
		$po_form->setDefaults($trans);

		$taxes = Data_TaxRatesCommon::get_tax_rates();
		$po_form->addElement('static', 'item_header', '');
		$po_form->setDefaults(array('item_header'=>__('Please revise items quantity, price and tax rate')));
		foreach ($items as $v) {
			$elements = array();
			$elements[] = $po_form->createElement('text', 'quantity', __('Price'), array('style'=>'width:auto;'));
			$elements[] = $po_form->createElement('currency', 'net_price', __('Price'));
			$elements[] = $po_form->createElement('select', 'tax_rate', __('Tax'), $taxes, array('style'=>'width:auto;'));
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
			$elements[] = $split->createElement('text', 'original_qty', __('Price'), array('id'=>'original_item__'.$v['id'] ,'onkeyup'=>'$(\'total_item__'.$v['id'].'\').innerHTML=parseInt(this.value)+parseInt($(\'new_item__'.$v['id'].'\').value);'));
			$elements[] = $split->createElement('text', 'new_qty', __('Price'), array('id'=>'new_item__'.$v['id'] ,'onkeyup'=>'$(\'total_item__'.$v['id'].'\').innerHTML=parseInt(this.value)+parseInt($(\'original_item__'.$v['id'].'\').value);'));
			$elements[] = $split->createElement('static', 'total', __('Total'));
			$split->addGroup($elements, 'item__'.$v['id'], Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
			$split->setDefaults(array('item__'.$v['id']=>array('original_qty'=>$v['quantity'], 'new_qty'=>0, 'total'=>'<b>'.__('Total').':</b> <span id="total_item__'.$v['id'].'">'.$v['quantity'].'</span>')));
		}
		return $split;
	}
	
	public function split_items_process($items, $trans, $vals) {
		$id = null;
		foreach ($items as $v) {
			$old = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders_details', $v['id']);
			if (intval($vals['item__'.$v['id']]['original_qty'])!=0) Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], array('quantity'=>intval($vals['item__'.$v['id']]['original_qty'])));
			else Utils_RecordBrowserCommon::delete_record('premium_warehouse_items_orders_details', $v['id']);
			if (intval($vals['item__'.$v['id']]['new_qty'])>0) {
				if ($id===null) {
                    $split_trans_old = $trans['split_transaction'];
                    $trans['split_transaction'] = array($trans['id']);
					$id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders', $trans);
                    $split_trans_old[] = $id;
                    Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], array('split_transaction' => $split_trans_old));
					$this->set_module_variable('split_transaction_id', $id);
				}
				$old['transaction_id'] = $id;
				$old['quantity'] = intval($vals['item__'.$v['id']]['new_qty']);
				Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details', $old);
			}
		}
	}
	
	public function check_if_items_available($trans, $items) {
		$items_check = $this->init_module('Libs/QuickForm'); 
		$items_check->addElement('static', 'item_header', '', __('The following items are unavailable'));
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

	public function get_select_serials_form($trans, $items, & $any_item) {
		$serials = $this->init_module('Libs/QuickForm');
		$serials->addElement('static', 'item_header', '');
		$serials->setDefaults(array('item_header'=>__('Please select serial numbers for Serialized Items')));
		load_js('modules/Premium/Warehouse/Items/Orders/js/serials.js');
		foreach ($items as $v) {
			if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1) {
				$any_item = true; 
				$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku', 'warehouse'), array($v['item_name'], $trans['warehouse']));
				if (!$loc_id) continue; // failsafe
				$item_serials_raw = DB::GetAssoc('SELECT id, serial FROM premium_warehouse_location_serial WHERE location_id=%d ORDER BY serial', array($loc_id));
				$item_serials = array();
				$empty = 0;
				$count_serials = 0;
				foreach ($item_serials_raw as $k=>$v2) {
					$count_serials++;
					if (!$v2) {
						if (!$empty) $item_serials['NULL'] = __('n/a');
						$empty++;
					} else $item_serials[$k] = $v2;
				}
				if ($count_serials==0) $item_serials['NULL'] = __('n/a');
				eval_js('allowed_empty_serials['.$v['id'].'] = '.(string)$empty.';');
				for ($i=0;$i<$v['quantity'];$i++)
					$serials->addElement('select', 'serial__'.$v['id'].'__'.$i, Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true), $item_serials, array('onchange'=>'check_serial_duplicates('.$v['id'].');', 'id'=>'serial__'.$v['id'].'__'.$i));
				eval_js('check_serial_duplicates('.$v['id'].');');
			}
		}
		return $serials;
	}
	
	public function change_status_leightbox($trans, $status) {
		if ($this->isset_module_variable('split_transaction_id')) {
			$id = $this->get_module_variable('split_transaction_id');
			$this->unset_module_variable('split_transaction_id');
			print('<b>'.__('The transaction was split succesfully.').'<br>'.__('The ID of newly created transaction is: %s', array(Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items_orders', 'transaction_id', $id))).'</b>');
		}
		$p = $trans['payment'];
		$lp = $this->init_module('Utils/LeightboxPrompt');
		if ($trans['transaction_type']==0 && isset($trans['id'])) { // Purchase
            // if not payment and status is other delivered or cancelled,
            // then set status to shipment received
			if (!$p && $status<20) $status = 4;
			switch ($status) {			
				case '': // Purchase - New
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$po_form = $this->init_module('Libs_QuickForm'); 

					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($po_form, 'employee', 'Employee', 'add', null, $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					$po_form->addElement('select', 'warehouse', __('Warehouse'), $warehouses);

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

					$lp->add_option('po', __('PO'), null, $po_form);

					$quote_form = $this->init_module('Libs/QuickForm');
					$quote_form->addElement('datepicker', 'expiration_date', __('Expiration Date'));
					$quote_form->setDefaults(array('expiration_date'=>date('Y-m-d', strtotime('+7 days'))));
					$lp->add_option('quote', __('Quote'), null, $quote_form);
					
					$this->display_module($lp, array(__('Ready to process?')));
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
				case 1: // Purchase - Purchase Quote
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$po_form = $this->init_module('Libs_QuickForm'); 

					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($po_form, 'employee', 'Employee', 'add', null, $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					$po_form->addElement('select', 'warehouse', __('Warehouse'), $warehouses);

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

					$lp->add_option('po', __('PO'), null, $po_form);

					$lp->add_option('cancel', __('Cancel'), null, null);
					
					$this->display_module($lp, array(__('Ready to process?')));
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
				case 2: // Purchase - Purchase Order
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					if (empty($items)) {
						$this->href = Utils_TooltipCommon::open_tag_attrs(__('No items were saved in this transaction, cannot proceed with processing.'),false);
						break;
					}
					$lp->add_option('ship', __('Accepted'), null, null);
					$lp->add_option('onhold', __('On Hold'), null, null);
					$this->display_module($lp, array(__('Purchase Order accepted?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='ship')?3:5;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 3: // Purchase - New shipment
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
					$lp->add_option('received', __('Items Received'), null, $ship_received);
					$lp->add_option('onhold', __('Put On Hold'), null, null);
					$this->display_module($lp, array(__('Was the shipment received?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='received')?4:5;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 4: // Purchase - shipment received
					$serials = $this->init_module('Libs/QuickForm');
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$serials->addElement('static', 'item_header', '');
					$serials->setDefaults(array('item_header'=>__('Please enter serial numbers and notes for Serialized Items')));
					$any_item = false;
					foreach ($items as $v) {
						if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1) {
							$any_item = true; 
							$ret = DB::Execute('SELECT ls.serial, notes, shelf FROM premium_warehouse_location_orders_serial os LEFT JOIN premium_warehouse_location_serial ls ON os.serial_id=ls.id WHERE os.order_details_id=%d ORDER BY ls.serial', array($v['id']));
							for ($i=0;$i<$v['quantity'];$i++) {
								$elements = array();
								$elements[] = $serials->createElement('text', 'serial', __('Serial'), Utils_TooltipCommon::open_tag_attrs(__('Serial')));
								$elements[] = $serials->createElement('text', 'note', __('Note'), 'style="width:200px;"'.Utils_TooltipCommon::open_tag_attrs(__('Note')));
								$elements[] = $serials->createElement('text', 'shelf', __('Shelf'), 'style="width:200px;"'.Utils_TooltipCommon::open_tag_attrs(__('Shelf')));
								$serials->addGroup($elements, 'serial__'.$v['id'].'__'.$i, Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
								$row = $ret->FetchRow();
								if ($row) {
									$serials->setDefaults(array(
										'serial__'.$v['id'].'__'.$i.'[serial]' => $row['serial'],
										'serial__'.$v['id'].'__'.$i.'[note]' => $row['notes'],
										'serial__'.$v['id'].'__'.$i.'[shelf]' => $row['shelf']
									));
								}
							}
						}
					}
					$split = $this->split_items_form($items);

					$lp->add_option('received', __('All items delivered'), null, $any_item?$serials:null);
					$lp->add_option('incomplete', __('Incomplete Shipment'), null, $split);
					$lp->add_option('onhold', __('Put On Hold'), null, null);
					$this->display_module($lp, array(__('Final Inspection. All items received?')));
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
								$serials = array();
								for ($i=0;$i<$v['quantity'];$i++) {
									$serials[$i] = array();
									$serials[$i]['serial'] = $vals['form']['serial__'.$v['id'].'__'.$i]['serial'];
									$serials[$i]['note'] = $vals['form']['serial__'.$v['id'].'__'.$i]['note'];
									$serials[$i]['shelf'] = $vals['form']['serial__'.$v['id'].'__'.$i]['shelf'];
								}
								Premium_Warehouse_Items_OrdersCommon::set_serials($v, $trans, $serials);
							}
						}
						location(array());
					}
					break;
				case 5: // Purchase - On hold
					$lp->add_option('items_available', __('Items Available'), null, null);
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$split = $this->split_items_form($items);
					$lp->add_option('partial_order', __('Partial Order'), null, $split);
					$lp->add_option('cancel', __('Cancel'), null, null);
					$this->display_module($lp, array(__('Final Inspection. All items received?')));
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
		} elseif ($trans['transaction_type']==1) { // Sale
			if (!$p && $status<2) $status = 2;
			switch ($status) {			
				case -2:
				case -1:
					$new_form = $this->init_module('Libs_QuickForm');
					$me = CRM_ContactsCommon::get_my_record();
					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($new_form, 'employee', 'Employee', 'add', $me['id'], $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					if (count($records)!=1)
						$new_form->addElement('select', 'warehouse', __('Warehouse'), $warehouses);
					else
						$the_one_warehouse = $v['id'];
					$new_form->setDefaults(array('warehouse'=>!$trans['warehouse']?Base_User_SettingsCommon::get('Premium_Warehouse', 'my_warehouse'):$trans['warehouse']));
					$new_form->setDefaults(array('employee'=>$me['id']));
					$lp->add_option('new', __('Order Received'), null, $new_form);
					$lp->add_option('cancel', __('Cancel'), null, null);
					$items_availability_table = $this->get_items_availability_table($trans);
					$this->display_module($lp, array(__('Recieve Online Order'), array(), $items_availability_table));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if ($vals['option']=='cancel') {
							$vals['form'] = array();
							$vals['form']['status'] = 21;
							Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						} else {
							if (isset($the_one_warehouse)) $vals['form']['warehouse'] = $the_one_warehouse;
							if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
							else $vals['form']['status'] = 2;
							if (is_numeric($vals['form']['employee']) && (!isset($vals['form']['warehouse']) || is_numeric($vals['form']['warehouse']))) 
								Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						}
						location(array());
					}
					break;
				case '':
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$so_form = $this->init_module('Libs_QuickForm');

					$so_form->addElement('currency', 'shipment_cost', __('Shipment Cost'));
					$so_form->addElement('currency', 'handling_cost', __('Handling Cost'));

					$table_rows = Utils_RecordBrowserCommon::init('premium_warehouse_items_orders');
					CRM_ContactsCommon::QFfield_contact($so_form, 'employee', 'Employee', 'add', null, $table_rows['Employee']);
					$warehouses = array(''=>'---');
					$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
					foreach ($records as $v) $warehouses[$v['id']] = $v['warehouse'];
					$so_form->addElement('select', 'warehouse', __('Warehouse'), $warehouses);

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

					$lp->add_option('so', __('Order Received'), null, $so_form);

					$quote_form = $this->init_module('Libs/QuickForm');
					$quote_form->addElement('datepicker', 'expiration_date', __('Expiration Date'));
					$quote_form->setDefaults(array('expiration_date'=>date('Y-m-d', strtotime('+7 days'))));
					$lp->add_option('quote', __('Sale Quote'), null, $quote_form);

					$items_unavailable = $this->check_if_items_available($trans, $items);
					if ($trans['payment_type']==0 && $trans['shipment_type']==0) $lp->add_option('all_done', __('Paid & Delievered'), null, $items_unavailable);
					
					$this->display_module($lp, array(__('Ready to process?')));
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
					$so_form->addElement('select', 'warehouse', __('Warehouse'), $warehouses);

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

					$lp->add_option('so', __('SO'), null, $so_form);

					$lp->add_option('cancel', __('Cancel'), null, null);
					
					$this->display_module($lp, array(__('Ready to process?')));
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
						$this->href = Utils_TooltipCommon::open_tag_attrs(__('No items were saved in this transaction, cannot proceed with processing.'),false);
						break;
					}
					
					$items_unavailable = $this->check_if_items_available($trans, $items);
					$serials = $this->get_select_serials_form($trans, $items, $any_item);

					if ($trans['payment_type']==0 && $trans['shipment_type']==0 && $items_unavailable===null) $lp->add_option('quick_delivery', $p?__('Paid & Delivered'):__('Delivered'), null, ($any_item?$serials:null));

					$lp->add_option('accepted', __('Payment Accepted'), null, null);
					$lp->add_option('onhold', __('Payment Declined - Put On Hold'), null, null);

					$this->display_module($lp, array($p?__('Payment processing'):__('Check out accepted?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						switch ($vals['option']) {
							case 'accepted': $vals['form']['status'] = $p?3:4; break; 
							case 'onhold': $vals['form']['status'] = 5; break; 
							case 'quick_delivery': if($items_unavailable===null)$vals['form']['status']=20; break; 
						}
						if ($vals['form']['status']==20) {
							foreach ($items as $v) {
								$serials = array();
								for ($i=0;$i<$v['quantity'];$i++)
									if (isset($vals['form']['serial__'.$v['id'].'__'.$i])) $serials[] = $vals['form']['serial__'.$v['id'].'__'.$i];
								Premium_Warehouse_Items_OrdersCommon::selected_serials($v, $trans, $serials);
							}
						}
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 3:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$items_unavailable = $this->check_if_items_available($trans, $items);
					$lp->add_option('available', __('Items Ready for Packing'), null, $items_unavailable);
					// check items qty

					$any_item = false;
					$ship_received = $this->get_select_serials_form($trans, $items, $any_item);

					if (!$any_item) $ship_received = $this->init_module('Libs/QuickForm');
					$emps_ids = CRM_ContactsCommon::get_contacts(Premium_Warehouse_ItemsCommon::employee_crits(), array(), array('last_name'=>'ASC','first_name'=>'ASC'));
					$emps = array(''=>'---');
					$my_id = '';
					foreach ($emps_ids as $v) {
						if ($v['login']==Acl::get_user()) $my_id = $v['id'];
						$emps[$v['id']] = CRM_ContactsCommon::contact_format_no_company($v,true);
					}
					$ship_type = array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Shipment_Types');
					$ship_received->addElement('select', 'shipment_type', __('Shipment - type'), $ship_type);
					$ship_received->addElement('datepicker', 'shipment_date', __('Shipment - send date'));
					$ship_received->addElement('select', 'shipment_employee', __('Shipment - sent by'), $emps);
					$ship_received->addElement('datepicker', 'shipment_eta', __('Shipment - ETA'));
					$ship_received->addElement('text', 'shipment_no', __('Shipment No'));
					$trans['shipment_date'] = date('Y-m-d');
					$trans['shipment_employee'] = $my_id;
					$trans['shipment_eta'] = date('Y-m-d',time()+3600*36);
					$ship_received->setDefaults(array('shipment_date'=>$trans['shipment_date'], 'shipment_employee'=>$trans['shipment_employee'], 'shipment_eta'=>$trans['shipment_eta'], 'shipment_no'=>$trans['shipment_no'], 'shipment_type'=>$trans['shipment_type']));
					$lp->add_option('ship', __('Packed & Ship'), null, $ship_received);

					$lp->add_option('onhold', __('Put On Hold'), null, null);

					$this->display_module($lp, array(__('Are all items available?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						switch ($vals['option']) {
							case 'available': $vals['form']['status'] = 4; break; 
							case 'onhold': $vals['form']['status'] = 5; break; 
							case 'ship': $vals['form']['status'] = 7; break; 
						}
						if ($items_unavailable!==null && $vals['form']['status']!=5) break;

						if ($vals['form']['status']==7) {
							foreach ($items as $v) {
								$serials = array();
								for ($i=0;$i<$v['quantity'];$i++)
									if (isset($vals['form']['serial__'.$v['id'].'__'.$i])) $serials[] = $vals['form']['serial__'.$v['id'].'__'.$i];
								Premium_Warehouse_Items_OrdersCommon::selected_serials($v, $trans, $serials);
							}
						}
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 4:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$items_unavailable = $this->check_if_items_available($trans, $items);

					$any_item = false;
					$serials = $this->get_select_serials_form($trans, $items, $any_item);

					$lp->add_option('available', __('Package ready'), null, ($items_unavailable===null)?($any_item?$serials:null):$items_unavailable);
					// check items qty
					$lp->add_option('onhold', __('Put On Hold'), null, null);
					$this->display_module($lp, array(__('Final Inspection: All items ready and packed?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='available')?6:5;
						if ($items_unavailable!==null && $vals['form']['status']==6) break;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						if ($vals['form']['status']==6) {
							foreach ($items as $v) {
								$serials = array();
								for ($i=0;$i<$v['quantity'];$i++)
									if (isset($vals['form']['serial__'.$v['id'].'__'.$i])) $serials[] = $vals['form']['serial__'.$v['id'].'__'.$i];
								Premium_Warehouse_Items_OrdersCommon::selected_serials($v, $trans, $serials);
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
					$ship_type = array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Shipment_Types');
					$ship_received->addElement('select', 'shipment_type', __('Shipment - type'), $ship_type);
					$ship_received->addElement('datepicker', 'shipment_date', __('Shipment - send date'));
					$ship_received->addElement('select', 'shipment_employee', __('Shipment - sent by'), $emps);
					$ship_received->addElement('datepicker', 'shipment_eta', __('Shipment - ETA'));
					$ship_received->addElement('text', 'shipment_no', __('Shipment No'));
					$trans['shipment_date'] = date('Y-m-d');
					$trans['shipment_employee'] = $my_id;
					$trans['shipment_eta'] = date('Y-m-d',time()+3600*36);
					$ship_received->setDefaults(array('shipment_date'=>$trans['shipment_date'], 'shipment_employee'=>$trans['shipment_employee'], 'shipment_eta'=>$trans['shipment_eta'], 'shipment_no'=>$trans['shipment_no'], 'shipment_type'=>$trans['shipment_type']));

					$lp->add_option('pickup', __('Pickup'), null, null);
					$lp->add_option('ship', __('Ship'), null, $ship_received);
					$this->display_module($lp, array(__('Select Shipping method.')));
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
				case 5: // Sale - On hold
					$lp->add_option('items_available', __('Items Available'), null, null);
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$split = $this->split_items_form($items);
					$lp->add_option('partial_order', __('Partial Order'), null, $split);
					$lp->add_option('cancel', __('Cancel'), null, null);
					$this->display_module($lp, array(__('Are all items available?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$up_vals = array();
						if ($vals['option']=='items_available') {
							$up_vals['status'] = $p?2:'';
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
					$lp->add_option('delivered', __('Delivered'), null, null);
					$lp->add_option('missing', __('Missing'), null, null);
					$this->display_module($lp, array(__('Was the shipment delivered?')));
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
					if (!$trans['warehouse']) {
						$this->href = Utils_TooltipCommon::open_tag_attrs(__('Source warehouse not set, cannot proceed with processing.'),false);
						break;
					}
					$lp->add_option('so', __('Transfer pending'), null, null);

					$quote_form = $this->init_module('Libs/QuickForm');
					$quote_form->addElement('datepicker', 'expiration_date', __('Expiration Date'));
					$quote_form->setDefaults(array('expiration_date'=>date('Y-m-d', strtotime('+7 days'))));
					$lp->add_option('quote', __('Quote'), null, $quote_form);
					
					$this->display_module($lp, array(__('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='quote')?1:2; 
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 1:
					$lp->add_option('so', __('SO'), null, null);

					$lp->add_option('cancel', __('Cancel'), null, null);
					
					$this->display_module($lp, array(__('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='po')?2:21; 
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 2:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					if (empty($items)) {
						$this->href = Utils_TooltipCommon::open_tag_attrs(__('No items were saved in this transaction, cannot proceed with processing.'),false);
						break;
					}
					$items_unavailable = $this->check_if_items_available($trans, $items);
					$lp->add_option('available', __('Items Available'), null, $items_unavailable);
					// check items qty
					$lp->add_option('onhold', __('Put On Hold'), null, null);
					$this->display_module($lp, array(__('Are all items available?')));
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

					$any_item = false;
					$serials = $this->get_select_serials_form($trans, $items, $any_item);

					$lp->add_option('available', __('Items Available'), null, ($items_unavailable===null)?($any_item?$serials:null):$items_unavailable);

					// check items qty
					$lp->add_option('onhold', __('Put On Hold'), null, null);
					$this->display_module($lp, array(__('Final Inspection: All items available?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='available')?5:4;
						if ($items_unavailable!==null && $vals['form']['status']==5) break;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						$trans['status'] = $vals['form']['status'];
						if ($vals['form']['status']==5) {
							foreach ($items as $v) {
								$serials = array();
								for ($i=0;$i<$v['quantity'];$i++)
									if (isset($vals['form']['serial__'.$v['id'].'__'.$i])) $serials[] = $vals['form']['serial__'.$v['id'].'__'.$i];
								Premium_Warehouse_Items_OrdersCommon::selected_serials($v, $trans, $serials);
							}
						}
						location(array());
					}
					break;
				case 5:
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$ship_received = $this->init_module('Libs/QuickForm');
					$emps_ids = CRM_ContactsCommon::get_contacts(Premium_Warehouse_ItemsCommon::employee_crits(), array(), array('last_name'=>'ASC','first_name'=>'ASC'));
					$emps = array(''=>'---');
					$my_id = '';
					foreach ($emps_ids as $v) {
						if ($v['login']==Acl::get_user()) $my_id = $v['id'];
						$emps[$v['id']] = CRM_ContactsCommon::contact_format_no_company($v,true);
					}
					$ship_received->addElement('datepicker', 'shipment_date', __('Shipment - send date'));
					$ship_received->addElement('select', 'shipment_employee', __('Shipment - sent by'), $emps);
					$ship_received->addElement('datepicker', 'shipment_eta', __('Shipment - ETA'));
					$ship_received->addElement('text', 'shipment_no', __('Shipment No'));
					$ship_received->setDefaults(array('shipment_date'=>date('Y-m-d'), 'shipment_employee'=>$my_id));

					$lp->add_option('pickup', __('Pickup'), null, null);
					$lp->add_option('ship', __('Ship'), null, $ship_received);
					$this->display_module($lp, array(__('Select Shipping method.')));
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
						$trans['status'] = $vals['form']['status'];
						if ($vals['form']['status'] == 20) {
							foreach ($items as $v) {
								$ret = DB::Execute('SELECT serial_id FROM premium_warehouse_location_orders_serial WHERE order_details_id=%d', array($v['id']));
								$serials = array();
								while ($row = $ret->FetchRow())
									$serials[] = $row['serial_id'];
								Premium_Warehouse_Items_OrdersCommon::selected_serials($v, $trans, $serials);
							}
						}
						location(array());
					}
					break;
				case 4: // WAREHOUSE TRANSFER - On hold
					$lp->add_option('items_available', __('Items Available'), null, null);
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$split = $this->split_items_form($items);
					$lp->add_option('partial_order', __('Partial Order'), null, $split);
					$lp->add_option('cancel', __('Cancel'), null, null);
					$this->display_module($lp, array(__('Are all items available?')));
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
					if($trans['created_by']==Acl::get_user()) {
//						$this->href = Utils_TooltipCommon::open_tag_attrs(__('Only target warehouse employee can mark this transfer as delivered.'),false);
//						break;					
					}
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));

					$lp->add_option('delivered', __('Delivered'), null, null);
					$lp->add_option('missing', __('Missing'), null, null);
					$this->display_module($lp, array(__('Was the shipment delivered?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='delivered')?20:22;
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						if ($vals['form']['status']==20) {
							foreach ($items as $v) {
								$ret = DB::Execute('SELECT serial_id FROM premium_warehouse_location_orders_serial WHERE order_details_id=%d', array($v['id']));
								$loc_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_location', array('item_sku', 'warehouse'), array($v['item_name'], $trans['target_warehouse']));
								while ($row = $ret->FetchRow()) {
									DB::Execute('UPDATE premium_warehouse_location_serial SET location_id=%d WHERE id=%d', array($loc_id, $row['serial_id']));
								}
							}
						}
						location(array());
					}
					break;
			}
		} elseif ($trans['transaction_type']==2) {
			switch ($status) {			
				case '':
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					if (empty($items)) {
						$this->href = Utils_TooltipCommon::open_tag_attrs(__('No items were saved in this transaction, cannot proceed with processing.'),false);
						break;
					}
					$lp->add_option('yes', __('Yes'), null, null);
					$lp->add_option('no', __('No'), null, null);
					
					$this->display_module($lp, array(__('Close Inv. Adjustment. Are you sure?')));
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

	public function get_items_availability_table($trans) {
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
		$gb = $this->init_module('Utils_GenericBrowser',null,'item_availability');
		$warehouses = array();
		$warehouse_score = array();
		$totals = array();
		$qts = array();
		$records = Utils_RecordBrowserCommon::get_records('premium_warehouse');
		foreach ($records as $v) {
			$warehouses[$v['id']] = $v['warehouse'];
			$warehouse_score[$v['id']] = 0;
		}
		foreach ($items as $k=>$v) {
			$item_id = $v['item_name'];
			$totals[$item_id] = 0;
			$qts[$item_id] = array();
			$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', array('item_sku'=>$item_id));
			foreach ($locs as $l) {
				$qts[$item_id][$l['warehouse']] = $l['quantity'];
				$totals[$item_id] += $l['quantity'];
				$warehouse_score[$l['warehouse']] += min($l['quantity'], $v['quantity']);
				if ($l['quantity']>$v['quantity']) $warehouse_score[$l['warehouse']] += ($l['quantity']-$v['quantity'])/1000;
				if ($l['quantity']>=$v['quantity']) $warehouse_score[$l['warehouse']] += 1000;
			}
		}
		arsort($warehouse_score);
		$header = array();
		$header[] = 'Item Name';
		$header[] = 'Required Quantity';
		foreach ($warehouse_score as $w=>$v) {
			if ($v==0) {
				unset($warehouse_score[$w]);
				continue;
			}
			$header[] = $warehouses[$w];
		}
		$gb->set_table_columns($header);
		foreach ($items as $i) {
			$item_id = $i['item_name'];
			$row = array();
			$row[] = Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $item_id, 'item_name');
			if ($i['quantity']>$totals[$item_id]) $qty_req = '<span style="color:red;"><b>'.$i['quantity'].'</b></span>';
			else $qty_req = $i['quantity'];
			$row[] = $qty_req;
			foreach ($warehouse_score as $w=>$v) {
				if (!isset($qts[$item_id][$w])) $qts[$item_id][$w] = 0;
				if ($qts[$item_id][$w]<$i['quantity']) $qts[$item_id][$w] = '<span style="color:#FF9999;"><b>'.$qts[$item_id][$w].'</b></span>';
				else $qts[$item_id][$w] = '<span style="color:#009911;"><b>'.$qts[$item_id][$w].'</b></span>';
				$row[] = $qts[$item_id][$w];
			}
			$gb->add_row_array($row);
		}
		$gb->set_inline_display();
		$html = '<br><br>'.$this->get_html_of_module($gb);
		return $html;
	}
	
	public function contact_orders_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders');
		$order = array(
			array('contact'=>$arg['id']), 
			array(), 
			array('transaction_date'=>'DESC', 'transaction_id'=>'DESC'));
		$rb->set_button(false);
		$rb->set_defaults(array('contact'=>$arg['id']));
		$this->display_module($rb,$order,'show_data');
	}

	public function company_orders_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders');
		$order = array(
			array('company'=>$arg['id']), 
			array(), 
			array('transaction_date'=>'DESC', 'transaction_id'=>'DESC'));
		$rb->set_button(false);
		$rb->set_defaults(array('company'=>$arg['id']));
		$this->display_module($rb,$order,'show_data');
	}

	public function get_href() {
		return $this->href;
	}

	public function caption(){
		return $this->rb?$this->rb->caption():'';
	}
}

?>