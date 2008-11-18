var ItemDetailsUpdate = Class.create();
ItemDetailsUpdate.prototype = {
	stop_f:null,
	trasn_v:null,
	loads:0,
	initialize:function(trans) {
		this.trans_v=trans;
		Event.observe('item_name','change',this.request.bindAsEventListener(this));
		Event.observe(document,'e:load',this.stop.bindAsEventListener(this));
		this.request();
	},
	stop:function() {
		this.loads++;
		if(this.loads==2) {
			if ($('item_name')!=null) {
				Event.stopObserving('item_name','change',this.request.bindAsEventListener(this));
			}
			Event.stopObserving(document,'e:load',this.stop.bindAsEventListener(this));
		}
	},
	request:function() {
		new Ajax.Request('modules/Premium/Warehouse/Items/Orders/item_details_update.php', {
			method: 'post',
			parameters:{
				rec_id:Object.toJSON($('item_name').value),
				trans:Object.toJSON(this.trans_v),
				cid: Epesi.client_id
			},
			onSuccess:function(t) {
				eval(t.responseText);
			}
		});
	}
};