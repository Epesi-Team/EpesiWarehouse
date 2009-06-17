wholesale_create_iframe = function (distr, file) {
	var ch = document.createElement('iframe');
	ch.id = 'premium_wholesale_scan_iframe';
	ch.src = 'modules/Premium/Warehouse/Wholesale/scanfile.php?id='+distr+'&file='+file;
	ch.style.display = "none";
	document.body.appendChild(ch);
},

wholesale_leightbox_switch = function (switch_type) {
	if (switch_type==0) {
		first = "block";
		second = "none";
	} else {
		first = "none";
		second = "block";
	}
	if($("wholesale_scan_file_form")) $("wholesale_scan_file_form").style.display=first;
	if($("wholesale_scan_file_progress")) $("wholesale_scan_file_progress").style.display=second;
},

wholesale_leightbox_switch_to_form = function () {
	wholesale_leightbox_switch(0);
},

wholesale_leightbox_switch_to_info = function () {
	wholesale_leightbox_switch(1);
}
