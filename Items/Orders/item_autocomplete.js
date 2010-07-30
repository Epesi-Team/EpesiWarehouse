warehouse_itemAutocompleter = Class.create(Ajax.Autocompleter, {
  last_update_value:null,
  initialize: function(element, update, url, options, trans) {
	options.frequency = 0.6;
    this.baseInitialize(element, update, options);
    this.options.asynchronous  = true;
    this.options.onComplete    = this.onComplete.bind(this);
    this.options.defaultParams = this.options.parameters || null;
    this.url                   = url;
    this.trans				   = trans;
  },

  hide: function() {
    this.stopIndicator();
    if(Element.getStyle(this.update, 'display')!='none') this.options.onHide(this.element, this.update);
    if(this.iefix) Element.hide(this.iefix);
	var e = $('item_name');
	if(!e) return;
	var value = e.value;
	if(value==this.last_update_value) return;
	this.last_update_value=value;
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
			trans:Object.toJSON(this.trans),
			cid: Epesi.client_id
		},
		onSuccess:function(t) {
			eval(t.responseText);
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
		}
	});
   }
});
