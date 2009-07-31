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
/*  if( !is_file( $sFileDb ) )
    return null;

  $oFF =& FlatFiles::getInstance( );

  $aForm = changeMassTxt( $aForm, 'HLen', Array( 'sContent', 'HBrLen' ) );
  $aForm['iComment'] = $oFF->throwLastId( $sFileDb, 'iComment' ) + 1;
  $aForm['iTime']    = time( );
  $aForm['iLink']    = $iLink;
  $aForm['sIp']      = $_SERVER['REMOTE_ADDR'];
  $aForm['iStatus']  = 0;

  $oFF->save( $sFileDb, $aForm, null, 'rsort' );
  */
  //{ epesi
  $aForm = changeMassTxt( $aForm, 'HLen', Array( 'sContent', 'HBrLen' ) );
  DB::Execute('INSERT INTO premium_ecommerce_product_comments_data_1(created_on,f_name,f_content,f_time,f_item_name,f_ip,f_publish,f_language) VALUES (%T,%s,%s,%T,%d,%s,%b,%s)',
		array(time(),$aForm['sName'],$aForm['sContent'],time(),$iLink,$_SERVER['REMOTE_ADDR'],0,LANGUAGE));
  //} epesi

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
  /*
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
*/
  //{ epesi
  $oTpl =& TplParser::getInstance( );
  $bPages = false;
  $fields = 'f_name as sName, f_content as sContent, f_time as iTime, f_ip as sIp, f_publish as iStatus';
  if( isset( $iLink ) && is_numeric( $iLink ) ){
    $ret = DB::Execute('SELECT '.$fields.' FROM premium_ecommerce_product_comments_data_1 WHERE f_item_name=%d AND f_language=\''.LANGUAGE.'\' AND f_publish=1 AND active=1',array($iLink));
  } else {
    $ret = DB::Execute('SELECT '.$fields.' FROM premium_ecommerce_product_comments_data_1 WHERE f_language=\''.LANGUAGE.'\' AND f_publish=1 AND active=1 LIMIT 50',array());
  }
  $content = null; {
  while($aData = $ret->FetchRow()) {
	$aData['iTime'] = strtotime($aData['iTime']);
  //} epesi
      if(isset( $bPages )) {
    	    $aData['sLinkName'] = $GLOBALS['oPage']->aPages[$aData['iLink']]['sName'];
      } else {
    	    $prod = $GLOBALS['oProduct']->getProduct($aData['iLink']);
    	    $aData['sLinkName'] = $prod['sName'];
      }
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
/*
function listCommentsCheck( $aData, $aCheck ){
  if( isset( $aData ) && $aData['iLink'] == $aCheck['iLink'] && $aData['iStatus'] >= $aCheck['iStatus'] ){
    return true;
  }
  else{
    return null;
  }
} */// end function listCommentsCheck
?>