<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Janusz Tylek <jtylek@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-salesreport
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_SalesReport extends Module {
	private $cats = array();
	private $format = '';
	private $columns = array();
	private $range_type = '';
	private $rbr = null;

	public function construct() {
		$this->rbr = $this->init_module('Utils/RecordBrowser/Reports');
	}

	public function recalculate() {
		Premium_Warehouse_SalesReportCommon::recalculate();
	}
	
	public function currency_exchange_editor() {
		if ($this->is_back())
			return false;
		$currency = Variable::get('premium_warehouse_ex_currency');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());

		$form = $this->init_module('Libs/QuickForm');
		$opts = Utils_CurrencyFieldCommon::get_currencies();
		unset($opts[$currency]);
		$form->addElement('checkbox', 'show_missing', 'Show only missing', null, array('onclick'=>$form->get_submit_form_js()));
		$form->addElement('select', 'sel_curr', 'Show only missing', $opts, array('onchange'=>$form->get_submit_form_js()));

		$show_missing = $this->get_module_variable('show_missing',1);
		$sel_curr = $this->get_module_variable('sel_curr',$currency==1?2:1);
		if ($form->validate()) {
			$show_missing = $form->exportValue('show_missing');
			$sel_curr = $form->exportValue('sel_curr');
			$this->set_module_variable('show_missing',$show_missing?1:0);
			$this->set_module_variable('sel_curr',$sel_curr);
		}
		$form->setDefaults(array('show_missing'=>$show_missing, 'sel_curr'=>$sel_curr));
		$form->display();

		if (!$show_missing) $show_missing = '';
		else $show_missing = 'AND re.exchange_rate IS NULL ';

		$gb = $this->init_module('Utils/GenericBrowser', null, 'currency_exchange_editor');
		$query = 'SELECT re.exchange_rate, o.f_transaction_date, od.f_net_price, o.id AS order_id FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_exchange AS re ON re.order_id=o.id AND re.currency=%d WHERE od.active=1 '.$show_missing.'AND (o.f_transaction_type=0 OR o.f_transaction_type=1) AND f_net_price!=\'\' AND f_net_price LIKE '.DB::Concat(DB::qstr('%'), DB::qstr('__'.$sel_curr)).' GROUP BY od.f_transaction_id';
		$limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM ('.$query.') AS tmp', array($sel_curr)));
		$ret = DB::SelectLimit($query, $limit['numrows'], $limit['offset'], array($sel_curr));
		$gb->set_table_columns(array(
			array('name'=>'Transaction ID'),
			array('name'=>'Transaction Date'),
			array('name'=>'Exchange')
		));
		$present = array();

		$form = $this->init_module('Libs/QuickForm');
		$form->addElement('hidden', 'exchange_rate', '', array('id'=>'exchange_rate'));
		$form->addElement('hidden', 'order_id', '', array('id'=>'order_id'));
		$form->addElement('hidden', 'currency', '', array('id'=>'currency'));
		$form->addElement('hidden', 'prompt_header', '', array('id'=>'prompt_header'));
		$form->addElement('hidden', 'submit_form_js', '', array('id'=>'submit_form_js'));
		$form->setDefaults(array('prompt_header'=>Base_LangCommon::ts('Premium_Warehouse_SalesReport','Enter new exchange rate')));
		$form->setDefaults(array('submit_form_js'=>$form->get_submit_form_js()));
		$form->display();
		if ($form->validate()) {
			$vals = $form->exportValues();
			$vals['exchange_rate'] = str_replace(',','.',$vals['exchange_rate']);
			if (!is_numeric($vals['exchange_rate'])) return true;
			DB::Execute('DELETE FROM premium_warehouse_sales_report_exchange WHERE order_id=%d AND currency=%d', array($vals['order_id'], $vals['currency']));
			DB::Execute('INSERT INTO premium_warehouse_sales_report_exchange (order_id,currency,exchange_rate) VALUES (%d,%d,%f)', array($vals['order_id'], $vals['currency'], $vals['exchange_rate']));
			location(array());
			return true;
		}
		load_js('modules/Premium/Warehouse/SalesReport/edit_exchange_rates.js');
		while ($row = $ret->FetchRow()) {
			$gb_row = $gb->get_new_row();
			$xr = DB::GetOne('SELECT exchange_rate FROM premium_warehouse_sales_report_exchange WHERE order_id=%d AND currency=%d', array($row['order_id'], $sel_curr));
//			$cur_code = Utils_CurrencyFieldCommon::get_code($sel_curr);
			$ex_rate = 
//				$cur_code.
				'&nbsp;<a href="javascript:void(0)" onclick="edit_exchange_rate('.$row['order_id'].','.$sel_curr.')">'.
					'<img border="0" src="'.Base_ThemeCommon::get_template_file('Utils/GenericBrowser', 'edit.png').'" />'.
				'</a>&nbsp;'.
				$xr;
			$gb_row->add_data(
				Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items_orders', 'transaction_id', $row['order_id']),
				Base_RegionalSettingsCommon::time2reg($row['f_transaction_date'],false),
				$ex_rate
			);
		}
		$this->display_module($gb);
		return true;
	}
	
	public function currency_exchange_addon($r) {
		$currency = Variable::get('premium_warehouse_ex_currency');
		$cur_code = Utils_CurrencyFieldCommon::get_code($currency);
		$form = $this->init_module('Libs/QuickForm');
		$form->addElement('hidden', 'currency_id', '', array('id'=>'currency_id'));
		$form->addElement('hidden', 'exch_rate', '', array('id'=>'exch_rate'));
		$form->addElement('hidden', 'prompt_header', '', array('id'=>'prompt_header'));
		$form->addElement('hidden', 'submit_form_js', '', array('id'=>'submit_form_js'));
		$form->setDefaults(array('prompt_header'=>$this->t('Enter the amount in '.$cur_code)));
		$form->setDefaults(array('submit_form_js'=>$form->get_submit_form_js()));
		$form->display();
		if ($form->validate()) {
			$vals = $form->exportValues();
			DB::Execute('DELETE FROM premium_warehouse_sales_report_exchange WHERE currency=%d AND order_id=%d', array($vals['currency_id'], $r['id']));
			DB::Execute('INSERT INTO premium_warehouse_sales_report_exchange (exchange_rate, currency, order_id) VALUES (%f, %d, %d)', array($vals['exch_rate'], $vals['currency_id'], $r['id']));
		}
		$mapping = DB::GetAssoc('SELECT currency, exchange_rate FROM premium_warehouse_sales_report_exchange WHERE order_id=%d', array($r['id']));
		$currencies = Utils_CurrencyFieldCommon::get_currencies();
		$gb = $this->init_module('Utils/GenericBrowser', null, 'reports_currency_exchange');
		$gb->set_table_columns(array(
			array('name'=>'Currency'),
			array('name'=>'Exchange')
		));
		load_js('modules/Premium/Warehouse/SalesReport/exchange.js');
		$current = Variable::get('premium_warehouse_ex_currency');
		foreach ($currencies as $k=>$v) {
			if ($k==$current) continue;
			$gb_row = $gb->get_new_row();
			$is_set = isset($mapping[$k]);
			if ($is_set) $is_exch = $mapping[$k];
			else $is_exch = $this->t('Not set');
			$gb_row->add_data($v, $is_exch);
			$gb_row->add_action('href="javascript:void(0);" onclick="report_edit_exchange('.$k.')"', 'edit', 'Edit');
			if ($is_set) $gb_row->add_action($this->create_callback_href(array($this, 'remove_mapping'), array($r['id'], $k)), 'move-down', 'Unset');
		}
		$this->display_module($gb);
	}

	public function remove_mapping($order_id, $currency_id) {
		DB::Execute('DELETE FROM premium_warehouse_sales_report_exchange WHERE currency=%d AND order_id=%d', array($currency_id, $order_id));
		return false;
	}

	public function admin() {
		$tb = $this->init_module('Utils_TabbedBrowser');
		$tb->set_tab('Currency', array($this, 'currency_admin'));
		$tb->set_tab('QoH', array($this, 'qoh_admin'));
		$tb->tag();
		$this->display_module($tb);
	}

	public function currency_admin() {
		$form = $this->init_module('Libs/QuickForm');
		$current = Variable::get('premium_warehouse_ex_currency');
		$currencies = Utils_CurrencyFieldCommon::get_currencies();
		$form->addElement('header', '', 'Select default currency for all reports');
		$form->addElement('select', 'currency', 'Currency for reports', $currencies);
		$form->addElement('submit', 'submit', 'Submit');
		$form->setDefaults(array('currency'=>$current));
		if ($form->validate()) {
			Variable::set('premium_warehouse_ex_currency', $form->exportValue('currency'));
			return false;
		}
		$form->display();
	}

	public function qoh_admin() {
		if (isset($_REQUEST['balance_all'])) $balance_all = true;
		else $balance_all = false;
		Base_ActionBarCommon::add('folder', 'Fix amounts', Module::create_href(array('balance_all'=>1)));

		$gb = $this->init_module('Utils_GenericBrowser',null,'qoh_sync');
		$gb->set_table_columns(array('Item','Warehouse','Current','Calculated'));

		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse');
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items');
		$count = 0;
		foreach ($items as $ik=>$i) {
			$qts = DB::GetAssoc('SELECT f_warehouse AS warehouse, f_quantity AS quantity FROM premium_warehouse_location_data_1 WHERE f_item_sku=%d', array($i['id']));
			$tdets = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('item_name'=>$i['id']));
			$cal_qts = array();
			foreach ($warehouses as $wk=>$w)
				$cal_qts[$w['id']] = 0;
			foreach ($tdets as $kd=>$d) {
				$t = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $d['transaction_id']);
				switch ($t['transaction_type']) {
					case 0: if ($t['status']!=20) continue;
							$cal_qts[$t['warehouse']] += $d['quantity'];
							break;
					case 1: if ($t['status']<6 || $t['status']==21) continue;
							$cal_qts[$t['warehouse']] -= $d['quantity'];
							break;
					case 2: $cal_qts[$t['warehouse']] += $d['quantity'];
							break;
					case 4: if ($t['status']<5) continue;
							$cal_qts[$t['warehouse']] -= $d['quantity'];
							if ($t['status']!=20) continue;
							$cal_qts[$t['target_warehouse']] += $d['quantity'];
							break;
				}
			}
			foreach ($warehouses as $wk=>$w) {
				$wid = $w['id'];
				if (!isset($qts[$wid])) $qts[$wid] = 0;
				if ($qts[$wid] != $cal_qts[$wid]) {
					if ($balance_all) {
						Premium_Warehouse_Items_OrdersCommon::change_quantity($i['id'], $wid, $cal_qts[$wid]-$qts[$wid]);
					} else {
						$gb->add_row(
							Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items',$i['id']).$i['sku'].': '.$i['item_name'].Utils_RecordBrowserCommon::record_link_close_tag(),
							$w['warehouse'],
							$qts[$wid],
							$cal_qts[$wid]
						);
					}
					$count++;
				}
			}
		}

		$this->display_module($gb);
	}

