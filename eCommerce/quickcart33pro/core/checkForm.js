/**
* checkForm
* @version  0.1.4
*/

// default input border color
if( !cfBorderColor )
  var cfBorderColor   = '#000000';
if( !cfWarningColor )
  var cfWarningColor  = '#ff0000';

var sAllWarnings = '';
var oFirstWrong;
var bIsWarnings = false;
var bAllGood 		= true;

// regular expresions
var	reS = /\s/gi;
var reEmail = /^[a-z0-9_.-]+([_\\.-][a-z0-9]+)*@([a-z0-9_\.-]+([\.][a-z]{2,4}))+$/i;
var reUrl = /^[a-z\d.\\\/\:]{1,}\.[a-z]{2,4}(\/.*)*$/i;
var	reFloat = /^-?[0-9]{1,}[.]{1}[0-9]{1,}$/i;
var	reInt = /^-?[0-9]{1,}$/i;
var	reDot = /\,/gi;


function fieldOperations( oObj, bCheck, sInfo ){

	if( bCheck === true ) {
    if( oObj.type != 'hidden' && oObj.type != 'radio' )
      oObj.style.borderColor = cfBorderColor;
	}
	else {
    if( sInfo )
  		sAllWarnings += sInfo +'\n';
		if( oObj.type != 'hidden' && oObj.type != 'radio' ){
  		oObj.style.borderColor = cfWarningColor;
      if( bIsWarnings == false )
        oFirstWrong = oObj;
		}
		bIsWarnings = true;
		return false;
	}

return true;
} // end function fieldOperations


function checkText( oObj, sInfo ) {

	checkT = oObj.value.replace( reS, "" );

  var bCheck = true;
	if( checkT.length <= 1 )
    bCheck = false;

  return fieldOperations( oObj, bCheck, sInfo );
} // end function checkText


function checkEmail( oObj ) {

	var sEmail = oObj.value.replace( reS, "" );

  var bCheck = true;
	if ( sEmail.search( reEmail ) == -1 )
    bCheck = false;

  return fieldOperations( oObj, bCheck, cfLangMail );
} // end function checkEmail


function checkWww( oObj ) {

	var sWww = oObj.value.replace( reS, "" );

  var bCheck = true;
	if( sWww.search( reUrl ) == -1 || sWww  == 'http://' )
    bCheck = false;

  return fieldOperations( oObj, bCheck, cfLangUrl );
} // end function checkWww


function checkFloat( oObj, sInfo ) {

  var bCheck = true;
	if( oObj.value.search( reFloat ) == -1 && oObj.value.search( reInt ) == -1 ){
    if( !sInfo )
      var sInfo = cfWrongValue;
    bCheck = false;
  }

  return fieldOperations( oObj, bCheck, sInfo );
} // end function checkFloat


function checkInt( oObj, sInfo ) {

  var bCheck = true;
	if( oObj.value.search( reInt ) == -1 ) {
    if( !sInfo )
      var sInfo = cfWrongValue;
    bCheck = false;
  }

  return fieldOperations( oObj, bCheck, sInfo );
} // end function checkInt


function checkFloatValue( oObj, iMinFloat, sInfo ) {

  var bCheck = true;
	if( +oObj.value <= +iMinFloat ) {
    if( !sInfo )
      var sInfo = cfToSmallValue;
    bCheck = false;
  }

  return fieldOperations( oObj, bCheck, sInfo );
} // end function checkFloatValue

function checkIntValue( oObj, minInt, sign, sInfo ) {
  if( !minInt )
    var minInt = 0;
  if( !sign )
    var sign = '==';
  if( !sInfo )
    var sInfo = cfWrongValue;

	eval ( 'var bCheck = ( '+ +oObj.value +' '+ sign +' '+ +minInt +' );' );

  return fieldOperations( oObj, bCheck, sInfo );
} // end function checkIntValue


function checkTxt( oObj, iMin, sInfo ) {
	if( !iMin )
		var iMin = 6;

	var check = oObj.value.replace( reS, "" );

  var bCheck = true;
	if( check.length < iMin ){
    bCheck = false;
    if( !sInfo )
      var sInfo = cfTxtToShort;
  }
  return fieldOperations( oObj, bCheck, sInfo );
} // end function checkTxt

function checkRadio( oObj, sInfo ) {

 var bCheck = false;
 if( oObj.length ){
   for( var i = 0; i < oObj.length; i++ ){
     if( oObj[i].type == 'radio' ){
       if( oObj[i].checked )
         bCheck = true;
     }
   } // end for
   return fieldOperations( oObj[0], bCheck, sInfo );
 }
 else{
   if( oObj.checked )
     bCheck = true;
   return fieldOperations( oObj, bCheck, sInfo );
 }
} // end function checkRadio

