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
        if(!Premium_Warehouse_eCommerce_CurrencyUpdatePricesCommon::update($data)) {
            Epesi::alert($this->t('Unable to get currency exchange rates'));
            return false;
        }
	    Variable::set('ecommerce_price_updater',serialize($data));
		Epesi::alert('Prices updated');
	    return true;
	}
}

?>