/************************************************************************************/
	public function body() {
		if (!Base_AclCommon::i_am_admin()) {
			print($this->t('You don\'t have permission to access this module'));
			return;
		}

		if (isset($_REQUEST['mode'])) $this->set_module_variable('mode',$_REQUEST['mode']);
		$mode = $this->get_module_variable('mode',null);

		switch ($mode) {
			case 'sales_by_warehouse': $this->sales_by_warehouse(); break;
			case 'sales_by_item': $this->sales_by_item(); break;
			case 'sales_by_transaction': $this->sales_by_transaction(); break;
			case 'value_by_warehouse': $this->value_by_warehouse(); break;
			default: print($this->t('Unknown mode'));
		}
	}
	
	public function sales_by_warehouse() {
		Base_ActionBarCommon::add('folder', 'Currencies', $this->create_callback_href(array($this, 'currency_exchange_editor')));
		Base_ActionBarCommon::add('search', 'Scan', $this->create_callback_href(array($this, 'recalculate')));
		$this->cats = array('Sales Trans.','Sales Volume','Purchase Trans.','Purchase Volume','Net Profit','Sales Earnings');
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$this->rbr->set_reference_records($recs);
		$this->rbr->set_reference_record_display_callback(array('Premium_WarehouseCommon','display_warehouse'));

		$form = $this->init_module('Libs/QuickForm');
		$form->addElement('select', 'method', $this->t('Method'), array('fifo'=>$this->t('FIFO'), 'lifo'=>$this->t('LIFO')));
		$form->addElement('select', 'prices', $this->t('Prices'), array('net'=>$this->t('Net'), 'gross'=>$this->t('Gross')));
		$form->setDefaults(array('method'=>'fifo','prices'=>'net'));

		$this->range_type = $this->rbr->display_date_picker(array(), $form);
		$this->rbr->set_categories($this->cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total'));
		$this->rbr->set_format(array(	$this->cats[0]=>'numeric', 
										$this->cats[1]=>'currency',
										$this->cats[2]=>'numeric',
										$this->cats[3]=>'currency',
										$this->cats[4]=>'currency',
										$this->cats[5]=>'currency'
									));
		$header = array('Warehouse');
		$this->columns = $this->range_type['dates'];
		switch ($this->range_type['type']) {
			case 'day': $this->format ='d M Y'; break;
			case 'week': $this->format ='W Y'; break;
			case 'month': $this->format ='M Y'; break;
			case 'year': $this->format ='Y'; break;
		} 
		foreach ($this->columns as $v)
			$header[] = date($this->format, $v);
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_sales_by_warehouse_cells'));
		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}

	
/************************************************************************************/
	public function display_sales_by_warehouse_cells($ref_rec){
		$currency = Variable::get('premium_warehouse_ex_currency');
		$prec = Utils_CurrencyFieldCommon::get_precission($currency);
		$multip = pow(10,$prec);

		$result = array();
		$hash = array();
		$i = 0;
		foreach ($this->columns as $v) {
		// all $cats must be initialized here individually to avoid: "Message: Undefined index: Purchase Volume" error - see private static $cats = array(... above
			$result[$i] = array(	$this->cats[0]=>0,
									$this->cats[1]=>array(),
									$this->cats[2]=>0,
									$this->cats[3]=>array(),
									$this->cats[4]=>array(),
									$this->cats[5]=>array($currency=>0));
			$hash[date($this->format, $v)] = $i;
			$i++;
		}

		$transs = DB::Execute('SELECT * FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 AND o.f_transaction_date>=%D AND o.f_transaction_date<=%D AND o.f_warehouse=%s', array($this->range_type['start'], $this->range_type['end'], $ref_rec['id']));
		while ($v = $transs->FetchRow()) {
			$d = date($this->format,strtotime($v['f_transaction_date']));
			if (isset($hash[$d]))
				$result[$hash[$d]][$this->cats[5]][$currency] += $v[$this->range_type['other']['prices'][0].'_earning_'.$this->range_type['other']['method']]/$multip;
		}
		
		$records = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('warehouse'=>$ref_rec['id']));
		// TODO: transaction status filter
		// TODO: warehouse transfer
		// transactions types: 0=>'Purchase',1=>'Sale',2=>'Inventory Adjustment',3=>'Rental',4=>'Warehouse Transfer
		foreach ($records as $v) {
			$d = date($this->format,strtotime($v['transaction_date']));
			if (isset($hash[$d])) {
				// count no. of Sales/Purchase Transactions
				// and sales/purchase volume
				// Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value()
				// returns array where keys are currency IDs and values are result numbers
		
				// Epesi::debug(print_r(Premium_Warehouse_Items_OrdersCommon::get_status_array($v)));
				switch ($v['transaction_type']) {
					/********************** Purchase *******************/
					case '0':
						// Include only Completed transactions - status=20
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
//							if (!isset($purchase_amount[$currency])) break;
							foreach ($purchase_amount as $c=>$v) {
								if (!isset($result[$hash[$d]][$this->cats[3]][$c])) $result[$hash[$d]][$this->cats[3]][$c] = 0;
								if (!isset($result[$hash[$d]][$this->cats[4]][$c])) $result[$hash[$d]][$this->cats[4]][$c] = 0;
								$result[$hash[$d]][$this->cats[3]][$c] += $v;
								// Net loss/profit - Decrease - note -=
								$result[$hash[$d]][$this->cats[4]][$c] -= $v;
							}
							}
						break;
					/********************** Sale *******************/
					case '1':
						// Include only 7=>'Shipped', 20=>'Delivered'
						if ($v['status']==7 || $v['status']==20) {
							$result[$hash[$d]][$this->cats[0]]++;
							$sale_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
//							if (!isset($sale_amount[$currency])) break;
							foreach ($sale_amount as $c=>$v) {
								if (!isset($result[$hash[$d]][$this->cats[1]][$c])) $result[$hash[$d]][$this->cats[1]][$c] = 0;
								if (!isset($result[$hash[$d]][$this->cats[4]][$c])) $result[$hash[$d]][$this->cats[4]][$c] = 0;
								$result[$hash[$d]][$this->cats[1]][$c] += $v;
								// Net loss/profit - Increase - note +=
								$result[$hash[$d]][$this->cats[4]][$c] += $v;
							}
						}
						break;
						/********************** Inventory Adjustment *******************/
					case '2':
						// 20=>'Completed'
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
//							if (!isset($purchase_amount[$currency])) break;
							foreach ($purchase_amount as $c=>$v) {
								if (!isset($result[$hash[$d]][$this->cats[3]][$c])) $result[$hash[$d]][$this->cats[3]][$c] = 0;
								if (!isset($result[$hash[$d]][$this->cats[4]][$c])) $result[$hash[$d]][$this->cats[4]][$c] = 0;
								$result[$hash[$d]][$this->cats[3]][$c] += $v;
								// Net loss/profit - Decrease - note -=
								$result[$hash[$d]][$this->cats[4]][$c] -= $v;
							}
							}
						break;
						/********************** WAREHOUSE TRANSFER *******************/
						/* Ignore - Sales/Purchase Volume = 0
					case 4:
						// ''=>'New', 1=>'Transfer Quote', 2=>'Pending', 3=>'Order Fullfilment', 4=>'On Hold', 5=>'Ready to Ship', 6=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing'
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							Epesi::debug($v['warehouse']);
							$result[$hash[$d]][$this->cats[3]]+=$purchase_amount[1];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][$this->cats[4]] -= $purchase_amount[1];
							}
						break;
						*/
				} // end of switch
			}
		}
		
		$i = 0;
		foreach ($this->columns as $v) {
			switch ($this->range_type['type']) {
				case 'day':		$start = date('Y-m-d',$v);
								$end = date('Y-m-d',$v);
								break;
				case 'week':	$m = date('N',$v)-1;
								$start = date('Y-m-d',$v-$m*86400);
								$end = date('Y-m-d',$v+(6-$m)*86400);
								break;
				case 'month':	$start = date('Y-m-01',$v);
								$end = date('Y-m-t',$v);
								break;
				case 'year':	$start = date('Y-01-01',$v);
								$end = date('Y-12-31',$v);
								break;
			}
			$end = date('Y-m-d',strtotime($end)+1);
			
			// drill-in report links
			// Sales transactions link
			if ($result[$i][$this->cats[0]]<>0) {
				$result[$i][$this->cats[0]] = '<a '.$this->create_callback_href(array($this,'display_sales'), array($ref_rec['id'], $start, $end)).'>'.$result[$i][$this->cats[0]].'</a>';
			}
			// Purchases transactions link
			if ($result[$i][$this->cats[2]]<>0) {
				$result[$i][$this->cats[2]] = '<a '.$this->create_callback_href(array($this,'display_purchases'), array($ref_rec['id'], $start, $end)).'>'.$result[$i][$this->cats[2]].'</a>';
			}
			$i++;
		}
		return $result;
	}

	public function sales_by_item() {
		Base_ActionBarCommon::add('folder', 'Currencies', $this->create_callback_href(array($this, 'currency_exchange_editor')));
		Base_ActionBarCommon::add('search', 'Scan', $this->create_callback_href(array($this, 'recalculate')));
		print('<br><b>Numbers in brackets indicate items sold that were omitted in earning calculation.</b><br><br>');
		$form = $this->init_module('Libs/QuickForm');
		$form->addElement('select', 'method', $this->t('Method'), array('fifo'=>$this->t('FIFO'), 'lifo'=>$this->t('LIFO')));
		$form->addElement('select', 'prices', $this->t('Prices'), array('net'=>$this->t('Net'), 'gross'=>$this->t('Gross')));
		$form->addElement('text', 'item_name', $this->t('Item Search'));
		$form->setDefaults(array('method'=>'fifo','prices'=>'net','item_name'=>''));
		$this->cats = array('Qty Sold','Earnings');
		$this->range_type = $this->rbr->display_date_picker(array(), $form);
		$order = '_earning_';
		if ($this->range_type['other']['method']=='fifo') $order = $order.'fifo';
		else $order = $order.'lifo';
		if ($this->range_type['other']['prices']=='net') $order = 'n'.$order;
		else $order = 'g'.$order;
		if ($this->range_type['other']['item_name']) $item_filter = 'AND i.f_item_name LIKE '.DB::Concat(DB::qstr('%'), DB::qstr($this->range_type['other']['item_name']), DB::qstr('%')).' ';
		else $item_filter = '';
		$items_ids = DB::GetCol('SELECT od.f_item_name FROM ((premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id) LEFT JOIN premium_warehouse_items_data_1 AS i ON od.f_item_name=i.id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 AND o.f_transaction_date>=%D AND o.f_transaction_date<=%D '.$item_filter.'GROUP BY od.f_item_name ORDER BY SUM('.$order.') DESC', array($this->range_type['start'], $this->range_type['end']));
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$items_amount = Utils_RecordBrowserCommon::get_records_count('premium_warehouse_items',array('id'=>$items_ids));
		$limit = $this->rbr->enable_paging($items_amount);
		$items_ids = array_splice($items_ids, $limit['offset'], $limit['numrows']);
		$items_recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('id'=>$items_ids),array(),array('item_name'=>'ASC'));
		$items = array();
		foreach ($items_ids as $v) {
			$items[$v] = $items_recs[$v];
		}
		$this->rbr->set_reference_records($items);
		$this->rbr->set_reference_record_display_callback(array('Premium_Warehouse_ItemsCommon','display_item_name'));
		$this->rbr->set_categories($this->cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total', 'callback'=>array($this,'sales_by_item_row_total')));
		$this->rbr->set_format(array(	$this->cats[0]=>'numeric', 
										$this->cats[1]=>'currency'
									));
		$header = array('Item Name');
		$this->columns = array();
		foreach ($warehouses as $v) {
			$header[] = $v['warehouse'];
			$this->columns[$v['id']] = $v['warehouse'];
		}
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_sales_by_item_cells'));
		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}	

	public function sales_by_item_row_total($results, $total, $cat) {
		if ($cat==$this->cats[1]) return $total;
		$total = array(0=>0, 1=>0);
		foreach ($results as $v) {
			$val = explode('(',trim($v[$this->cats[0]],')'));
			$total[0] += $val[0];
			if (isset($val[1])) $total[1] += $val[1];
		}
		$total_disp = $total[0];
		if ($total[1]!=0) $total_disp .= ' ('.$total[1].')';
		return $total_disp;
	}
	
	public function display_sales_by_item_cells($ref_rec) {
		$currency = Variable::get('premium_warehouse_ex_currency');
		$prec = Utils_CurrencyFieldCommon::get_precission($currency);
		$multip = pow(10,$prec);
		
		$transs = DB::Execute('SELECT * FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND od.f_item_name=%d AND o.f_status=20 AND o.f_transaction_date>=%D AND o.f_transaction_date<=%D', array($ref_rec['id'], $this->range_type['start'], $this->range_type['end']));
		$ret = array();
		$i=0;
		$hash = array();
		$qty_with_unkn_price = array();
		foreach ($this->columns as $k=>$v) {
			$ret[$i] = array(	$this->cats[0]=>0,
								$this->cats[1]=>array($currency=>0));
			$qty_with_unkn_price[$i] = 0;
			$hash[$k] = $i;
			$i++;
		}
		while ($trans=$transs->FetchRow()) {
			$w = $hash[$trans['f_warehouse']];
			$ret[$w][$this->cats[0]] += $trans['f_quantity'];
			$ret[$w][$this->cats[1]][$currency] += $trans[$this->range_type['other']['prices'][0].'_earning_'.$this->range_type['other']['method']]/$multip;
			$qty_with_unkn_price[$w] += $trans['f_quantity']-$trans['quantity_'.$this->range_type['other']['method']];
		}
		foreach ($this->columns as $k=>$v) { 
			if (isset($qty_with_unkn_price[$hash[$k]]) && $qty_with_unkn_price[$hash[$k]]!=0) {
				$ret[$hash[$k]][$this->cats[0]] = $ret[$hash[$k]][$this->cats[0]].' ('.$qty_with_unkn_price[$hash[$k]].')';
			}
		}
		return $ret;
	}
	
	public function sales_by_transaction() {
		Base_ActionBarCommon::add('search', 'Scan', $this->create_callback_href(array($this, 'recalculate')));
		Base_ActionBarCommon::add('folder', 'Currencies', $this->create_callback_href(array($this, 'currency_exchange_editor')));
		print('<br><b>Numbers in brackets indicate items sold that were omitted in earning calculation.</b><br><br>');
		$form = $this->init_module('Libs/QuickForm');
		$form->addElement('select', 'method', $this->t('Method'), array('fifo'=>$this->t('FIFO'), 'lifo'=>$this->t('LIFO')));
		$form->addElement('select', 'prices', $this->t('Prices'), array('net'=>$this->t('Net'), 'gross'=>$this->t('Gross')));
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$warehouse_choice = array(''=>Base_LangCommon::ts('Premium_Warehouse_SalesReport','[all]'));
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		if (!$my_warehouse) $my_warehouse = '';
		foreach ($warehouses as $k=>$v)
			$warehouse_choice[$v['id']] = $v['warehouse'];
		$form->addElement('select', 'warehouse', $this->t('Warehouse'), $warehouse_choice);
		$form->setDefaults(array('method'=>'fifo','prices'=>'net', 'warehouse'=>$my_warehouse));
		$this->cats = array('Qty Sold','Earnings');
		$this->range_type = $this->rbr->display_date_picker(array(), $form);
		if ($this->range_type['other']['warehouse']!='') {
			$warehouse_sql = 'AND o.f_warehouse=%d '; 
		} else {
			$warehouse_sql = ''; 
		}
		$transactions_count = DB::GetOne('SELECT COUNT(*) FROM (SELECT o.id FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 AND o.f_transaction_date>=%D AND o.f_transaction_date<=%D '.$warehouse_sql.'GROUP BY o.id) AS tmp', array_merge(array($this->range_type['start'], $this->range_type['end']),$this->range_type['other']['warehouse']==''?array():array($this->range_type['other']['warehouse'])));
		$limit = $this->rbr->enable_paging($transactions_count);
		$order = '_earning_';
		if ($this->range_type['other']['method']=='fifo') $order = $order.'fifo';
		else $order = $order.'lifo';
		if ($this->range_type['other']['prices']=='net') $order = 'n'.$order;
		else $order = 'g'.$order;
		$trans_ids_tmp = DB::SelectLimit('SELECT o.id FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 AND o.f_transaction_date>=%D AND o.f_transaction_date<=%D '.$warehouse_sql.'GROUP BY o.id ORDER BY SUM('.$order.') DESC', $limit['numrows'], $limit['offset'], array_merge(array($this->range_type['start'], $this->range_type['end']),$this->range_type['other']['warehouse']==''?array():array($this->range_type['other']['warehouse'])));
		$trans_ids = array();
		while ($x=$trans_ids_tmp->FetchRow()) $trans_ids[] = $x['id'];
		$trans_recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',array('id'=>$trans_ids));
		$transactions = array();
		foreach ($trans_ids as $v) {
			$transactions[$v] = $trans_recs[$v];
		}
		$this->rbr->set_reference_records($transactions);
		$this->rbr->set_reference_record_display_callback(array($this,'display_transaction_id'));
		$this->rbr->set_categories($this->cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
//		$this->rbr->set_summary('row', array('label'=>'Total', 'callback'=>array($this,'sales_by_item_row_total')));
		$this->rbr->set_format(array(	$this->cats[0]=>'numeric', 
										$this->cats[1]=>'currency'
									));
		$header = array('Transaction', 'Earnings');
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_sales_by_transaction_cells'));
//		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
//		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
//		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}	
	
	public function display_transaction_id($r) {
		return Premium_Warehouse_Items_OrdersCommon::display_transaction_id($r,false).' - '.Base_RegionalSettingsCommon::time2reg($r['transaction_date'], false).' - '.CRM_ContactsCommon::contact_format_no_company($r['employee']);
	}
	
	public function display_sales_by_transaction_cells($transaction) {
		$currency = Variable::get('premium_warehouse_ex_currency');
		$prec = Utils_CurrencyFieldCommon::get_precission($currency);
		$multip = pow(10,$prec);
		
		$transs = DB::Execute('SELECT * FROM (premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id) LEFT JOIN premium_warehouse_sales_report_earning AS se ON se.order_details_id=od.id WHERE od.active=1 AND o.f_transaction_type=1 AND o.id=%d AND o.f_status=20', array($transaction['id']));
		$ret = array(0=>array(	$this->cats[0]=>0,
							$this->cats[1]=>array($currency=>0)));
		$qty_with_unkn_price = 0;
		while ($trans=$transs->FetchRow()) {
			$ret[0][$this->cats[0]] += $trans['f_quantity'];
			$ret[0][$this->cats[1]][$currency] += $trans[$this->range_type['other']['prices'][0].'_earning_'.$this->range_type['other']['method']]/$multip;
			$qty_with_unkn_price += $trans['f_quantity']-$trans['quantity_'.$this->range_type['other']['method']];
		}
		if ($qty_with_unkn_price!=0) {
			$ret[0][$this->cats[0]] = $ret[0][$this->cats[0]].' ('.$qty_with_unkn_price.')';
		}
		return $ret;
	}

	public function value_by_warehouse() {
		Base_ActionBarCommon::add('folder', 'Currencies', $this->create_callback_href(array($this, 'currency_exchange_editor')));
		Base_ActionBarCommon::add('search', 'Scan', $this->create_callback_href(array($this, 'recalculate')));
		$this->cats = array('Items Qty','Items Volume');
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$this->rbr->set_reference_records($recs);
		$this->rbr->set_reference_record_display_callback(array('Premium_WarehouseCommon','display_warehouse'));

		$form = $this->init_module('Libs/QuickForm');
		$form->addElement('select', 'method', $this->t('Method'), array('fifo'=>$this->t('FIFO'), 'lifo'=>$this->t('LIFO')));
		$form->addElement('select', 'prices', $this->t('Prices'), array('net'=>$this->t('Net'), 'gross'=>$this->t('Gross')));
		$form->setDefaults(array('method'=>'fifo','prices'=>'net'));

		$this->range_type = $this->rbr->display_date_picker(array(), $form, false);
		$this->rbr->set_categories($this->cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total'));
		$this->rbr->set_format(array(	$this->cats[0]=>'numeric', 
										$this->cats[1]=>'currency'
									));
		$header = array('Warehouse', 'Stock');
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_value_by_warehouse_cells'));
		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}

	
/************************************************************************************/
	public function display_value_by_warehouse_cells($ref_rec){
		$currency = Variable::get('premium_warehouse_ex_currency');
		$prec = Utils_CurrencyFieldCommon::get_precission($currency);
		$multip = pow(10,$prec);

		$result = array();
		$result[0] = array(	$this->cats[0]=>0,
							$this->cats[1]=>array());

		$result[0][$this->cats[0]] = DB::GetOne('SELECT SUM(f_quantity) FROM premium_warehouse_location_data_1 WHERE active=1 AND f_warehouse=%d', array($ref_rec['id']));
		
		if ($this->range_type['other']['method']=='fifo') $method = 'fifo';
		else $method = 'lifo';
		if ($this->range_type['other']['prices']=='net') $prices = 'net_price';
		else $prices = 'gross_price';
		
		$result[0][$this->cats[1]][$currency] = DB::GetOne('SELECT SUM('.$prices.') FROM premium_warehouse_sales_report_purchase_'.$method.'_tmp WHERE warehouse=%s', array($ref_rec['id']))/$multip;
		

		return $result;
	}



	
/************************************************************************************/
	public function display_sales($warehouse_id, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','orders_module');
		$order_date='transaction_date';
		$cols='';
		// sales status can be 7 or 20
		$orders = array(array('warehouse'=>$warehouse_id,'transaction_type'=>'1','status'=>array(20,7),'>='.$order_date=>$start, '<='.$order_date=>$end), $cols, array($order_date=>'DESC'));
		$rb->set_header_properties(array('terms'=>array('width'=>1, 'wrapmode'=>'nowrap'),'status'=>array('width'=>1, 'wrapmode'=>'nowrap')));
		$this->display_module($rb,$orders,'show_data');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		return true;
	}
	
/************************************************************************************/
	public function display_purchases($warehouse_id, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','orders_module');
		$order_date='transaction_date';
		$cols='';
		$orders = array(array('warehouse'=>$warehouse_id,'transaction_type'=>'0','status'=>'20', '>='.$order_date=>$start, '<='.$order_date=>$end), $cols, array($order_date=>'DESC'));
		$rb->set_header_properties(array('terms'=>array('width'=>1, 'wrapmode'=>'nowrap'),'status'=>array('width'=>1, 'wrapmode'=>'nowrap')));
		$this->display_module($rb,$orders,'show_data');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		return true;
	}
	
/************************************************************************************/
	public function caption() {
		return 'Sales Report';
	}

}

?>