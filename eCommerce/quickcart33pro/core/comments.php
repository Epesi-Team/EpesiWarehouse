<?php
/**
* Save comment
* @return void
* @param array  $aForm
* @param int    $iLink
* @param string $sFileDb
*/
function addComment( $aForm, $iLink, $sFileDb = null ){
  if( !isset( $sFileDb ) )
    $sFileDb = DB_PAGES_COMMENTS;
  if( !is_file( $sFileDb ) )
    return null;

  $oFF =& FlatFiles::getInstance( );

  $aForm = changeMassTxt( $aForm, 'HLen', Array( 'sContent', 'HBrLen' ) );
  $aForm['iComment'] = $oFF->throwLastId( $sFileDb, 'iComment' ) + 1;
  $aForm['iTime']    = time( );
  $aForm['iLink']    = $iLink;
  $aForm['sIp']      = $_SERVER['REMOTE_ADDR'];
  $aForm['iStatus']  = 0;

  $oFF->save( $sFileDb, $aForm, null, 'rsort' );

} // end function addComment

/**
* List comment for page
* @return string
* @param  string  $sFile
* @param  int     $iLink
* @param string   $sFileDb
*/
function listComments( $sFile, $iLink, $sFileDb = null ){

  if( !isset( $sFileDb ) ){
    $sFileDb = DB_PAGES_COMMENTS;
    $bPages = true;
  }
  if( !is_file( $sFileDb ) )
    return null;

  $oTpl =& TplParser::getInstance( );
  $oFF  =& FlatFiles::getInstance( );

  $iCommentsLimit = 50;
  $iStatus = defined( 'CUSTOMER_PAGE' ) ? 1 : 0;
  
  if( isset( $iLink ) && is_numeric( $iLink ) ){
    $aFile  = $oFF->throwFileArray( $sFileDb, 'listCommentsCheck', Array( 'iLink' => $iLink, 'iStatus' => $iStatus ) );
    $iCount = count( $aFile );
  }
  else{
    $aFile  = $oFF->throwFileArray( $sFileDb );
    $iCount = count( $aFile );
    if( $iCount > $iCommentsLimit )
      $iCount = $iCommentsLimit;
  }
  
  $content= null;
  if( isset( $aFile ) && is_array( $aFile ) ){
    for( $i = 0; $i < $iCount; $i++ ){  
      $aData = $aFile[$i];

      $aData['sLinkName'] = isset( $bPages ) ? $GLOBALS['oPage']->aPages[$aData['iLink']]['sName'] : $GLOBALS['oProduct']->aProducts[$aData['iLink']]['sName'];
      $aData['iStyle']  = ( $i % 2 ) ? 0: 1;
      $aData['sStyle']  = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
      $aData['sDate']   = date( 'Y-m-d H:i', $aData['iTime'] );

      $aData['sLink'] = isset( $bPages ) ? 'iPage' : 'iProduct';
      $aData['sPage'] = isset( $bPages ) ? 'p' : 'products';

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'COMMENTS_LIST' );
    } // end for
    if( isset( $content ) )
      return $oTpl->tbHtml( $sFile, 'COMMENTS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'COMMENTS_FOOT' );
  }
} // end function listComments

/**
* Check comments in database
* @return bool
* @param array  $aData
* @param array  $aCheck
*/
function listCommentsCheck( $aData, $aCheck ){
  if( isset( $aData ) && $aData['iLink'] == $aCheck['iLink'] && $aData['iStatus'] >= $aCheck['iStatus'] ){
    return true;
  }
  else{
    return null;
  }
} // end function listCommentsCheck
?>