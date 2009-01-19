<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2007, Telaxus LLC
 * @version 1.0
 * @license SPL
 * @package warehouse
 */


die();


define('CID',false); //i know that i won't access $_SESSION['client']
require_once('../../../include.php');
ModuleManager::load_modules();

DB::Execute('DELETE FROM modules WHERE name LIKE '.DB::Concat(DB::qstr('Premium_Warehouse'),DB::qstr('%')));
DB::Execute('DELETE FROM recordbrowser_table_properties WHERE tab LIKE '.DB::Concat(DB::qstr('premium_warehouse'),DB::qstr('%')));
DB::Execute('DELETE FROM recordbrowser_table_properties WHERE tab LIKE '.DB::Concat(DB::qstr('premium_ecommerce'),DB::qstr('%')));
DB::Execute('DELETE FROM utils_watchdog_category WHERE callback LIKE '.DB::Concat(DB::qstr('Premium_Warehouse'),DB::qstr('%')));

@DB::DropTable('premium_warehouse_location_serial');
@DB::DropTable('premium_warehouse_location_orders_serial');

foreach (array('premium_warehouse_items',
	'premium_warehouse_location',
	'premium_warehouse_items_orders',
	'premium_warehouse_items_orders_details',
	'premium_warehouse',
	'premium_ecommerce_products',
	'premium_ecommerce_names',
	'premium_ecommerce_descriptions',
	'premium_ecommerce_parameters',
	'premium_ecommerce_parameter_labels',
	'premium_ecommerce_products_parameters') as $t)
	foreach (Utils_RecordBrowserCommon::get_tables($t) as $tt) {
		@DB::DropTable($tt);
	}


print('Script run successfully');

?>
