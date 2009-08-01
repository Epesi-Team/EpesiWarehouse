<?php

function getVariable($name) {
	static $variables;
	if(!isset($variables)) {
		$variables = array();
		$ret = DB::Execute("SELECT name,value FROM variables");
		while($row = $ret->FetchRow())
			$variables[$row['name']] = unserialize($row['value']);
	}

	return $variables[$name];
}

/**
* Return status limit
* @return int
*/
function throwStatus( ){
  if( isset( $_SESSION['bUserQCMS'] ) && $_SESSION['bUserQCMS'] === true ){
    if( defined( 'CUSTOMER_PAGE' ) ){
      if( HIDDEN_SHOWS === true )
        return 0;
      else
        return 1;
    }
    else
      return 0;
  }
  else
    return 1;
} // end function throwStatus

/**
* Return value to $p variable from $_GET
* @return array
*/
function getUrlFromGet( ){
  global $a;
  if( isset( $_GET ) && is_array( $_GET ) ){
    foreach( $_GET as $mKey => $mValue ){
      if( strstr( $mKey, ',' ) ){
        $mKey = htmlspecialchars( $mKey );
        $aExp = explode( ',', $mKey );
        if( empty( $aExp[2] ) )
          $aExp[2] = 'p';

        for( $i = 2; $i < count( $aExp ); $i++ ){
          $aActions['o'.( $i )] = $aExp[$i];
          if( $aActions['o'.( $i )] == 'p' )
            $aActions['o'.( $i )] = null;
        } // end for

        if( is_numeric( $aExp[2] ) )
          $aExp[2] = 'p';

        if( is_numeric( $aExp[0] ) && !is_numeric( $aExp[1] ) ){
          $iProduct = $aExp[0];
          $aExp[0] = $aExp[1];
          $aExp[1] = $iProduct;
          $aExp[2] = 'products';
        }

        $aActions['o1']   = $aExp[0];
        $aActions['f']    = $aExp[2];
        $aActions['a']    = $aExp[1];
        $aActions['sLink']= '?'.$mKey;

        $a = $aActions['a'];

        return $aActions;
      }
    }
    $a = null;
    return Array( 'f' => 'p', 'a' => null, 'sLink' => '?' );
  }
} // end function getUrlFromGet

/**
* Returns extensions icons
* @return array
*/
function throwIconsFromExt( ){

  $aExt['rar'] = 'zip';
  $aExt['zip'] = 'zip';
  $aExt['bz2'] = 'zip';
  $aExt['gz']  = 'zip';

  $aExt['fla'] = 'fla';

  $aExt['mp3']  = 'media';
  $aExt['mpeg'] = 'media';
  $aExt['mpe']  = 'media';
  $aExt['mov']  = 'media';
  $aExt['mid']  = 'media';
  $aExt['midi'] = 'media';
  $aExt['asf']  = 'media';
  $aExt['avi']  = 'media';
  $aExt['wav']  = 'media';
  $aExt['wma']  = 'media';

  $aExt['msg']  = 'msg';
  $aExt['eml']  = 'msg';

  $aExt['pdf']  = 'pdf';

  $aExt['jpg']  = 'pic';
  $aExt['jpeg'] = 'pic';
  $aExt['jpe']  = 'pic';
  $aExt['gif']  = 'pic';
  $aExt['bmp']  = 'pic';
  $aExt['tif']  = 'pic';
  $aExt['tiff'] = 'pic';
  $aExt['wmf']  = 'pic';

  $aExt['png']  = 'png';

  $aExt['chm']  = 'chm';
  $aExt['hlp']  = 'chm';

  $aExt['psd']  = 'psd';

  $aExt['swf']  = 'swf';

  $aExt['pps']  = 'pps';
  $aExt['ppt']  = 'pps';

  $aExt['sys']  = 'sys';
  $aExt['dll']  = 'sys';

  $aExt['txt']  = 'txt';
  $aExt['doc']  = 'txt';
  $aExt['rtf']  = 'txt';

  $aExt['vcf']  = 'vcf';

  $aExt['xls']  = 'xls';

  $aExt['xml']  = 'xml';

  $aExt['tpl']  = 'web';
  $aExt['html'] = 'web';
  $aExt['htm']  = 'web';

  $aExt['com']  = 'exe';
  $aExt['bat']  = 'exe';
  $aExt['exe']  = 'exe';

  return $aExt;
} // end function throwIconsFromExt

/**
* Return rss link
* @return string
* @param int  $iLink
* @param int  $sLinkName
*/
function throwRssUrl( $iLink, $sLinkName ){
  if( defined( 'FRIENDLY_LINKS' ) ){
    if( FRIENDLY_LINKS == 1 ){
      return str_replace( '.html', null, $sLinkName ).',rss.xml';
    }
    else{
      return $sLinkName.',rss';
    }
  }
  else{
    return $sLinkName.',rss';
  }
} // end function throwRssUrl

/**
* Return price format and display
* @return float
* @param float  $fPrice
*/
function displayPrice( $fPrice ){
  return $fPrice;
} // end function displayPrice

/**
* Return price format to save in database
* @return float
* @param float  $fPrice
*/
function normalizePrice( $fPrice ){
  return sprintf( '%01.2f', ereg_replace( ',', '.', $fPrice ) );
} // end function normalizePrice

