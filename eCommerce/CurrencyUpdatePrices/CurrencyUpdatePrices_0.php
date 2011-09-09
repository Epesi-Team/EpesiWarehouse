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
			} else {
				$this->parent->reset();
				return;
			}
		}
		
		$form = & $this->init_module('Libs/QuickForm',null,'currency_setup');

		$rates = Variable::get('ecommerce_price_updater_rates',false);
		$dater = Variable::get('ecommerce_price_updater_last_upd',false);
		if($rates && $dater && $rates = @unserialize($rates)) {
		    $form->addElement('header','hu',$this->t('Last update'));
		    $form->addElement('static','last_up',$this->t('Date'),Base_RegionalSettingsCommon::time2reg($dater));
		    $r = array();
		    foreach($rates as $k=>$v) {
			$r[] = '<b>EUR/'.Utils_CurrencyFieldCommon::get_code($k).':</b> '.$v;
		    }
		    $form->addElement('static','last_rates',$this->t('Rates'),implode('<br />',$r));
		}

		$form->addElement('header','h',$this->t('eCommerce Prices Auto Updater'));
		
		$form->addElement('text','profit',$this->t('Profit margin (in percent)'));
		$form->addRule('profit',$this->t('Not a number'),'regex','([1-9][0-9]{0,1}|100)');
		$form->addRule('profit',$this->t('Field required'),'required');
		$currencies = DB::GetAssoc('SELECT code, code FROM utils_currency WHERE active=1');
		$form->addElement('multiselect','currencies',$this->t('Currencies'),$currencies);
		$form->addRule('currencies',$this->t('Field required'),'required');
		
		$def = Variable::get('ecommerce_price_updater',false);
		if($def && $def = @unserialize($def)) 
		    $form->setDefaults($def);
		else 
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
	$ret = Premium_Warehouse_eCommerce_CurrencyUpdatePricesCommon::update($data);
        if($ret===false) {
            Epesi::alert($this->t('Unable to get currency exchange rates'));
            return false;
        }
        if($ret === null) {
            Epesi::alert($this->t('Invalid return from update function'));
            return false;        
        }
	    Variable::set('ecommerce_price_updater',serialize($data));
		Epesi::alert('Prices updated');
	    return true;
	}
}

?>