<?php
if( isset( $aActions['a'] ) && is_numeric( $aActions['a'] ) ){
  $iProduct = $aActions['a'];
  $sBasket  = null;
  $aData = $oProduct->throwProduct( $iProduct );
  if( isset( $aData ) ){
    if( !empty( $aData['sTemplate'] ) )
      $oTpl->setFileAlt( $config['default_products_template'] );
    else
      $aData['sTemplate'] = $config['default_products_template'];

    if( !empty( $aData['sTheme'] ) )
      $sTheme = $aData['sTheme'];
    if( !empty( $aData['sMetaKeywords'] ) )
      $sKeywords = $aData['sMetaKeywords'];
    if( !empty( $aData['sMetaDescription'] ) )
      $sDescription = $aData['sMetaDescription'];
    if( empty( $aData['sDescriptionFull'] ) )
      $aData['sDescriptionFull'] = $aData['sDescriptionShort'];
    $aData['sDescriptionFull'] = changeTxt( $aData['sDescriptionFull'], 'nlNds' );

    $aData['sPages'] = $oProduct->throwProductsPagesTree( $iProduct );

    $sTxtSize   = ( $config['text_size'] == true ) ? $oTpl->tbHtml( $aData['sTemplate'], 'TXT_SIZE' ) : null;
    $sAvailable = !empty( $aData['sAvailable'] ) ? $oTpl->tbHtml( $aData['sTemplate'], 'AVAILABLE' ) : null;
    $sTitle     = strip_tags( ( !empty( $aData['sNameTitle'] ) ? $aData['sNameTitle'] : $aData['sName'] ).' - ' );

    $aImages    = $oFile->listImagesByTypes( $aData['sTemplate'], $iProduct, 2 );
    $sFilesList = $oFile->listFiles( $aData['sTemplate'], $iProduct, 2 );

    $oTpl->unsetVariables( );

    if( is_numeric( $aData['fPrice'] ) ){
      if( isset( $config['basket_page'] ) && isset( $oPage->aPages[$config['basket_page']] ) ){
        $sBasketPage = $oPage->aPages[$config['basket_page']]['sLinkName'];
        $sBasket = $oTpl->tbHtml( $aData['sTemplate'], 'BASKET' );
      }
      $sPrice = $oTpl->tbHtml( $aData['sTemplate'], 'PRICE' );
    }
    else{
      $sPrice = $oTpl->tbHtml( $aData['sTemplate'], 'NO_PRICE' );
    }

    $content .= $oTpl->tbHtml( $aData['sTemplate'], 'CONTAINER' );
  }
  else{
    $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );
  }
}
?>