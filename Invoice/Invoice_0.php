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
 * @subpackage warehouse-items-invoice
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Invoice extends Module {
	public function browse_mode_details($form, $filters, $vals, $crits, $rb) {
		$rb->set_search_calculated_callback(array($this, 'search_invoice_number'));
	}
	
	public function search_invoice_number($search) {
		$crits = array();
		foreach ($search as $k=>$v) {
			if (is_array($v)) $v = reset($v);
			if (strpos($k, 'invoice_number')!==false) {
				$parse = explode('/', $v);
				if (!isset($parse[2])) continue;
				$date = strtotime($parse[0].'-'.str_pad($parse[1], 2, '0', STR_PAD_LEFT).'-01');
				if (!$date) continue;
				$parse[2] = explode('-', $parse[2]);
				if (!isset($parse[2][1])) continue;
				$war = Utils_RecordBrowserCommon::get_records('premium_warehouse', array('invoice_number_code'=>$parse[2][1]));
				if (empty($war)) continue;
				$crits['receipt'] = false;
				$crits['transaction_type'] = 1;
				$crits['>=transaction_date'] = date('Y-m-d', $date);
				$crits['<=transaction_date'] = date('Y-m-t', $date);
				$crits['invoice_number'] = $parse[2][0];
				$war_id = array();
				foreach ($war as $w) $war_id[] = $w['id'];
				$crits['warehouse'] = $war_id;
				$search = array();
				break;
			}
		}
		return $crits;
	}
	
	public function admin() {
		if ($this->is_back()) {
			$this->parent->reset();
			return;
		}

        Base_ActionBarCommon::add('scan', __('Refresh templates'), $this->create_callback_href(array($this, 'refresh_templates')));
        $templates = Premium_Warehouse_InvoiceCommon::available_templates();

        print __('Put your template directory to %s', array('<i>' . $this->get_module_dir() . 'theme</i>')) .
            '<br /><br />' . __('Below you can manage enabled templates.') .
            '<br />' . __('Enter display name for template to enable it or leave empty to disable.') .
            '<br />' . __('If several templates will be enabled, then choose form will be displayed upon print button.');
        $form = $this->init_module('Libs/QuickForm');
        foreach ($templates as $template_name) {
            $form->addElement('text', $template_name, $template_name);
        }

		$style = Variable::get('premium_warehouse_invoice_style', false);
		if (!$style) $style = 'US';
        if (!is_array($style)) $style = array($style => $style);
		
		$form->setDefaults($style);
		
		Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href());
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		
		if ($form->validate()) {
            $values = $form->exportValues();
            $save_templates = array();
            foreach ($templates as $template_name) {
                if (isset($values[$template_name])) {
                    $label = trim($values[$template_name]);
                    if ($label)
                        $save_templates[$template_name] = $label;
                }
            }
			Variable::set('premium_warehouse_invoice_style', $save_templates);
			$this->parent->reset();
		} else {
            $form->display();
        }
	}

    public function refresh_templates() {
        Base_ThemeCommon::themeup();
    }
}
?>
