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
		return 'Warehouse';
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
		return array('Warehouse'=>array(
			array('name'=>'my_warehouse','label'=>'My main Warehouse','type'=>'select','values'=>$warehouses,'default'=>'','translate'=>false)
			));
	}
	
	public static function access_warehouse($action, $param=null){
		$i = self::Instance();
		switch ($action) {
			case 'browse_crits': return $i->acl_check('browse warehouses');
			case 'browse':	return $i->acl_check('browse warehouses');
			case 'view':	return $i->acl_check('view warehouses');
			case 'clone':
			case 'add':
			case 'edit':	return $i->acl_check('edit warehouses');
			case 'delete':	return $i->acl_check('delete warehouses');
		}
		return false;
    }

    public static function menu() {
		if (self::access_warehouse('browse'))
			return array('Warehouse'=>array('__submenu__'=>1,'Warehouses'=>array()));
		return array();
	}

	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse',
				Base_LangCommon::ts('Premium_Warehouse','Warehouse'),
				$rid,
				$events,
				'warehouse',
				$details
			);
	}
	
	public static function search_format($id) {
		if(Acl::check('Premium_Warehouse','browse warehouses')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse', $row['id']).Base_LangCommon::ts('Premium_Warehouse', 'Warehouse (attachment) #%d, %s', array($row['id'], $row['warehouse'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}
}
?>
