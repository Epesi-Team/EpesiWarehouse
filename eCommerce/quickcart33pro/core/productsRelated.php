<?php

/**
* Throw select from products list
* @return string
* @param string $sFile
* @param int  $iProduct
*/
/*
function listProductsRelatedSelect( $sFile, $iProduct ){
  $oFF    =& FlatFiles::getInstance( );
  $content = null;
  $aData = $oFF->throwFileArraySmall( DB_PRODUCTS, 'iProduct', 'sName' );
  if( isset( $aData ) && is_array( $aData ) ){
    asort( $aData );
    $aRelatedIds  = throwProductsRelated( $iProduct );
    $iCount       = count( $aData );
    foreach( $aData as $iProduct => $sName ){  
      $sSelected = ( isset( $aRelatedIds[$iProduct] ) ) ? ' selected="selected"' : null;
      $content .= '<option value="'.$iProduct.'" '.$sSelected.'>'.$sName.'</option>';
    } // end for
  }
  return $content;
} // end function
*/

/**
* Check if product related can be shown
* @return bool
* @param array  $aData
* @param array  $aCheck
*/
/*
function checkIsProductRelated( $aData, $aCheck ){
  if( is_array( $aData ) && isset( $aCheck[0][$aData['iProduct']] ) && ( $aCheck[1] === 0 || ( isset( $aCheck[2][$aData['iProduct']] ) && $aData['iStatus'] >= $aCheck[1] ) ) )
    return true;
  else
    return null;
} // end function
*/


/**
* Trow list of related products
* @return string
* @param string $sFile
* @param int    $iProduct
*/
function listProductsRelated( $sFile, $iProduct ){
//  $oFF    =& FlatFiles::getInstance( );//commented by epesi team
  $oTpl   =& TplParser::getInstance( );
  $oFile  =& Files::getInstance( );
  $aRelatedIds = throwProductsRelated( $iProduct );
  $sLanguageUrl = ( LANGUAGE_IN_URL == true ) ? LANGUAGE.LANGUAGE_SEPARATOR : null;
  if( !isset( $aRelatedIds ) )
    return null;
  $iStatus = throwStatus( );
    $aProducts = array();
    if($aRelatedIds) {
	    $oProduct =& Products::getInstance( );
	    $aProducts = $oProduct->getProducts('pr.id in ('.implode($aRelatedIds,',').')');
    }

  $iColumns = 3;
  $i2       = 0;
  $iWidth = (int) ( 100 / $iColumns );

  if( function_exists( 'throwSpecialProductPrice' ) )
    $bDiscount = true;

  $content      = null;
  foreach($aProducts as $aData){
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
    if( isset( $oFile->aImagesDefault[2][$aData['iProduct']] ) ){
      $aDataImage = $oFile->aFilesImages[2][$oFile->aImagesDefault[2][$aData['iProduct']]];
      $oTpl->setVariables( 'aDataImage', $aDataImage );
      $aData['sImage'] = $oTpl->tbHtml( $sFile, 'RELATED_IMAGE' );
    }
    else{
      $aData['sImage'] = $oTpl->tbHtml( $sFile, 'RELATED_NO_IMAGE' );
    }
    
    if( isset( $bDiscount ) )
      $aData['fPrice'] = throwSpecialProductPrice( $aData['iProduct'], $aData['fPrice'] );

    $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
    $oTpl->setVariables( 'aData', $aData );

    if( is_numeric( $aData['fPrice'] ) )
      $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'RELATED_PRICE' );
    else
      $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'RELATED_NO_PRICE' );

    $oTpl->setVariables( 'aData', $aData );
    $content .= $oTpl->tbHtml( $sFile, 'RELATED_LIST' );
    $i2++;
  } // end for

  while( $i2 % $iColumns > 0 ){
    $content .= $oTpl->tbHtml( $sFile, 'RELATED_BLANK' );
    $i2++;
  } // end while

  if( !empty( $content ) )
    return $oTpl->tbHtml( $sFile, 'RELATED_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'RELATED_FOOT' );
  return $content;
} // end function

/**
* Check if product is related with selected
* @return bool
* @param array $aData
* @param int $iProduct
*/
/*
function checkThrowProductsRelated( $aData, $iProduct ){
  if( $aData['iProduct'] == $iProduct )
    return true;
  else
    return null;
} // end function checkThrowProductsRelated
*/

/**
* Throw array with ids of related products (id of related product as index and value)
* @return array
* @param int  $iProduct
*/
function throwProductsRelated( $iProduct ){
  /*$oFF      =& FlatFiles::getInstance( );
  $aData    = $oFF->throwFileArray( DB_PRODUCTS_RELATED, 'checkThrowProductsRelated', $iProduct );
  $iCount   = count( $aData );
  $aReturn  = null;
  for( $i = 0; $i < $iCount; $i++ ){  
    $aReturn[$aData[$i]['iRelated']] = $aData[$i]['iRelated'];
  } // end for
  return $aReturn;
*/
    //{ epesi
    $aReturn  = array();
    $rel = array_filter(explode('__',DB::GetOne('SELECT f_related_products FROM premium_ecommerce_products_data_1 WHERE f_item_name=%d AND active=1',array($iProduct))));
    if($rel)
	return array_combine($rel,$rel);
    return array();
    //} epesi
} // end function

?>