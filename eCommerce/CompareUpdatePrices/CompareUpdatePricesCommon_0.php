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

class Premium_Warehouse_eCommerce_CompareUpdatePricesCommon extends ModuleCommon {

	public static function item_addon_parameters($r) {
		$ret = Utils_RecordBrowserCommon::get_records_count('premium_ecommerce_products',array('item_name'=>$r['id']));
		if(!$ret)
			return array('show'=>false);
		return array('show'=>true, 'label'=>'Compare Services');
	}

	public static function access_parameters($action, $param=null){
		$i = self::Instance();
		switch ($action) {
			case 'browse_crits':    return $i->acl_check('browse');
			case 'browse':  return $i->acl_check('browse');
			case 'view':    
				if (!$i->acl_check('view')) return false;
				return true;
			case 'clone':
			case 'add':
			case 'edit': 
				if($i->acl_check('edit'))
					return array('currency'=>false,'gross_price'=>false);
				return false;
			case 'delete':  return $i->acl_check('delete');
		}
		return false;
	}
	
	public static function update() {
		$ret = Utils_RecordBrowserCommon::get_records('premium_ecommerce_compare_prices');
		$services = array();
		foreach($ret as $row) {
			if(!isset($services[$row['plugin']])) {
				require_once('modules/Premium/Warehouse/eCommerce/CompareUpdatePrices/plugins/'.$row['plugin'].'.php');
				$klasa = 'Premium_Warehouse_eCommerce_CompareService_'.$row['plugin'];
				$services[$row['plugin']] = new $klasa();
			}
			$a = $services[$row['plugin']];
			$tax = Data_TaxRatesCommon::get_tax_rate($row['tax_rate']);
			
			if(!$a->fetch($row['url'],$tax,$row['gross_price'],$row['currency'])) {
				$a->price = null;
				$a->currency = null;
			}
		
			if($a->price && $a->currency) {
				$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$row['item_name']);
				$qty = DB::GetOne('SELECT SUM(f_quantity) FROM premium_warehouse_location_data_1 WHERE f_item_sku=%d AND active=1',array($row['item_name']));
				if($qty) {
					$value = Utils_CurrencyFieldCommon::get_values($item['last_purchase_price']);
					if($value[1] != $a->currency || $a->price <= $value[0]) {
						$a->price = null;
						$a->currency = null;						
					}
				} else {
					$ff = DB::GetOne('SELECT price FROM premium_warehouse_wholesale_items WHERE item_id=%d AND quantity>0 AND price_currency=%d ORDER BY price',array($row['item_name'],$a->currency));
					if($ff>($a->price*100/(100+$tax))) {
						$a->price = null;
						$a->currency = null;
					}
				}
			}
			
			Utils_RecordBrowserCommon::update_record('premium_ecommerce_compare_prices',$row['id'],array('gross_price'=>$a->price,'currency'=>$a->currency));
		}
		Variable::set('premium_ecommerce_compare_servic',time());
		return true;
	}
	
	public static function cron() {
	    if(Variable::get('premium_ecommerce_compare_servic',0)<(time()-12*3600)) {
		if(self::update()===false) return 'Unable to update compare services prices';
	    }
	    return '';
	}

}

abstract class Premium_Warehouse_eCommerce_CompareService {
	public $price;
	public $currency;
	
	abstract function fetch($url,$tax,$price,$currency);
}

?>