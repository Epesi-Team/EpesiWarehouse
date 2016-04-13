adjust_parameters = function () {
	new Ajax.Request('modules/Premium/Warehouse/DrupalCommerce/adjust_parameters.php', {
		method: 'post',
		parameters:{
			plugin_id:Object.toJSON($('plugin').value),
			cid: Epesi.client_id
		},
		onSuccess:function(t) {
			eval(t.responseText);
		}
	});
};
