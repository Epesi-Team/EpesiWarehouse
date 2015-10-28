<?php
/**
 * Warehouse Orders Invoicing Module
 * @author pbukowski@telaxus.com
 * @copyright Telaxus LLC
 * @license Commercial
 * @version 0.1
 * @package epesi-Premium/Warehouse/Items/Orders
 * @subpackage Invoice
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_Orders_InvoiceInstall extends ModuleInstall {

	public function install() {
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium_Warehouse_Items_Orders_Invoice', 'addon', 'Premium_Warehouse_Items_Orders_InvoiceCommon::addon_params');
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders',array('name' => _M('Invoices'),	'type'=>'calculated','extra'=>false, 'required'=>false, 'visible'=>true,'display_callback'=>'Premium_Warehouse_Items_Orders_InvoiceCommon::display_invoices','position'=>'Status'));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders',array('name' => _M('Billed'),	'type'=>'checkbox','extra'=>false, 'required'=>false, 'visible'=>true,'filter'=>true,'QFfield_callback'=>'Premium_Warehouse_Items_Orders_InvoiceCommon::QFfield_billed','position'=>'Status'));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders_details',array('name' => _M('Invoices'),	'type'=>'multiselect','param'=>'premium_invoice::Invoice Number','extra'=>false, 'required'=>false, 'visible'=>false,'QFfield_callback'=>'Premium_Warehouse_Items_Orders_InvoiceCommon::QFfield_invoices'));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders_details',array('name' => _M('Billed Quantity'),	'type'=>'calculated','param'=>Utils_RecordBrowserCommon::actual_db_type('integer'),'extra'=>false, 'required'=>false, 'visible'=>true,'position'=>'Quantity'));
		Utils_RecordBrowserCommon::new_record_field('premium_invoice_payment_types',array('name' => _M('Order Payment Type'),	'type'=>'multiselect', 'param'=>Utils_RecordBrowserCommon::multiselect_from_common('Premium_Items_Orders_Payment_Types'), 'required'=>false, 'extra'=>false, 'visible'=>true));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items_orders', array('Premium_Warehouse_Items_Orders_InvoiceCommon', 'submit_order'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_Orders_InvoiceCommon', 'submit_item'));
		DB::Execute('UPDATE premium_warehouse_items_orders_details_data_1 SET f_billed_quantity = f_quantity');
		$payment_types = Utils_CommonDataCommon::get_array('Premium_Items_Orders_Payment_Types');
		foreach($payment_types as $key=>$val) {
			$t = Utils_RecordBrowserCommon::get_records('premium_invoice_payment_types',array('(~name'=>$key,'|~name'=>$val));
			if($t) {
				$t = array_shift($t);
				Utils_RecordBrowserCommon::update_record('premium_invoice_payment_types',$t['id'],array('order_payment_type'=>array_merge($t['order_payment_type'],array($key=>$key))));
			}
		}
		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium_Warehouse_Items_Orders_Invoice', 'addon');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders',_M('Invoices'));
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders',_M('Billed'));
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders_details',_M('Invoices'));
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items_orders_details',_M('Billed Quantity'));
		Utils_RecordBrowserCommon::delete_record_field('premium_invoice_payment_types',_M('Order Payment Type'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items_orders', array('Premium_Warehouse_Items_Orders_InvoiceCommon', 'submit_order'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_Orders_InvoiceCommon', 'submit_item'));
		return true;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Premium/Invoice','version'=>0),
			array('name'=>'Premium/Warehouse/Items','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Orders','version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Warehouse Orders Invoicing Module',
			'Author'=>'pbukowski@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
		return true;
	}
	
}

?>