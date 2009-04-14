check_item_price_cost_difference = function(decp, msg) {
	if ($('last_item_price').value=="") return true;
	sales_net_price=parseFloat($('net_price').value.split(decp).join("."));
	sales_currency=$('__net_price__currency').value;
	last_purchase_cost = $('last_item_price').value.split("__")[0];
	last_purchase_currency = $('last_item_price').value.split("__")[1];
	if (sales_currency==last_purchase_currency && last_purchase_cost!=0) {
		$('last_item_price').value = "";
		if (last_purchase_cost>sales_net_price) {
			alert(msg);
			return false;
		}
	}
	return true;
};