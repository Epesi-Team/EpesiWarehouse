<?php
/**
* Return templates select
* @return string
* @param string $sPrefix
* @param string $sFileCurrent
*/
function throwTemplatesSelect( $sPrefix, $sFileCurrent = null ){

  if( empty( $sFileCurrent ) ){
    $sFileCurrent = $GLOBALS['config']['default_pages_template'];
  }

  $oDir = dir( DIR_TEMPLATES );
  while( false !== ( $sFileName = $oDir->read( ) ) ){
    if( is_file( DIR_TEMPLATES.$sFileName ) && strstr( $sFileName, '.tpl' ) && strstr( $sFileName, $sPrefix ) ){
      $aFiles[] = $sFileName;
    }
  }
  $oDir->close( );

  if( isset( $aFiles ) ){
    $content = null;
    sort( $aFiles );
    $iCount = count( $aFiles );
    for( $i = 0; $i < $iCount; $i++ ){
      $sSelected  = ( $sFileCurrent == $aFiles[$i] ) ? ' selected="selected"' : null;
      $content .= '<option value="'.$aFiles[$i].'"'.$sSelected.'>'.$aFiles[$i].'</option>';
    } // end for

    return $content;
  }
} // end function throwTemplatesSelect

/**
* Return templates select
* @return string
* @param string $sFileCurrent
*/
function throwCssSelect( $sFileCurrent = null ){

  if( empty( $sFileCurrent ) ){
    $sFileCurrent = $GLOBALS['config']['template'];
  }

  $oFF =& FlatFiles::getInstance( );

  $oDir = dir( DIR_TEMPLATES );
  while( false !== ( $sFileName = $oDir->read( ) ) ){
    if( is_file( DIR_TEMPLATES.$sFileName ) && $oFF->checkCorrectFile( $sFileName, 'css' ) && !ereg( 'plugins', $sFileName ) ){
      $aFiles[] = $sFileName;
    }
  }
  $oDir->close( );

  if( isset( $aFiles ) ){
    $content = null;
    sort( $aFiles );
    $iCount = count( $aFiles );
    for( $i = 0; $i < $iCount; $i++ ){
      $sSelected  = ( $sFileCurrent == $aFiles[$i] ) ? ' selected="selected"' : null;
      $content .= '<option value="'.$aFiles[$i].'"'.$sSelected.'>'.$aFiles[$i].'</option>';
    } // end for

    return $content;
  }
} // end function throwCssSelect

/**
* Return themes select
* @return string
* @param string $sFileCurrent
*/
function throwThemesSelect( $sFileCurrent = null ){
  
  if( empty( $sFileCurrent ) ){
    $sFileCurrent = $GLOBALS['config']['default_theme'];
  }

  $oDir = dir( DIR_THEMES );
  while( false !== ( $sFileName = $oDir->read( ) ) ){
    if( is_file( DIR_THEMES.$sFileName ) && strstr( $sFileName, '.php' ) ){
      $aFiles[] = $sFileName;
    }
  }
  $oDir->close( );

  if( isset( $aFiles ) ){
    $content = null;
    sort( $aFiles );
    $iCount = count( $aFiles );
    for( $i = 0; $i < $iCount; $i++ ){
      $sSelected  = ( $sFileCurrent == $aFiles[$i] ) ? ' selected="selected"' : null;
      $sValue     = ( $aFiles[$i] == $GLOBALS['config']['default_theme'] ) ? null : $aFiles[$i];

      $content .= '<option value="'.$sValue.'"'.$sSelected.'>'.$aFiles[$i].'</option>';
    } // end for

    return $content;
  }
} // end function throwThemesSelect

/**
* Saves variables to config
* @return void
* @param array  $aForm
* @param string $sFile
* @param string $sVariable
*/
function saveVariables( $aForm, $sFile, $sVariable = 'config' ){
  $aFile  = file( $sFile );
  $iCount = count( $aFile );
  $rFile  = fopen( $sFile, 'w' );

  for( $i = 0; $i < $iCount; $i++ ){
    foreach( $aForm as $sKey => $sValue ){
      if( ereg( $sVariable."\['".$sKey."'\]", $aFile[$i] ) && ereg( '=', $aFile[$i] ) ){
        $sValue = changeSpecialChars( $sValue );
        $sValue = ereg_replace( '"', '&quot;', $sValue );
        $sValue = stripslashes( $sValue );
        if( ( is_numeric( $sValue ) || preg_match( '/^(true|false|null)$/', $sValue ) == true ) && !ereg( '0[0-9]+', $sValue ) )
          $aFile[$i] = "\$".$sVariable."['".$sKey."'] = ".$sValue.";\n";
        else
          $aFile[$i] = "\$".$sVariable."['".$sKey."'] = \"".$sValue."\";\n";
      }
    } // end foreach

    fwrite( $rFile, $aFile[$i] );

  } // end for
  fclose( $rFile );
} // end function saveVariables

