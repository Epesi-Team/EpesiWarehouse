function showLang() {
	if(document.getElementById('langPopup').style.display == 'block') {
		document.getElementById('langPopup').style.display = 'none';
	}
	else {
		document.getElementById('langPopup').style.display = 'block';
	}
}

function more_less(element) {
	if(document.getElementById(element).innerHTML != '') {
		if(document.getElementById(element).style.height == 'auto') {
			document.getElementById(element).style.height = '295px';
			elems = document.getElementById('more-less').getElementsByClassName('more');
			for(var el=0; el<elems.length; el++) {
				elems[el].style.display='inline';
			}
			elems = document.getElementById('more-less').getElementsByClassName('less');
			for(var el=0; el<elems.length; el++) {
				elems[el].style.display='none';
			}
		}
		else {
			document.getElementById(element).style.height = 'auto';
			elems = document.getElementById('more-less').getElementsByClassName('more');
			for(var el=0; el<elems.length; el++) {
				elems[el].style.display='none';
			}
			elems = document.getElementById('more-less').getElementsByClassName('less');
			for(var el=0; el<elems.length; el++) {
				elems[el].style.display='inline';
			}
			//document.getElementById('more-less').href = '#';
		}
	}
}

function emptyProdDesc() {
	var element = 'productDescription';
	var tmp = document.getElementById(element);
	if(tmp != null) {
		if(document.getElementById(element).innerHTML == '' || document.getElementById(element).clientHeight < 295) {
			document.getElementById(element).style.height = 'auto';
			var xx = document.getElementById('more-less');
			if(xx) xx.innerHTML = '';
		}
		else {
			document.getElementById(element).style.height = '295px';
		}
	}
}

var states = {};
function loadStates(l,pre) {
	var elSel = document.getElementById('o'+pre+'State');
	if(typeof elSel == "undefined") return;
	elSel.options.length = 0;
	if(typeof states[l] == "undefined") {
	    elSel.disabled=1;
	    return;
	}
	elSel.disabled=0;
	var i = 0;
	for(k in states[l]) {
	    elSel.options[i++] = new Option(states[l][k],k);
	}
}

var addressesList={};
function changeAddr(val){
    var a=addressesList[val];
    for(p in a){
	document.forms["orderForm"].elements[p].value=a[p];
    }
    loadStates(a['sCountry'],'');
    document.forms["orderForm"].elements['sState'].value=a['sState'];
}