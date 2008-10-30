var ContractorUpdate = Class.create();
ContractorUpdate.prototype = {
	request_by_company_f:null,
	request_by_contact_f:null,
	stop_f:null,
	loads:0,
	initialize:function() {
		this.request_by_company_f= this.request_by_company.bindAsEventListener(this);
		this.request_by_contact_f= this.request_by_contact.bindAsEventListener(this);
		Event.observe('company','change',this.request_by_company_f);
		Event.observe('contact','change',this.request_by_contact_f);
		this.stop_f= this.stop.bindAsEventListener(this);
		Event.observe(document,'e:load',this.stop_f);
	},
	stop:function() {
		this.loads++;
		if(this.loads==2) {
			if ($('company')!=null)
				Event.stopObserving('company','change',this.request_by_company_f);
			if ($('contact')!=null)
				Event.stopObserving('contact','change',this.request_by_contact_f);
			Event.stopObserving(document,'e:load',this.stop_f);
		}
	},
	request_by_company:function() {
		new Ajax.Request('modules/Premium/Warehouse/Items/Orders/contractor_update.php', {
			method: 'post',
			parameters:{
				parameters:Object.toJSON('company'),
				rec_id:Object.toJSON($('company').value),
				cid: Epesi.client_id
			},
			onSuccess:function(t) {
				eval(t.responseText);
			}
		});
	},
	request_by_contact:function() {
		new Ajax.Request('modules/Premium/Warehouse/Items/Orders/contractor_update.php', {
			method: 'post',
			parameters:{
				parameters:Object.toJSON('contact'),
				rec_id:Object.toJSON($('contact').value),
				cid: Epesi.client_id
			},
			onSuccess:function(t) {
				eval(t.responseText);
			}
		});
	}
};