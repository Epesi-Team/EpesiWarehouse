item_type_changed = function(type) {
	if (type==1) {
		$("serials_section").style.display="";
	} else {
		$("serials_section").style.display="none";
	}
},

item_source_changed = function (checked) {
	if (checked == 1) {
		$('brand_new_section').style.display = 		"";
		$('brand_new_long_section').style.display = "";
		$('existing_item_section').style.display = 	"none";
	} else {
		$('brand_new_section').style.display = 		"none";
		$('brand_new_long_section').style.display = "none";
		$('existing_item_section').style.display = 	"";
	}
}
