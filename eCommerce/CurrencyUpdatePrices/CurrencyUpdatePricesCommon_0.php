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

class Premium_Warehouse_eCommerce_CurrencyUpdatePricesCommon extends ModuleCommon {

	public static function admin_caption() {
		return 'eCommerce prices';
	}
	
	public static function update($data = null) {
        if($data===null && Variable::get('ecommerce_price_updater_last_upd')>time()-12*3600) return null;
	if($data===null) $data = Variable::get('ecommerce_price_updater',false);
        if(!$data) return null;
        if(!is_array($data)) {
            $data = @unserialize($data);
	    if(!$data) return null;
	}
        
        set_time_limit(0);
        $ret = @simplexml_load_file('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');
        if(!$ret) {
            return false;
        }
		$currencies = DB::GetAssoc('SELECT code,id FROM utils_currency WHERE active=1');
		$rates = array();
		if(isset($currencies['EUR']))
		    $rates[$currencies['EUR']]=1;
		foreach($ret->Cube->Cube->Cube as $r) {
		    if(isset($currencies[(String)$r['currency']])) {
        		    $rates[$currencies[(String)$r['currency']]] = (String)$r['rate'];
    		}
		}
		$currencies_to_conv = array();
		foreach($data['currencies'] as $r) {
		    if(isset($currencies[$r]))
    		    $currencies_to_conv[] = $currencies[$r];
		}
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array(),array('net_price','id','quantity_on_hand','tax_rate'));
		$location = ModuleManager::is_installed('Premium_Warehouse_Items_Location')>=0;
		$wholesale = ModuleManager::is_installed('Premium_Warehouse_Wholesale')>=0;
		$autoprice = Variable::get('ecommerce_autoprice');
		$autoprice_minimal = Variable::get('ecommerce_minimal_profit');
		$autoprice_percentage = Variable::get('ecommerce_percentage_profit');

		foreach($recs as $r) {
		    list($value,$curr) = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		    if(is_numeric($value) && $value)
			$value = $value*(100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))/(100*$rates[$curr]);
		    if($wholesale && $autoprice && !$value && 
		        (($location && Premium_Warehouse_Items_LocationCommon::get_item_quantity_in_warehouse($r['id'])==0) || 
		        (!$location && $r['quantity_on_hand']==0))) {
		        $fff = DB::GetAll('SELECT i.price,i.price_currency,dist.f_minimal_profit,dist.f_percentage_profit FROM premium_warehouse_wholesale_items i INNER JOIN premium_warehouse_distributor_data_1 dist ON dist.id=i.distributor_id WHERE i.item_id=%d AND i.quantity>0 AND dist.active=1',array($r['id']));
		        $min_value = null;
		        $min_curr = null;
		        foreach($fff as $ff) {
				$dvalue = $ff['price'];
				$dcurr = $ff['price_currency'];
				$profit = $dvalue*(is_numeric($ff['f_percentage_profit'])?$ff['f_percentage_profit']:$autoprice_percentage)/100;
				$minimal = (is_numeric($ff['f_minimal_profit'])?$ff['f_minimal_profit']:$autoprice_minimal);
				if($profit<$minimal) $profit = $minimal;
				$dvalue += $profit;
			        $euro = $dvalue*(100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))/(100*$rates[$dcurr]);
				if($min_value===null || $min_value>$euro) {
				    $min_value = $euro;
				    $min_curr = $dcurr;
				}
			}
			if(!is_numeric($value) || !$value || $min_value<$value) {
				$value = $min_value;
				$curr = $min_curr;
			}
		    }
		    if(is_numeric($value) && $value>0) {
		        $euro = $value*(100+$data['profit'])/100;
		        foreach($currencies_to_conv as $curr2) {
		            $to_up = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$r['id'],'currency'=>$curr2),array('auto_update'));
		    	    if($curr==$curr2) {
		    		if($to_up) {
			        	$to_up = array_shift($to_up);
			        	if($to_up['auto_update'])
			        		Utils_RecordBrowserCommon::delete_record('premium_ecommerce_prices',$to_up['id']);
		    		
		    		}
		    		continue;
		    	    }
		            $price = $euro*$rates[$curr2];
		            if($to_up) {
		        	$to_up = array_shift($to_up);
		        	if($to_up['auto_update'])
		            	    Utils_RecordBrowserCommon::update_record('premium_ecommerce_prices',$to_up['id'],array('gross_price'=>$price,'tax_rate'=>$r['tax_rate']));
		            } else {
		                Utils_RecordBrowserCommon::new_record('premium_ecommerce_prices',array('currency'=>$curr2,'item_name'=>$r['id'],'gross_price'=>$price,'tax_rate'=>$r['tax_rate'],'auto_update'=>1));
		            }
		        }
		    } else {
		        $to_del = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$r['id'],'auto_update'=>1),array());
		        foreach($to_del as $del)
		            Utils_RecordBrowserCommon::delete_record('premium_ecommerce_prices',$del['id']);
		    }
		}
	    Variable::set('ecommerce_price_updater_rates',serialize($rates));
	    Variable::set('ecommerce_price_updater_last_upd',time());
	    return true;
	}
	
	public static function cron() {
	    if(self::update()===false) return 'Unable to get ECB currencies rates';
	    return '';
	}

}

?>