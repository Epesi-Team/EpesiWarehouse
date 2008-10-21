var ItemDetailsUpdate = Class.create();
ItemDetailsUpdate.prototype = {
	request_f:null,
	stop_f:null,
	loads:0,
	initialize:function() {
		this.request_f= this.request.bindAsEventListener(this);
		Event.observe('item_sku','change',this.request_f);
		this.stop_f= this.stop.bindAsEventListener(this);
		Event.observe(document,'e:load',this.stop_f);
	},
	stop:function() {
		this.loads++;
		if(this.loads==2) {
			if ($('item_sku')!=null)
				Event.stopObserving('item_sku','change',this.request_f);
			Event.stopObserving(document,'e:load',this.stop_f);
		}
	},
	request:function() {
		new Ajax.Request('modules/Premium/Warehouse/Items/Orders/item_details_update.php', {
			method: 'post',
			parameters:{
				rec_id:Object.toJSON($('item_sku').value),
				cid: Epesi.client_id
			},
			onSuccess:function(t) {
				var resp = t.responseText.evalJSON();
				eval(resp);
			}
		});
	}
};