/**
* Log in and out actions
* @return void
* @param string $p
* @param string $sKey
* @param string $sFile
* @date 2007-09-20 09:42:35
*/
function loginActions( $p, $sKey = 'bLogged', $sFile ){
  global $sLoginInfo, $sLoginPage;
  $oTpl   =& TplParser::getInstance( );
  $sCheck = 'checkContent';
  
  if( !isset( $_SESSION[$sKey] ) || $_SESSION[$sKey] !== TRUE ){
    if( $p == 'login' && isset( $_POST['sLogin'] ) && isset( $_POST['sPass'] ) ){
      $iCheckLogin = checkLogin( $_POST['sLogin'], $_POST['sPass'], $sKey );
      if( $iCheckLogin == 1 ){
        if( !isset( $_COOKIE['sLogin'] ) || $_COOKIE['sLogin'] != $_POST['sLogin'] )
          @setCookie( 'sLogin', $_POST['sLogin'], time( ) + 2592000 );
        
        $sRedirect = !empty( $_POST['sLoginPageNext'] ) ? $_POST['sLoginPageNext'] : $_SERVER['PHP_SELF'];

        header( 'Location: '.$sRedirect );
        exit;
      }
      elseif( $iCheckLogin == 2 ){
        $sLoginPage     = $_SERVER['PHP_SELF'];
        $sLoginContent  = $oTpl->tbHtml( 'login.tpl', 'INACTIVE' );
      }
      else{
        $sLoginPage     = $_SERVER['PHP_SELF'];
        $sLoginContent  = $oTpl->tbHtml( 'login.tpl', 'INCORRECT' );
      }
    }
    else{
      $sLoginPage    = '?p=login';
      if( !empty( $_SERVER['REQUEST_URI'] ) )
        $_SERVER['REQUEST_URI'] = strip_tags( $_SERVER['REQUEST_URI'] );
      $sLoginContent = $oTpl->tbHtml( 'login.tpl', 'FORM' );
    }

    unset( $GLOBALS['aActions'] );

    $oTpl->setVariables( 'sLoginContent', $sLoginContent );
    $sContent = $oTpl->tbHtml( 'login.tpl', 'PANEL' );
    echo $oTpl->tbHtml( $sFile, 'HEAD' ).$sContent.$oTpl->tbHtml( $sFile, 'FOOT' );
    exit;
  }
  else{
    if( $p == 'logout' ){
      unset( $_SESSION[$sKey] );
      $sLoginPage = $_SERVER['PHP_SELF'];
      header( 'Location: '.$_SERVER['PHP_SELF'] );
      exit;
    }
    if( isset( $sCheck ) )
      $sCheck();
  }
} // end function loginActions

/**
* Check login and password saved in config/general.php
* @return int
* @param string $sLogin
* @param string $sPass
* @param string $sKey
*/
function checkLogin( $sLogin, $sPass, $sKey ){
  if( $GLOBALS['config']['login'] == $sLogin && $GLOBALS['config']['pass'] == $sPass ){
    $_SESSION[$sKey] = true;
    return 1;
  }
  else{
    return 0;
  }
} // end function checkLogin

/**
* Return subpages show select
* @return string
* @param int  $iShow
*/
function throwSubpagesShowSelect( $iShow = null ){
  $aSubpages[1] = $GLOBALS['lang']['Subpage_show_1'];
  $aSubpages[2] = $GLOBALS['lang']['Subpage_show_2'];
  return throwSelectFromArray( $aSubpages, $iShow );
} // end function throwSubpagesShowSelect

/**
* Return true/false select
* @return string
* @param bool $bTrueFalse
*/
function throwTrueFalseSelect( $bTrueFalse = false ){
  
  $aSelect = Array( null, null );
  
  if( $bTrueFalse == true )
    $aSelect[1] = 'selected="selected"';
  else
    $aSelect[0] = 'selected="selected"';
  
  $sOption =  '<option value="true" '.$aSelect[1].'>'.LANG_YES_SHORT.'</option>';
  $sOption .= '<option value="false" '.$aSelect[0].'>'.LANG_NO_SHORT.'</option>';
  return $sOption;
} // end function throwTrueFalseSelect

/**
* Return true/null select
* @return string
* @param bool $bTrueNull
*/
function throwTrueNullSelect( $bTrueNull = null ){
  
  $aSelect = Array( null, null );
  
  if( $bTrueNull == true )
    $aSelect[1] = 'selected="selected"';
  else
    $aSelect[0] = 'selected="selected"';
  
  $sOption =  '<option value="true" '.$aSelect[1].'>'.LANG_YES_SHORT.'</option>';
  $sOption .= '<option value="null" '.$aSelect[0].'>'.LANG_NO_SHORT.'</option>';
  return $sOption;
} // end function throwTrueNullSelect

/**
* Delete from address iPage=Something
* @return string
* @param string $sUrl
*/
function changeUri( $sUrl ){
  return ereg_replace( "&iPage=[0-9]*", '', $sUrl );
} // end function changeUri
?>