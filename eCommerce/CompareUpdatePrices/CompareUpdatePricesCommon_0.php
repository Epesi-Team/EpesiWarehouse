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
		if(!isset($r['id'])) 
			return array('show'=>false);
		$ret = Utils_RecordBrowserCommon::get_records_count('premium_ecommerce_products',array('item_name'=>$r['id']));
		if(!$ret)
			return array('show'=>false);
		return array('show'=>true, 'label'=>__('Compare Services'));
	}

	public static function update() {
		$ret = Utils_RecordBrowserCommon::get_records('premium_ecommerce_compare_prices',array('<last_update'=>time()-3600*12),array(),array('last_update'=>'ASC'));
		$services = array();
		foreach($ret as $row) {
			if(defined('TEST')) print($row['item_name'].' '.$row['plugin'].' '.$row['url']."\n");
			if(!isset($services[$row['plugin']])) {
				require_once('modules/Premium/Warehouse/eCommerce/CompareUpdatePrices/plugins/'.$row['plugin'].'.php');
				$klasa = 'Premium_Warehouse_eCommerce_CompareService_'.$row['plugin'];
				$services[$row['plugin']] = new $klasa();
			}
			$a = $services[$row['plugin']];
			$a->price = null;
			$a->prices = null;
			$tax = Data_TaxRatesCommon::get_tax_rate($row['tax_rate']);
			
			if(!$a->fetch($row['url'],$tax)) {
				$a->price = '';
				$a->currency = '';
			}
			if(!isset($a->price) && $a->currency && is_array($a->prices)) {
				$len = count($a->prices);
				if($len<2)  {
					$a->price = '';
					$a->currency = '';
				} else {
					if(is_numeric($row['position']) && $row['position']>0)
						for($i=min($len-2,$row['position']-1);$i>0; $i--)
							array_shift($a->prices);
					$a->price = (array_shift($a->prices)+array_shift($a->prices))/2;
				}
			}
		
			if($a->price && $a->currency) {
				if(defined('TEST')) print($a->price."\n");
				$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$row['item_name']);
				$qty = DB::GetOne('SELECT SUM(f_quantity) FROM premium_warehouse_location_data_1 WHERE f_item_sku=%d AND active=1',array($row['item_name']));
				$res = Premium_Warehouse_Items_OrdersCommon::get_reserved_qty($row['item_name']);
				$qty -= $res['total'];
				if($qty>0) {
					$value = Utils_CurrencyFieldCommon::get_values($item['last_purchase_price']);
					if($value[1] != $a->currency || ($a->price*100/(100+$tax)) <= $value[0]) {
						$a->price = '';
						$a->currency = '';
					}
				} else {
					$ffs = DB::GetAll('SELECT i.price, d.f_minimal_profit FROM premium_warehouse_wholesale_items i INNER JOIN premium_warehouse_distributor_data_1 d ON d.id=i.distributor_id WHERE i.item_id=%d AND i.quantity>%d AND i.price_currency=%d AND i.price is not null AND i.price!=""',array($row['item_name'],-$qty,$a->currency));
					if(!$ffs) {
						$a->price = '';
						$a->currency = '';
					} else {
					    $ff = array_shift($ffs);
					    foreach($ffs as $ff2) {
						if($ff2['price']+$ff2['f_minimal_profit']<$ff['price']+$ff['f_minimal_profit'])
							$ff = $ff2;
					    }
					    $dpr = $ff['price']+$ff['f_minimal_profit'];
					    if(defined('TEST')) print($dpr.'>'.($a->price*100/(100+$tax))." => ".($dpr*(100+$tax)/100)."\n");
					    if($dpr>($a->price*100/(100+$tax))) {
						$a->price = $dpr*(100+$tax)/100;
//						$a->currency = '';
					    }
					}
				}
			}
			
			Utils_RecordBrowserCommon::update_record('premium_ecommerce_compare_prices',$row['id'],array('gross_price'=>$a->price,'currency'=>$a->currency,'last_update'=>time()));
			
			sleep(mt_rand(1,10));
		}
		Variable::set('premium_ecommerce_compare_servic',time());
		return true;
	}
	
	public static function cron() {
//        return array('cron2'=>1);
    }

    public static function cron2() {
/*	    if(Variable::get('premium_ecommerce_compare_servic',0)<(time()-900)) {
		if(self::update()===false) return 'Unable to update compare services prices';
	    }*/
	    return '';
	}

	public static function compare_filter($choice) {
		if ($choice=='__NULL__') return array();
		$ids = DB::GetCol('SELECT f_item_name FROM premium_ecommerce_compare_prices_data_1 WHERE active=1');
		if($choice) return array('id'=>$ids);
		return array('!id'=>$ids);
	}
}

abstract class Premium_Warehouse_eCommerce_CompareService {
	public $prices;
	public $price;
	public $currency;
	
	abstract function fetch($url,$tax);
}

?>