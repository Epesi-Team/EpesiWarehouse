<?php
/**
* Lists products stats
* @return string
* @param string $sFile
* @param string $sDateFrom
* @param string $sDateTo
*/
function listProductsStats( $sFile, $sDateFrom, $sDateTo ){
  $oTpl =& TplParser::getInstance( );
  $oProduct =& Products::getInstance( );
  $rF = fopen( DB_PRODUCTS_STATS, 'r' );
  $aStats = null;
  $i = 0;
  $iTimeStart = dateToTime( $sDateFrom, 'ymd' );
  $iTimeStop =  dateToTime( $sDateTo.' 23:59:59', 'ymd' );
  while( ( $aFile = fgetcsv( $rF, 30, '$' ) ) !== FALSE ){
    if( $i > 0 ){
      list( $aData['iProduct'], $aData['iTime'] ) = $aFile;
      if( $aData['iTime'] >= $iTimeStart && $aData['iTime'] <= $iTimeStop ){
        if( isset( $aStats[$aData['iProduct']] ) )
          $aStats[$aData['iProduct']]++;
        else
          $aStats[$aData['iProduct']] = 1;
      }
    }
    $i++;
  }
  fclose( $rF );

  $content = null;
  if( isset( $aStats ) && is_array( $aStats ) ){
    arsort( $aStats );
    $i = 0;
    foreach( $aStats as $aData['iProduct'] => $aData['iVisits'] ){
      if( isset( $oProduct->aProducts[$aData['iProduct']]['sName'] ) )
        $aData['sName'] = $oProduct->aProducts[$aData['iProduct']]['sName'];
      else
        $aData['sName'] = null;
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sLink'] = 'iProduct';
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      $i++;
    } // end for
  }

  if( isset( $content ) )
    $content = $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );

  return $content;
} // end function listProductsStats

/**
* Lists product stats
* @return string
* @param string $sFile
*/
function listProductStats( $sFile, $iProduct ){
  $oTpl =& TplParser::getInstance( );
  $rF = fopen( DB_PRODUCTS_STATS, 'r' );
  $aStats = null;
  $i = 0;
  while( ( $aFile = fgetcsv( $rF, 30, '$' ) ) !== FALSE ){
    if( $i > 0 ){
      list( $aData['iProduct'], $aData['iTime'] ) = $aFile;
      if( $aData['iProduct'] == $iProduct ){
        $sTime = date( 'Y-m-d', $aData['iTime'] );
        if( isset( $aStats[$sTime] ) )
          $aStats[$sTime]++;
        else
          $aStats[$sTime] = 1;
      }
    }
    $i++;
  }
  fclose( $rF );

  $content = null;
  if( isset( $aStats ) && is_array( $aStats ) ){
    krsort( $aStats );
    $i = 0;
    foreach( $aStats as $aData['sDate'] => $aData['iVisits'] ){
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'DETAILS_LIST' );
      $i++;
    } // end for
  }

  if( isset( $content ) )
    $content = $oTpl->tbHtml( $sFile, 'DETAILS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'DETAILS_FOOT' );

  return $content;
} // end function listProductStats

/**
* Lists pages stats
* @return string
* @param string $sFile
* @param string $sDateFrom
* @param string $sDateTo
*/
function listPagesStats( $sFile, $sDateFrom, $sDateTo ){
  $oTpl =& TplParser::getInstance( );
  $oPage=& Pages::getInstance( );
  $rF = fopen( DB_PAGES_STATS, 'r' );
  $aStats = null;
  $i = 0;
  $iTimeStart = dateToTime( $sDateFrom, 'ymd' );
  $iTimeStop =  dateToTime( $sDateTo.' 23:59:59', 'ymd' );
  while( ( $aFile = fgetcsv( $rF, 30, '$' ) ) !== FALSE ){
    if( $i > 0 ){
      list( $aData['iPage'], $aData['iTime'] ) = $aFile;
      if( $aData['iTime'] >= $iTimeStart && $aData['iTime'] <= $iTimeStop ){
        if( isset( $aStats[$aData['iPage']] ) )
          $aStats[$aData['iPage']]++;
        else
          $aStats[$aData['iPage']] = 1;
      }
    }
    $i++;
  }
  fclose( $rF );

  $content = null;
  if( isset( $aStats ) && is_array( $aStats ) ){
    arsort( $aStats );
    $i = 0;
    foreach( $aStats as $aData['iPage'] => $aData['iVisits'] ){
      if( isset( $oPage->aPages[$aData['iPage']]['sName'] ) )
        $aData['sName'] = $oPage->aPages[$aData['iPage']]['sName'];
      else
        $aData['sName'] = null;
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sLink'] = 'iPage';
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      $i++;
    } // end for
  }

  if( isset( $content ) )
    $content = $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );

  return $content;
} // end function listPagesStats

