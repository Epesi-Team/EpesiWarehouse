function Premium_Warehouse_DrupalCommerce_Allegro_konkurencja(el) {
    jQuery.get('modules/Premium/Warehouse/DrupalCommerce/Allegro/konkurencja.php',{name:el.attr('name'),cat:el.attr('cat'),id:el.attr('auction_id')},function(data){
	el.html(data);
    });
}
function Premium_Warehouse_DrupalCommerce_Allegro_konkurencja_field(el2) {
    var n = el2.val();
    var k = el2.parent().find('.allegro_konkurencja');
    if(!n) return k.html();
    k.html('...');
    jQuery.get('modules/Premium/Warehouse/DrupalCommerce/Allegro/konkurencja.php',{name:n,cat:k.attr('cat'),id:k.attr('auction_id')},function(data){
	k.html(data);
    });
}
function Premium_Warehouse_DrupalCommerce_Allegro_koszt(el) {
    var auction = el.attr('auction_id');
    jQuery.get('modules/Premium/Warehouse/DrupalCommerce/Allegro/koszt.php',{
	    id:auction,
	    buy_price:jQuery('#allegro_buy_price_'+auction).val(),
	    minimal_price:jQuery('#allegro_minimal_price_'+auction).val(),
	    initial_price:jQuery('#allegro_initial_price_'+auction).val(),
	    post_service_price:jQuery('#allegro_post_service_price_'+auction).val(),
	    post_service_price_p:jQuery('#allegro_post_service_price_p_'+auction).val(),
	    ups_price:jQuery('#allegro_ups_price_'+auction).val(),
	    ups_price_p:jQuery('#allegro_ups_price_p_'+auction).val(),
	    qty:jQuery('#allegro_qty_'+auction).val(),
	    days:jQuery('#allegro_days_'+auction).val(),
	    name:jQuery('#allegro_name_'+auction).val(),
	},function(data){
	    el.html(data);
	    Premium_Warehouse_DrupalCommerce_Allegro_profit(auction);
	});
    jQuery('#allegro_qty_'+auction+', #allegro_buy_price_'+auction+', #allegro_minimal_price_'+auction+', #allegro_initial_price_'+auction+', #allegro_days_'+auction).change(function() {
	el.html('...');
        jQuery.get('modules/Premium/Warehouse/DrupalCommerce/Allegro/koszt.php',{
	    id:auction,
	    buy_price:jQuery('#allegro_buy_price_'+auction).val(),
	    minimal_price:jQuery('#allegro_minimal_price_'+auction).val(),
	    initial_price:jQuery('#allegro_initial_price_'+auction).val(),
	    post_service_price:jQuery('#allegro_post_service_price_'+auction).val(),
	    post_service_price_p:jQuery('#allegro_post_service_price_p_'+auction).val(),
	    ups_price:jQuery('#allegro_ups_price_'+auction).val(),
	    ups_price_p:jQuery('#allegro_ups_price_p_'+auction).val(),
	    qty:jQuery('#allegro_qty_'+auction).val(),
	    days:jQuery('#allegro_days_'+auction).val(),
	    name:jQuery('#allegro_name_'+auction).val(),
	},function(data){
	    el.html(data);
	    Premium_Warehouse_DrupalCommerce_Allegro_profit(auction);
	});
    });

    jQuery('#allegro_buy_price_'+auction+', #allegro_minimal_price_'+auction+', #allegro_initial_price_'+auction).change(function(){
	Premium_Warehouse_DrupalCommerce_Allegro_profit(auction);
    });
    Premium_Warehouse_DrupalCommerce_Allegro_profit(auction);
}
function Premium_Warehouse_DrupalCommerce_Allegro_profit(auction) {
	var b = jQuery('#allegro_buy_price_'+auction).val();
	var m = jQuery('#allegro_minimal_price_'+auction).val();
	var i = jQuery('#allegro_initial_price_'+auction).val();
	var p = jQuery('#allegro_profit_'+auction);
	var c = parseFloat(jQuery('#allegro_cost_'+auction).html());
	var ac = parseFloat(jQuery('#allegro_auction_cost_'+auction).html());
	var pr = jQuery('#allegro_profit_price_'+auction);
	if(!ac) {
	    p.html('---');
	    pr.html('---');
	    return;
	}
	if(b!="") {
	    pr.html(b);
	    var profit = (parseFloat(b)-c-ac).toFixed(2);
	    p.html(profit+' ('+(100*b/c-100).toFixed(0)+'%), '+(profit/1.23).toFixed(2)+' netto');
	    return;
	}
	if(m!="") {
	    pr.html(m);
	    var profit = (parseFloat(m)-c-ac).toFixed(2);
	    p.html(profit+' ('+(100*m/c-100).toFixed(0)+'%), '+(profit/1.23).toFixed(2)+' netto');
	    return;
	}
	if(i!="") {
	    pr.html(i);
	    var profit = (parseFloat(i)-c-ac).toFixed(2);
	    p.html(profit+' ('+(100*i/c-100).toFixed(0)+'%), '+(profit/1.23).toFixed(2)+' netto');
	    return;
	}
}
function Premium_Warehouse_DrupalCommerce_Allegro_wystaw(status,auction) {
    status.innerHTML='...';
    jQuery.get('modules/Premium/Warehouse/DrupalCommerce/Allegro/wystaw.php',{
	    id:auction,
	    buy_price:jQuery('#allegro_buy_price_'+auction).val(),
	    minimal_price:jQuery('#allegro_minimal_price_'+auction).val(),
	    initial_price:jQuery('#allegro_initial_price_'+auction).val(),
	    qty:jQuery('#allegro_qty_'+auction).val(),
	    post_service_price:jQuery('#allegro_post_service_price_'+auction).val(),
	    post_service_price_p:jQuery('#allegro_post_service_price_p_'+auction).val(),
	    ups_price:jQuery('#allegro_ups_price_'+auction).val(),
	    ups_price_p:jQuery('#allegro_ups_price_p_'+auction).val(),
	    days:jQuery('#allegro_days_'+auction).val(),
	    name:jQuery('#allegro_name_'+auction).val(),
	    add_auction_cost:jQuery('#allegro_add_auction_cost_'+auction).is(':checked')?1:0,
	    auction_cost:jQuery('#allegro_auction_cost_'+auction).val(),
	},function(data){
	    if(typeof status != "undefined") {
		if(data.indexOf("OK")<0) {
		    jQuery('#allegro_error_'+auction).html(data);
		} else status.innerHTML = data;
	    }
	    else alert('Wystawianie aukcji: '+data);
	});
}
function Premium_Warehouse_DrupalCommerce_Allegro_usun(status,auction) {
    if(confirm("Usunąć auckję listy do ponownego wystawienia?")) {
      status.innerHTML='...';
      jQuery.get('modules/Premium/Warehouse/DrupalCommerce/Allegro/usun.php',{
	    id:auction,
	},function(data){
	    if(typeof status != "undefined") {
		if(data.indexOf("OK")<0) {
		    jQuery('#allegro_error_'+auction).html(data);
		} else status.parentNode.style.display="none";
	    }
	    else alert('Usuwanie aukcji z listy: '+data);
	});
    }
}
