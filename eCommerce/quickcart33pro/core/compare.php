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
  
  set_time_limit(0);
  ini_set('memory_limit', '512M');


  $sFeaturesBlock = $oTpl->tbHtml( $sFile, $sBlock.'_FEATURES' );
  if(!empty($sFeaturesBlock)) {
    $ret2 = DB::Execute('SELECT pp.f_item_name as iProduct,
				pp.f_value as sValue,
				p.f_parameter_code as iFeature,
				pl.f_label
				FROM premium_ecommerce_products_parameters_data_1 pp
				INNER JOIN premium_ecommerce_parameters_data_1 p ON (p.id=pp.f_parameter)
				LEFT JOIN premium_ecommerce_parameter_labels_data_1 pl ON (pl.f_parameter=p.id AND pl.f_language="'.LANGUAGE.'" AND pl.active=1)
				WHERE pp.active=1 AND pp.f_language="'.LANGUAGE.'"');
    while($row = $ret2->FetchRow())
	$aFeaturesProducts[$row['iProduct']][$row['f_label']] = $row['sValue'];
  }
  
    $ret = DB::Execute('SELECT c.id, c.f_company_name
			FROM premium_warehouse_items_data_1 i INNER JOIN (company_data_1 c,premium_ecommerce_products_data_1 d) 
			ON (c.id=i.f_manufacturer AND d.f_item_name=i.id AND d.active=1)
			WHERE i.active=1');
    while($r = $ret->FetchRow()) {
	    $id = $r['id']*4+1;
    	    $aProducers[$id] = $r['f_company_name'];
    }


  for($part=0;; $part++) {
    $aProducts = $GLOBALS['oProduct']->getProducts('',200,200*$part);
    if( !$aProducts) break;
    foreach($aProducts as $aData){
      if(!$aData['fPrice']) continue;
	if( $sBlock == 'CENEO' ){
          if(isset($aData['sAvailableCode'])) {
		if(preg_match('/([0-9]+)h/i', $aData['sAvailableCode'], $reqs)) {
			$aData['iAvailableDays'] = $reqs[1]/24;
		} elseif(preg_match('/([0-9]+)/', $aData['sAvailableCode'], $reqs)) {
			$aData['iAvailableDays'] = $reqs[1];
		}
		if(isset($aData['iAvailableDays'])) {
			if($aData['iAvailableDays']>7) $aData['iAvailableDays']=14;
			elseif($aData['iAvailableDays']<7 && $aData['iAvailableDays']>3) $aData['iAvailableDays']=7;
			elseif($aData['iAvailableDays']>1 && $aData['iAvailableDays']<3) $aData['iAvailableDays']=3;
			elseif($aData['iAvailableDays']<1) $aData['iAvailableDays']=1;
		}
	}
	}
      if((!isset($_REQUEST['outOfStock']) || !$_REQUEST['outOfStock']) && (!$aData['iQuantity'] || $aData['f_exclude_compare_services']))
      	continue;
      $aData['sPages'] = preg_replace( '/&nbsp;&raquo;&nbsp;/', '/', strip_tags( $GLOBALS['oProduct']->throwProductsPagesTree( $aData['aCategories'] ) ) );
      $aData['sPagesOnet'] = preg_replace( '/&nbsp;&raquo;&nbsp;/', ' &gt; ', strip_tags( $GLOBALS['oProduct']->throwProductsPagesTree( $aData['aCategories'] ) ) );
      $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );

      if( $aDataImage = $oFile->throwDefaultImage($aData['iProduct'],2) ) {
        $oTpl->setVariables( 'aDataImage', $aDataImage );
        $aData['sImage'] = $oTpl->tbHtml( $sFile, $sBlock.'_LIST_IMAGE' );
      }
      else
        $aData['sImage'] = null;

      $aPages = array_keys( $aData['aCategories'] );
      $aData['iPage'] = $aPages[0];
      $aData['sCategoryNokaut'] = preg_replace( '/&nbsp;&raquo;&nbsp;/', ' / ', strip_tags( $GLOBALS['oProduct']->throwProductsPagesTree( array($aData['iPage']=>$aData['iPage']) ) ) );

      if(empty( $aData['sDescriptionFull'] ) && !empty( $aData['sDescriptionShort'] ) )
    	$aData['sDescriptionFull'] = $aData['sDescriptionShort'];
      else
        $aData['sDescriptionFull'] = preg_replace( '/\|n\|/', '', $aData['sDescriptionFull'] );;

      $aData['sFeatures'] = null;
      if( isset( $aFeaturesProducts[$aData['iProduct']] ) ){
        foreach( $aFeaturesProducts[$aData['iProduct']] as $aData['sFeatureName'] => $aData['sFeatureValue'] ){
          foreach(array_keys($aData) as $s)
         	$aData[$s.'Escaped'] = htmlspecialchars($aData[$s]);
          $oTpl->setVariables( 'aData', $aData );
          $aData['sFeatures'] .= $oTpl->tbHtml( $sFile, $sBlock.'_FEATURES' );
        } // end for
        unset($aFeaturesProducts[$aData['iProduct']]);
      }

      if( isset( $aData['iProducer'] ) && isset( $aProducers[$aData['iProducer']] ) )
        $aData['sProducer'] = $aProducers[$aData['iProducer']];
      else
        $aData['sProducer'] = null;
        
      foreach(array_keys($aData) as $s)
      	$aData[$s.'Escaped'] = htmlspecialchars($aData[$s]);

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, $sBlock.'_LIST' );
    } // end for
    

    }
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
/**
* Throws minimal couriers price
* @return float
*/
function throwCourierMinPrice( ){
    //{ epesi
    global $config;

    $currency = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s',array($config['currency_symbol']));
    if($currency===false) 
    	die('Currency not defined in Epesi: '.$config['currency_symbol']);

    return DB::GetOne('SELECT MIN(f_price) FROM premium_ecommerce_payments_carriers_data_1 WHERE active=1 AND f_currency=%d',array($currency));
    //} epesi
} // end function throwCourierMinPrice
?>