function checkExt( oObj, sExtensions, sInfo ){

  var aFileExt    = oObj.value.split( "." );
  var sFileExt    = aFileExt[aFileExt.length - 1];

  var aGoodExt    = sExtensions.split( "|" );
  var iGoodCount  = aGoodExt.length;

  var bCheckExt = false;
  for( var i = 0; i < iGoodCount; i++ ){
   if( sFileExt == aGoodExt[i] ){
     bCheckExt = true;
     break;
   }
  } // end for

  if( !sInfo )
    var sInfo = cfWrongExt;

  return fieldOperations( oObj, bCheckExt, sInfo );
} // end function checkExt


function checkOneCheckbox( oObj, sInfo ) {
  return fieldOperations( oObj, oObj.checked, sInfo );
} // end function checkOneCheckbox

function cfDot( oObj ){
	return oObj.value.replace(reDot, "\.");
}  // end function cfDot

function cfFix( f ){
	f	= f.toString( );
	var re	= /\,/gi;
	f	= f.replace( re, "\." );

	f = Math.round( f * 100 );
	f = f.toString( );
	var sMinus = f.slice( 0, 1 );
	if( sMinus == '-' ){
	 f = f.slice( 1, f.length )
	}
	else
	 sMinus = '';
	if( f.length < 3 ) {
		while( f.length < 3 )
			f = '0' + f;
	}

	var w = sMinus + f.slice( 0, f.length-2 ) + "." + f.slice( f.length-2, f.length );

	if( w.search( reFloat ) == -1 )
		w = '0.00';
	return w;
} // end function cfFix

var sAllWarnings 	= '';
var bIsWarnings 	= false;
var bAllGood			= true;
var oFirstWrong 	= '';

function checkForm( form ) {

  sAllWarnings 	= '';
  bIsWarnings 	= false;
  bAllGood			= true;
  oFirstWrong 	= '';

  aInputs = form.getElementsByTagName( 'input' );
  checkFormElements( aInputs, null, form )

  aInputs = form.getElementsByTagName( 'textarea' );
  checkFormElements( aInputs, true )

  aInputs = form.getElementsByTagName( 'select' );
  checkFormElements( aInputs, true )

  if( bIsWarnings == true ) {
		sAllWarnings = cfLangNoWord + '\n' + sAllWarnings;
    alert ( sAllWarnings );
    if( oFirstWrong )
      oFirstWrong.focus();
    return false;
	}
return true;
} // end function checkForm

function checkFormElements( aInputs, bTitle, oForm ){
  var oO; 
  var sT; // typ
  var aParams;
  var aRadio = Array( );

  for( var i = 0; i < aInputs.length; i++ ){
    oO = aInputs[i];
    if( oO.alt || ( oO.title && bTitle && bTitle == true ) ){
      if( bTitle && bTitle == true )
        aParams = oO.title.split( ';' );
      else
        aParams = oO.alt.split( ';' );
      sT = aParams[0];
      if( sT == 'simple' ){
        bAllGood = checkText( oO, aParams[1] );
      }
      else if( ( sT == 'email' ) && ( ( aParams[1] == 'if' && oO.value ) || !aParams[1] ) ){
        bAllGood = checkEmail( oO );
      }
      else if( ( sT == 'www' ) && ( ( aParams[1] == 'if' && oO.value ) || !aParams[1] ) ){
        bAllGood = checkWww( oO );
      }
      else if( ( sT == 'float' ) && ( ( aParams[2] == 'if' && oO.value ) || !aParams[2] ) ){
        oO.value = cfDot( oO );
        bAllGood = checkFloat( oO );
        if( bAllGood ){
          oO.value = cfFix( oO.value );
          if(	aParams[1] != '' )
            bAllGood = checkFloatValue( oO, aParams[1] );
        }
      }
      else if( ( sT == 'txt' ) && ( ( aParams[3] == 'if' && oO.value ) || !aParams[3] ) ){
        bAllGood = checkTxt( oO, aParams[1], aParams[2] );
      }
      else if( ( sT == 'extension' ) && ( ( aParams[3] == 'if' && ( oO.value ) ) || !aParams[3] ) ){
        bAllGood = checkExt( oO, aParams[1], aParams[2] );
      }
      else if( ( sT == 'int' ) && ( ( aParams[4] == 'if' && oO.value ) || !aParams[4] ) ){
        bAllGood = checkInt( oO, aParams[2] );
        if( aParams[1] && bAllGood ) {
          if( aParams[3] ) {
            bAllGood = checkIntValue( oO, aParams[1], aParams[3], aParams[2] );
          }
          else
            bAllGood = checkFloatValue( oO, aParams[1], aParams[2] );
        }
      }
      else if( ( sT == 'box' ) && ( ( aParams[2] == 'if' && ( oO.value ) ) || !aParams[2] ) ){
        bAllGood = checkOneCheckbox( oO, aParams[1] );
      } 
      else if( sT == 'radio' ){
        var bRadioChecked = false;
        for( var iR = 0; iR < aRadio.length; iR++ ){
          if( aRadio[iR] == oO.name )
            bRadioChecked = true;
        }
        if( bRadioChecked == false ){
          bAllGood = checkRadio( oForm[oO.name], aParams[1] )
          aRadio[aRadio.length] = oO.name;
        }
      } 
    }
  }
} // end function checkFormElements