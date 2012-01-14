<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse extends Module {
	private $rb;

	public function body() {
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse','premium_warehouse_module');
		$this->rb->set_defaults(array(	'country'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_country'),
										'zone'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_state')));
		$this->display_module($this->rb);
	}

	public function admin() {
		if($this->is_back()) {
			if($this->parent->get_type()=='Base_Admin')
				$this->parent->reset();
			else
				location(array());
			return;
		}
		$orders = (ModuleManager::is_installed('Premium_Warehouse_Items_Orders')>-1);
	
		$form = & $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, $this->t('Warehouse'));

		$form->addElement('text', 'weight_units', $this->t('Weight units'));
		$form->addRule('weight_units', $this->t('Must be between 1 and 10 chars'), 'rangelength', array(1,10));
		$form->addRule('weight_units', $this->t('Field required'), 'required');
		$form->addElement('text', 'volume_units', $this->t('Volume units'));
		$form->addRule('volume_units', $this->t('Must be between 1 and 10 chars'), 'rangelength', array(1,10));
		$form->addRule('volume_units', $this->t('Field required'), 'required');

		$form->addElement('static', 'notice', $this->t('Notice'), 'You can use upper indexing.<br />Example: "dm^3" will be displayed as "dm<sup>3</sup>"');

		if ($orders) {
			$form->addElement('header', 'disable_trans_types_header', $this->t('Disable Transaction Types'));
			$form->addElement('checkbox', 'disable_purchase', $this->t('Disable Purchase'));
			$form->addElement('checkbox', 'disable_sales_quote', $this->t('Disable Sales Quote'));
			$form->addElement('checkbox', 'disable_sale', $this->t('Disable Sale'));
			$form->addElement('checkbox', 'disable_inv_adj', $this->t('Disable Inv. Adjustment'));
//			$form->addElement('checkbox', 'disable_rental', $this->t('Disable Rental'));
			$form->addElement('checkbox', 'disable_transfer', $this->t('Disable Warehouse Transfer'));
			$form->addElement('checkbox', 'disable_checkin', $this->t('Check-in'));
			$form->addElement('checkbox', 'disable_checkout', $this->t('Check-out'));
			$disabled = Variable::get('premium_warehouse_trans_types', false);
			if (!$disabled) $disabled = array();
			foreach ($disabled as $d)
				$form->setDefaults(array($d=>true));
		}

		
		$form->setDefaults(array(
			'weight_units'=>preg_replace('/\<sup\>([0-9]+)\<\/sup\>/', '^$1', Variable::get('premium_warehouse_weight_units')),
			'volume_units'=>preg_replace('/\<sup\>([0-9]+)\<\/sup\>/', '^$1', Variable::get('premium_warehouse_volume_units'))
		));

		if($form->validate()) {
			$vals = $form->exportValues();
			$vals['weight_units']=preg_replace('/\^([0-9]+)/', '<sup>$1</sup>', $vals['weight_units']);
			$vals['volume_units']=preg_replace('/\^([0-9]+)/', '<sup>$1</sup>', $vals['volume_units']);
			Variable::set('premium_warehouse_weight_units', $vals['weight_units']);
			Variable::set('premium_warehouse_volume_units', $vals['volume_units']);

			if ($orders) {
				$result = array();
				foreach ($vals as $k=>$v) {
					if (strpos($k, 'disable_')!==false && $v==true) $result[] = $k;
				}
				Variable::set('premium_warehouse_trans_types', $result);
			}

			if($this->parent->get_type()=='Base_Admin')
				$this->parent->reset();
			else
				location(array());
			return;
		} else $form->display();

		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		Base_ActionBarCommon::add('save', 'Save', $form->get_submit_form_href());
		
    	return true;
	}

	public function caption(){
		if (isset($this->rb)) return $this->rb->caption();
	}
}

?>