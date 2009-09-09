<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Items
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items extends Module {
	private $rb;

	public function body() {
		$mod = $this->get_module_variable('recordset');
		if (isset($_REQUEST['recordset']) || $mod=='categories') {
			if (isset($_REQUEST['recordset'])) $this->set_module_variable('recordset', 'categories');
			$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_categories');
			$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
			$this->rb->force_order(array('position'=>'ASC','category_name'=>'ASC'));
			$this->display_module($this->rb, array(array(),array('parent_category'=>'')));
			return;
		}
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items');
		$this->rb->set_default_order(array('item_name'=>'ASC'));
		$this->rb->set_cut_lengths(array('item_name'=>50,'vendor'=>35));
		$defaults = array('quantity_on_hand'=>'0','reorder_point'=>'0');
		$this->rb->set_defaults(array(
			$this->t('Inv. Item')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'inv_item.png'), 'defaults'=>array_merge($defaults,array('item_type'=>0))),
			$this->t('Serialized Item')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'serialized.png'), 'defaults'=>array_merge($defaults,array('item_type'=>1))),
			$this->t('Non-Inv. Items')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'non-inv.png'), 'defaults'=>array_merge($defaults,array('item_type'=>2))),
			$this->t('Service')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'service.png'), 'defaults'=>array_merge($defaults,array('item_type'=>3)))
			), true);
			
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse');
		$opts = array('__NULL__'=>'---');
		$my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse');
		foreach ($warehouses as $v)
			$opts[$v['id']] = $v['warehouse'];
		$this->rb->set_custom_filter('sku',array('type'=>'select','label'=>$this->t('Warehouse'),'args'=>$opts,'trans_callback'=>array($this, 'trans_filter')));
		if (Base_User_SettingsCommon::get('Premium_Warehouse_Items_Orders', 'filter_by_my_warehouse')) $this->rb->set_filters_defaults(array('sku'=>$my_warehouse));
		
		$cols = array();
		if (ModuleManager::is_installed('Premium_Warehouse_Items_Orders')!=-1) {
			$display = Base_User_SettingsCommon::get('Premium_Warehouse_Items_Orders', 'display_qty');
			$cols['available_quantity'] = $display==0||$display==2;
			$cols['quantity_on_hand'] = $display==1||$display==2;
		}
			
		$this->rb->set_header_properties(array(
						'quantity_on_hand'=>array('name'=>'On Hand', 'width'=>1, 'wrapmode'=>'nowrap'),
						'quantity_en_route'=>array('name'=>'En Route', 'width'=>1, 'wrapmode'=>'nowrap'),
						'available_qty'=>array('name'=>'Avail. Qty', 'width'=>1, 'wrapmode'=>'nowrap'),
						'dist_qty'=>array('name'=>'Dist. Qty', 'width'=>1, 'wrapmode'=>'nowrap'),
						'reserved_qty'=>array('name'=>'Res. Qty', 'width'=>1, 'wrapmode'=>'nowrap'),
						'manufacturer_part_number'=>array('name'=>'Part Number', 'width'=>1, 'wrapmode'=>'nowrap'),
						'item_type'=>array('width'=>1, 'wrapmode'=>'nowrap'),
						'gross_price'=>array('name'=>'Price','width'=>1, 'wrapmode'=>'nowrap'),
						'item_name'=>array('wrapmode'=>'nowrap'),
						'sku'=>array('width'=>1, 'wrapmode'=>'nowrap')
						));

		if(ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0)
    			$this->rb->set_additional_actions_method(array('Premium_Warehouse_eCommerceCommon', 'warehouse_item_actions'));

		$this->display_module($this->rb, array(array(),array(),$cols));
	}

	public function actions_for_position($r, & $gb_row) {
		$tab = 'premium_warehouse_items_categories';
		if(isset($_REQUEST['pos_action']) && $r['id']==$_REQUEST['pos_action'] && is_numeric($_REQUEST['old']) && is_numeric($_REQUEST['new'])) {
		    $recs = Utils_RecordBrowserCommon::get_records($tab,array('position'=>$_REQUEST['new']), array('id'));
		    foreach($recs as $rr)
			Utils_RecordBrowserCommon::update_record($tab,$rr['id'],array('position'=>$_REQUEST['old']));
    		    Utils_RecordBrowserCommon::update_record($tab,$r['id'],array('position'=>$_REQUEST['new']));
		    location(array());
		}
		if($r['position']>0)
		    $gb_row->add_action(Module::create_href(array('pos_action'=>$r['id'],'old'=>$r['position'],'new'=>$r['position']-1)),'move-up');
		static $max;
		if(!isset($max))
		    $max = Utils_RecordBrowserCommon::get_records_count($tab);
		if($r['position']<$max-1)
    		    $gb_row->add_action(Module::create_href(array('pos_action'=>$r['id'],'old'=>$r['position'],'new'=>$r['position']+1)),'move-down');
	}
	
	public function trans_filter($choice) {
		if ($choice=='__NULL__') return array();
		$locations = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', array('!quantity'=>0, 'warehouse'=>$choice));
		$ids = array();
		foreach ($locations as $v) $ids[] = $v['item_sku']; 
		return array('id'=>$ids);
	}
	
	public function subcategories_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_categories');
		$order = array(array('parent_category'=>$arg['id']), array(), array('category_name'=>'ASC'));
		$rb->set_defaults(array('parent_category'=>$arg['id']));
//		$rb->set_header_properties(array(
//			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
//			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
//									));
		$this->display_module($rb,$order,'show_data');
	}

	public function applet($conf,$opts) {
		$opts['go'] = true; // enable full screen
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items','premium_warehouse_items');
		$limit = null;
		$crits = array();
		
		$sorting = array('item_name'=>'ASC');
		$cols = array(
							array('field'=>'item_name', 'width'=>10, 'cut'=>18),
							array('field'=>'quantity_on_hand', 'width'=>10)
										);

		$conds = array(
									$cols,
									$crits,
									$sorting,
									array('Premium_Warehouse_ItemsCommon','applet_info_format'),
									$limit,
									$conf,
									& $opts
				);
		$opts['actions'][] = Utils_RecordBrowserCommon::applet_new_record_button('premium_warehouse_items',array());
		$this->display_module($rb, $conds, 'mini_view');
	}

	public function attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Warehouse/Items/'.$arg['id']));
		$a->set_view_func(array('Premium_Warehouse_ItemsCommon','search_format'),array($arg['id']));
		$a->additional_header('Item: '.$arg['item_name']);
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$this->display_module($a);
	}
	
	public function caption(){
		if (isset($this->rb)) return $this->rb->caption();
	}
}

?>