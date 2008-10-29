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

class Premium_Warehouse_Items_Orders extends Module {
	private $rb;

	public function body() {
		$lang = $this->init_module('Base/Lang');
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
			$lang->t('Purchase')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'purchase.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>0))),
			$lang->t('Sale')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'sale.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>1))),
			$lang->t('Inv. Adjustment')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'inv_adj.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>2)))
			), true);
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
		$order = array(array('item_sku'=>$arg['id']), array('quantity_on_hand'=>false,'item_name'=>false,'item_sku'=>false, ($arg['item_type']==1)?'quantity':'serial'=>false), array('transaction_id'=>'DESC'));
		$rb->set_button(false);
		$rb->set_defaults(array('item_sku'=>$arg['id']));
		$this->display_module($rb,$order,'show_data');
	}

	public function order_details_addon($arg){
		// TODO: leightbox do wybierania przedmiotow do select'a (sic! ^^)
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$cols = array('transaction_id'=>false);
		$cols['transaction_type'] = false;			
		$cols['transaction_date'] = false;			
		$cols['warehouse'] = false;			
		if ($arg['transaction_type']==0)
			$rb->set_header_properties(array('net_price'=>array('name'=>'Net Cost'), 'gross_price'=>array('name'=>'Gross Cost')));
		if ($arg['transaction_type']==2) {
			$cols['tax_rate'] = false;
			$cols['total'] = false;
			$cols['price'] = false;			
		}
		$order = array(array('transaction_id'=>$arg['id']), $cols, array());
		$rb->set_button(false);
		$rb->set_defaults(array('transaction_id'=>$arg['id']));
		$rb->enable_quick_new_records();
		$this->display_module($rb,$order,'show_data');
	}

	public function attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array($arg['id'],'Premium/Warehouse/Items/Orders/'.$arg['id']));
		$a->additional_header('Transaction ID: '.$arg['transaction_id']);
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$this->display_module($a);
	}

	public function caption(){
		return $this->rb->caption();
	}
}

?>