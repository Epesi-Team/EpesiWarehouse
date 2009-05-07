<?php
class OrdersAdmin extends Orders
{

  /**
  * Generate cache variables
  * @return void
  */
  function generateCache( ){

    if( !is_file( DB_ORDERS ) )
      return null;

    $aFile      = file( DB_ORDERS );
    $iCount     = count( $aFile );

    $this->aOrders = null;

    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      $this->aOrders[$aExp[0]] = orders( $aExp );
      $this->aOrders[$aExp[0]]['sDate'] = displayDate( $this->aOrders[$aExp[0]]['iTime'] );
      $this->aOrders[$aExp[0]]['sStatus'] = $this->throwStatus( $this->aOrders[$aExp[0]]['iStatus'] );
    } // end for
  } // end function generateCache

  /**
  * Return orders list
  * @return string
  * @param string $sFile
  * @param int    $iList
  */
  function listOrdersAdmin( $sFile, $iList = null ){
    if( !isset( $this->aOrders ) )
      $this->generateCache( );
    $oTpl   =& TplParser::getInstance( );
    $oFile  =& Files::getInstance( );
    $content= null;

    if( ( isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] ) ) || ( isset( $_GET['iStatus'] ) && is_numeric( $_GET['iStatus'] ) ) ){
      $aOrders = $this->generateOrdersSearchListArray( $GLOBALS['sPhrase'], $GLOBALS['iStatus'] );
    }
    else{
      if( isset( $this->aOrders ) ){
        foreach( $this->aOrders as $iOrder => $aData ){
          $aOrders[] = $iOrder;
        } // end foreach
      }
    }

    if( isset( $aOrders ) ){
      $iCount    = count( $aOrders );
      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['admin_list'];
      }

      $iOrders = ceil( $iCount / $iList );
      $iPageNumber = isset( $_GET['iPage'] ) ? $_GET['iPage'] : 1;
      if( !isset( $iPageNumber ) || !is_numeric( $iPageNumber ) || $iPageNumber < 1 )
        $iPageNumber = 1;
      if( $iPageNumber > $iOrders )
        $iPageNumber = $iOrders;

      $iEnd   = $iPageNumber * $iList;
      $iStart = $iEnd - $iList;

      if( $iEnd > $iCount )
        $iEnd = $iCount;
      $this->mData = null;

      for( $i = $iStart; $i < $iEnd; $i++ ){
        $aData = $this->aOrders[$aOrders[$i]];

        if( $aData['iStatus'] == 1 )
          $aData['sStatus'] = '<b>'.$aData['sStatus'].'</b>';

        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      } // end for

      if( isset( $content ) ){
        $aData['sPages'] = countPagesClassic( $iCount, $iList, $iPageNumber, changeUri( $_SERVER['REQUEST_URI'] ) );
        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
      }
    }    
  } // end function listOrdersAdmin
  
  /**
  * Save orders status
  * @return void
  * @param array  $aForm
  */
  function saveOrders( $aForm ){
    if( isset( $aForm['aStatus'] ) && is_array( $aForm['aStatus'] ) && $aForm['iStatus'] > 0 ){
      if( !isset( $this->aOrders ) )
        $this->generateCache( );

      foreach( $aForm['aStatus'] as $iOrder => $sValue ){
        if( $this->aOrders[$iOrder]['iStatus'] != $aForm['iStatus'] ){
          $aChange[$iOrder] = $aForm['iStatus'];
        }
      }
    }

    if( isset( $aChange ) ){
      $oFF    =& FlatFiles::getInstance( );
      $iCount = count( $aChange );
      $i      = 1;
      $iTime  = time( );

      foreach( $aChange as $iOrder => $iStatus ){
        $aSave = $this->aOrders[$iOrder];
        $aSave['iStatus'] = $iStatus;

        if( $i == $iCount ){
          $oFF->save( DB_ORDERS, $aSave, 'iOrder', 'rsort' );
          $oFF->save( DB_ORDERS_STATUS, Array( 'iOrder' => $iOrder, 'iStatus' => $iStatus, 'iTime' => $iTime ), null, 'rsort' );
        }
        else{
          $oFF->save( DB_ORDERS, $aSave, 'iOrder' );
          $oFF->save( DB_ORDERS_STATUS, Array( 'iOrder' => $iOrder, 'iStatus' => $iStatus, 'iTime' => $iTime ), null );
        }
        
        $i++;
      } // end foreach

      $this->generateCache( );
    }
  } // end function saveOrders

  /**
  * Return array with orders
  * @return array
  * @param string $sPhrase
  * @param int    $iStatus
  */
  function generateOrdersSearchListArray( $sPhrase, $iStatus ){
    if( isset( $this->aOrders ) ){
      if( isset( $iStatus ) && !is_numeric( $iStatus ) )
        $iStatus = null;

      if( !empty( $sPhrase ) ){
        $aExp   = explode( ' ', $sPhrase );
        $iCount = count( $aExp );
        for( $i = 0; $i < $iCount; $i++ ){
          $aExp[$i] = trim( $aExp[$i] );
          if( !empty( $aExp[$i] ) )
            $aWords[] = $aExp[$i];
        } // end for

        $iCount = count( $aWords );
      }
      foreach( $this->aOrders as $iOrder => $aData ){
        $bFound = true;

        if( isset( $iStatus ) && $iStatus != $aData['iStatus'] ){
          $bFound = null;
        }

        if( isset( $bFound ) && isset( $aWords ) ){
          $iFound = 0;

          for( $i = 0; $i < $iCount; $i++ ){
            if( stristr( implode( ' ', $aData ), $aWords[$i] ) )
              $iFound++;
          } // end for

          if( $iFound != $iCount ){
            $bFound = null;
            $aNotFound[$iOrder] = true;
          }
        }

        if( isset( $bFound ) ){
          $aFound[$iOrder] = true;
        }
      }

      if( isset( $aNotFound ) && isset( $aWords ) && isset( $_GET['iProducts'] ) ){
        $rFile = fopen( DB_ORDERS_PRODUCTS, 'r' );
        $i2    = 0;
        while( ( $aFile = fgetcsv( $rFile, 200000, '$' ) ) !== FALSE ){
          if( $i2 >= 1 && isset( $aNotFound[$aFile[1]] ) ){
            $iFound = 0;

            for( $i = 0; $i < $iCount; $i++ ){
              if( stristr( implode( ' ', $aFile ), $aWords[$i] ) )
                $iFound++;
            } // end for

            if( $iFound == $iCount )
              $aFound[$aFile[1]] = true;
          }
          $i2++;
        } // end while
        fclose( $rFile );
      }

      if( isset( $aFound ) ){
        foreach( $this->aOrders as $iOrder => $aData ){
          if( isset( $aFound[$iOrder] ) )
            $aReturn[] = $iOrder;
        } // end foreach   
        return $aReturn;
      }
    }  
  } // end function generateOrdersSearchListArray

  /**
  * Delete order
  * @return void
  * @param int  $iOrder
  */
  function deleteOrder( $iOrder ){
    $oFF =& FlatFiles::getInstance( );
    $oFF->deleteInFile( DB_ORDERS, $iOrder, 'iOrder' );
    $oFF->deleteInFile( DB_ORDERS_STATUS, $iOrder, 'iOrder' );
    $oFF->deleteInFile( DB_ORDERS_PRODUCTS, $iOrder, 'iOrder' );
    $oFF->deleteInFile( DB_ORDERS_COMMENTS, $iOrder, 'iOrder' );
  } // end function deleteOrder

  /**
  * Save order data
  * @return void
  * @param array  $aForm
  */
  function saveOrder( $aForm ){
    $oFF =& FlatFiles::getInstance( );
    $aForm = changeMassTxt( $aForm, 'H', Array( 'sComment', 'LenHNds' ) );

    if( $aForm['iStatus'] != $this->aOrders[$aForm['iOrder']]['iStatus'] ){
      $oFF->save( DB_ORDERS_STATUS, Array( 'iOrder' => $aForm['iOrder'], 'iStatus' => $aForm['iStatus'], 'iTime' => time( ) ), null, 'rsort' );
    }
    
    if( $aForm['sComment'] != $this->aOrders[$aForm['iOrder']]['sComment'] ){
      $oFF->save( DB_ORDERS_COMMENTS, $aForm, 'iOrder' );
    }

    $aForm = array_merge( $this->aOrders[$aForm['iOrder']], $aForm );
    $oFF->save( DB_ORDERS, $aForm, 'iOrder', null );

    $this->generateProducts( $aForm['iOrder'] );
    if( isset( $this->aProducts ) ){
      foreach( $this->aProducts as $iElement => $aData ){
        if( isset( $aForm['aProductsDelete'][$iElement] ) ){
          $oFF->deleteInFile( DB_ORDERS_PRODUCTS, $iElement, 'iElement' );
          $aForm['aProducts'][$iElement] = null;
        }

        if( isset( $aForm['aProducts'][$iElement] ) && ( $aForm['aProducts'][$iElement]['sName'] != $aData['sName'] || $aForm['aProducts'][$iElement]['fPrice'] != $aData['fPrice'] || $aForm['aProducts'][$iElement]['iQuantity'] != $aData['iQuantity'] ) ){
          $aSave = array_merge( $aData, $aForm['aProducts'][$iElement] );
          $aSave = changeMassTxt( $aSave, 'H' );
          $oFF->save( DB_ORDERS_PRODUCTS, $aSave, 'iElement', null );
        }
      } // end foreach
    }
    
    if( !empty( $aForm['aNewProduct']['iProduct'] ) && !empty( $aForm['aNewProduct']['sName'] ) && !empty( $aForm['aNewProduct']['fPrice'] ) && !empty( $aForm['aNewProduct']['iQuantity'] ) ){
      $aForm['aNewProduct']['fPrice'] = normalizePrice( $aForm['aNewProduct']['fPrice'] );
      $aForm['aNewProduct']['sName'] = trim( $aForm['aNewProduct']['sName'] );
      $aForm['aNewProduct']['iOrder'] = $aForm['iOrder'];
      $aForm['aNewProduct']['iElement'] = $oFF->throwLastId( DB_ORDERS_PRODUCTS, 'iElement' ) + 1;
      $aForm['aNewProduct'] = changeMassTxt( $aForm['aNewProduct'], 'H' );
      $oFF->save( DB_ORDERS_PRODUCTS, $aForm['aNewProduct'] );
    }
  } // end function saveOrder

  /**
  * List all payment methods
  * @return string
  * @param string $sFile
  */
  function listPaymentsAdmin( $sFile ){
    $oTpl =& TplParser::getInstance( );
    $oFF  =& FlatFiles::getInstance( );
    $content = null;
    $aData = $oFF->throwFileArray( DB_PAYMENTS );
    if( isset( $aData ) ){
      $iCount = count( $aData );
      for( $i = 0; $i < $iCount; $i++ ){
        $aData[$i]['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData[$i]['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $oTpl->setVariables( 'aData', $aData[$i] );
        $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      } // end for
      return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
    }
  } // end function listPaymentsAdmin

  /**
  * Delete payment data
  * @return void
  * @param int  $iPayment
  */
  function deletePayment( $iPayment ){
    $oFF =& FlatFiles::getInstance( );
    $oFF->deleteInFile( DB_PAYMENTS, $iPayment, 'iPayment' );
    $oFF->deleteInFile( DB_CARRIERS_PAYMENTS, $iPayment, 'iPayment' );
  } // end function deletePayment

  /**
  * Save payment data
  * @return void
  * @param array $aForm
  */
  function savePayment( $aForm ){
    $oFF =& FlatFiles::getInstance( );
    if( isset( $aForm['iPayment'] ) && is_numeric( $aForm['iPayment'] ) ){
      $sParam = 'iPayment';
      $oFF->deleteInFile( DB_CARRIERS_PAYMENTS, $aForm['iPayment'], 'iPayment' );
    }
    else{
      $sParam = null;
      $aForm['iPayment'] = $oFF->throwLastId( DB_PAYMENTS, 'iPayment' ) + 1;
    }

    $oFF->save( DB_PAYMENTS, changeMassTxt( $aForm, '' ), $sParam );

    if( isset( $aForm['aCarriers'] ) ){
      foreach( $aForm['aCarriers'] as $iCarrier => $iValue ){
        $sPrice = isset( $aForm['aCarriersPrices'][$iCarrier] ) ? $aForm['aCarriersPrices'][$iCarrier] : null;
        #if( !is_numeric( ereg_replace( '%', '', $sPrice ) ) )
        #  $sPrice = null;
        $oFF->save( DB_CARRIERS_PAYMENTS, changeMassTxt( Array( 'iPayment' => $aForm['iPayment'], 'sPrice' => $sPrice, 'iCarrier' => $iCarrier ), '' ) );
      }
    }

    return $aForm['iPayment'];
  } // end function savePayment

  /**
  * List all carriers methods
  * @return string
  * @param string $sFile
  */
  function listCarriersAdmin( $sFile ){
    $oTpl =& TplParser::getInstance( );
    $oFF =& FlatFiles::getInstance( );
    $content = null;
    $aData = $oFF->throwFileArray( DB_CARRIERS );
    if( isset( $aData ) ){
      $iCount = count( $aData );
      for( $i = 0; $i < $iCount; $i++ ){
        $aData[$i]['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData[$i]['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData[$i]['sPrice'] = displayPrice( $aData[$i]['fPrice'] );
        $oTpl->setVariables( 'aData', $aData[$i] );
        $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      } // end for
      return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
    }
  } // end function listCarriersAdmin

  /**
  * Delete carrier data
  * @return void
  * @param int  $iCarrier
  */
  function deleteCarrier( $iCarrier ){
    $oFF =& FlatFiles::getInstance( );
    $oFF->deleteInFile( DB_CARRIERS, $iCarrier, 'iCarrier' );
    $oFF->deleteInFile( DB_CARRIERS_PAYMENTS, $iCarrier, 'iCarrier' );
  } // end function deleteCarrier

  /**
  * Save carrier data
  * @return void
  * @param array $aForm
  */
  function saveCarrier( $aForm ){
    $oFF =& FlatFiles::getInstance( );
    if( isset( $aForm['iCarrier'] ) && is_numeric( $aForm['iCarrier'] ) ){
      $sParam = 'iCarrier';
      $oFF->deleteInFile( DB_CARRIERS_PAYMENTS, $aForm['iCarrier'], 'iCarrier' );
    }
    else{
      $sParam = null;
      $aForm['iCarrier'] = $oFF->throwLastId( DB_CARRIERS, 'iCarrier' ) + 1;
    }

    $oFF->save( DB_CARRIERS, changeMassTxt( $aForm, '' ), $sParam );

    if( isset( $aForm['aPayments'] ) ){
      foreach( $aForm['aPayments'] as $iPayment => $iValue ){
        $sPrice = isset( $aForm['aPaymentsPrices'][$iPayment] ) ? $aForm['aPaymentsPrices'][$iPayment] : null;
        #if( !is_numeric( ereg_replace( '%', '', $sPrice ) ) )
        #  $sPrice = null;
        $oFF->save( DB_CARRIERS_PAYMENTS, changeMassTxt( Array( 'iPayment' => $iPayment, 'sPrice' => $sPrice, 'iCarrier' => $aForm['iCarrier'] ), '' ) );
      }
    }

    return $aForm['iCarrier'];
  } // end function saveCarrier

  /**
  * Return list of carrier payments
  * @return string
  * @param string $sFile
  * @param int    $iCarrier
  */
  function listPaymentsCarriersAdmin( $sFile, $iCarrier = null ){
    $oTpl =& TplParser::getInstance( );
    $oFF =& FlatFiles::getInstance( );
    $content = null;

    $aData = $oFF->throwFileArray( DB_PAYMENTS );
    if( isset( $aData ) ){
      if( isset( $iCarrier ) ){
        $aPaymentCarriers = $oFF->throwFileArray( DB_CARRIERS_PAYMENTS );
        $iCount = count( $aPaymentCarriers );
        for( $i = 0; $i < $iCount; $i++ ){
          if( $aPaymentCarriers[$i]['iCarrier'] == $iCarrier ){
            $aPayments[$aPaymentCarriers[$i]['iPayment']] = $aPaymentCarriers[$i]['sPrice'];
          }
        } // end for
      }

      $iCount = count( $aData );
      for( $i = 0; $i < $iCount; $i++ ){
        
        if( isset( $aPayments[$aData[$i]['iPayment']] ) ){
          $aData[$i]['sPrice'] = $aPayments[$aData[$i]['iPayment']];
          $aData[$i]['sChecked'] = 'checked="checked"';
          $aData[$i]['sDisable'] = null;
        }
        else{
          $aData[$i]['sPrice'] = null;
          $aData[$i]['sChecked'] = null;
          $aData[$i]['sDisable'] = ' inputrd';
        }

        $oTpl->setVariables( 'aData', $aData[$i] );
        $content .= $oTpl->tbHtml( $sFile, 'PAYMENT_LIST' );
      } // end for
      return $oTpl->tbHtml( $sFile, 'PAYMENT_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'PAYMENT_FOOT' );
    }
  } // end function listPaymentsCarriersAdmin

  /**
  * Return list of payments carrier
  * @return string
  * @param string $sFile
  * @param int    $iPayment
  */
  function listCarriersPaymentsAdmin( $sFile, $iPayment = null ){
    $oTpl =& TplParser::getInstance( );
    $oFF =& FlatFiles::getInstance( );
    $content = null;

    $aData = $oFF->throwFileArray( DB_CARRIERS );
    if( isset( $aData ) ){
      if( isset( $iPayment ) ){
        $aPaymentCarriers = $oFF->throwFileArray( DB_CARRIERS_PAYMENTS );
        $iCount = count( $aPaymentCarriers );
        for( $i = 0; $i < $iCount; $i++ ){
          if( $aPaymentCarriers[$i]['iPayment'] == $iPayment ){
            $aCarriers[$aPaymentCarriers[$i]['iCarrier']] = $aPaymentCarriers[$i]['sPrice'];
          }
        } // end for
      }

      $iCount = count( $aData );
      for( $i = 0; $i < $iCount; $i++ ){

        if( isset( $aCarriers[$aData[$i]['iCarrier']] ) ){
          $aData[$i]['sPrice'] = $aCarriers[$aData[$i]['iCarrier']];
          $aData[$i]['sChecked'] = 'checked="checked"';
          $aData[$i]['sDisable'] = null;
        }
        else{
          $aData[$i]['sPrice'] = null;
          $aData[$i]['sChecked'] = null;
          $aData[$i]['sDisable'] = ' inputrd';
        }

        $oTpl->setVariables( 'aData', $aData[$i] );
        $content .= $oTpl->tbHtml( $sFile, 'CARRIER_LIST' );
      } // end for
      return $oTpl->tbHtml( $sFile, 'CARRIER_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'CARRIER_FOOT' );
    }
  } // end function listPaymentsCarriersAdmin
};
?>