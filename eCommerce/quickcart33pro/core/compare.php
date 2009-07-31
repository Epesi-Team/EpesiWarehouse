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
  //$oFF =& FlatFiles::getInstance( );//epesi commented out
  $content= null;
  $sBlock = strtoupper( $sBlock );

  $aProducts = $GLOBALS['oProduct']->getProducts('pri.f_gross_price=concat(\'\', 0+pri.f_gross_price)');
  
  $sFeaturesBlock = $oTpl->tbHtml( $sFile, $sBlock.'_FEATURES' );
  if(!empty($sFeaturesBlock)) {
    $ret2 = DB::Execute('SELECT pp.f_item_name as iProduct,
				pp.f_value as sValue,
				p.f_parameter_code as iFeature,
				FROM premium_ecommerce_products_parameters_data_1 pp
				INNER JOIN premium_ecommerce_parameters_data_1 p ON (p.id=pp.f_parameter)
				WHERE pp.active=1 AND pp.f_language="'.LANGUAGE.'"');
    while($row = $ret2->FetchRow())
	$aFeaturesProducts[$row['iProduct']][$row['iFeature']] = $row['sValue'];
  }
  
    $ret = DB::Execute('SELECT c.id, c.f_company_name
			FROM premium_warehouse_items_data_1 i INNER JOIN (company_data_1 c,premium_ecommerce_products_data_1 d) 
			ON (c.id=i.f_vendor AND d.f_item_name=i.id AND d.active=1)
			WHERE i.active=1');
    while($r = $ret->FetchRow()) {
	    $id = $r['id']*4+1;
    	    $aProducers[$id] = $r['f_company_name'];
    }


  if( isset( $aProducts ) ){
    foreach($aProducts as $aData){
      $aData['sPages'] = ereg_replace( '&nbsp;&raquo;&nbsp;', '/', strip_tags( $GLOBALS['oProduct']->throwProductsPagesTree( $aData['aCategories'] ) ) );
      $aData['sPagesOnet'] = ereg_replace( '&nbsp;&raquo;&nbsp;', ' &gt; ', strip_tags( $GLOBALS['oProduct']->throwProductsPagesTree( $aData['aCategories'] ) ) );
      $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );

      if( isset( $oFile->aImagesDefault[2][$aData['iProduct']] ) ){
        $aDataImage = $oFile->aFilesImages[2][$oFile->aImagesDefault[2][$aData['iProduct']]];
        $oTpl->setVariables( 'aDataImage', $aDataImage );
        $aData['sImage'] = $oTpl->tbHtml( $sFile, $sBlock.'_LIST_IMAGE' );
      }
      else
        $aData['sImage'] = null;

      $aPages = array_keys( $aData['aCategories'] );
      $aData['iPage'] = $aPages[0];
      if( isset( $aCategoriesNokaut ) ){
        $iCategoryNokaut = $GLOBALS['oPage']->aPages[$aPages[0]]['iCategoryNokaut'];
        if( isset( $aCategoriesNokaut[$iCategoryNokaut] ) )
          $aData['sCategoryNokaut'] = $aCategoriesNokaut[$iCategoryNokaut];
        else
          $aData['sCategoryNokaut'] = null;
      }

      if(empty( $aData['sDescriptionFull'] ) && !empty( $aData['sDescriptionShort'] ) )
    	$aData['sDescriptionFull'] = $aData['sDescriptionShort'];
      else
        $aData['sDescriptionFull'] = ereg_replace( '\|n\|', '', $aData['sDescriptionFull'] );

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
/*
function throwCategoriesNokautNames( ){
  $oFF =& FlatFiles::getInstance( );
  return $oFF->throwFileArraySmall( DB_CATEGORIES_NOKAUT_NAMES, 'iCategory', 'sName' );
} // end function throwCategoriesNokautNames

*/
/**
* Throws minimal couriers price
* @return float
*/
function throwCourierMinPrice( ){
  /*$oFF =& FlatFiles::getInstance( );
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
    */
    //{ epesi
    global $config;

    $currency = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s',array($config['currency_symbol']));
    if($currency===false) 
    	die('Currency not defined in Epesi: '.$config['currency_symbol']);

    return DB::GetOne('SELECT MIN(f_price) FROM premium_ecommerce_payments_carriers_data_1 WHERE active=1 AND f_currency=%d',array($currency));
    //} epesi
} // end function throwCourierMinPrice
?>