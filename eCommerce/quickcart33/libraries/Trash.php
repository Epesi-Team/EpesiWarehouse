<?php
if( !defined( 'MAX_PAGES' ) )
  define( 'MAX_PAGES', 10 );

if( !defined( 'LANG_YES_SHORT' ) )
  define( 'LANG_YES_SHORT', 'Tak' );

if( !defined( 'LANG_NO_SHORT' ) )
  define( 'LANG_NO_SHORT', 'Nie' );

if( !defined( 'LANG_PAGE_PREV' ) )
  define( 'LANG_PAGE_PREV', '&laquo; Poprzednia' );

if( !defined( 'LANG_PAGE_NEXT' ) )
  define( 'LANG_PAGE_NEXT', 'Następna &raquo;' );

if( !defined( 'MAX_STR_LEN' ) )
  define( 'MAX_STR_LEN', 80 );


/**
* Library with all kind functions
* @version 0.6.6
*/

/**
* Function return HTML select
* @return string
* @param int    $nr
*/
function throwYesNoSelect( $nr ){
  for( $l = 0; $l < 2; $l++ ){
    if( is_numeric( $nr ) && $nr == $l ) 
      $select[$l] = 'selected="selected"';
    else		
      $select[$l] = '';
  } // end for

  $option =  '<option value="1" '.$select[1].'>'.LANG_YES_SHORT.'</option>';
  $option .= '<option value="0" '.$select[0].'>'.LANG_NO_SHORT.'</option>';

  return $option;
} // end function throwYesOrNoSelect

/**
* Function return HTML checkbox and it will be selected
* when $iYesNo will be 1
* @return string
* @param string $sBoxName
* @param int    $iYesNo
*/
function throwYesNoBox( $sBoxName, $iYesNo = 0 ){
  if( $iYesNo == 1 )
    $sChecked = 'checked="checked"';
  else
    $sChecked = null;

  return '<input type="checkbox" '.$sChecked.' name="'.$sBoxName.'" value="1" />';
} // end function throwYesNoBox

/**
* Return Yes if $nr will be 1
* @return string
* @param int $nr
*/
function throwYesNoTxt( $nr = 0 ){
  return $nr == 1 ? LANG_YES_SHORT : LANG_NO_SHORT;
} // end function throwYesNoTxt

/**
* Function change recieved string
* @return string
* @param string $sContent
* @param mixed  $sOption
*/
function changeTxt( $sContent, $sOption = null ){

  if( preg_match( '/tag/i', $sOption ) )
    $sContent = changeHtmlEditorTags( $sContent );

  if( preg_match( '/h/i', $sOption ) )
    $sContent = htmlspecialchars( $sContent );

  $sContent = changeSpecialChars( $sContent );

  if( !preg_match( '/nds/i', $sOption ) ){
    $aSea[] = '"';
    $aRep[] = '&quot;';
  }

  if( preg_match( '/sl/i', $sOption ) )
    $sContent = addslashes( $sContent );
  else
    $sContent = stripslashes( $sContent );

  $sContent = ereg_replace( "\r", '', $sContent );

  if( preg_match( '/len/i', $sOption ) )
    $sContent = checkLengthOfTxt( $sContent );

  if( preg_match( '/nl/i', $sOption ) ){
    $aSea[] = "\n";
    $aRep[] = null;
    $aSea[] = '|n|';
    $aRep[] = "\n";
  }
  else{
    if( preg_match( '/br/i', $sOption ) ){
      $aSea[] = "\n";
      $aRep[] = '<br />';
    }
    else{
      $aSea[] = "\n";
      $aRep[] = '|n|';
    }
  }

  if( preg_match( '/space/i', $sOption ) ){
    $aSea[] = ' ';
    $aRep[] = null;
  }

  if( isset( $aSea ) )
    $sContent = str_replace( $aSea, $aRep, $sContent );

  return $sContent;
} // end function changeTxt

