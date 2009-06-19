order_disable_contact_details = function(arg) {
	if($("company"))		$("company").disabled=arg;
	if($("company_name"))	$("company_name").disabled=arg;
	if($("last_name"))		$("last_name").disabled=arg;
	if($("first_name"))		$("first_name").disabled=arg;
	if($("contact"))		$("contact").disabled=arg;
	if($("address_1"))		$("address_1").disabled=arg;
	if($("address_2"))		$("address_2").disabled=arg;
	if($("city"))			$("city").disabled=arg;
	if($("country"))		$("country").disabled=arg;
	if($("zone"))			$("zone").disabled=arg;
	if($("postal_code"))	$("postal_code").disabled=arg;
	if($("tax_id"))			$("tax_id").disabled=arg;
	if($("phone"))			$("phone").disabled=arg;
};