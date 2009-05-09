<?php
/**
* Return lang variables list
* @return string
* @param string $sFile
* @param string $sLang
*/
function listLangVariables( $sFile, $sLang ){
  if( is_file( DIR_LANG.$sLang.'.php' ) ){
    include DIR_LANG.$sLang.'.php';
    $oTpl     =& TplParser::getInstance( );
    $content  = null;
    $i        = 0;
    foreach( $lang as $aData['sKey'] => $aData['sValue'] ){
      $i++;

      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sValue'] = changeTxt( $aData['sValue'], '' );
      $aData['sValue'] = ereg_replace( '\|n\|', '\n', $aData['sValue'] );

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LANG_LIST' );
    }

    if( isset( $content ) ){
      return $oTpl->tbHtml( $sFile, 'LANG_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'LANG_FOOT' );
    }
  }
} // end function listLangVariables

/**
* Return array with languages
* @return array
*/
function throwLanguages( ){
  $oDir = dir( DIR_LANG );
  $oFF  =& FlatFiles::getInstance( );
  while( false !== ( $sFile = $oDir->read( ) ) ) {
    $sFileName = is_file( DIR_LANG.$sFile ) ? $oFF->throwNameOfFile( $sFile ) : null;

    if( isset( $sFileName ) && strlen( $sFileName ) == 2 ){
      $aLanguages[$sFileName] = $sFileName;
    }
  } // end while
  $oDir->close( );
  if( isset( $aLanguages ) )
    return $aLanguages;
} // end function throwLanguages

/**
* List all language files
* @return string
* @param string $sFile
*/
function listLanguages( $sFile ){
  $content    = null;
  $aLanguages = throwLanguages( );
  if( isset( $aLanguages ) && is_array( $aLanguages ) ){
    $iCount = count( $aLanguages );
    $i      = 0;
    $oTpl   =& TplParser::getInstance( );
    foreach( $aLanguages as $aData['sName'] ){
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      $i++;
    } // end foreach

    return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
  }
} // end function listLanguages

/**
* Return language files select
* @return string
* @param string $sLang
*/
function throwLangSelect( $sLang = null ){
  $content    = null;
  $aLanguages = throwLanguages( );
  if( isset( $aLanguages ) && is_array( $aLanguages ) ){
    foreach( $aLanguages as $sFileName ){
      $sSelected = ( isset( $sLang ) && $sLang == $sFileName ) ? ' selected="selected"' : null;
      $content .= '<option value="'.$sFileName.'"'.$sSelected.'>'.$sFileName.'</option>';
    } // end foreach
  }
  return $content;
} // end function throwLangSelect

/**
* Add language files
* @return void
* @param string $sLanguage
* @param string $sLanguageFrom
*/
function addLanguage( $sLanguage, $sLanguageFrom ){
  if( is_file( DIR_LANG.$sLanguage.'.php' ) )
    return null;
  if( !is_file( DIR_LANG.$sLanguageFrom.'.php' ) )
    return null;

  copy( 'config/lang_'.$sLanguageFrom.'.php', 'config/lang_'.$sLanguage.'.php' );
  copy( DIR_LANG.$sLanguageFrom.'.php', DIR_LANG.$sLanguage.'.php' );
  $oDir = dir( DIR_DB );
  $oFF  =& FlatFiles::getInstance( );
  while( false !== ( $sFile = $oDir->read( ) ) ) {
    $sFileName = is_file( DIR_DB.$sFile ) ? $oFF->throwNameOfFile( $sFile ) : null;
    if( isset( $sFileName ) && substr( $sFileName, 0, 3 ) == $sLanguageFrom.'_' ){
      if( ereg( '\.def', $sFileName ) ){
        $sContent = $oFF->throwFile( DIR_DB.$sFile );
        $sFunction= str_replace( '.def', '', $sFileName );
        $sContent = str_replace( $sFunction, $sLanguage.substr( $sFunction, 2 ), $sContent );

        $rFile = fopen( DIR_DB.$sLanguage.substr( $sFile, 2 ), 'w' );
        fwrite( $rFile, $sContent );
        fclose( $rFile );
      }
      else{
        $rFile = fopen( DIR_DB.$sLanguage.substr( $sFile, 2 ), 'w' );
        fwrite( $rFile, '<?php exit; ?>'."\n" );
        fclose( $rFile );
      }
    }
  } // end while
  $oDir->close( );
} // end function addLanguage

/**
* Delete language files
* @return void
* @param string $sLanguage
*/
function deleteLanguage( $sLanguage ){
  if( is_file( DIR_LANG.$sLanguage.'.php' ) )
    unlink( DIR_LANG.$sLanguage.'.php' );
  if( is_file( 'config/lang_'.$sLanguage.'.php' ) )
    unlink( 'config/lang_'.$sLanguage.'.php' );
  $oDir = dir( DIR_DB );
  $oFF  =& FlatFiles::getInstance( );
  while( false !== ( $sFile = $oDir->read( ) ) ) {
    $sFileName = is_file( DIR_DB.$sFile ) ? $oFF->throwNameOfFile( $sFile ) : null;
    if( isset( $sFileName ) && substr( $sFileName, 0, 3 ) == $sLanguage.'_' ){
      unlink( DIR_DB.$sFile );
    }
  } // end while
  $oDir->close( );
} // end function deleteLanguage
?>