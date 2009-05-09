<?php

/**
* List products
* @return string
* @param string $sFile
* @param int    $iContent
*/
function listProductsCompare( $sFile, $sBlock ){
  $oTpl   =& TplParser::getInstance( );
  $oFile  =& Files::getInstance( );
  $oFF =& FlatFiles::getInstance( );
  $content= null;
  $sBlock = strtoupper( $sBlock );

  if( isset( $GLOBALS['oProduct']->aProducts ) ){
    foreach( $GLOBALS['oProduct']->aProducts as $iProduct => $aData ){
      if( is_numeric( $aData['fPrice'] ) )
        $aProducts[] = $iProduct;
    } // end foreach
  }

  $sFeaturesBlock = $oTpl->tbHtml( $sFile, $sBlock.'_FEATURES' );
  if( defined( 'DB_FEATURES' ) && is_file( DB_FEATURES ) && !empty( $sFeaturesBlock ) ){
    $aFeatures = $oFF->throwFileArraySmall( DB_FEATURES, 'iFeature', 'sName' );
    $aFeaturesProductsAll = $oFF->throwFileArray( DB_FEATURES_PRODUCTS, null );
    if( isset( $aFeaturesProductsAll ) && is_array( $aFeaturesProductsAll ) ){
      $iCount   = count( $aFeaturesProductsAll );
      for( $i = 0; $i < $iCount; $i++ ){
        $aFeaturesProducts[$aFeaturesProductsAll[$i]['iProduct']][$aFeaturesProductsAll[$i]['iFeature']] = $aFeaturesProductsAll[$i]['sValue'];
      } // end for
    }
  }
  if( defined( 'DB_PRODUCERS' ) && is_file( DB_PRODUCERS ) ){
    $aProducers = $oFF->throwFileArraySmall( DB_PRODUCERS, 'iProducer', 'sName' );
  }


  if( isset( $aProducts ) ){
    if( defined( 'DB_CATEGORIES_NOKAUT_NAMES' ) && $sBlock == 'NOKAUT' )
      $aCategoriesNokaut = throwCategoriesNokautNames( );

    $aDescriptionFull = $oFF->throwFileArraySmall( DB_PRODUCTS_EXT, 'iProduct', 'sDescriptionFull' );

    $iCount = count( $aProducts );

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $GLOBALS['oProduct']->aProducts[$aProducts[$i]];

      $aData['sPages'] = ereg_replace( '&nbsp;&raquo;&nbsp;', '/', strip_tags( $GLOBALS['oProduct']->throwProductsPagesTree( $aData['iProduct'] ) ) );
      $aData['sPagesOnet'] = ereg_replace( '&nbsp;&raquo;&nbsp;', ' &gt; ', strip_tags( $GLOBALS['oProduct']->throwProductsPagesTree( $aData['iProduct'] ) ) );
      $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );

      if( isset( $oFile->aImagesDefault[2][$aData['iProduct']] ) ){
        $aDataImage = $oFile->aFilesImages[2][$oFile->aImagesDefault[2][$aData['iProduct']]];
        $oTpl->setVariables( 'aDataImage', $aDataImage );
        $aData['sImage'] = $oTpl->tbHtml( $sFile, $sBlock.'_LIST_IMAGE' );
      }
      else
        $aData['sImage'] = null;

      $aPages = array_keys( $GLOBALS['oProduct']->aProductsPages[$aData['iProduct']] );
      $aData['iPage'] = $aPages[0];
      if( isset( $aCategoriesNokaut ) ){
        $iCategoryNokaut = $GLOBALS['oPage']->aPages[$aPages[0]]['iCategoryNokaut'];
        if( isset( $aCategoriesNokaut[$iCategoryNokaut] ) )
          $aData['sCategoryNokaut'] = $aCategoriesNokaut[$iCategoryNokaut];
        else
          $aData['sCategoryNokaut'] = null;
      }

      if( empty( $aDescriptionFull[$aData['iProduct']] ) && !empty( $aData['sDescriptionShort'] ) )
        $aData['sDescriptionFull'] = $aData['sDescriptionShort'];
      else
        $aData['sDescriptionFull'] = ereg_replace( '\|n\|', '', $aDescriptionFull[$aData['iProduct']] );

      $aData['sFeatures'] = null;
      if( isset( $aFeaturesProducts[$aData['iProduct']] ) ){
        foreach( $aFeaturesProducts[$aData['iProduct']] as $aData['iFeature'] => $aData['sFeatureValue'] ){
          $aData['sFeatureName'] = $aFeatures[$aData['iFeature']];
          $oTpl->setVariables( 'aData', $aData );
          $aData['sFeatures'] .= $oTpl->tbHtml( $sFile, $sBlock.'_FEATURES' );
        } // end for
      }

      if( isset( $aData['iProducer'] ) && isset( $aProducers[$aData['iProducer']] ) )
        $aData['sProducer'] = $aProducers[$aData['iProducer']];
      else
        $aData['sProducer'] = null;

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, $sBlock.'_LIST' );
    } // end for

    if( $sBlock == 'SKAPIEC' ){
      $aData['sSkapiecDate'] = date( 'Y-m-d' );
      $aData['fSkapiecMinCourier'] = throwCourierMinPrice( );
      $aData['sSkapiecCategories'] = listPagesCompare( $sFile );
    }

    if( isset( $content ) ){
      $oTpl->setVariables( 'aData', $aData );
      return $oTpl->tbHtml( $sFile, $sBlock.'_HEAD' ).$content.$oTpl->tbHtml( $sFile, $sBlock.'_FOOT' );
    }
    else
      return null;
  }
} // end function listProductsCompare

