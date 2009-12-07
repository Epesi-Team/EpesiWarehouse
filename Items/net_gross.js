format_currency=function(val,decp){
	all=Math.round(val*100);
	first=parseInt(all/100);
	second=Math.round(all-first*100).toString(10);
	if(isNaN(first)||isNaN(second))return"";
	if(second.length==1)second="0"+second;
	return first+decp+second;
}
update_net=function(decp,net,gross,tax,switch_field){
	if (!$(gross).value) return;
	if(switch_field)$(switch_field).value=0;
	val=$(gross).value.split(decp).join(".");
	$(net).value=format_currency(100*parseFloat(val)/(100+parseFloat(1*tax_values[$(tax).value])),decp);
}
update_gross=function(decp,net,gross,tax,switch_field){
	if (!$(net).value) return;
	if(switch_field)$(switch_field).value=1;
	val=$(net).value.split(decp).join(".");
	$(gross).value=format_currency(parseFloat(val)+parseFloat(val*tax_values[$(tax).value])/100,decp);
}
switch_currencies=function(val,net,gross){
	$("__"+net+"__currency").selectedIndex=val;
	$("__"+gross+"__currency").selectedIndex=val;
}
