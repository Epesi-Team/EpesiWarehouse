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

		$ret = DB::CreateTable('premium_ecommerce_allegro_auctions','
							auction_id I4 NOTNULL KEY,
							item_id I4 NOTNULL,
							active I1 DEFAULT 1,
							buy_price F,
							created_by I4,
							started_on T,
							ended_on T,
							prefs X',
				array('constraints'=>', FOREIGN KEY (item_id) REFERENCES premium_warehouse_items_data_1(id),
										FOREIGN KEY (created_by) REFERENCES user_login(id)'));
		if(!$ret){
			print('Unable to create table premium_ecommerce_allegro_cats.<br>');
			return false;
		}

		$ret = DB::CreateTable('premium_ecommerce_allegro_cross','
							ret_auction_id I4 NOTNULL,
							ret_item_id I4 NOTNULL,
							item_id I4 NOTNULL,
							position I4 NOTNULL,
							ip C(32) NOTNULL,
							created_on T DEFTIMESTAMP',
				array('constraints'=>', FOREIGN KEY (item_id) REFERENCES premium_warehouse_items_data_1(id), FOREIGN KEY (ret_item_id) REFERENCES premium_warehouse_items_data_1(id), KEY(item_id,position,ip)'));
		if(!$ret){
			print('Unable to create table premium_ecommerce_allegro_cross.<br>');
			return false;
		}

		$ret = DB::CreateTable('premium_ecommerce_allegro_stan','
					cat_id I4 NOTNULL,
					field_id I4 NOTNULL,
					country I4 NOTNULL',
				array('constraints'=>', KEY(cat_id,country)'));
		if(!$ret){
			print('Unable to create table premium_ecommerce_allegro_stan.<br>');
			return false;
		}
		
		$this->create_data_dir();
		
		$this->add_aco('settings',array('Employee'));
		
		return true;
	}
	
	public function uninstall() {
		DB::DropTable('premium_ecommerce_allegro_stan');
		DB::DropTable('premium_ecommerce_allegro_cats');
		DB::DropTable('premium_ecommerce_allegro_auctions');
		DB::DropTable('premium_ecommerce_allegro_cross');
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