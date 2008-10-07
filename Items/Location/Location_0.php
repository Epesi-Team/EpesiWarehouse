<?php
/**
 * Warehouse - Location
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_Location extends Module {
	private $rb;

	public function body() {
		$lang = $this->init_module('Base/Lang');
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location','premium_warehouse_location');
		// set defaults
//		$this->rb->set_default_order(array(':Created_on'=>'DESC'));		
		$this->rb->set_cut_lengths(array('item'=>30));
		$this->display_module($this->rb);
	}

	public function location_addon($arg){
		// TODO: order by created_on (/sign, dammit...)
		// Add button? Add what? Order or Details? (/sign, dammit...)
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location');
		$order = array(array('item_sku'=>$arg['id']), array('item_sku'=>false), array());
//		$rb->set_defaults(array('item_sku'=>$arg['id']));
		$this->display_module($rb,$order,'show_data');
	}

/*	public function attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array($arg['id'],'Premium/Warehouse/Location/'.$arg['id']));
		$a->additional_header('Order ID: '.$arg['order_id']);
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$this->display_module($a);
	}*/

	public function caption(){
		return $this->rb->caption();
	}
}

?>