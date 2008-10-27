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

	public function location_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location');
		$rb->set_button(false);
		$order = array(array('item_sku'=>$arg['id'], '!quantity'=>0), array('item_sku'=>false, 'serial'=>($arg['item_type']==1)?true:false), array());
		$this->display_module($rb,$order,'show_data');
	}

	public function caption(){
		return $this->rb->caption();
	}

	public function warehouse_item_list_addon($arg) {
		$lang = $this->init_module('Base/Lang');
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location','premium_warehouse_location_module');
		$rb->set_default_order(array('item_sku'=>'ASC'));		
		$this->display_module($rb, array(array('warehouse'=>$arg['id'], '!quantity'=>0), array('warehouse'=>false,'item_name'=>true)), 'show_data');
	}

}

?>