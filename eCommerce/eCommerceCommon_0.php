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
	public static function access_parameters($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse ecommerce');
			case 'view':	if($i->acl_check('view ecommerce')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit ecommerce');
			case 'delete':	return $i->acl_check('delete ecommerce');
			case 'fields':	return array('position'=>'hide');
		}
		return false;
    }

	public function display_item_name($r, $nolink, $desc) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items','item_name',$r['id'],$nolink);
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
		$lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
		$id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_descriptions', array('item', 'language'), array($r['item_name'], $lang_code));
		$lan = Utils_commonDataCommon::get_value('eCommerce_Languages/'.$lang_code);
		if (!is_numeric($id)) return Base_LangCommon::ts('Premium_eCommerce','Description in <b>%s</b> missing', array($lan?$lan:$lang_code));
		return Utils_RecordBrowserCommon::get_value('premium_ecommerce_descriptions',$id,'description');
	}
	
	public function display_product_name($r, $nolink, $desc) {
		$lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
		$id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_descriptions', array('item', 'language'), array($r['item_name'], $lang_code));
		$lan = Utils_commonDataCommon::get_value('eCommerce_Languages/'.$lang_code);
		if (!is_numeric($id)) return Base_LangCommon::ts('Premium_eCommerce','Product name in <b>%s</b> missing', array($lan?$lan:$lang_code));
		return 	Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$r['id'],$nolink).
				Utils_RecordBrowserCommon::get_value('premium_ecommerce_descriptions',$id,'product_name').
				Utils_RecordBrowserCommon::record_link_close_tag();
	}
	
	public function items_crits() {
		Utils_RecordBrowserCommon::get_records();
		return array();
	}

    public static function menu() {
		return array('Warehouse'=>array(
			'__submenu__'=>1,
			'eCommerce'=>array(
				'__submenu__'=>1, 
				'Products'=>array('recordset'=>'products'), 
				'Parameters'=>array()
			)
		));
	}

}
?>
