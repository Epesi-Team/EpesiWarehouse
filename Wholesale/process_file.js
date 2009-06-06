wholesale_create_iframe = function (distr, file) {
	var ch = document.createElement('iframe');
	ch.id = 'premium_wholesale_scan_iframe';
	ch.src = 'modules/Premium/Warehouse/Wholesale/scanfile.php?id='+distr+'&file='+file;
	ch.style.display = "none";
	document.body.appendChild(ch);
}