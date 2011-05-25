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
