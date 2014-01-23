<?php

/**
* Trow list of related products
* @return string
* @param string $sFile
* @param int    $iProduct
*/
function listProductsRelated( $sFile, $iProduct, $tbl='related' ){
  global $lang,$oOrder;
//  $oFF    =& FlatFiles::getInstance( );//commented by epesi team
  $oOrder->generateBasket( );
  $oTpl   =& TplParser::getInstance( );
  $oFile  =& Files::getInstance( );
  $aRelatedIds = throwProductsRelated( $iProduct, $tbl );
  $sLanguageUrl = LANGUAGE.LANGUAGE_SEPARATOR;
  if( !isset( $aRelatedIds ) )
    return null;
    $aProducts = array();
    if($aRelatedIds) {
	    $oProduct =& Products::getInstance( );
	    $aProducts = $oProduct->getProducts('it.id in ('.implode($aRelatedIds,',').')');
    }

  $iColumns = 3;
  $i2       = 0;
  $iWidth = (int) ( 100 / $iColumns );

  if( function_exists( 'throwSpecialProductPrice' ) )
    $bDiscount = true;

  $output      = null;
  if(count($aProducts)<=6) {
    $arr = array($lang['Products_related_client']=>$aProducts);
  } else {
    $oPage  =& Pages::getInstance( );
    $arr = array();
    foreach($aProducts as $aData){
        if( !is_numeric( $aData['fPrice'] ) || !$aData['iQuantity']) continue;
	$cat = $oPage->aPages[array_shift($aData['aCategories'])]['sName'];
	if(!isset($arr[$cat])) $arr[$cat]=array();
	$arr[$cat][] = $aData;
    }
  }
  
  foreach($arr as $cat=>$aProducts) {
  $i2       = 0;
  $content = null;
  foreach($aProducts as $aData){
    if($i2==6) 
        $content .= $oTpl->tbHtml( $sFile, 'RELATED_MORE' );

    $aData['iWidth'] = $iWidth;
    if( $i2 % 2 )
      $aData['iStyle'] = 0;
    else
      $aData['iStyle'] = 1;

    if( $i2 > 0 && $i2 % $iColumns == 0 )
      $content .= $oTpl->tbHtml( $sFile, 'RELATED_BREAK' );

    $aData['sLinkName'] = throwPageUrl( $aData['iProduct'].','.$sLanguageUrl.change2Url( $aData['sName'] ) );

    if( $GLOBALS['config']['users_plugin_active'] == true && defined( 'CUSTOMER_PAGE' ) && isset( $_SESSION['iUserPriceGroup'] ) && is_numeric( $_SESSION['iUserPriceGroup'] ) ){
      if( $_SESSION['iUserPriceGroup'] == 2 && isset( $aData['fPrice3'] ) && is_numeric( $aData['fPrice3'] ) )
        $aData['fPrice'] = $aData['fPrice3'];
      elseif( $_SESSION['iUserPriceGroup'] == 1 && isset( $aData['fPrice2'] ) && is_numeric( $aData['fPrice2'] ) )
        $aData['fPrice'] = $aData['fPrice2'];
    }

    $oTpl->setVariables( 'aData', $aData );
    if($aDataImage = $oFile->throwDefaultImage($aData['iProduct'],2)){
      $oTpl->setVariables( 'aDataImage', $aDataImage );
      $aData['sImage'] = $oTpl->tbHtml( $sFile, 'RELATED_IMAGE' );
    }
    else{
      $aData['sImage'] = $oTpl->tbHtml( $sFile, 'RELATED_NO_IMAGE' );
    }
    
    if( isset( $bDiscount ) )
      $aData['fPrice'] = throwSpecialProductPrice( $aData['iProduct'], $aData['fPrice'] );

    $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
    if(isset($oOrder->aProducts[$aData['iProduct']])) {
        $aData['sDisplayAddButton'] = 'style="display:none"';
	$aData['sDisplayDeleteButton'] = '';
    } else {
        $aData['sDisplayAddButton'] = '';
	$aData['sDisplayDeleteButton'] = 'style="display:none"';
    }
    $oTpl->setVariables( 'aData', $aData );

    global $config,$oPage,$iContent;
    if( isset( $config['basket_page'] ) && isset( $oPage->aPages[$config['basket_page']] )){
        global $sBasketPage;
        $sBasketPage = $oPage->aPages[$config['basket_page']]['sLinkName'];
        $aData['sBasket'] = $oTpl->tbHtml( $sFile, 'BASKET'.($iContent == $config['basket_page']?'':'_AJAX') );
    }
    $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'RELATED_PRICE' );

    $oTpl->setVariables( 'aData', $aData );
    $content .= $oTpl->tbHtml( $sFile, 'RELATED_LIST' );
    $i2++;
  } // end for

  while( $i2 % $iColumns > 0 ){
    $content .= $oTpl->tbHtml( $sFile, 'RELATED_BLANK' );
    $i2++;
  } // end while

  if( !empty( $content ) ) {
     $oTpl->setVariables('sCategoryName',$cat);
     $output .= $oTpl->tbHtml( $sFile, 'RELATED_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'RELATED_FOOT' );
  }
  }
  return $output;
} // end function

/**
* Throw array with ids of related products (id of related product as index and value)
* @return array
* @param int  $iProduct
*/
function throwProductsRelated( $iProduct, $tbl = 'related' ){
    //{ epesi
    $aReturn  = array();
    if(!is_array($iProduct)) $iProduct = array($iProduct);
    $rels = DB::GetCol('SELECT f_'.$tbl.'_products FROM premium_ecommerce_products_data_1 WHERE f_item_name IN ('.implode(',',$iProduct).') AND active=1 AND f_publish=1');
    $rel = array();
    foreach($rels as $rr)
        $rel = array_merge($rel,array_filter(explode('__',$rr)));
    if(!$rel) return array();
    $rel = array_unique($rel);
    $rel = DB::GetCol('SELECT f_item_name FROM premium_ecommerce_products_data_1 WHERE id IN ('.implode(',',$rel).') AND active=1');
    $rel = array_diff($rel,$iProduct);
    if($rel)
    	return array_combine($rel,$rel);
	return array();
    //} epesi
} // end function

?>