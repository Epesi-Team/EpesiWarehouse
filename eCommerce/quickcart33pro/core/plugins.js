/*
* Plugins JS scripts
/*/

function windowNew( sAdres, iWidth, iHeight, sTitle, iReturn ){
  if ( !sTitle )
    sTitle = '';
  if( !iReturn )
    iReturn = false;

	if( !iWidth )
		var iWidth = 750;
	if( !iHeight )
		var iHeight = 530;

	if( +iWidth > 750 )
		iWidth = 750;
	else
		iWidth = +iWidth + 40;

	if( +iHeight > 530 )
		iHeight = 530
	else
		iHeight = +iHeight + 40;

	var iX = ( screen.availWidth - iWidth ) / 2;
	var iY = ( screen.availHeight - iHeight ) / 2;

  var refOpen = window.open( sAdres, sTitle, "height="+iHeight+",width="+iWidth+",top="+iY+",left="+iX+",resizable=yes,scrollbars=yes,status=0;" );
  
  if( iReturn == true )
  	return refOpen
}