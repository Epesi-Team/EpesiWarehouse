<?php
/**
* Return list of emails
* @return string
*/
function throwNewsletterEmails( ){
  $oFF =& FlatFiles::getInstance( );
  $aData = $oFF->throwFileArray( DB_NEWSLETTER );
  if( isset( $aData ) && is_array( $aData ) ){
    $iCount = count( $aData );
    $content= null;
    for( $i = 0; $i < $iCount; $i++ ){
      $content .= $aData[$i]['sEmail'].', ';
    } // end for
    return $content;
  }
} // end function throwNewsletterEmails

/**
* Return list of emails
* @return string
*/
function listNewsletterEmails( $sFile ){
  $oFF    =& FlatFiles::getInstance( );
  $oTpl   =& TplParser::getInstance( );
  $aFile  = $oFF->throwFileArray( DB_NEWSLETTER );
  if( isset( $aFile ) && is_array( $aFile ) ){
    $iCount = count( $aFile );
    $content= null;
    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $aFile[$i];
      $aData['iStyle']  = ( $i % 2 ) ? 0: 1;
      $aData['sStyle']  = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
    } // end for
    if( !empty( $content ) )
      return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
  }
} // end function listNewsletterEmails
?>