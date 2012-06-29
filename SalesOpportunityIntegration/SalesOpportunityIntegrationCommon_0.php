<?php
/**
 * Sales Opportunity Tracker
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-premium
 * @subpackage salesopportunity
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_SalesOpportunityIntegrationCommon extends ModuleCommon {
	public static function QFfield_salesopp(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		if (isset($rb_obj->record) && isset($rb_obj->record['transaction_type']) && $rb_obj->record['transaction_type']==1) {
			if ($mode=='view') {
				$val = $default?self::display_opportunity_name($default):'---';
				$form->addElement('static', $field, $label, $val);
			} else {
				$crits = array();
				$callback = array('Premium_Warehouse_SalesOpportunityIntegrationCommon','display_opportunity_name');
				$form->addElement('autoselect', $field, $label, array(), array(array('Utils_RecordBrowserCommon','automulti_suggestbox'), array('premium_salesopportunity', $crits, $callback, $desc['param'])), $callback);
				$form->setDefaults(array($field=>$default));
			}
		}
	}
	public static function display_opportunity_name($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_salesopportunity', 'Opportunity Name', $v, $nolink);
	}
}
?>
