update_net=function(net,gross,tax,switch_field){
  var currency = Utils_CurrencyField.currencies[jq('#__'+gross+'__currency').val()];
  var gross_field = jq('#'+gross);
  var val = '';
  var net_val = '';
  var tax = parseFloat(tax_values[jq('#'+tax).val()]);
  if (gross_field.length && !isNaN(tax)) {
    val = gross_field.val();
    if(val!='') {
      if(switch_field)jq('#'+switch_field).val(0);
      val=parseFloat(val.split(currency['decp']).join("."));
      net_val = 100*val/(100+tax);
      jq('#'+net).val(Utils_CurrencyField.format_amount(net_val,jq('#__'+net+'__currency').val()));
    } else {
      jq('#'+net).val('');
    }
  }
  update_total(net_val,val);
}
update_gross=function(net,gross,tax,switch_field){
  var currency = Utils_CurrencyField.currencies[jq('#__'+net+'__currency').val()];
  var net_field = jq('#'+net);
  var val = '';
  var gross_val = '';
  var tax = parseFloat(tax_values[jq('#'+tax).val()]);
  if (net_field.length && !isNaN(tax)) {
    var val = net_field.val();
    if(val!='') {
      if(switch_field)jq('#'+switch_field).val(1);
      val=parseFloat(val.split(currency['decp']).join("."));
      gross_val=val+val*tax/100;
      jq('#'+gross).val(Utils_CurrencyField.format_amount(gross_val,jq('#__'+gross+'__currency').val()));
    } else {
      jq('#'+gross).val('');
    }
  }
  update_total(val,gross_val);
}
update_total=function(net,gross) {
    var quantity_field = jq('#quantity');
    if(!quantity_field.length) return;
	var quantity = parseFloat(quantity_field.val());
	var net_total = jq('#premium_warehouse_items_orders_details__net_total___');
	if(net_total.length) {
	  if(net!='' && quantity!='') {
	    net_total.html(Utils_CurrencyField.format_amount(quantity*net,jq('#__'+net+'__currency').val()));
	  } else {
	    net_total.html('---');
	  }
	}
	var gross_total = jq('#premium_warehouse_items_orders_details__gross_total___');
	if(gross_total.length) {
	  if(gross!='' && quantity!='') {
	    gross_total.html(Utils_CurrencyField.format_amount(quantity*gross,jq('#__'+gross+'__currency').val()));
	  } else {
	    gross_total.html('---');
	  }
	}
	var tax = jq('#premium_warehouse_items_orders_details__tax_value___');
	if(tax.length) {
	  if(gross!='' && net!='' && quantity!='') {
	    tax.html(Utils_CurrencyField.format_amount(quantity*(gross-net),jq('#__'+net+'__currency').val()));
	  } else {
	    tax.html('---');
	  }
	}
}
update_unit=function(unit,net,discount){
  var net_field = jq('#'+net);
  if (!net_field.length) return;
  var currency = Utils_CurrencyField.currencies[jq('#__'+net+'__currency').val()];

  var val = net_field.val();
  if(val=='') {
    jq('#'+unit).val('');
    return;
  }
  val=parseFloat(val.split(currency['decp']).join("."));

  var discount = jq('#'+discount);
  if(discount.length) {
    discount = discount.val();
    if(!discount) discount = 0;
    else discount = parseFloat(discount);
  } else discount = 0;

  jq('#'+unit).val(Utils_CurrencyField.format_amount(100*val/(100+discount),jq('#__'+net+'__currency').val()));
}
update_net_discount=function(unit,net,gross,discount){
  var unit_field = jq('#'+unit);
  if (!unit_field.length) return;
  var currency = Utils_CurrencyField.currencies[jq('#__'+net+'__currency').val()];

  var val=unit_field.val();
  if(val=='') {
    jq('#'+gross).val('');
    jq('#'+net).val('');
    return;
  }
  val = parseFloat(val.split(currency['decp']).join("."));

  var discount = jq('#'+discount);
  if(discount.length) {
    discount = discount.val();
    if(!discount) discount = 0;
    else discount = parseFloat(discount);
  } else discount = 0;

  jq('#'+net).val(Utils_CurrencyField.format_amount(val+val*discount/100,jq('#__'+net+'__currency').val()));
}
switch_currencies=function(val,net,gross,unit){
    var el;
    el = $("__"+net+"__currency");
    if (el) el.selectedIndex = val;
	el = $("__"+gross+"__currency");
    if (el) el.selectedIndex = val;
    if(unit) {
        el = $("__"+unit+"__currency");
        if (el) el.selectedIndex = val;
    }
}