/**
* Change all array values using changeTxt function
* @return array
* @param array  $aData
* @param string $sOption
* 1. $aData = changeMassTxt( $aData, 'sl' );
* 2. $aData = changeMassTxt( $aData, 'sl', Array( 'index1', 'Nds' ), Array( 'index2', 'SlNds' ) );
*/
function changeMassTxt( $aData, $sOption = null ){
  $iParams = func_num_args( );
  if( $iParams > 2 ){
    $aParam = func_get_args( );
    for( $i = 2; $i < $iParams; $i++ ){
      $aData[$aParam[$i][0]]    = changeTxt( $aData[$aParam[$i][0]], $aParam[$i][1] );
      $aDontDo[$aParam[$i][0]]  = true;
    } // end for
  }
    
  foreach( $aData as $mKey => $mValue )
    if( !isset( $aDontDo[$mKey] ) && !is_numeric( $mValue ) && !is_array( $mValue ) )
      $aData[$mKey] = changeTxt( $mValue, $sOption );
  return $aData;
} // end function changeMassTxt

/**
* Check string length and add space if string is longer then defined limit
* @return string
* @param string $sContent
*/
function checkLengthOfTxt( $sContent ){
  return wordwrap( $sContent, MAX_STR_LEN, ' ', 1 );
} // end function checkLengthOfTxt

/**
* Return UNIX time from defined date
* @return int
* @param string $sDateTime
* @param string $dateFormat
* @param string $sepDate
* @param string $sepTime
*/
function dateToTime( $sDateTime, $dateFormat = 'ymd', $sepDate = '-', $sepTime = ':' ){
  
  $exp = explode( ' ', $sDateTime );
  $date = $exp[0];
  if( !empty( $exp[1] ) )
    $time = $exp[1];

  if( $dateFormat == 'dmy' ){
    $y	= 2;
    $m	= 1;
    $d	= 0;
  }
  else{
    $y	= 0;
    $m	= 1;
    $d	= 2;
  }

  $exp =		@explode( $sepDate, $date );
  $year =		$exp[$y];
  $month =	sprintf( '%01.0f', $exp[$m] );
  $day =		sprintf( '%01.0f', $exp[$d] );

  if( empty( $time ) )
    $time = '00'.$sepTime.'00'.$sepTime.'00';
  
  $exp =		@explode( $sepTime, $time );
  $hour=		sprintf( '%01.0f', $exp[0] );
  $minute=	sprintf( '%01.0f', $exp[1] );

  if( count( $exp ) == 3 )
    $second=	sprintf( '%01.0f', $exp[2] );
  else
    $second=	0;
 

  return @mktime( $hour, $minute, $second, $month, $day, $year );
} // end function dateToTime

