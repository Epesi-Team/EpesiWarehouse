/*var posnet_applet = document.createElement('applet');
posnet_applet.id = "posnetApplet";
posnet_applet.width = 1;
posnet_applet.height = 1;
var posnet_applet_param = document.createElement('param');
posnet_applet_param.name = "jnlp_href";
posnet_applet_param.value = "modules/Premium/Warehouse/InvoicePL/Posnet/Posnet.jnlp";
posnet_applet.appendChild(posnet_applet_param);
document.body.appendChild(posnet_applet);*/
var posnet_applet = null;
var posnet_container = document.createElement('iframe');
posnet_container.src = "modules/Premium/Warehouse/InvoicePL/Posnet/launch.html";
posnet_container.width = 3;
posnet_container.height = 3;
posnet_container.style.border = 0;
posnet_container.style.overflow = 'hidden';
document.body.appendChild(posnet_container);


function print_receipt(order_id) {
    if(posnet_applet == null || typeof posnet_applet.IsOnline == "undefined") {
        alert("Błąd ładowania appletu do obsługi drukarki");
        return;
    }    
    if(!posnet_applet.IsOnline() && !posnet_applet.OpenPort()) {
        alert("Drukarka fiskalna niedostępna");
        return;
    }
    new Ajax.Request('modules/Premium/Warehouse/InvoicePL/print_receipt.php', {
			method: 'post',
			parameters: {
			    record_id: order_id
			},
			onException: function(t,e) {
				throw(e);
			},
			onFailure: function(t) {
				alert('Failure ('+t.status+')');
				Epesi.text(t.responseText,'error_box','p');
			}
		});
}
