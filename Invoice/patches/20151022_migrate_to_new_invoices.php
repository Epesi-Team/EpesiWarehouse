<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

ModuleManager::install('Premium/Warehouse/Items/Orders/Invoice');

//migrate invoice number style
$numbering_checkpoint = Patch::checkpoint('invoices');
if(!$numbering_checkpoint->is_done()) {
    $warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse');
    $numbering = array();
    foreach ($warehouses as $w) {
        $wc = '';
        if (isset($w['invoice_number_code']) && $w['invoice_number_code']) $wc = $w['invoice_number_code'];
        $wc = mb_strtoupper($w['warehouse'], 'UTF-8');
        $numbering[$w['id']] = Utils_RecordBrowserCommon::new_record('premium_invoice_numbering', array('name' => __('Warehouse'), 'numbering_type' => '0', 'number_format' => '%Y/%n/%N-' . $wc, 'quote' => 0, 'active' => 1));
    }
    $numbering_checkpoint->set('numbering',$numbering);

    $payment_types = Utils_CommonDataCommon::get_array('Premium_Items_Orders_Payment_Types');
    foreach($payment_types as $pk=>&$pt) {
        $pt = Utils_RecordBrowserCommon::new_record('premium_invoice_payment_types',array('name'=>$pt));
    }
    $numbering_checkpoint->set('payments',$payment_types);
}
$numbering = $numbering_checkpoint->get('numbering');
$payment_types = $numbering_checkpoint->get('payments');


//migrate invoices
Patch::set_message('Processing invoices');
$invoices_checkpoint = Patch::checkpoint('invoices');
if(!$invoices_checkpoint->is_done()) {
    if($invoices_checkpoint->has('invoices')) {
        $invoices = $invoices_checkpoint->get('invoices');
    } else {
        $invoices = 0;
    }
    if($invoices_checkpoint->has('invoices_qty')) {
        $invoices_qty = $invoices_checkpoint->get('invoices_qty');
    } else {
        $invoices_qty = DB::GetOne('SELECT count(*) FROM premium_warehouse_items_orders_data_1 WHERE active=1 AND f_transaction_type=1 AND f_invoice_number is not null AND f_invoice_number!=""');
        $invoices_checkpoint->set('invoices_qty',$invoices_qty);
    }

    while($ret = DB::SelectLimit('SELECT id FROM premium_warehouse_items_orders_data_1 WHERE active=1 AND f_transaction_type=1 AND f_invoice_number is not null AND f_invoice_number!="" ORDER BY id',1,$invoices++)) {
        $row = $ret->FetchRow();
        if(!$row) break;

        $order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$row['id']);
        $items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details',array('transaction_id'=>$row['id']));

        $item = array_shift($items);
        list($amount,$curr) = Utils_CurrencyFieldCommon::get_values($item['net_price']);
        reset($items);

        $invoice_id = Utils_RecordBrowserCommon::new_record('premium_invoice',array(
            'company_issuing_invoice'=>CRM_ContactsCommon::get_main_company(),
            'quote'=>0,
            'invoice_number'=>$order['invoice_number'],
            'invoice_number_series'=>$numbering[$order['warehouse']],
            'invoice_date'=>$order['invoice_print_date'],
            'employee'=>$order['employee'],
            'related'=> array('premium_warehouse_items_orders/'.$row['id']),
            'sales_date'=>$order['transaction_date'],
            'due_date'=>date('Y-m-d',strtotime('+'.$order['terms'].' days',strtotime($order['invoice_print_date']))),
            'print_date'=>$order['invoice_print_date'],
            'target_currency'=>$curr,
            'payment_type'=>$payment_types[$order['payment_type']],
            'paid_date'=>'',
            'price_calculation'=>'gross',
            'tax_calculation'=>$order['tax_calculation'],
            'company_or_contact'=>$order['company']?'C:'.$order['company']:'P:'.$order['contact'],
            'company_name'=>$order['company_name'],
            'first_name'=>$order['first_name'],
            'last_name'=>$order['last_name'],
            'address_1'=>$order['address_1'],
            'address_2'=>$order['address_2'],
            'city'=>$order['city'],
            'country'=>$order['country'],
            'zone'=>$order['zone'],
            'postal_code'=>$order['postal_code'],
            'phone'=>$order['phone'],
            'tax_id'=>$order['tax_id']
        ));
        foreach($items as $item) {
            $it = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item['item_name']);
            if(ModuleManager::is_installed('Premium/Warehouse/DrupalCommerce')>=0) {
                $descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions', array('item_name'=>$item['item_name'],'language'=>Base_LangCommon::get_lang_code()));
                foreach($descs as $desc) {
                    if($desc['display_name']) {
                        $it['item_name'] = $desc['display_name'];
                        break;
                    }
                }
            }

            Utils_RecordBrowserCommon::new_record('premium_invoice_items',array(
                'invoice_number' => $invoice_id,
                'item_name'=>$it['item_name'],
                'description'=>$item['description'],
                'classification'=>$item['sww'],
                'quantity'=>$item['quantity'],
                'unit'=> __('ea.'),
                'net_price'=>$item['net_price'],
                'tax_rate'=>$item['tax_rate'],
                'gross_price'=>$item['gross_price'],
                'billed_method'=>serialize(array('Premium_Warehouse_Items_Orders_InvoiceCommon','invoice')),
                'billed_method_args'=>serialize(array($item['id']))
            ));
            Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details',$item['id'],array('billed_quantity'=>$item['quantity'],'invoices'=>array_merge(is_array($item['invoices'])?$item['invoices']:array(),array($invoice_id))));
        }
        Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$row['id'],array('billed'=>1));

        $invoices_checkpoint->set('invoices',$invoices);
    }

    $invoices_checkpoint->done();
}


//remove old module
//dla testow zakomentowane
//ModuleManager::uninstall('Premium/Warehouse/Invoice');

//kolumny do skasowania z orders: payment_type, terms
//common data: 'Premium_Items_Orders_Payment_Types', Premium_Items_Orders_Terms