/**
* Lists page stats
* @return string
* @param string $sFile
*/
function listPageStats( $sFile, $iPage ){
  $oTpl =& TplParser::getInstance( );
  $rF = fopen( DB_PAGES_STATS, 'r' );
  $aStats = null;
  $i = 0;
  while( ( $aFile = fgetcsv( $rF, 30, '$' ) ) !== FALSE ){
    if( $i > 0 ){
      list( $aData['iPage'], $aData['iTime'] ) = $aFile;
      if( $aData['iPage'] == $iPage ){
        $sTime = date( 'Y-m-d', $aData['iTime'] );
        if( isset( $aStats[$sTime] ) )
          $aStats[$sTime]++;
        else
          $aStats[$sTime] = 1;
      }
    }
    $i++;
  }
  fclose( $rF );

  $content = null;
  if( isset( $aStats ) && is_array( $aStats ) ){
    krsort( $aStats );
    $i = 0;
    foreach( $aStats as $aData['sDate'] => $aData['iVisits'] ){
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'DETAILS_LIST' );
      $i++;
    } // end for
  }

  if( isset( $content ) )
    $content = $oTpl->tbHtml( $sFile, 'DETAILS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'DETAILS_FOOT' );

  return $content;
} // end function listPageStats

/**
* Lists searched phrases
* @return string
* @param string $sFile
* @param string $sDateFrom
* @param string $sDateTo
*/
function listSearchedPhrases( $sFile, $sDateFrom, $sDateTo ){
  $oTpl =& TplParser::getInstance( );
  $oPage=& Pages::getInstance( );
  $rF = fopen( DB_SEARCHED_WORDS, 'r' );
  $aStats = null;
  $i = 0;
  $iTimeStart = dateToTime( $sDateFrom, 'ymd' );
  $iTimeStop =  dateToTime( $sDateTo.' 23:59:59', 'ymd' );
  while( ( $aFile = fgetcsv( $rF, 500, '$' ) ) !== FALSE ){
    if( $i > 0 ){
      list( $aData['sWords'], $aData['iTime'] ) = $aFile;
      if( $aData['iTime'] >= $iTimeStart && $aData['iTime'] <= $iTimeStop ){
        if( isset( $aStats[$aData['sWords']] ) )
          $aStats[$aData['sWords']]++;
        else
          $aStats[$aData['sWords']] = 1;
      }
    }
    $i++;
  }
  fclose( $rF );

  $content = null;
  if( isset( $aStats ) && is_array( $aStats ) ){
    arsort( $aStats );
    $i = 0;
    foreach( $aStats as $aData['sPhrase'] => $aData['iSearched'] ){
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'PHRASES_LIST' );
      $i++;
    } // end for
  }

  if( isset( $content ) )
    $content = $oTpl->tbHtml( $sFile, 'PHRASES_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'PHRASES_FOOT' );

  return $content;
} // end function listSearchedPhrases

/**
* List customers stats
* @return string
* @param string $sFile
* @param string $sDateFrom
* @param string $sDateTo
* @param int    $iStatus1
* @param int    $iStatus2
*/
function listCustomersStats( $sFile, $sDateFrom, $sDateTo, $iStatus1, $iStatus2 ){
  $aOrders = returnOrdersStats( $sDateFrom, $sDateTo, $iStatus1, $iStatus2 );

  if( isset( $aOrders ) ){
    $oTpl =& TplParser::getInstance( );
    $oProduct =& Products::getInstance( );

    $rFile = fopen( DB_ORDERS_PRODUCTS, 'r' );
    $i = 0;
    while( ( $aFile = fgetcsv( $rFile, 700, '$' ) ) !== FALSE ){
      if( $i > 0 ){
        $aData = orders_products( $aFile );
        if( isset( $aOrders[$aData['iOrder']] ) ){
          if( !isset( $aOrdersSummary[$aData['iOrder']] ) ){
            $aOrdersSummary[$aData['iOrder']] = 0;
          }
          $aOrdersSummary[$aData['iOrder']] += ( $aData['iQuantity'] * $aData['fPrice'] );
        }
      }
      $i++;
    } // end while
    fclose( $rFile );

    $rFile = fopen( DB_ORDERS, 'r' );
    $i = 0;
    while( ( $aFile = fgetcsv( $rFile, 700, '$' ) ) !== FALSE ){
      if( $i > 0 ){
        $aData = orders( $aFile );
        if( isset( $aOrders[$aData['iOrder']] ) ){
          $aData['sCustomer'] = strtolower( trim( $aData['sFirstName'].$aData['sLastName'] ) );
          if( !isset( $aCustomers[$aData['sCustomer']] ) ){
            $aCustomers[$aData['sCustomer']]['iQuantity'] = 0;
            $aCustomers[$aData['sCustomer']]['fSummary'] = 0;
            $aCustomers[$aData['sCustomer']]['sName'] = $aData['sFirstName'].' '.$aData['sLastName'];
          }
          $aCustomers[$aData['sCustomer']]['fSummary'] += isset( $aOrdersSummary[$aData['iOrder']] ) ? $aOrdersSummary[$aData['iOrder']] : 0;
          $aCustomers[$aData['sCustomer']]['iQuantity']++;
        }
      }
      $i++;
    } // end while
    fclose( $rFile );

    if( isset( $aCustomers ) ){
      rsort( $aCustomers );
      $iCount = count( $aCustomers );
      if( $iCount > 50 )
        $iCount = 50;
      $content= null;
      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $aCustomers[$i];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['fSummary'] = normalizePrice( $aData['fSummary'] );
        $aData['sSummary'] = displayPrice( $aData['fSummary'] );
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'CUSTOMERS_LIST' );
      } // end for

      if( isset( $content ) )
        return $oTpl->tbHtml( $sFile, 'CUSTOMERS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'CUSTOMERS_FOOT' );
    }
  }
} // end function listCustomersStats