/**
* Count pages by defined positions / max positions per page
* @return string
* @param int    $iMax
* @param int    $iMaxPerPage
* @param int    $iPage
* @param string $sAddress
* @param string $sAddress2
* @param bool   $bRewrite
* @param string $sSeparator
* @param int    $iMaxPagesPerPage
*/
function countPages( $iMax, $iMaxPerPage, $iPage, $sAddress, $sAddress2 = null, $bRewrite = null, $sSeparator = null, $iMaxPagesPerPage = MAX_PAGES ){

  if( !isset( $iMaxPagesPerPage ) )
    $iMaxPagesPerPage = MAX_PAGES;
  if( isset( $sAddress2 ) && isset( $bRewrite ) )
    $sAddress2 = '?'.$sAddress2;
  $sExt     = isset( $bRewrite ) ? '.html' : null;
  $iPage    = (int) $iPage;
  $iSubPages= ceil( $iMax / $iMaxPerPage ); 
  $sPages   = null;
  
  if( $iSubPages > $iPage ) 
    $iNext = 1; 
  else  
    $iNext = 0; 

  $iMax = ceil( $iPage + ( $iMaxPagesPerPage / 2 ) );
  $iMin = ceil( $iPage - ( $iMaxPagesPerPage / 2 ) );
  if( $iMin < 0 )
    $iMax += -( $iMin );
  if( $iMax > $iSubPages )
    $iMin -= $iMax - $iSubPages;

  $l['min'] = 0;
  $l['max'] = 0;
  for( $i = 1; $i <= $iSubPages; $i++ ){
    
    $sUrl = ' <a href="'.$sAddress.','.$i.$sExt.$sAddress2.'">';

    if( $i >= $iMin && $i <= $iMax ){
      if ( $i == $iPage ) 
        $sPages .= $sSeparator.' <strong>'.$i.'</strong> '; 
      else 
        $sPages .= $sSeparator.$sUrl.$i.'</a> '; 
    }
    elseif( $i < $iMin ) {
      if( $i == 1 )
        $sPages .= $sSeparator.$sUrl.$i.'</a> '; 
      else{
        if( $l['min'] == 0 ){
          $sPages .= $sSeparator.' ... '; 
          $l['min'] = 1;
        }
      }
    }
    elseif( $i > $iMin ) {
      if( $i == $iSubPages ){
        $sPages .= $sSeparator.$sUrl.$i.'</a> '; 
      }
      else{
        if( $l['max'] == 0 ){
          $sPages .= $sSeparator.' ... '; 
          $l['max'] = 1;
        }
      }
    }
  } // end for
  $sPages .= $sSeparator;

  
  if( $iPage > 1 ){
    $sUrl   = ' <a href="'.$sAddress.','.( $iPage - 1 ).$sExt.$sAddress2.'" class="pPrev">';
    $sPrev  = $sUrl.LANG_PAGE_PREV.'</a> ';
  }
  else
    $sPrev = null;
  if( $iNext == 1 ){
    $sUrl   = ' <a href="'.$sAddress.','.( $iPage + 1 ).$sExt.$sAddress2.'" class="pNext">';
    $sNext  = $sUrl.LANG_PAGE_NEXT.'</a>';
  }
  else
    $sNext = null;

  return $sPrev.$sPages.$sNext;
} // end function countPages

/**
* Count pages by defined positions / max positions per page
* @return string
* @param int    $iMax
* @param int    $iMaxPerPage
* @param int    $iPage
* @param string $sAddress
* @param string $sSeparator
* @param int    $iMaxPagesPerPage
* @param string $sUrlName
*/
function countPagesClassic( $iMax, $iMaxPerPage, $iPage, $sAddress, $sSeparator = null, $iMaxPagesPerPage = null, $sUrlName = 'iPage' ){

  if( !isset( $iMaxPagesPerPage ) )
    $iMaxPagesPerPage = MAX_PAGES;
  $iPage = (int) $iPage;
  $iSubPages= ceil( $iMax / $iMaxPerPage ); 
  $sPages   = null;
  
  if( $iSubPages > $iPage ) 
    $iNext = 1; 
  else  
    $iNext = 0; 

  $iMax = ceil( $iPage + ( $iMaxPagesPerPage / 2 ) );
  $iMin = ceil( $iPage - ( $iMaxPagesPerPage / 2 ) );
  if( $iMin < 0 )
    $iMax += -( $iMin );
  if( $iMax > $iSubPages )
    $iMin -= $iMax - $iSubPages;

  $l['min'] = 0;
  $l['max'] = 0;
  for ( $i = 1; $i <= $iSubPages; $i++ ) { 
    if( $i >= $iMin && $i <= $iMax ) {
      if ( $i == $iPage ) 
        $sPages .= $sSeparator.' <strong>'.$i.'</strong> '; 
      else 
        $sPages .= $sSeparator.' <a href="'.$sAddress.'&amp;'.$sUrlName.'='.$i.'">'.$i.'</a> '; 
    }
    elseif( $i < $iMin ) {
      if( $i == 1 )
        $sPages .= $sSeparator.' <a href="'.$sAddress.'&amp;'.$sUrlName.'='.$i.'">'.$i.'</a> '; 
      else{
        if( $l['min'] == 0 ){
          $sPages .= $sSeparator.' ... '; 
          $l['min'] = 1;
        }
      }
    }
    elseif( $i > $iMin ) {
      if( $i == $iSubPages ){
        $sPages .= $sSeparator.' <a href="'.$sAddress.'&amp;'.$sUrlName.'='.$i.'">'.$i.'</a> '; 
      }
      else{
        if( $l['max'] == 0 ){
          $sPages .= $sSeparator.' ... '; 
          $l['max'] = 1;
        }
      }
    }
  } // end for
  $sPages .= $sSeparator;

  if( $iPage > 1 )
    $sPrev = '<a href="'.$sAddress.'&amp;'.$sUrlName.'='.($iPage-1).'" class="pPrev">'.LANG_PAGE_PREV.'</a> ';
  else
    $sPrev = null;
  if( $iNext == 1 )
    $sNext = ' <a href="'.$sAddress.'&amp;'.$sUrlName.'='.($iPage+1).'" class="pNext">'.LANG_PAGE_NEXT.'</a>';
  else
    $sNext = null;
  $sPages = $sPrev.$sPages.$sNext;

  return $sPages;
} // end function countPagesClassic

