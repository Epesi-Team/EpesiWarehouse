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
			document.getElementById('more-less').innerHTML = 'More >>>';
			//document.getElementById('more-less').href = '#product';
		}
		else {
			document.getElementById(element).style.height = 'auto';
			document.getElementById('more-less').innerHTML = '<<< Less';
			//document.getElementById('more-less').href = '#';
		}
	}
}

function emptyProdDesc(element) {
	var tmp = document.getElementById(element);
	if(tmp != null) {
		if(document.getElementById(element).innerHTML == '') {
			document.getElementById(element).style.height = 'auto';
			document.getElementById('more-less').innerHTML = '';
		}
		else {
			document.getElementById(element).style.height = '295px';
		}
	}
}