/**
* Create price using two variables
* @return float
* @param float $fPrice1
* @param float $fPrice2
*/
function generatePrice( $fPrice1, $fPrice2 ){
  if( ereg( '%', $fPrice2 ) ){
    $fPrice2 = ereg_replace( '%', '', $fPrice2 );
    if( $fPrice2 < 0 ){
      return normalizePrice( $fPrice1 - ( $fPrice1 * ( -$fPrice2 / 100 ) ) );
    }
    else
      return normalizePrice( $fPrice1 + ( $fPrice1 * ( $fPrice2 / 100 ) ) );
  }
  else{
    return normalizePrice( $fPrice1 + $fPrice2 );
  }
} // end function generatePrice

/**
* Check email format
* @return bool
* @param  string  $sEmail
*/
function checkEmail( $sEmail ){
  if( eregi( "^[a-z0-9_.-]+([_\\.-][a-z0-9]+)*@([a-z0-9_\.-]+([\.][a-z]{2,4}))+$", $sEmail ) )
    return true;
  else
    return false;
} // end function checkEmail

/**
* Send e-mail
* @return string
* @param array  $aForm
* @param string $sFile
* @param string $sTargetMail
*/
function sendEmail( $aForm, $sFile = 'messages.tpl', $sTargetEmail = null ){
  extract( $aForm );
  $oTpl =& TplParser::getInstance( );

  if( !empty( $sTopic ) && !empty( $sMailContent ) && checkEmail( $sSender ) === true ){
    $sMailContent = change2Latin( $sMailContent );
    $sTopic       = change2Latin( $sTopic );

    if( !empty( $sPhone ) )
      $sMailContent = $GLOBALS['lang']['Phone'].': '.change2Latin( $sPhone )."\n".$sMailContent;
    if( !empty( $sName ) )
      $sMailContent = $GLOBALS['lang']['Name_and_surname'].': '.change2Latin( $sName )."\n".$sMailContent;

    if( !isset( $sTargetEmail ) )
      $sTargetEmail = $GLOBALS['config']['email'];

    if( @mail( $sTargetEmail, $sTopic, $sMailContent, 'From: '.$sSender ) ){
      if( isset( $sFile ) )
        return $oTpl->tbHtml( $sFile, 'MAIL_SEND_CORRECT' );
    }
    else{
      if( isset( $sFile ) )
        return $oTpl->tbHtml( $sFile, 'MAIL_SEND_ERROR' );
    }
  }
  else{
    if( isset( $sFile ) )
      return $oTpl->tbHtml( $sFile, 'REQUIRED_FIELDS' );
  }
} // end function sendEmail

/**
* List of all pages and products and generate xml file
* @return string
* @param string $sFile
*/
function generateSiteMap2Xml( $sFile ){
  $sSiteUrl = 'http://'.$_SERVER['HTTP_HOST'].dirname( $_SERVER['REQUEST_URI'] ).'/';
  $content  = null;
  $oTpl     =& TplParser::getInstance( );

  if( isset( $GLOBALS['oPage']->aPages ) ){
    foreach( $GLOBALS['oPage']->aPages as $iPage => $aData ){
      $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
      $aData['sSiteUrl']          = $sSiteUrl;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'SITEMAP2XML_LIST' );
    } // end foreach
  }

  $products = $GLOBALS['oProduct']->getProducts();
  if( $products ){
    foreach( $products as $iProduct => $aData ){
      $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
      $aData['sSiteUrl']          = $sSiteUrl;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'SITEMAP2XML_LIST' );
    } // end foreach
  }

  if( isset( $content ) ){
    return $oTpl->tbHtml( $sFile, 'SITEMAP2XML_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'SITEMAP2XML_FOOT' );
  }
} // end function generateSiteMap2Xml

/**
* Display date changed by $config['time_diff']
* @return string
* @param int    $iTime
* @param string $sFormat
*/
function displayDate( $iTime = null, $sFormat = 'Y-m-d H:i' ){
  return isset( $iTime ) ? date( $sFormat, $iTime + ( TIME_DIFF * 60 ) ) : date( $sFormat );
} // end function displayDate

/**
* Returns language based on url parameter
* @return string
*/
function getLanguageFromUrl( ){
  $aUrl = getUrlFromGet( );
  if( isset( $aUrl['o1'] ) ){
    $iLangPos = strpos( $aUrl['o1'], LANGUAGE_SEPARATOR );
    if( $iLangPos === false )
      return null;
    else
      return substr( $aUrl['o1'], 0, $iLangPos );
  }
  else
    return null;
} // end function getLanguageFromUrl

/**
* Return street details
* @return array
* @param string $sStreet
*/
function throwStreetDetails( $sStreet ){
  $aData['sStreetName'] = $sStreet;
  preg_match_all( "/[ ][0-9]{1,}[\/a-z0-9]*[ ]*$/i", $sStreet, $aStreet );
  if( isset( $aStreet[0][0] ) ){
    preg_match_all( "/([0-9a-z]{1,})[\/]*([0-9]*)[ ]*$/i", $aStreet[0][0], $aStreet );
    $aData['sStreetName'] = ereg_replace( $aStreet[0][0]."$", "", $sStreet );
    if( isset( $aStreet[1][0] ) )
      $aData['sStreetNumber1'] = $aStreet[1][0];
    if( isset( $aStreet[2][0] ) )
      $aData['sStreetNumber2'] = $aStreet[2][0];
  }
  return $aData;
} // end function throwStreetDetails

/**
* Returns standard or friendly url
* @return string
* @param string $sUrl
* @param bool   $bPages
*/
function throwPageUrl( $sUrl, $bPages = null ){
  if( defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true ){
    if( isset( $bPages ) )
      $sUrl = str_replace( '.html', null, $sUrl );
    else
      $sUrl = $sUrl.'.html';
  }
  else{
    if( !isset( $bPages ) )
      $sUrl = '?'.$sUrl;
  }
  return $sUrl;
} // end function throwPageUrl

?>