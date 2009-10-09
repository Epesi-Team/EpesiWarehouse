edit_exchange_rate = function(order_id, currency) {
	v=window.prompt($('prompt_header').value,'');
	if(v) {
		$('exchange_rate').value = v;
		$('order_id').value = order_id;
		$('currency').value = currency;
		eval($('submit_form_js').value);
	};
}