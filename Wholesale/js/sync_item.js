var item_already_selected
var create_new_item_display

create_new_item_change = function (val) {
	create_new_item_display = val
	if (val==1) {
		$("create_new_item_section").style.display = "";
		$("existing_item_display_section").style.display="none";
		$("existing_item_change_candidate_section").style.display="none";
		$("existing_item_section").style.display = "none";
		$("existing_item_search_candicates_section").style.display = "none";
	} else {
		item_was_selected(item_already_selected);
		$("create_new_item_section").style.display = "none";
//		$("existing_item_section").style.display = "";
//		$("existing_item_search_candicates_section").style.display = "";
	}
}

add_new_candidate = function() {
	var val = $("__autocomplete_id_add_candidates").value;
	$("__autocomplete_id_add_candidates").value = '';
	var vals = val.split("__");
	if (vals[6]) {
		var el = $('add_candidates_row');
		var button = el.childNodes[1].innerHTML;
		var cnodes = el.parentNode.childNodes;
		var id_to_use = 'candidate_item_'+vals[0];
		for (i=0; i<cnodes.length; i++)
			if (cnodes[i].id==id_to_use) return;
		var new_el = document.createElement("tr");
		el.parentNode.insertBefore(new_el, el);
		new_el.id = id_to_use;
		new_el.setAttribute("data", val);
		new_el.innerHTML = 
			'<td class="Utils_GenericBrowser__td">'+button+"</td>"+
			'<td class="Utils_GenericBrowser__td">'+vals[1]+"</td>"+
			'<td class="Utils_GenericBrowser__td">'+vals[2]+"</td>"+
			'<td class="Utils_GenericBrowser__td">'+vals[3]+"</td>"+
			'<td class="Utils_GenericBrowser__td">'+vals[4]+"</td>"+
			'<td class="Utils_GenericBrowser__td">'+vals[5]+"</td>"+
			'<td class="Utils_GenericBrowser__td">'+vals[6]+"</td>";
	}
}

item_was_selected = function(arg) {
	if (create_new_item_display==1) return;
	item_already_selected = arg;
	if (arg) {
		$("existing_item_display_section").style.display="";
		$("existing_item_change_candidate_section").style.display="";
		$("existing_item_section").style.display = "none";
		$("existing_item_search_candicates_section").style.display = "none";
	} else {
		$("existing_item_display_section").style.display="none";
		$("existing_item_change_candidate_section").style.display="none";
		$("existing_item_section").style.display = "";
		$("existing_item_search_candicates_section").style.display = "";
	}
}

select_candidate_to_use = function(img_tag) {
	item_was_selected(true);
	var tr_tag = img_tag.parentNode.parentNode.parentNode;
	var vals = tr_tag.getAttribute("data").split("__");
	$("selected_existing_item").value = vals[0];
	$("e_item_name").value = vals[1];
	$("_e_item_name__display").innerHTML = vals[1];
	
	$("e_category").value = vals[2];
	$("_e_category__display").innerHTML = vals[2];
	
	$("e_price").value = vals[3];
	$("_e_price__display").innerHTML = vals[3];
	
	$("e_manufacturer").value = vals[4];
	$("_e_manufacturer__display").innerHTML = vals[4];
	
	$("e_mpn").value = vals[5];
	$("_e_mpn__display").innerHTML = vals[5];
	
	$("e_upc").value = vals[6];
	$("_e_upc__display").innerHTML = vals[6];
}

create_or_select_manufacturer = function () {
	if ($("n_enable_create_manufacturer").checked) {
		$("n_manufacturer").style.display="none";
		$("n_create_manufacturer").style.display = "";
	} else {
		$("n_manufacturer").style.display="";
		$("n_create_manufacturer").style.display = "none";
	}
}
