var warehouse_order_mode = 'view';

warehouse_orders_hide_field = function(field, hideit) {
	if ($('_'+field+'__data')) $('_'+field+'__data').parentNode.style.display = (hideit?"none":"");
}

warehouse_orders_hide_fields = function(trans_type, status, shipment_type, payment_type) {
	if (!trans_type && $('transaction_type')) trans_type = $('transaction_type').value;
	if (!status && $('status')) status = $('status').value;
	if (!shipment_type && $('shipment_type')) shipment_type = $('shipment_type').value;
	if (!payment_type && $('payment_type')) payment_type = $('payment_type').value;

	warehouse_orders_hide_field('online_order', (trans_type!=1));
	warehouse_orders_hide_field('related', true);
	warehouse_orders_hide_field('status', (warehouse_order_mode=='add'));
	warehouse_orders_hide_field('company', (warehouse_order_mode=='view' || trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('contact', (warehouse_order_mode=='view' || trans_type==2 || trans_type==4));
	var payments = $('payment') && $('payment').checked?true:false;
	warehouse_orders_hide_field('payment_type', (!payments || trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('payment_no', ((status<2 && trans_type!=3) || payment_type==0 || !payments || trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('terms', ((status<2 && trans_type!=3) || !payments || trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('total_value', ((status<2 && trans_type==0) || !payments || trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('invoice_number', (!payments || trans_type==4));
	warehouse_orders_hide_field('invoice_print_date', (!payments || trans_type==4));
	warehouse_orders_hide_field('tax_value', ((status<2 && trans_type==0) || !payments || trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('receipt', (!payments || trans_type==2 || trans_type==4));

	warehouse_orders_hide_field('target_warehouse', (trans_type!=1 || warehouse_order_mode=='view'));

	warehouse_orders_hide_field('payment', (trans_type!=3));
	warehouse_orders_hide_field('return_date', (trans_type!=3));
	
	warehouse_orders_hide_field('tax_id', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('company_name', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('first_name', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('last_name', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('address_1', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('address_2', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('city', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('country', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('zone', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('postal_code', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('phone', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_company', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_contact', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_tax_id', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_company_name', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_first_name', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_last_name', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_address_1', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_address_2', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_city', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_country', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_zone', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_postal_code', (trans_type==2 || trans_type==4));
	warehouse_orders_hide_field('shipping_phone', (trans_type==2 || trans_type==4));

	warehouse_orders_hide_field('shipment_type', (trans_type==2 || (trans_type==0 && status<2) || (trans_type==1 && status<2 && status>=0)));
	warehouse_orders_hide_field('payment_type', ((trans_type==0 && status<2) || (trans_type==1 && status<2 && status>=0)));
	warehouse_orders_hide_field('shipment_date', (trans_type==2 || shipment_type==0 || (trans_type==0 && status<4) || (trans_type==1 && status<7)));
	warehouse_orders_hide_field('shipment_no', (trans_type==2 || shipment_type==0 || (trans_type==0 && status<20) || (trans_type==1 && status<7)));
	warehouse_orders_hide_field('shipment_employee', (trans_type==2 || shipment_type==0 || (trans_type==0 && status<4) || (trans_type==1 && status<7)));
	warehouse_orders_hide_field('shipment_eta', (trans_type==2 || shipment_type==0 || (trans_type==0 && status<4)));
	warehouse_orders_hide_field('shipment_cost', (trans_type==2 || shipment_type==0 || (trans_type==0 && status<4)));
	warehouse_orders_hide_field('handling_cost', (trans_type==2 || (trans_type==0 && status<4)));
	warehouse_orders_hide_field('expiration_date', (trans_type==2 || (trans_type==0 && status!=1) || (trans_type==1 && status!=1)));
}

var order_details_trans_type = 0;
var order_details_trans_payment = 0;

warehouse_order_details_hide_fields = function() {
	warehouse_orders_hide_field('return_date', (order_details_trans_type!=3));
	warehouse_orders_hide_field('returned', (order_details_trans_type!=3));

	warehouse_orders_hide_field('net_price', (order_details_trans_type==4 || !order_details_trans_payment));
	warehouse_orders_hide_field('net_total', (order_details_trans_type==4 || !order_details_trans_payment));
	warehouse_orders_hide_field('tax_rate', (order_details_trans_type==4 || !order_details_trans_payment));
	warehouse_orders_hide_field('tax_value', (order_details_trans_type==4 || !order_details_trans_payment));
	warehouse_orders_hide_field('gross_price', (order_details_trans_type==4 || !order_details_trans_payment));
	warehouse_orders_hide_field('gross_total', (order_details_trans_type==4 || !order_details_trans_payment));

	warehouse_orders_hide_field('transaction_date', (order_details_trans_type==3));
	warehouse_orders_hide_field('transaction_type', (order_details_trans_type==3));
	warehouse_orders_hide_field('warehouse', (order_details_trans_type==3));
	warehouse_orders_hide_field('debit', (order_details_trans_type==3));
	warehouse_orders_hide_field('credit', (order_details_trans_type==3));
	warehouse_orders_hide_field('net_price', (order_details_trans_type==3));
	warehouse_orders_hide_field('net_total', (order_details_trans_type==3));
	warehouse_orders_hide_field('tax_rate', (order_details_trans_type==3));
	warehouse_orders_hide_field('tax_value', (order_details_trans_type==3));
	warehouse_orders_hide_field('gross_price', (order_details_trans_type==3));
	warehouse_orders_hide_field('gross_total', (order_details_trans_type==3));
	warehouse_orders_hide_field('quantity_on_hand', (order_details_trans_type==3));

	warehouse_orders_hide_field('credit', (order_details_trans_type!=2));
	warehouse_orders_hide_field('debit', (order_details_trans_type!=2));

	warehouse_orders_hide_field('net_price', (order_details_trans_type==2));
	warehouse_orders_hide_field('net_total', (order_details_trans_type==2));
	warehouse_orders_hide_field('tax_rate', (order_details_trans_type==2));
	warehouse_orders_hide_field('tax_value', (order_details_trans_type==2));
	warehouse_orders_hide_field('gross_price', (order_details_trans_type==2));
	warehouse_orders_hide_field('gross_total', (order_details_trans_type==2));
}

