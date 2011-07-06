var ContractorUpdate = Class.create();
ContractorUpdate.prototype = {
	request_by_company_f:null,
	request_by_contact_f:null,
	request_by_company_sf:null,
	request_by_contact_sf:null,
	stop_f:null,
	loads:0,
	initialize:function() {
		this.request_by_company_f= this.request_by_company.bindAsEventListener(this);
		this.request_by_contact_f= this.request_by_contact.bindAsEventListener(this);
		this.request_by_company_sf= this.request_by_company.bindAsEventListener(this,true);
		this.request_by_contact_sf= this.request_by_contact.bindAsEventListener(this,true);
		Event.observe('company','change',this.request_by_company_f);
		Event.observe('contact','change',this.request_by_contact_f);
		Event.observe('company','native:change',this.request_by_company_f);
		Event.observe('contact','native:change',this.request_by_contact_f);
		Event.observe('shipping_company','change',this.request_by_company_sf);
		Event.observe('shipping_contact','change',this.request_by_contact_sf);
		Event.observe('shipping_company','native:change',this.request_by_company_sf);
		Event.observe('shipping_contact','native:change',this.request_by_contact_sf);
		this.stop_f= this.stop.bindAsEventListener(this);
		Event.observe(document,'e:load',this.stop_f);
	},
	stop:function() {
		this.loads++;
		if(this.loads==2) {
			if ($('company')!=null)
				Event.stopObserving('company','change',this.request_by_company_f);
			if ($('shipping_company')!=null)
				Event.stopObserving('shipping_company','change',this.request_by_company_sf);
			if ($('contact')!=null)
				Event.stopObserving('contact','change',this.request_by_contact_f);
			if ($('shipping_contact')!=null)
				Event.stopObserving('shipping_contact','change',this.request_by_contact_sf);
			Event.stopObserving(document,'e:load',this.stop_f);
		}
	},
	request_by_company:function(e,shipping) {
		new Ajax.Request('modules/Premium/Warehouse/Items/Orders/contractor_update.php', {
			method: 'post',
			parameters:{
				parameters:Object.toJSON('company'),
				rec_id:Object.toJSON($((shipping?'shipping_':'')+'company').value),
				cid: Epesi.client_id,
				ship: shipping
			},
			onSuccess:function(t) {
				eval(t.responseText);
			}
		});
	},
	request_by_contact:function(e,shipping) {
		new Ajax.Request('modules/Premium/Warehouse/Items/Orders/contractor_update.php', {
			method: 'post',
			parameters:{
				parameters:Object.toJSON('contact'),
				rec_id:Object.toJSON($((shipping?'shipping_':'')+'contact').value),
				cid: Epesi.client_id,
				ship: shipping
			},
			onSuccess:function(t) {
				eval(t.responseText);
			}
		});
	}
};