/**
* Change string to latin
* @return string
* @param string $sContent
*/
function change2Latin( $sContent ){
  return str_replace(
    Array( 'ś', 'ą', 'ź', 'ż', 'ę', 'ł', 'ó', 'ć', 'ń', 'Ś', 'Ą', 'Ź', 'Ż', 'Ę', 'Ł', 'Ó', 'Ć', 'Ń', 'á', 'č', 'ď', 'é', 'ě', 'í', 'ň', 'ř', 'š', 'ť', 'ú', 'ů', 'ý', 'ž', 'Á', 'Č', 'Ď', 'É', 'Ě', 'Í', 'Ň', 'Ř', 'Š', 'Ť', 'Ú', 'Ů', 'Ý', 'Ž', 'ä', 'ľ', 'ĺ', 'ŕ', 'Ä', 'Ľ', 'Ĺ', 'Ŕ', 'ö', 'ü', 'ß', 'Ö', 'Ü' ),
    Array( 's', 'a', 'z', 'z', 'e', 'l', 'o', 'c', 'n', 'S', 'A', 'Z', 'Z', 'E', 'L', 'O', 'C', 'N', 'a', 'c', 'd', 'e', 'e', 'i', 'n', 'r', 's', 't', 'u', 'u', 'y', 'z', 'A', 'C', 'D', 'E', 'E', 'I', 'N', 'R', 'S', 'T', 'U', 'U', 'Y', 'Z', 'a', 'l', 'l', 'r', 'A', 'L', 'L', 'R', 'o', 'u', 'S', 'O', 'U' ),
    $sContent
  );
} // end function change2Latin

/**
* Changes charset to default iso-8859-2
* @return string
* @param string $sContent
*/
function changeCharset( $sContent ){
  return $sContent;
  //return str_replace( Array( 'ś', 'ą', 'ź', 'Ś', 'Ą', 'Ź' ), Array( '¶', '±', 'Ľ', '¦', 'ˇ', '¬' ), $sContent );
} // end function changeCharset

/**
* Change '$' to '&#36;'
* @return string
* @param string $sTxt
*/
function changeSpecialChars( $sTxt ){
  return str_replace( '$', '&#36;', $sTxt );
} // end function changeSpecialChars

/**
* Check that date format is correct
* @return boolean
* @param string $date
* @param string $format
* @param string $separator
*/
function is_date( $date, $format='ymd', $separator='-' ){

  $f['y'] = 4;
  $f['m'] = 2;
  $f['d'] = 2;

  if ( ereg( "([0-9]{".$f[$format[0]]."})".$separator."([0-9]{".$f[$format[1]]."})".$separator."([0-9]{".$f[$format[2]]."})", $date ) ){
    
    $y =    strpos( $format, 'y' );
    $m =    strpos( $format, 'm' );
    $d =    strpos( $format, 'd' );
    $dates= explode( $separator, $date );

    return  checkdate( $dates[$m], $dates[$d], $dates[$y] );
  }
  else
    return false;
} // end function is_date

/**
* Return string length
* @return int
* @param string $sContent
*/
function throwStrLen( $sContent ){
  return strlen( trim( changeTxt( $sContent, 'hBrSpace' ) ) );
} // end function throwStrLen

/**
* Return microtime
* @return float
*/
function throwMicroTime( ){ 
  $exp = explode( " ", microtime( ) ); 
  return ( (float) $exp[0] + (float) $exp[1] ); 
} // end function throwMicroTime

