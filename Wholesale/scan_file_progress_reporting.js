wholesale_processing_message = function (msg, det_disp, class) {
	if($("wholesale_scan_file_form"))$("wholesale_scan_file_form").style.display="none";
	if($("wholesale_scan_file_progress"))$("wholesale_scan_file_progress").style.display="block";
	if($("wholesale_scan_status_details"))$("wholesale_scan_status_details").style.display=det_disp;
	if($("wholesale_scan_status_message")){
		$("wholesale_scan_status_message").innerHTML=msg;
		$("wholesale_scan_status_message").setAttribute("class",class);
	}
},

update_wholesale_scan_status = function(total, scanned, available, item_exist, link_exist, new_items_added, new_categories_added) {
	$("wholesale_scan_status_scanned").innerHTML=scanned;
	$("wholesale_scan_status_total").innerHTML=total;
	$("wholesale_scan_status_available").innerHTML=available;
	$("wholesale_scan_status_item_exist").innerHTML=item_exist;
	$("wholesale_scan_status_link_exist").innerHTML=link_exist;
	$("wholesale_scan_status_new_items_added").innerHTML=new_items_added;
	$("wholesale_scan_status_new_categories_added").innerHTML=new_categories_added;
}
