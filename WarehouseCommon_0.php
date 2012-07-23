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

class Premium_WarehouseCommon extends ModuleCommon {
    public static function admin_caption() {
		return array('label'=>__('Inventory'), 'section'=>__('Features Configuration'));
    }

    public static function get_warehouse($id) {
		return Utils_RecordBrowserCommon::get_record('premium_warehouse', $id);
    }

	public static function get_warehouses($crits=array(),$cols=array()) {
    		return Utils_RecordBrowserCommon::get_records('premium_warehouse', $crits, $cols);
	}

    public static function display_warehouse($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse', 'Warehouse', $v, $nolink);
	}
	
	public static function user_settings(){
		$rec = Utils_RecordBrowserCommon::get_records('premium_warehouse', array(), array('warehouse'), array('warehouse'=>'ASC'));
		$warehouses = array(''=>'---');
		foreach ($rec as $v)
			$warehouses[$v['id']] = $v['warehouse'];
		$ret = array(__('Inventory')=>array(
			array('name'=>'my_warehouse','label'=>__('My main Warehouse'),'type'=>'select','values'=>$warehouses,'default'=>'')
			));
		$ret[__('Subscriptions')] = array(
                array('name'=>'new_online_order_header','label'=>__('Other subscriptions'),'type'=>'header'),
                array('name'=>'new_online_order_auto_subs','label'=>__('New Online Orders'),'type'=>'select','values'=>array(__('Disabled'), __('Enabled')),'default'=>0)
			);
		return $ret;
	}
	
    public static function menu() {
		if (Utils_RecordBrowserCommon::get_access('premium_warehouse','browse'))
			return array(_M('Inventory')=>array('__submenu__'=>1,_M('Warehouses')=>array()));
		return array();
	}

	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse',
				__('Warehouse'),
				$rid,
				$events,
				'warehouse',
				$details
			);
	}
	
	public static function search_format($id) {
		if(!Utils_RecordBrowserCommon::get_access('premium_warehouse','browse')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse', $row['id']).__( 'Warehouse (attachment) #%d, %s', array($row['id'], $row['warehouse'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}
}
?>