/**
* Check content
* @return void
*/
/*
function checkContent( ){
  global $sMsg, $config;
  if( !defined( base64_decode( 'TElDRU5TRV9OT19MSU5L' ) ) ){
    $oDir = dir( DIR_TEMPLATES );
    $oFF  =& FlatFiles::getInstance( );
    while( false !== ( $sFileName = $oDir->read( ) ) ){
      if( strstr( $sFileName, '.tpl' ) ){
        $sContent = $oFF->throwFile( DIR_TEMPLATES.$sFileName );
        if( stristr( $sContent, base64_decode( 'aHR0cDovL29wZW5zb2x1dGlvbi5vcmc=' ) ) || stristr( $sContent, base64_decode( 'aHR0cDovL3d3dy5vcGVuc29sdXRpb24ub3Jn' ) ) )
          return null;
      }
    }
    $oDir->close();
    if( !isset( $config['email'] ) )
      $config['email'] = null;
    $sMsg = base64_decode('PGRpdiBpZD0ibXNnIiBjbGFzcz0iZXJyb3IiPllvdXIgUGFnZSBicmVha3Mgb3VyIHByb2R1Y3QgbGljZW5zZSE8YnIgLz48YnIgLz5QbGVhc2UgcmV0dXJuIGxpbmsgdG8gcGFnZSBodHRwOi8vb3BlbnNvbHV0aW9uLm9yZy8gd2l0aCB0aXRsZQ==').base64_decode('ICJwb3dlcmVkIGJ5IFF1aWNrLkNhcnQi').base64_decode('PC9kaXY+PGlmcmFtZSBzcmM9Imh0dHA6Ly9vcGVuc29sdXRpb24ub3JnL2xpY2Vuc2UtYnJlYWss' ).$config['email'].','.$config['language'].base64_decode('Lmh0bWwiIHN0eWxlPSJkaXNwbGF5Om5vbmU7Ij48L2lmcmFtZT4=');
  }
} // end function checkContent
*/
/**
* Return HTML select from defined array
* @return string
* @param array  $aData
* @param mixed  $mData
*/
function throwSelectFromArray( $aData, $mData = null ){
  $sOption  = null;

  foreach( $aData as $iKey => $mValue ){
    if( isset( $mData ) && $mData == $iKey )
      $sSelected = 'selected="selected"';
    else
      $sSelected = null;

    $sOption .= '<option value="'.$iKey.'" '.$sSelected.'>'.$mValue.'</option>';  
  }

  return $sOption;
} // end function throwSelectFromArray

/**
* Get file name from $p parameter
* @return string
* @param string   $p
*/
function getAction( $p ){
  global $a;
  if( ereg( '-', $p ) ){
    $aExp   = explode( '-', $p );
    $iCount = count( $aExp );
    for( $i = 0; $i < $iCount; $i++ ){
      if( !empty( $aExp[$i] ) ){
        if( $i == 0 )
          $aActions['f'] = $aExp[$i];
        elseif( $i == 1 )
          $aActions['a'] = $aExp[$i];
        else{
          $aActions['o'.( $i - 1 )] = $aExp[$i];
        }

      }
    } // end for
    if( !empty( $aActions['f'] ) && !empty( $aActions['a'] ) ){
      $a = $aActions['a'];
      $aActions['sLink'] = '?p='.$p;
      return $aActions;
    }
  }
} // end function getAction

//$oDupa  =& Dupa::getInstance( );
/**
* Change string parameter to url name
* @return string
* @param string $sContent
*/
function change2Url( $sContent ){
  return strtolower( change2Latin( str_replace( 
    Array( ' ', '&raquo;', '/', '$', '\'', '"', '~', '\\', '?', '#', '%', '+', '^', '*', '>', '<', '@', '|', '&quot;', '%', ':', '&', ',', '=', '--', '--', '[', ']' ),
    Array( '-', '',        '-', '-', '',   '',  '-', '-',  '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-',      '-', '-', '',  '-', '-', '-',  '-', '(', ')' ),
    trim( $sContent )
  ) ) );
} // end function change2Url
?>