/**
* List ordered products
* @return string
* @param string $sFile
* @param string $sDateFrom
* @param string $sDateTo
* @param int    $iStatus1
* @param int    $iStatus2
*/
function listOrderedProductsStats( $sFile, $sDateFrom, $sDateTo, $iStatus1, $iStatus2 ){
  $aOrders = returnOrdersStats( $sDateFrom, $sDateTo, $iStatus1, $iStatus2, strtolower( LANGUAGE ) );

  if( isset( $aOrders ) ){
    $oTpl =& TplParser::getInstance( );
    $oProduct =& Products::getInstance( );
    $i = 0;

    $rFile = fopen( DB_ORDERS_PRODUCTS, 'r' );
    while( ( $aFile = fgetcsv( $rFile, 700, '$' ) ) !== FALSE ){
      if( $i > 0 ){
        $aData = orders_products( $aFile );
        if( isset( $aOrders[$aData['iOrder']] ) ){
          if( !isset( $aProducts[$aData['iProduct']] ) ){
            $aProducts[$aData['iProduct']]['iQuantity'] = 0;
            $aProducts[$aData['iProduct']]['fSummary'] = 0;
            $aProducts[$aData['iProduct']]['iProduct'] = $aData['iProduct'];
            $aProducts[$aData['iProduct']]['sName']    = $aData['sName'];
          }
          $aProducts[$aData['iProduct']]['iQuantity'] += $aData['iQuantity'];
          $aProducts[$aData['iProduct']]['fSummary']  += ( $aData['iQuantity'] * $aData['fPrice'] );
        }
      }
      $i++;
    } // end while
    fclose( $rFile );

    if( isset( $aProducts ) ){
      rsort( $aProducts );
      $iCount = count( $aProducts );
      if( $iCount > 50 )
        $iCount = 50;
      $content= null;
      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $aProducts[$i];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sName'] = isset( $oProduct->aProducts[$aData['iProduct']] ) ? $oProduct->aProducts[$aData['iProduct']]['sName'] : $aData['sName'];
        $aData['fSummary'] = normalizePrice( $aData['fSummary'] );
        $aData['sSummary'] = displayPrice( $aData['fSummary'] );
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'ORDERS_PRODUCTS_LIST' );
      } // end for

      if( isset( $content ) )
        return $oTpl->tbHtml( $sFile, 'ORDERS_PRODUCTS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'ORDERS_PRODUCTS_FOOT' );
    }
  }
} // end function listOrderedProductsStats

/**
* Generate orders array
* @return array
* @param string $sDateFrom
* @param string $sDateTo
* @param int    $iStatus1
* @param int    $iStatus2
* @param string $sLang
*/
function returnOrdersStats( $sDateFrom, $sDateTo, $iStatus1, $iStatus2, $sLang = null ){
  $i = 0;
  $iTimeStart = dateToTime( $sDateFrom, 'ymd' );
  $iTimeStop  = dateToTime( $sDateTo.' 23:59:59', 'ymd' );

  $rFile = fopen( DB_ORDERS, 'r' );
  while( ( $aFile = fgetcsv( $rFile, 700, '$' ) ) !== FALSE ){
    if( $i > 0 ){
      $aData = orders( $aFile );
      if( $aData['iStatus'] >= $iStatus1 && $aData['iStatus'] <= $iStatus2 && $aData['iTime'] >= $iTimeStart && $aData['iTime'] <= $iTimeStop ){
        if( ( isset( $sLang ) && $sLang == $aData['sLanguage'] ) || !isset( $sLang ) )
          $aReturn[$aData['iOrder']] = true;
      }
    }
    $i++;
  }
  fclose( $rFile );
  if( isset( $aReturn ) )
    return $aReturn;
} // end function returnOrdersStats

/**
* Delete all pages stats
* @return void
*/
function deleteAllPagesStats( ){
  $rF = fopen( DB_PAGES_STATS, 'w' );
  fwrite( $rF, '<?php exit; ?>'."\n" );
  fclose( $rF );
} // end function deleteAllPagesStats

/**
* Delete all products stats
* @return void
*/
function deleteAllProductsStats( ){
  $rF = fopen( DB_PRODUCTS_STATS, 'w' );
  fwrite( $rF, '<?php exit; ?>'."\n" );
  fclose( $rF );
} // end function deleteAllProductsStats

/**
* Delete all searched words
* @return void
*/
function deleteAllSearchWords( ){
  $rF = fopen( DB_SEARCHED_WORDS, 'w' );
  fwrite( $rF, '<?php exit; ?>'."\n" );
  fclose( $rF );
} // end function deleteAllSearchWords

?>