/**
* Return pages list
* @return string
* @param string $sFile
*/
function listPagesCompare( $sFile ){
  $oTpl = TplParser::getInstance( );
  $content = null;

  foreach( $GLOBALS['oPage']->aPages as $iPage => $aData ){
    $oTpl->setVariables( 'aData', $aData );
    if( $aData['iProducts'] == 1 )
      $content .= $oTpl->tbHtml( $sFile, 'PAGES_LIST' );
    if( isset( $GLOBALS['oPage']->aPagesChildrens[$aData['iPage']] ) ){
      $content .= listSubPagesCompare( $sFile, $aData['iPage'] );
    }
  } // end for
  if( isset( $content ) )
    return $oTpl->tbHtml( $sFile, 'PAGES_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'PAGES_FOOT' );
} // end function listPagesCompare

/**
* Return subpages
* @return string
* @param string $sFile
* @param int    $iPageParent
* @param int    $iDepth
*/
function listSubPagesCompare( $sFile, $iPageParent ){
  $oTpl = TplParser::getInstance( );
  $content = null;
  $iCount  = count( $GLOBALS['oPage']->aPagesChildrens[$iPageParent] );
  for( $i = 0; $i < $iCount; $i++ ){
    $aData = $GLOBALS['oPage']->aPages[$GLOBALS['oPage']->aPagesChildrens[$iPageParent][$i]];

    $oTpl->setVariables( 'aData', $aData );
    if( $aData['iProducts'] == 1 )
      $content .= $oTpl->tbHtml( $sFile, 'PAGES_LIST' );
    if( isset( $GLOBALS['oPage']->aPagesChildrens[$aData['iPage']] ) ){
      $content .= listSubPagesCompare( $sFile, $aData['iPage'] );
    }
  } // end for
  return $content;
} // end function listSubPagesCompare


/**
* Zwraca nazwy kategorii nokaut
* @return array
*/
function throwCategoriesNokautNames( ){
  $oFF =& FlatFiles::getInstance( );
  return $oFF->throwFileArraySmall( DB_CATEGORIES_NOKAUT_NAMES, 'iCategory', 'sName' );
} // end function throwCategoriesNokautNames

/**
* Throws minimal couriers price
* @return float
*/
function throwCourierMinPrice( ){
  $oFF =& FlatFiles::getInstance( );
  $aData = $oFF->throwFileArray( DB_CARRIERS );

  $fMinPrice = null;
  if( isset( $aData ) ){
    $iCount   = count( $aData );
    for( $i = 0; $i < $iCount; $i++ ){
      if( !isset( $fMinPrice ) || $aData[$i]['fPrice'] < $fMinPrice )
        $fMinPrice = $aData[$i]['fPrice'];
    } // end for
    if( !isset( $fMinPrice ) )
      $fMinPrice = 0;

    return $fMinPrice;
  }
  else
    return null;
} // end function throwCourierMinPrice
?>