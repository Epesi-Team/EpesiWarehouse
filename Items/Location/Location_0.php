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

	public function location_serial_addon($arg){
		$gb = $this->init_module('Utils/GenericBrowser','premium_warehouse_location_serials','premium_warehouse_location_serials');
		$gb->set_table_columns(array(
			array('name'=>'Serial'),
			array('name'=>'Warehouse'),
			array('name'=>'Owner'),
			array('name'=>'Notes'),
			array('name'=>'Shelf')
								));
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location', array('item_sku'=>$arg['id']));
		$na_serials = array();
		$myrec = CRM_ContactsCommon::get_my_record();
		foreach ($recs as $v) {
		    if(Utils_RecordBrowserCommon::get_access('premium_warehouse_location','browse'))
              	$item_serials = DB::GetAssoc('SELECT id, serial, owner, notes, shelf FROM premium_warehouse_location_serial WHERE location_id=%d', array($v['id']));
//    	    elseif(browse my location))
//            	$item_serials = DB::GetAssoc('SELECT id, serial, owner, notes, shelf FROM premium_warehouse_location_serial WHERE location_id=%d AND owner=%d', array($v['id'],$myrec['id']));
			foreach ($item_serials as $w) {
				if (!$w['serial']) {
					if (!isset($na_serials[$v['warehouse']])) $na_serials[$v['warehouse']] = array();
					if (!isset($na_serials[$v['warehouse']][$w['owner']])) $na_serials[$v['warehouse']][$w['owner']] = 0;
					$na_serials[$v['warehouse']][$w['owner']]++;
					continue;
				}
				$gb->add_row(
					$w['serial'], 
					Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'Warehouse', $v['warehouse']),
					CRM_ContactsCommon::autoselect_company_contact_format($w['owner']),
					$w['notes'],
					$w['shelf']
				);
			}
		}
		foreach ($na_serials as $w=>$mag)
			foreach ($mag as $owner=>$q)
				$gb->add_row(__('n/a').' ('.$q.')', Utils_RecordBrowserCommon::create_linked_label('premium_warehouse', 'Warehouse', $w), CRM_ContactsCommon::autoselect_company_contact_format($owner), '', '');
		$this->display_module($gb);
	}

	public function location_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location');
		$rb->set_button(false);
		$order = array(array('item_sku'=>$arg['id'],'!quantity'=>0), array('item_sku'=>false), array('warehouse'=>'ASC'));
		$this->display_module($rb,$order,'show_data');
	}

	public function warehouse_item_list_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location','premium_warehouse_location_module');
		$this->display_module($rb, array(array('warehouse'=>$arg['id'], '!quantity'=>0), array('warehouse'=>false,'item_name'=>true), array('item_sku'=>'ASC')), 'show_data');
	}

	public function company_items_addon($arg){
	    $items = DB::GetCol('SELECT location_id FROM premium_warehouse_location_serial WHERE owner=%s', array('C:'.$arg['id']));
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_location','premium_warehouse_location_module');
		$this->display_module($rb, array(array('id'=>$items, '!quantity'=>0), array('warehouse'=>false,'item_name'=>true), array('item_name'=>'ASC')), 'show_data');
	}
}

?>