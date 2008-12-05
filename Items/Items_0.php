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
		// Quickjump jak do ticketow, ale po UPC code
		// TODO: service/non-inventory: quantity rename to amount sold and set to +X instead -X
		$lang = $this->init_module('Base/Lang');
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items','premium_warehouse_items_module');
		$this->rb->set_default_order(array('item_name'=>'ASC'));		
		$this->rb->set_cut_lengths(array('item_name'=>30));
		$defaults = array('quantity_on_hand'=>'0','reorder_point'=>'0');
		$this->rb->set_defaults(array(
			$lang->t('Inv. Item')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'inv_item.png'), 'defaults'=>array_merge($defaults,array('item_type'=>0))),
			$lang->t('Serialized Item')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'serialized.png'), 'defaults'=>array_merge($defaults,array('item_type'=>1))),
			$lang->t('Non-Inv. Items')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'non-inv.png'), 'defaults'=>array_merge($defaults,array('item_type'=>2))),
			$lang->t('Service')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'service.png'), 'defaults'=>array_merge($defaults,array('item_type'=>3)))
			), true);
		$this->display_module($this->rb);
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