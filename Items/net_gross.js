format_currency=function(val,decp){
	all=Math.round(val*100);
	first=parseInt(all/100);
	second=Math.round(all-first*100).toString(10);
	if(isNaN(first)||isNaN(second))return"";
	if(second.length==1)second="0"+second;
	return first+decp+second;
}
update_net=function(decp,net,gross,tax,switch_field){
  var gross_field = jq('#'+gross);
  var val = '';
  var net_val = '';
  var tax = parseFloat(tax_values[jq('#'+tax).val()]);
  if (gross_field.length && !isNaN(tax)) {
    val = gross_field.val();
    if(val!='') {
      if(switch_field)jq('#'+switch_field).val(0);
      val=parseFloat(val.split(decp).join("."));
      net_val = 100*val/(100+tax);
      jq('#'+net).val(format_currency(net_val,decp));
    } else {
      jq('#'+net).val('');
    }
  }
  update_total(net_val,val);
}
update_gross=function(decp,net,gross,tax,switch_field){
  var net_field = jq('#'+net);
  var val = '';
  var gross_val = '';
  var tax = parseFloat(tax_values[jq('#'+tax).val()]);
  if (net_field.length && !isNaN(tax)) {
    var val = net_field.val();
    if(val!='') {
      if(switch_field)jq('#'+switch_field).val(1);
      val=parseFloat(val.split(decp).join("."));
      gross_val=val+val*tax/100;
      jq('#'+gross).val(format_currency(gross_val,decp));
    } else {
      jq('#'+gross).val('');
    }
  }
  update_total(val,gross_val,decp);
}
update_total=function(net,gross,decp) {
    var quantity_field = jq('#quantity');
    if(!quantity_field.length) return;
	var quantity = parseFloat(quantity_field.val());
	var net_total = jq('#premium_warehouse_items_orders_details__net_total___');
	if(net_total.length) {
	  if(net!='' && quantity!='') {
	    net_total.html(format_currency(quantity*net,decp));
	  } else {
	    net_total.html('---');
	  }
	}
	var gross_total = jq('#premium_warehouse_items_orders_details__gross_total___');
	if(gross_total.length) {
	  if(gross!='' && quantity!='') {
	    gross_total.html(format_currency(quantity*gross,decp));
	  } else {
	    gross_total.html('---');
	  }
	}
	var tax = jq('#premium_warehouse_items_orders_details__tax_value___');
	if(tax.length) {
	  if(gross!='' && net!='' && quantity!='') {
	    tax.html(format_currency(quantity*(gross-net),decp));
	  } else {
	    tax.html('---');
	  }
	}
}
update_unit=function(decp,unit,net,discount){
  var net_field = jq('#'+net);
  if (!net_field.length) return;

  var val = net_field.val();
  if(val=='') {
    jq('#'+unit).val('');
    return;
  }
  val=parseFloat(val.split(decp).join("."));

  var discount = jq('#'+discount);
  if(discount.length) {
    discount = discount.val();
    if(!discount) discount = 0;
    else discount = parseFloat(discount);
  } else discount = 0;

  jq('#'+unit).val(format_currency(100*val/(100+discount),decp));
}
update_net_discount=function(decp,unit,net,gross,discount){
  var unit_field = jq('#'+unit);
  if (!unit_field.length) return;

  var val=unit_field.val();
  if(val=='') {
    jq('#'+gross).val('');
    jq('#'+net).val('');
    return;
  }
  val = parseFloat(val.split(decp).join("."));

  var discount = jq('#'+discount);
  if(discount.length) {
    discount = discount.val();
    if(!discount) discount = 0;
    else discount = parseFloat(discount);
  } else discount = 0;

  jq('#'+net).val(format_currency(val+val*discount/100,decp));
}
switch_currencies=function(val,net,gross,unit){
	$("__"+net+"__currency").selectedIndex=val;
	$("__"+gross+"__currency").selectedIndex=val;
    if(unit) $("__"+unit+"__currency").selectedIndex=val;
}
