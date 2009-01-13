<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * eCommerce
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerceCommon extends ModuleCommon {
	public function display_item_name($r, $nolink, $desc) {
		$ret = 	Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$r['id'],$nolink).
				Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$r[$desc['id']],'item_name').
				Utils_RecordBrowserCommon::record_link_close_tag();
		$ret .= ' ';
		$ret .= Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items',$r[$desc['id']],$nolink).
				'[warehouse item link]'.
				Utils_RecordBrowserCommon::record_link_close_tag();
		return $ret;
	}
	
	public function QFfield_description_language(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		$opts = array(''=>'---')+Utils_CommonDataCommon::get_translated_array('eCommerce_Languages');
		if ($mode!='view') {
			$form->addElement('select', $field, $label, $opts, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label, array('id'=>'status'));
			$form->setDefaults(array($field=>$opts[$default]));
		}
	}
	
	public function display_description($r, $nolink, $desc) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items','item_name',$r[$desc['id']]);
	}
	
	public function items_crits() {
		Utils_RecordBrowserCommon::get_records();
		return array();
	}

    public static function menu() {
		return array('Warehouse'=>array('__submenu__'=>1,'eCommerce'=>array('__submenu__'=>1, 'Products'=>array(), 'Parameters'=>array())));
	}

}
?>
