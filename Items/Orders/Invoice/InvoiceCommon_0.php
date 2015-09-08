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

class Premium_Warehouse_Items_Orders_InvoiceCommon extends ModuleCommon {
    public static function addon_params($record, $rb_obj) {
        if (Utils_RecordBrowser::$mode != 'view')
            return;
        if (!Utils_RecordBrowserCommon::get_access('premium_invoice','add'))
            return;
        if(!Utils_RecordBrowserCommon::get_records_count('premium_warehouse_items_orders_details', array('transaction_id' => $record['id'])) || $record['status']<1) return array('show'=>false);
        // Standard print
        if (!$record['receipt'] && $record['transaction_type']==1) {
            if(isset($_REQUEST['create_invoice']) && $_REQUEST['create_invoice']==$record['id']) {
                Premium_InvoiceCommon::clear_queue();
                self::add_items_to_invoice($record);
                Premium_InvoiceCommon::set_tax_calculation($record['company'],$record['tax_calculation']);
                $shipment = Utils_CurrencyFieldCommon::get_values($record['shipment_cost']);
                if($shipment[0]) Premium_InvoiceCommon::queue_item(__('Shipment Cost'), $record['shipment_cost'],'',null,1,__('pc'),'', $record['company'],null,array(),'shipment');
                $handling = Utils_CurrencyFieldCommon::get_values($record['handling_cost']);
                if($handling[0]) Premium_InvoiceCommon::queue_item(__('Handling Cost'), $record['handling_cost'],'',null,1,__('pc'),'', $record['company'],null,array(),'handling');
                Premium_InvoiceCommon::pending_items();
            }
            if(self::not_billed_items($record['id'])) {
                Base_ActionBarCommon::add('print', __('Invoice'), Module::create_href(array('create_invoice'=>$record['id'])));
            }
        }
        return array('show'=>false);
    }
    
    private static function add_items_to_invoice($record) {
        if(is_numeric($record)) $record = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$record);
        $transaction_id = $record['id'];
        $items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id' => $transaction_id));
        foreach($items as $item) {
            if($item['billed_quantity']<$item['quantity']) {
                Premium_InvoiceCommon::queue_item(Premium_Warehouse_ItemsCommon::display_item_name($item['item_name'],true), $item['net_price'],$item['tax_rate'],$item['gross_price'],$item['quantity']-$item['billed_quantity'],__('pc'),__('Transaction ID').': '.$record['transaction_id'], $record['company'],
                                array('Premium_Warehouse_Items_Orders_InvoiceCommon','invoice'),array($item['id']),$item['id']);

            }
        }
    }

    private static function not_billed_items($transaction_id) {
        $items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id' => $transaction_id));
        foreach($items as $item) {
            if($item['billed_quantity']<$item['quantity']) {
                return true;
            }
        }
        return false;
    }
    
    public static function invoice($id,$invoice,$inv_item) {
        $item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders_details', $id);
        if(!in_array($invoice['id'],$item['invoices'])) {
            $item['invoices'][] = $invoice['id'];
        }
        if(!in_array('premium_warehouse_items_orders/'.$item['transaction_id'],$invoice['related'])) {
            $invoice['related'][] = 'premium_warehouse_items_orders/'.$item['transaction_id'];
        }
        Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $id, array('billed_quantity' => $inv_item['quantity'], 'invoices'=>$item['invoices']));
        Utils_RecordBrowserCommon::update_record('premium_invoice', $invoice['id'], array('related' => $invoice['related']));
    }

    public static function display_invoices($r, $nolink) {
        $items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id' => $r['id']));
        $invoices = array();
        foreach($items as $item) {
            $invoices = array_merge($invoices,$item['invoices']);
        }
        $invoices = array_unique($invoices);
        foreach($invoices as & $inv) {
            $inv = Utils_RecordBrowserCommon::create_default_linked_label('premium_invoice', $inv, $nolink);
        }
        return implode('<br />',$invoices);
    }

    public static function QFfield_invoices(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
        Utils_RecordBrowserCommon::QFfield_multiselect($form,$field,$label,$mode,$default,$desc,$rb_obj);
        $form->freeze($field);
    }

    public static function QFfield_billed(&$form, $field, $label, $mode, $default,$desc,$rb) {
        Utils_RecordBrowserCommon::QFfield_checkbox($form, $field, $label, $mode, $default,$desc,$rb);
        $form->freeze($field);
    }
    
    public static function submit_order($vals,$action) {
        static $done = false;
        if($action=='browse' && $done!=Utils_RecordBrowser::$rb_obj->get_path() && !Utils_RecordBrowser::$rb_obj->isset_module_variable('rp_fs_path')) {
            $done = Utils_RecordBrowser::$rb_obj->get_path();
            if (Utils_RecordBrowserCommon::get_access('premium_invoice','add')) {
                $filters = Utils_RecordBrowser::$rb_obj->get_module_variable('def_filter',array());
                $rb = Utils_RecordBrowser::$rb_obj->init_module('Utils/RecordBrowser/RecordPickerFS',array('premium_warehouse_items_orders',array('transaction_type'=>array(1,3),'billed'=>0),array(),array('Start'=>'DESC'),array('transaction_type'=>0),$filters));
		$rb->set_default_order(array('transaction_date'=>'DESC','transaction_id'=>'DESC'));
//		$rb->set_module_variable('def_filter',$filters);
		Base_ActionBarCommon::add('save',__('Invoice Transactions'),$rb->create_open_href());
		Utils_RecordBrowser::$rb_obj->display_module($rb);
		$sel = $rb->get_selected();
		if($sel) {
			$rb->clear_selected();
                        Premium_InvoiceCommon::clear_queue();
			foreach($sel as $s) {
			    $record = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$s);
                            self::add_items_to_invoice($s);
                            $shipment = Utils_CurrencyFieldCommon::get_values($record['shipment_cost']);
                            if($shipment[0]) Premium_InvoiceCommon::queue_item(__('Shipment Cost'), $record['shipment_cost'],'',null,1,__('pc'),__('Transaction ID').': '.$s, $record['company'],null,array(),'shipment');
                            $handling = Utils_CurrencyFieldCommon::get_values($record['handling_cost']);
                            if($handling[0]) Premium_InvoiceCommon::queue_item(__('Handling Cost'), $record['handling_cost'],'',null,1,__('pc'),__('Transaction ID').': '.$s, $record['company'],null,array(),'handling');
                            Premium_InvoiceCommon::pending_items();
			}
		}
	    }
        }
        if($action=='edit') {
            if(self::not_billed_items($vals['id'])) $vals['billed'] = 0;
            else $vals['billed'] = 1;
        }
        return $vals;
    }

    public static function submit_item($vals,$action) {
        if($action=='added' || $action=='edited') {
            if($vals['billed_quantity']<$vals['quantity'])
                Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$vals['transaction_id'],array('billed'=>0));
            elseif(!self::not_billed_items($vals['transaction_id']))
                Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$vals['transaction_id'],array('billed'=>1));
        }
        return $vals;
    }
}

?>