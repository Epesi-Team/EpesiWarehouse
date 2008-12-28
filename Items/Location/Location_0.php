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

	public function location_serial_addon($arg){
		$gb = $this->init_module('Utils/GenericBrowser','premium_warehouse_location_serials','premium_warehouse_location_serials');
		$gb->set_table_columns(array(
			array('name'=>'Serial'),
			array('name'=>'Warehouse')
								));
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', array('item_sku'=>$arg['id']));
		foreach ($recs as $v) {
			$item_serials = DB::GetAssoc('SELECT id, serial FROM premium_warehouse_location_serial WHERE active=1 AND location_id=%d', array($v['id']));
			foreach ($item_serials as $w) {
				$gb->add_row($w, Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'Warehouse', $v['warehouse']));
			}
		}
		$this->display_module($gb);
	}

	public function location_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location');
		$rb->set_button(false);
		$order = array(array('item_sku'=>$arg['id'], '!quantity'=>0), array('item_sku'=>false), array('warehouse'=>'ASC'));
		$this->display_module($rb,$order,'show_data');
	}

	public function caption(){
		return $this->rb->caption();
	}

	public function warehouse_item_list_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location','premium_warehouse_location_module');
		$this->display_module($rb, array(array('warehouse'=>$arg['id'], '!quantity'=>0), array('warehouse'=>false,'item_name'=>true), array('item_name'=>'ASC')), 'show_data');
	}

}

?>