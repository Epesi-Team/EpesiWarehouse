warehouse_items_hide_fields = function(item_type) {
	if (typeof(item_type) == 'undefined') {
		if ($('item_type'))
			item_type = $('item_type').value;
		else
			return;
	}
	var countable = (item_type!=2 && item_type!=3);
	$('_reorder_point__data').parentNode.style.display=countable?"":"none";
	$('_quantity_on_hand__data').parentNode.style.display=countable?"":"none";
	$('_available_qty__data').parentNode.style.display=countable?"":"none";
	$('_reserved_qty__data').parentNode.style.display=countable?"":"none";
	$('_upc__data').parentNode.style.display=countable?"":"none";
	$('_manufacturer_part_number__data').parentNode.style.display=countable?"":"none";
	$('_quantity_en_route__data').parentNode.style.display=countable?"":"none";
	$('_weight__data').parentNode.style.display=countable?"":"none";
	$('_volume__data').parentNode.style.display=countable?"":"none";
	$('_quantity_sold__data').parentNode.style.display=countable?"none":"";
}
