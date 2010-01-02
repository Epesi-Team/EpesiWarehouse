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
			Base_ActionBarCommon::add('attach',$this->ht('Merge categories'),$this->create_callback_href(array($this,'merge_categories')));
			return;
		}
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items');
		$this->rb->set_default_order(array('item_name'=>'ASC'));
		$this->rb->set_cut_lengths(array('item_name'=>50,'vendor'=>35));
		$defaults = array('quantity_on_hand'=>'0','reorder_point'=>'0','weight'=>1);
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
	
	public function merge_categories($root='') {
		$x = ModuleManager::get_instance('/Base_Box|0');
		if(!$x) trigger_error('There is no base box module instance',E_USER_ERROR);
		$x->push_main($this->get_type(),'merge_categories_body',array($root));
	}
	
	public function merge_categories_body($root='') {
		if($this->is_back()) {
			$x = ModuleManager::get_instance('/Base_Box|0');
			if(!$x) trigger_error('There is no base box module instance',E_USER_ERROR);
			$x->pop_main();
			return;
		}
		if(!isset($root)) $root = '';
		else {
			$m = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_categories', $root);
			print('<h2>'.$m['category_name'].'</h2><br>');
		}
		
		$qf = $this->init_module('Libs/QuickForm');
		
		$opts = array();
		Premium_Warehouse_ItemsCommon::build_category_tree($opts,$root);
		$qf->addElement('select', 'master_cat', $this->t('Master category'), $opts);
		$qf->addRule('master_cat',$this->t('Field required'),'required');
		$e = $qf->addElement('multiselect', 'cats', $this->t('Merge categories'), $opts,array('style'=>'height:380px;width:300px'));
		$qf->addRule('cats',$this->t('Field required'),'required');
		$qf->addRule(array('cats','master_cat'),$this->t('You must select at least one category different then master category'),'callback',array($this,'check_merge_cats'));
		
		if($qf->validate()) {
			set_time_limit(0);
			$vals = $qf->exportValues();
			$master = strrchr($vals['master_cat'],'/');
			if($master!==false)
				$master = substr($master,1);
			else
				$master = $vals['master_cat'];
			$cats = array();
			foreach($vals['cats'] as $cat) {
				$cat2 = strrchr($cat,'/');
				if($cat2!==false)
					$cat2 = substr($cat2,1);
				else
					$cat2 = $cat;
				if($cat2===$master) continue;
				$items = DB::GetAssoc('SELECT id,f_category FROM premium_warehouse_items_data_1 WHERE f_category LIKE '.DB::Concat(DB::qstr('%'),DB::qstr($cat),DB::qstr('%')));
				foreach($items as $id=>$it_cats) {
					DB::Execute('UPDATE premium_warehouse_items_data_1 SET f_category=%s WHERE id=%d',array(str_replace($it_cats,$cat,$vals['master_cat']),$id));
				}
				$cats[] = $cat2;
				DB::Execute('UPDATE premium_warehouse_items_categories_data_1 SET f_parent_category=%d WHERE f_parent_category=%d',array($master,$cat2));
			}
			
			$ret = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_categories',array('parent_category'=>$master),array(),array('position'=>'ASC','category_name'=>'ASC'));
			$data = array();
			foreach($ret as $r) {
				$data[] = $r['id'];
			}

			foreach($data as $k=>$v) {
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items_categories',$v,array('position'=>$k));
			}
			
			if(ModuleManager::is_installed('Premium/Warehouse/eCommerce')>=0) {
				foreach($cats as $cat) {
					$langs = array_map(array('DB','qstr'),DB::GetCol('SELECT f_language FROM premium_ecommerce_cat_descriptions_data_1 WHERE f_category=%d',array($master)));
					if($langs)
						DB::Execute('DELETE FROM premium_ecommerce_cat_descriptions_data_1 WHERE f_category=%d AND f_language IN ('.implode(',',$langs).')',array($cat));
					DB::Execute('UPDATE premium_ecommerce_cat_descriptions_data_1 SET f_category=%d WHERE f_category=%d',array($master,$cat));
					DB::Execute('UPDATE premium_ecommerce_categories_stats SET obj=%d WHERE obj=%d',array($master,$cat));
				}
			}
			
			foreach($cats as $cat) {
				$values = DB::GetRow('SELECT f_parent_category as parent_category,f_position as position FROM premium_warehouse_items_categories_data_1 WHERE id=%d',array($cat));
				if($values['parent_category']!=='')
				  	DB::Execute('UPDATE premium_warehouse_items_categories_data_1 SET f_position=f_position-1 WHERE f_position>%d and f_parent_category=%d',array($values['position'],$values['parent_category']));
				  else
				  	DB::Execute('UPDATE premium_warehouse_items_categories_data_1 SET f_position=f_position-1 WHERE f_position>%d and f_parent_category is null',array($values['position']));
			}
			DB::Execute('DELETE FROM premium_warehouse_items_categories_data_1 WHERE id IN ('.implode(',',$cats).')');

			location(array());
			Epesi::alert('Categories merged');
		}
		
		$qf->display();
	
		Base_ActionBarCommon::add('save','Merge',$qf->get_submit_form_href());
		Base_ActionBarCommon::add('back','Back',$this->create_back_href(true,'processing... this operation can take couple minutes...'));
	}
	
	public static function check_merge_cats($val) {
		$cats = array_values(array_filter(explode('__SEP__',$val[0])));
		if(count($cats)==1 && $cats[0]==$val[1]) 
			return false;
		return true;
	}

	public function actions_for_position($r, $gb_row) {
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
		    $max = Utils_RecordBrowserCommon::get_records_count($tab,array('parent_category'=>$r['parent_category']));
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
		$order = array(array('parent_category'=>$arg['id']), array(), array());
		$rb->set_defaults(array('parent_category'=>$arg['id']));
		$rb->force_order(array('position'=>'ASC','category_name'=>'ASC'));
//		$rb->set_header_properties(array(
//			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
//			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
//									));
		$rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->display_module($rb,$order,'show_data');
		Base_ActionBarCommon::add('attach',$this->ht('Merge categories'),$this->create_callback_href(array($this,'merge_categories'),array($arg['id'])));
	}

	public function applet($conf,$opts) {
		$opts['go'] = true; // enable full screen
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items','premium_warehouse_items');
		$limit = null;
		$crits = array();
		if(ModuleManager::is_installed('Premium_Warehouse_Items_Location')>=0)
			$av = DB::GetCol('SELECT DISTINCT f_item_sku FROM premium_warehouse_location_data_1 WHERE f_quantity>0 AND active=1');
		else
			$av = DB::GetCol('SELECT id FROM premium_warehouse_items_data_1 WHERE f_quantity_on_hand>0 AND active=1');
		$sold = DB::GetCol('SELECT DISTINCT d.f_item_name FROM premium_warehouse_items_orders_details_data_1 d INNER JOIN premium_warehouse_items_orders_data_1 o ON o.id=d.f_transaction_id WHERE o.created_on>=%T AND d.active=1 AND o.active=1 AND o.f_transaction_type=1',array(date('Y-m-d H:i:s',time()-$conf['older'])));
		$crits['!id'] = $sold;
		$crits['id'] = $av;

		
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