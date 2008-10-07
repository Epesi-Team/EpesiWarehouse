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
		// set defaults
//		$this->rb->set_default_order(array(':Created_on'=>'DESC'));		
		$this->rb->set_defaults(array('transaction_date'=>date('Y-m-d')));
		$this->rb->set_cut_lengths(array('item'=>30));
		$this->display_module($this->rb);
	}

	public function applet($conf,$opts) {
		$opts['go'] = true; // enable full screen
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$limit = null;
		$crits = array();
		// $conds - parameters for the applet
		// 1st - table field names, width, truncate
		// 2nd - criteria (filter)
		// 3rd - sorting
		// 4th - function to return tooltip
		// 5th - limit how many records are returned, null = no limit
		// 6th - Actions icons - default are view + info (with tooltip)
		
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
		// TODO: order by created_on (/sign, dammit...)
		// Add button? Add what? Order or Details? (/sign, dammit...)
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$order = array(array('item_sku'=>$arg['id']), array('item_sku'=>false));
//		$order = array();
		$rb->set_defaults(array('item_sku'=>$arg['id']));
		$this->display_module($rb,$order,'show_data');
	}

	public function order_details_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$order = array(array('order_id'=>$arg['id']), array('order_id'=>false), array());
		$rb->set_defaults(array('order_id'=>$arg['id']));
		$rb->enable_quick_new_records();
		$this->display_module($rb,$order,'show_data');
		$js =	'Event.observe(\'item_sku\',\'change\', onchange_item_sku);'.
				'function onchange_item_sku() {'.
					'var isku=$("item_sku");'.
					'$("item_name").value = isku.options[isku.selectedIndex].text;'.
				'};';
		eval_js($js);
	}

	public function attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array($arg['id'],'Premium/Warehouse/Items/Orders/'.$arg['id']));
		$a->additional_header('Order ID: '.$arg['order_id']);
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$this->display_module($a);
	}

	public function caption(){
		return $this->rb->caption();
	}
}

?>