var last_update_value = null;

warehouse_update_item_details = function(trans) {
	var e = $('item_name');
	if(!e) return;
	var value = e.value;
	if(value==last_update_value) return;
	last_update_value=value;
	e = $('description');
	if(e) e.disabled=true;
	e = $('sww');
	if(e) e.disabled=true;
	e = $('quantity');
	if(e) e.disabled=true;
	e = $('gross_price');
	if(e) e.disabled=true;
	e = $('__gross_price__currency');
	if(e) e.disabled=true;
	e = $('net_price');
	if(e) e.disabled=true;
	e = $('__net_price__currency');
	if(e) e.disabled=true;
	e = $('tax_rate');
	if(e) e.disabled=true;
	new Ajax.Request('modules/Premium/Warehouse/Items/Orders/item_details_update.php', {
		method: 'post',
		parameters:{
			rec_id:Object.toJSON(value),
			trans:Object.toJSON(trans),
			cid: Epesi.client_id
		},
		onSuccess:function(t) {
			var e = $('item_name');
			if(e) e.disabled=false;
			e = $('description');
			if(e) e.disabled=false;
			e = $('sww');
			if(e) e.disabled=false;
			e = $('quantity');
			if(e) e.disabled=false;
			e = $('gross_price');
			if(e) e.disabled=false;
			e = $('__gross_price__currency');
			if(e) e.disabled=false;
			e = $('net_price');
			if(e) e.disabled=false;
			e = $('__net_price__currency');
			if(e) e.disabled=false;
			e = $('tax_rate');
			if(e) e.disabled=false;
			eval(t.responseText);
		}
	});
}

