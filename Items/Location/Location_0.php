<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Location
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-location
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_Location extends Module {
	private $rb;

	public function location_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location');
		$rb->set_button(false);
		$rb->set_additional_actions_method($this, 'rental_actions');
		$order = array(array('item_sku'=>$arg['id'], '!quantity'=>0), array('rental_item'=>($arg['item_type']==1)?true:false, 'item_sku'=>false, 'serial'=>($arg['item_type']==1)?true:false), array());
		$this->display_module($rb,$order,'show_data');
	}

	public function caption(){
		return $this->rb->caption();
	}
	
	public function set_rental($r, $state=true){
		Utils_RecordBrowserCommon::update_record('premium_warehouse_location', $r['id'], array('rental_item'=>$state?1:0));
		return false;
	}

	public function rental_actions($r, &$gb_row) {
		if (!$r['rental_item']) $gb_row->add_action($this->create_callback_href(array($this,'set_rental'),array($r,true)),'Activate', null, 'active-off');
		else $gb_row->add_action($this->create_callback_href(array($this,'set_rental'),array($r,false)),'Deactivate', null, 'active-on');
	}

	public function warehouse_item_list_addon($arg) {
		$lang = $this->init_module('Base/Lang');
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location','premium_warehouse_location_module');
		$rb->set_default_order(array('item_sku'=>'ASC'));		
		$this->display_module($rb, array(array('warehouse'=>$arg['id'], '!quantity'=>0), array('warehouse'=>false,'item_name'=>true)), 'show_data');
	}

}

?>