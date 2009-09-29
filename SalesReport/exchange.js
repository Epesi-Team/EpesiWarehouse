report_edit_exchange = function(currency_id) {
	v=window.prompt($('prompt_header').value,'');
	if(v) {
		$('currency_id').value = currency_id;
		$('exch_rate').value = v;
		eval($('submit_form_js').value);
	};
}