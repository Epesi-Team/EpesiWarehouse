<?php
/**
 * 
 * @author shacky@poczta.fm
 * @copyright Telaxus LLC
 * @license MIT
 * @version 0.1
 * @package epesi-Premium/Warehouse/eCommerce
 * @subpackage CurrencyUpdatePrices
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce_CompareUpdatePrices extends Module {

	public function body() {
	
	}

	public function item_addon($r) {
		/* @var $rb Utils_RecordBrowser */
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_compare_prices');
		$rb->set_defaults(array('item_name'=>$r['id']));
		$order = array(array('item_name'=>$r['id']), array('item_name'=>false), array('plugin'=>'ASC'));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function product_addon($r) {
		$this->item_addon(array('id'=>$r['item_name']));
	}
}

?>