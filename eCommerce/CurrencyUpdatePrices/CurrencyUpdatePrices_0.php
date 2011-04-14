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

class Premium_Warehouse_eCommerce_CurrencyUpdatePrices extends Module {

	public function body() {
	
	}

	public function admin() {
		if($this->is_back()) {
			if($this->isset_module_variable('module') && $this->isset_module_variable('original')) {
				$this->unset_module_variable('module');
				$this->unset_module_variable('original');
			} else
				$this->parent->reset();
		}
		
		$form = & $this->init_module('Libs/QuickForm',null,'currency_setup');

		$form->addElement('header','h',$this->t('eCommerce Prices Auto Updater'));
		
		$form->addElement('text','profit',$this->t('Profit margin (in percent)'));
		$form->addRule('profit',$this->t('Not a number'),'regex','([1-9][0-9]{0,1}|100)');
		$form->addRule('profit',$this->t('Field required'),'required');
		$currencies = DB::GetAssoc('SELECT code, code FROM utils_currency WHERE active=1');
		$form->addElement('multiselect','currencies',$this->t('Currencies'),$currencies);
		$form->addRule('currencies',$this->t('Field required'),'required');
		$form->addElement('select','tax',$this->t('Tax'),Data_TaxRatesCommon::get_tax_rates());
		$form->addRule('tax',$this->t('Field required'),'required');
		
		
		
		$form->setDefaults(array('profit'=>5));
		
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		Base_ActionBarCommon::add('save', 'Update', $form->get_submit_form_href());
		
		if($form->validate()) {
			if($form->process(array($this,'submit_admin'))) {
				$this->parent->reset();
				return;
			}
		} 
		$form->display();
    }
    
    public function submit_admin($data) {
        set_time_limit(0);
        $ret = @simplexml_load_file('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');
        if(!$ret) {
            Epesi::alert($this->t('Unable to get currency exchange rates'));
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
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array(),array('net_price'));
		foreach($recs as $r) {
		    list($value,$curr) = Utils_CurrencyFieldCommon::get_values($r['net_price']);
		    if(is_numeric($value) && $value>0) {
		        $euro = $value*(100+Data_TaxRatesCommon::get_tax_rate($data['tax']))/(100*$rates[$curr]);
		        $euro += $euro*$data['profit']/100;
		        foreach($currencies_to_conv as $curr) {
		            $price = $euro*$rates[$curr];
		            $to_up = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$r['id'],'currency'=>$curr),array());
		            if($to_up) {
		        	$to_up = array_shift($to_up);
		                Utils_RecordBrowserCommon::update_record('premium_ecommerce_prices',$to_up['id'],array('gross_price'=>$price,'tax_rate'=>$data['tax']));
		            } else {
		                Utils_RecordBrowserCommon::new_record('premium_ecommerce_prices',array('currency'=>$curr,'item_name'=>$r['id'],'gross_price'=>$price,'tax_rate'=>$data['tax']));
		            }
		        }
		    } else {
		        $to_del = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$r['id']),array());
		        foreach($to_del as $del)
		            Utils_RecordBrowserCommon::delete_record('premium_ecommerce_prices',$del['id']);
		    }
		}
		Epesi::alert('Prices updated');
	    return true;
	}

}

?>