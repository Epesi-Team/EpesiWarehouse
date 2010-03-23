var ItemDetailsUpdate = Class.create();
ItemDetailsUpdate.prototype = {
	stop_f:null,
	trasn_v:null,
	loads:0,
	initialize:function(trans, event_type) {
		this.trans_v=trans;
		alert(event_type);
		Event.observe('item_name', event_type, this.request.bindAsEventListener(this));
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
				trans:Object.toJSON(this.trans_v),
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
};