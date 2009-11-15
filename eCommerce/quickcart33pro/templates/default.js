function showLang() {
	if(document.getElementById('lang').style.display == 'block') {
		document.getElementById('lang').style.display = 'none';
	}
	else {
		document.getElementById('lang').style.display = 'block';
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

function emptyProdDesc(element) {
	var tmp = document.getElementById(element);
	if(tmp != null) {
		if(document.getElementById(element).innerHTML == '' || document.getElementById(element).clientHeight < 295) {
			document.getElementById(element).style.height = 'auto';
			document.getElementById('more-less').innerHTML = '';
		}
		else {
			document.getElementById(element).style.height = '295px';
		}
	}
}