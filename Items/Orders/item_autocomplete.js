warehouse_itemAutocompleter = Class.create(Ajax.Autocompleter, {
  initialize: function(element, update, url, options, trans) {
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
	$('item_name').disabled=true;
	$('description').disabled=true;
	$('sww').disabled=true;
	$('quantity').disabled=true;
	$('gross_price').disabled=true;
	$('__gross_price__currency').disabled=true;
	$('net_price').disabled=true;
	$('__net_price__currency').disabled=true;
	$('tax_rate').disabled=true;
	new Ajax.Request('modules/Premium/Warehouse/Items/Orders/item_details_update.php', {
		method: 'post',
		parameters:{
			rec_id:Object.toJSON($('item_name').value),
			trans:Object.toJSON(this.trans),
			cid: Epesi.client_id
		},
		onSuccess:function(t) {
			eval(t.responseText);
			$('item_name').disabled=false;
			$('description').disabled=false;
			$('sww').disabled=false;
			$('quantity').disabled=false;
			$('gross_price').disabled=false;
			$('__gross_price__currency').disabled=false;
			$('net_price').disabled=false;
			$('__net_price__currency').disabled=false;
			$('tax_rate').disabled=false;
		}
	});
   }
});
