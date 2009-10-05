/*
* Common JS scripts
*/

function gEBI( objId ){
  return document.getElementById( objId );
}

function createCookie( sName, sValue, iDays ){
  sValue = escape( sValue );
  if( iDays ){
    var oDate = new Date();
    oDate.setTime( oDate.getTime() + ( iDays*24*60*60*1000 ) );
    var sExpires = "; expires="+oDate.toGMTString();
  }
  else
    var sExpires = "";
  document.cookie = sName+"="+sValue+sExpires+"; path=/";
}

function throwCookie( sName ){
  var sNameEQ = sName + "=";
  var aCookies = document.cookie.split( ';' );
  for( var i=0; i < aCookies.length; i++ ){
    var c = aCookies[i];
    while( c.charAt(0) == ' ' )
      c = c.substring( 1, c.length );
    if( c.indexOf( sNameEQ ) == 0 )
      return c.substring( sNameEQ.length, c.length );
  }
  return null;
}

function delCookie( sName ){
  createCookie( sName, "", -1 );
}

function isset( sVar ){
  return( typeof( window[sVar] ) != 'undefined' );
}

function targetBlank( ){
  var oLinks = document.getElementsByTagName( 'a' );
  for( var i=0; i < oLinks.length; i++ ) {
    if( oLinks[i].className == 'new-window' || oLinks[i].className == 'new-windowMark' ) {
      oLinks[i].onclick = function( ){
        window.open( this.href );
        return false;
      };
    }
  }
} // end function

_bUa=navigator.userAgent.toLowerCase();
_bOp=(_bUa.indexOf("opera")!=-1?true:false);
_bIe=(_bUa.indexOf("msie")!=-1&&!_bOp?true:false);
_bIe4=(_bIe&&(_bUa.indexOf("msie 2.")!=-1||_bUa.indexOf("msie 3.")!=-1||_bUa.indexOf("msie 4.")!=-1)&&!_bOp?true:false)
isIe=function(){return _bIe;}
isOldIe=function(){return _bIe4;}
var olArray=[];

function AddOnload( f ){
  if( isIe && isOldIe ){
    window.onload = ReadOnload;
    olArray[olArray.length] = f;
  }
  else if( window.onload ){
    if( window.onload != ReadOnload ){
      olArray[0] = window.onload;
      window.onload = ReadOnload;
    }
    olArray[olArray.length] = f;
  }
  else
    window.onload=f;
}
function ReadOnload(){
  for( var i=0; i < olArray.length; i++ ){
    olArray[i]();
  }
}


/*
* Orders
*/

function fix( f ){
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

  var poprawnyFloat = /^-?[0-9]{1,}[.]{1}[0-9]{1,}$/i;
	if( w.search( poprawnyFloat ) == -1 )
		w = '0.00';
	return w;

}

function changePriceFormat( fPrice ){
  // config start
  var sDecimalSeparator = '.';
  var sThousandSeparator = '';
  // config end

  fPrice = fix( fPrice );
  var aPrice = fPrice.split( '.' );
  var iPriceFull = aPrice[0];
  var aPriceFull = new Array( );

  var j = 0;
  for( var i = iPriceFull.length - 1; i >= 0; i-- ){
    if( j > 0 && j%3 == 0 )
      aPriceFull[j] = iPriceFull.substr( i, 1 )+''+sThousandSeparator;
    else
      aPriceFull[j] = iPriceFull.substr( i, 1 );
    j++;
  } // end for

  aPriceFull.reverse( );
  sPriceFull = aPriceFull.join( '' );
  sPrice = sPriceFull+''+sDecimalSeparator+''+aPrice[1];
  return sPrice;
} // end function changePriceFormat

function generatePrice( fPrice1, fPrice2 ){
  fPrice1 = fix( fPrice1 );
  fPrice2 = fPrice2.toString();
  if( fPrice2.match( /\%/ ) ){
    fPrice2 = fix( fPrice2.replace( /\%/, '' ) );
    if( fPrice2 < 0 ){
      return changePriceFormat( fPrice1 - ( fPrice1 * ( -fPrice2 / 100 ) ) );
    }
    else
      return changePriceFormat( +fPrice1 + ( fPrice1 * ( fPrice2 / 100 ) ) ); 
  }
  else{
    fPrice2 = fix( fPrice2 ) * 1;
    return changePriceFormat( +fPrice1 + fPrice2 ); 
  }
} // end function generatePrice


function countCarrierPrice( oObj ){

  if( oObj.value != '' )
    aCourier = oObj.value.split( ";" );
  else
    aCourier = Array( 0, 0, '0.00' );

  fDeliveryCost = fix( aCourier[2] ) * 1;

  gEBI( 'carrierCost' ).innerHTML = changePriceFormat( fDeliveryCost );
  gEBI( 'orderSummary' ).innerHTML = changePriceFormat( +fOrderSummary + fDeliveryCost )

} // end function 


var aUserDataNames = new Array( 'sFirstName', 'sLastName', 'sCompanyName', 'sStreet', 'sZipCode', 'sCity', 'sPhone', 'sEmail', 'sNip' );

function saveUserData( sName, sValue ){
  createCookie( sName, sValue, 2 );
}

function checkSavedUserData( ){
  var iCount = aUserDataNames.length;
  var sCookie = null;
  var oForm = gEBI( "orderForm" );
  for( var i = 0; i < iCount; i++ ){
    sCookie = throwCookie( aUserDataNames[i] );
    if( sCookie && sCookie != '' ){
      if( gEBI( aUserDataNames[i] ) )
        gEBI( aUserDataNames[i] ).value = unescape( sCookie );
      else if( oForm[aUserDataNames[i]] )
        oForm[aUserDataNames[i]].value = unescape( sCookie );
    }
  } // end for
} // end function checkSavedUserData

function delSavedUserData( ){
  var iCount = aUserDataNames.length;
  var sCookie = null;
  for( var i = 0; i < iCount; i++ ){
    delCookie( aUserDataNames[i] );
  } // end for
} // end function delSavedUserData

function txtSize( iSize ){
  var aSizes = Array( '0.917em', '', '1.333em' );
  var aChange = Array( 'pageDescription', 'h5', 'productDescription', 'features' );
  var aSizesDefault = Array( '1.083em', '1em', '1.08em' );
  for( var i = 0; i < aChange.length; i++ ){
    if( !aSizes[iSize] )
      iSize = 1;
    if( gEBI( aChange[i] ) ){
      gEBI( aChange[i] ).style.fontSize = ( iSize != 1 ) ? aSizes[iSize] : aSizesDefault[i];
    }
    else if( gEBI( 'content' ).getElementsByTagName( aChange[i] ) ){
      aH5 = gEBI( 'content' ).getElementsByTagName( aChange[i] );
      for( var j = 0; j < aH5.length; j++ ){
        aH5[j].style.fontSize =  ( iSize != 1 ) ? aSizes[iSize] : aSizesDefault[i];
      }
    }
  }
}
