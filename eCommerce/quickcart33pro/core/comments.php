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

?>