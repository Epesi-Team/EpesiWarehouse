<?php
/**
 * 
 * @author bukowski@crazyit.pl
 * @copyright Telaxus LLC
 * @license Commercial
 * @version 0.1
 * @package epesi-Premium/Warehouse/eCommerce
 * @subpackage Allegro
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce_AllegroInstall extends ModuleInstall {

	public function install() {
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/eCommerce/Allegro', 'warehouse_item_addon', 'Premium_Warehouse_eCommerce_AllegroCommon::warehouse_item_addon_label');
		$ret = DB::CreateTable('premium_ecommerce_allegro_cats','
					id I4 NOTNULL,
					name X NOTNULL,
					country I4 NOTNULL',
				array('constraints'=>', KEY(id,country)'));
		if(!$ret){
			print('Unable to create table premium_ecommerce_allegro_cats.<br>');
			return false;
		}
		
		$this->create_data_dir();
		
		$this->add_aco('settings',array('Employee'));
		
		return true;
	}
	
	public function uninstall() {
		DB::DropTable('premium_ecommerce_allegro_cats');
		Variable::delete('ecommerce_allegro_cats_up');
		return true;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Utils/RecordBrowser','version'=>0),
			array('name'=>'Premium/Warehouse/eCommerce','version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'',
			'Author'=>'bukowski@crazyit.pl',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
		return true;
	}
	
}

?>