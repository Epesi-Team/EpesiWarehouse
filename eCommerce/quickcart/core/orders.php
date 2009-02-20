<?php
class Orders
{

  var $aOrders = null;
  var $aProducts = null;

  /**
  * List products in basket
  * @return string
  * @param string $sFile
  * @param int    $iId
  * @param string $sBlock
  */
  function listProducts( $sFile, $iId = null, $sBlock = null ){
    $oTpl =& TplParser::getInstance( );
    $content = null;

    if( !isset( $this->aProducts ) ){
      if( !isset( $iId ) ){
        $this->generateBasket( );
      }
      else{
        $this->generateProducts( $iId );
      }
    }

    if( !isset( $sBlock ) )
      $sBlock = 'BASKET_';

    if( isset( $this->aProducts ) ){
      $i = 0;
      $iCount = count( $this->aProducts );
      foreach( $this->aProducts as $aData ){
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['sSummary'] = displayPrice( normalizePrice( $aData['fSummary'] ) );
        $aData['sPrice'] = displayPrice( $aData['fPrice'] );
        $aData['sLinkDelete'] = defined( 'CUSTOMER_PAGE' ) ? $GLOBALS['aData']['sLinkName'].'&amp;iProductDelete='.$aData['iProduct'] : null;
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, $sBlock.'LIST' );
        $i++;
      }

      $aData['fProductsSummary'] = normalizePrice( $this->fProductsSummary );
      $aData['sProductsSummary'] = displayPrice( $aData['fProductsSummary'] );
      if( isset( $iId ) && isset( $this->aOrders[$iId] ) ){
        $this->aOrders[$iId]['fProductsSummary'] = $aData['fProductsSummary'];
        if( !empty( $this->aOrders[$iId]['fPaymentCarrierPrice'] ) ){
          $this->aOrders[$iId]['fOrderSummary'] = $aData['fOrderSummary'] = normalizePrice( $aData['fProductsSummary'] +  $this->aOrders[$iId]['fPaymentCarrierPrice'] );
          $this->aOrders[$iId]['sOrderSummary'] = $aData['sOrderSummary'] = displayPrice( $aData['fOrderSummary'] );
        }
      }
      $oTpl->setVariables( 'aData', $aData );
      return $oTpl->tbHtml( $sFile, $sBlock.'HEAD' ).$content.$oTpl->tbHtml( $sFile, $sBlock.'FOOT' );
    }
  } // end function listProducts

  /**
  * Generates variable with products in basket
  * @return void
  */
  function generateBasket( ){
  	//{ epesi
	    $this->aProducts = null;
    	$this->fProductsSummary   = null;
	    $_SESSION['iOrderQuantity'.LANGUAGE]  = 0;
    	$_SESSION['fOrderSummary'.LANGUAGE]   = null;
		
		$ret = DB::Execute('SELECT * FROM premium_ecommerce_orders_temp WHERE customer=%s',array($_SESSION['iCustomer'.LANGUAGE]));
		while($row = $ret->FetchRow()) {
	        $this->aProducts[$row['product']] = Array( 'iCustomer' => $row['customer'], 'iProduct' => $row['product'], 'iQuantity' => $row['quantity'], 'fPrice' => $row['price'], 'sName' => $row['name'], 'tax'=>$row['tax'] );
	        $this->aProducts[$row['product']]['sLinkName'] = '?'.$row['product'].','.change2Url( $this->aProducts[$row['product']]['sName'] );
	        $this->aProducts[$row['product']]['fSummary'] = normalizePrice( $this->aProducts[$row['product']]['fPrice'] * $this->aProducts[$row['product']]['iQuantity']);
	        $_SESSION['iOrderQuantity'.LANGUAGE] += $row['quantity'];
    	    $_SESSION['fOrderSummary'.LANGUAGE]  += ( $row['quantity'] * $row['price'] );
		}
	    if( isset( $_SESSION['fOrderSummary'.LANGUAGE] ) )
    		$this->fProductsSummary = $_SESSION['fOrderSummary'.LANGUAGE] = normalizePrice( $_SESSION['fOrderSummary'.LANGUAGE] );
	//} epesi
  
/*
    $aFile      = file( DB_ORDERS_TEMP );
    $iCount     = count( $aFile );
    $this->aProducts = null;
    $this->fProductsSummary   = null;
    $_SESSION['iOrderQuantity'.LANGUAGE]  = 0;
    $_SESSION['fOrderSummary'.LANGUAGE]   = null;

    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      if( isset( $aExp[0] ) && $aExp[0] == $_SESSION['iCustomer'.LANGUAGE] ){
        $this->aProducts[$aExp[1]] = orders_temp( $aExp );
        $this->aProducts[$aExp[1]]['sLinkName'] = '?'.$aExp[1].','.change2Url( $this->aProducts[$aExp[1]]['sName'] );
        $this->aProducts[$aExp[1]]['fSummary'] = normalizePrice( $this->aProducts[$aExp[1]]['fPrice'] * $this->aProducts[$aExp[1]]['iQuantity'] );
        $_SESSION['iOrderQuantity'.LANGUAGE] += $aExp[2];
        $_SESSION['fOrderSummary'.LANGUAGE]  += ( $aExp[2] * $aExp[3] );
      }
    } // end for
    if( isset( $_SESSION['fOrderSummary'.LANGUAGE] ) )
      $this->fProductsSummary = $_SESSION['fOrderSummary'.LANGUAGE] = normalizePrice( $_SESSION['fOrderSummary'.LANGUAGE] );*/
  } // end function generateBasket

  /**
  * Generates variable with products in order
  * @return void
  * @param int  $iOrder
  */
  function generateProducts( $iOrder ){
    $aFile  = file( DB_ORDERS_PRODUCTS );
    $iCount = count( $aFile );
    $this->fProductsSummary = null;
    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      if( isset( $aExp[1] ) && $aExp[1] == $iOrder ){
        $this->aProducts[$aExp[0]] = orders_products( $aExp );
        $this->aProducts[$aExp[0]]['fSummary'] = normalizePrice( $this->aProducts[$aExp[0]]['fPrice'] * $this->aProducts[$aExp[0]]['iQuantity'] );
        $this->fProductsSummary += $this->aProducts[$aExp[0]]['fPrice'] * $this->aProducts[$aExp[0]]['iQuantity'];
      }
    } // end for

    if( isset( $this->fProductsSummary ) ){
      $this->fProductsSummary = normalizePrice( $this->fProductsSummary );
    }
  } // end function generateProducts

  /**
  * Check basket is empty or not
  * @return bool
  */
  function checkEmptyBasket( ){
    $this->generateBasket( );
    return ( isset( $this->aProducts ) ) ? false : true;
  } // end function checkEmptyBasket

  /**
  * Save basket. //This is indeed basket quantity update!
  * @return void
  * @param array $aForm
  */
  function saveBasket( $aForm ){
    if( isset( $aForm['aProducts'] ) && is_array( $aForm['aProducts'] ) ){
		//{ epesi
		$qty = DB::GetAssoc('SELECT product,quantity FROM premium_ecommerce_orders_temp WHERE customer=%s',array($_SESSION['iCustomer'.LANGUAGE]));
		foreach($qty as $p=>$q) {
			if(isset( $aForm['aProducts'][$p] ) && is_numeric( $aForm['aProducts'][$p] ) && $aForm['aProducts'][$p] > 0 && $aForm['aProducts'][$p] < 10000 && $q!=$aForm['aProducts'][$p]) {
				DB::Execute('UPDATE premium_ecommerce_orders_temp SET quantity=%d WHERE customer=%s AND product=%d',array($aForm['aProducts'][$p],$_SESSION['iCustomer'.LANGUAGE],$p));
			}
		}
		//} epesi
	/*
      $aFile  = file( DB_ORDERS_TEMP );
      $iCount = count( $aFile );
      $rFile = fopen( DB_ORDERS_TEMP, 'w' );
      flock( $rFile, LOCK_EX );
      for( $i = 0; $i < $iCount; $i++ ){
        if( $i > 0 ){
          $aFile[$i]  = rtrim( $aFile[$i] );
          $aExp       = explode( '$', $aFile[$i] );
          if( isset( $aForm['aProducts'][$aExp[1]] ) && is_numeric( $aForm['aProducts'][$aExp[1]] ) && $aForm['aProducts'][$aExp[1]] > 0 && $aForm['aProducts'][$aExp[1]] < 10000 && $aExp[0] == $_SESSION['iCustomer'.LANGUAGE] ){
            $aExp[2] = (int) $aForm['aProducts'][$aExp[1]];
            $aFile[$i] = trim( implode( '$', $aExp ) )."\n";
          }
          else
            $aFile[$i] .= "\n";
        }
        else{
          $aFile[$i] = '<?php exit; ?>'."\n";
        }

        fwrite( $rFile, $aFile[$i] );
      } // end for
      flock( $rFile, LOCK_UN );
      fclose( $rFile );*/
    }
  } // end function saveBasket

  /**
  * Delete product from basket
  * @return void
  * @param int  $iProduct
  * @param int  $iOrder
  */
  function deleteFromBasket( $iProduct, $iOrder = null ){
  	//{ epesi
    if( !isset( $iOrder ) )
    	 $iOrder = $_SESSION['iCustomer'.LANGUAGE];
	DB::Execute('DELETE FROM premium_ecommerce_orders_temp WHERE product=%d AND customer=%s',array($iProduct,$iOrder));
	//} epesi
	
/*    if( !isset( $iOrder ) ){
      $iOrder = $_SESSION['iCustomer'.LANGUAGE];
      $sDb    = DB_ORDERS_TEMP;
    }
    $aFile  = file( $sDb );
    $iCount = count( $aFile );
    $rFile = fopen( $sDb, 'w' );
    flock( $rFile, LOCK_EX );
    for( $i = 0; $i < $iCount; $i++ ){
      if( $i > 0 ){
        $aFile[$i]  = rtrim( $aFile[$i] );
        $aExp       = explode( '$', $aFile[$i] );
        if( $aExp[1] == $iProduct && $aExp[0] == $iOrder ){
          $aFile[$i] = null;
        }
        else
          $aFile[$i] .= "\n";
      }
      else{
        $aFile[$i] = '<?php exit; ?>'."\n";
      }

      fwrite( $rFile, $aFile[$i] );
    } // end for
    flock( $rFile, LOCK_UN );
    fclose( $rFile );  */
  } // end function deleteFromBasket

  /**
  * Add product to basket
  * @return void
  * @param int  $iProduct
  * @param int  $iQuantity
  * @param int  $iOrder
  */
  function addToBasket( $iProduct, $iQuantity, $iOrder = null ){
  	//{ epesi
    if( !isset( $iOrder ) )
    	 $iOrder = $_SESSION['iCustomer'.LANGUAGE];

    $iQuantity = (int) $iQuantity;

	// delete empty orders older then 72 hours
	DB::Execute('DELETE FROM premium_ecommerce_orders_temp WHERE created_on < %d',array(time()-259200));
	
	$old_q = DB::GetOne('SELECT quantity FROM premium_ecommerce_orders_temp WHERE product=%d AND customer=%s',array($iProduct,$iOrder));
	if($old_q) {
		$iQuantity+=$old_q;
		DB::Execute('UPDATE premium_ecommerce_orders_temp SET quantity=%d WHERE product=%d AND customer=%s',array($iQuantity,$iProduct,$iOrder));
	} else {
		$oProduct =& Products::getInstance( );
		DB::Execute('INSERT INTO premium_ecommerce_orders_temp(customer,product,quantity,price,name,tax) VALUES (%s,%d,%d,%s,%s,%s)',array($iOrder,$iProduct,$iQuantity,$oProduct->aProducts[$iProduct]['fPrice'],$oProduct->aProducts[$iProduct]['sName'],$oProduct->aProducts[$iProduct]['tax']));
	}
	//} epesi
	
/*    if( !isset( $iOrder ) ){
      $iOrder = $_SESSION['iCustomer'.LANGUAGE];
      $sDb    = DB_ORDERS_TEMP;
    }

    $iQuantity = (int) $iQuantity;
    $aFile  = file( $sDb );
    $iCount = count( $aFile );
    $rFile = fopen( $sDb, 'w' );
    $iTime = time( );
    flock( $rFile, LOCK_EX );
    for( $i = 0; $i < $iCount; $i++ ){
      if( $i > 0 ){
        $aFile[$i]  = rtrim( $aFile[$i] );
        $aExp       = explode( '$', $aFile[$i] );
        if( $aExp[0] == $iOrder ){
          if( $aExp[1] == $iProduct ){
            if( ( $aExp[2] + $iQuantity ) < 10000 )
              $aExp[2] += (int) $iQuantity;
            $aFile[$i] = trim( implode( '$', $aExp ) )."\n";
            $bFound = true;
          }
          else{
            $aFile[$i] .= "\n";
          }
        }
        else{
          if( $iTime - substr( $aExp[0], 0, 10 ) >= 259200 ) // delete empty orders older then 72 hours
            $aFile[$i] = null;
          else
            $aFile[$i] .= "\n";
        }
      }
      else{
        $aFile[$i] = '<?php exit; ?>'."\n";
      }

      fwrite( $rFile, $aFile[$i] );
    } // end for

    if( !isset( $bFound ) ){
      $oProduct =& Products::getInstance( );

      fwrite( $rFile, $iOrder.'$'.$iProduct.'$'.$iQuantity.'$'.$oProduct->aProducts[$iProduct]['fPrice'].'$'.$oProduct->aProducts[$iProduct]['sName'].'$'."\n" );
    }
    flock( $rFile, LOCK_UN );
    fclose( $rFile );*/
  } // end function addToBasket

  /**
  * Check order fields
  * @return bool
  * @param array  $aForm
  */
  function checkFields( $aForm ){
    if( isset( $aForm['sPaymentCarrier'] ) ){
      $aExp = explode( ';', $aForm['sPaymentCarrier'] );
      if( isset( $aExp[0] ) && isset( $aExp[1] ) )
        $sPrice = $this->throwPaymentCarrierPrice( $aExp[0], $aExp[1] );
    }
    else{
      return false;
    }

    if(
      throwStrLen( $aForm['sFirstName'] ) > 1
      && throwStrLen( $aForm['sLastName'] ) > 2
      && throwStrLen( $aForm['sStreet'] ) > 1
      && throwStrLen( $aForm['sZipCode'] ) > 2
      && throwStrLen( $aForm['sCity'] ) > 2
      && throwStrLen( $aForm['sPhone'] ) > 2
      && checkEmail( $aForm['sEmail'] )
      && isset( $sPrice )
      && ( ( isset( $aForm['iRules'] ) && isset( $aForm['iRulesAccept'] ) ) || !isset( $aForm['iRules'] ) )
    )
      return true;
    else
      return false;
  } // end function checkFields

  /**
  * Add order to database
  * @return int
  * @param array  $aForm
  */
  function addOrder( $aForm ){
/*    $oFF  =& FlatFiles::getInstance( );
    $aForm = changeMassTxt( $aForm, 'H', Array( 'sComment', 'LenHNds' ) );
    $aForm['iOrder'] = $oFF->throwLastId( DB_ORDERS, 'iOrder' ) + 1;
    $aForm['iTime'] = time( );
    $aForm['sIp'] = $_SERVER['REMOTE_ADDR'];
    $aForm['iStatus'] = 1;
    $aForm['sLanguage'] = LANGUAGE;

    $aExp = explode( ';', $aForm['sPaymentCarrier'] );
    $aCarrier = $this->throwCarrier( $aExp[0] );
    $aPayment = $this->throwPayment( $aExp[1] );

    $aForm['sCarrierName']  = $aCarrier['sName'];
    $aForm['fCarrierPrice'] = $aCarrier['fPrice'];
    $aForm['iCarrier']      = $aCarrier['iCarrier'];
    $aForm['sPaymentName']  = $aPayment['sName'];
    $aForm['iPayment']      = $aPayment['iPayment'];
    $aForm['sPaymentPrice'] = $this->throwPaymentCarrierPrice( $aExp[0], $aExp[1] );

    $oFF->save( DB_ORDERS, $aForm, null, 'rsort' );
    $oFF->save( DB_ORDERS_COMMENTS, $aForm );

    if( isset( $this->aProducts ) ){
      $iElement = $oFF->throwLastId( DB_ORDERS_PRODUCTS, 'iElement' ) + 1;
      foreach( $this->aProducts as $aData ){
        $oFF->save( DB_ORDERS_PRODUCTS, Array( 'iElement' => $iElement++, 'iOrder' => $aForm['iOrder'], 'iProduct' => $aData['iProduct'], 'iQuantity' => $aData['iQuantity'], 'fPrice' => $aData['fPrice'], 'sName' => $aData['sName'] ) );
      }
    }
*/
	//{ epesi
	/* 
	//'iOrder' => 0, 
	//'iStatus' => 2, 
	//'iTime' => 3, 
	'iCarrier' => 4, 
	'iPayment' => 5, 
	'sCarrierName' => 6, 
	'fCarrierPrice' => 7, 
	'sPaymentName' => 8, 
	'sPaymentPrice' => 9, 
	//'sFirstName' => 10, 
	//'sLastName' => 11, 
	//'sCompanyName' => 12, 
	//'sStreet' => 13, 
	//'sZipCode' => 14, 
	//'sCity' => 15, 
	//'sPhone' => 16, 
	//'sLanguage' => 1, 
	//'sEmail' => 17, 
	//'sIp' => 18 )*/
	global $config;
	$t = time();
	$memo = "Language: ".LANGUAGE."\ne-mail: ".$aForm['sEmail']."\nIp: ".$_SERVER['REMOTE_ADDR']."\nComment:\n".$aForm['sComment'];
	DB::Execute('INSERT INTO premium_warehouse_items_orders_data_1(f_transaction_type,f_transaction_date,f_status,
						f_company_name,f_last_name,f_first_name,f_address_1,f_city,f_postal_code,f_phone,f_country,f_zone,f_memo,created_on) VALUES 
						(1,%D,"-1",%s,%s,%s,%s,%s,%s,%s,"","",%s,%T)',array($t,$aForm['sCompanyName'],$aForm['sLastName'],$aForm['sFirstName'],$aForm['sStreet'],$aForm['sCity'],$aForm['sZipCode'],$aForm['sPhone'],$memo,$t));
	$id = DB::Insert_ID('premium_warehouse_items_orders_data_1','id');
	$trans_id = '#'.str_pad($id, 6, '0', STR_PAD_LEFT);
	DB::Execute('UPDATE premium_warehouse_items_orders_data_1 SET f_transaction_id=%s WHERE id=%d',array($trans_id,$id));


	$currency = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s',array($config['currency_symbol']));
	if($currency===false) 
		die('Currency not defined in Epesi: '.$config['currency_symbol']);

    if( isset( $this->aProducts ) ){
      foreach( $this->aProducts as $aData ){
	$net = $aData['fPrice']*100/(100+$aData['tax']);
        DB::Execute('INSERT INTO premium_warehouse_items_orders_details_data_1(f_transaction_id,f_item_name,f_quantity,f_description,f_gross_price,f_tax_rate,created_on,f_net_price) 
							VALUES (%s,%d,%d,%s,%s,%s,%T,%s)', array($id,$aData['iProduct'],$aData['iQuantity'],$aData['sName'],$aData['fPrice'].'__'.$currency,$aData['tax'],$t,$net.'__'.$currency));
      }
    }
	
  	DB::Execute('DELETE FROM premium_ecommerce_orders_temp WHERE customer=%s',array($_SESSION['iCustomer'.LANGUAGE]));
	//} epesi
//    $oFF->deleteInFile( DB_ORDERS_TEMP, $_SESSION['iCustomer'.LANGUAGE], 'iCustomer' );

    $_SESSION['iOrderQuantity'.LANGUAGE]  = 0;
    $_SESSION['fOrderSummary'.LANGUAGE]   = null;

    return $id;
  } // end function addOrder 

  /**
  * Return order status name
  * @return string
  * @param int    $iStatus
  */
  function throwStatus( $iStatus = null ){
    global $lang;
    $aStatus[1] = $lang['Orders_pending'];
    $aStatus[2] = $lang['Orders_processing'];
    $aStatus[3] = $lang['Orders_finished'];
    $aStatus[4] = $lang['Orders_canceled'];
    return isset( $iStatus ) ? $aStatus[$iStatus] : $aStatus;
  } // end function throwStatus

  /**
  * Return order data
  * @return array
  * @param int  $iOrder
  */
  function throwOrder( $iOrder ){
//    $oFF  =& FlatFiles::getInstance( );
    
    if( isset( $this->aOrders[$iOrder] ) ){
      return $this->aOrders[$iOrder];
    }else{
	//{ epesi
	$aData = DB::GetRow('SELECT id as iOrder, 
				    1 as iStatus, 
				    created_on as iTime,
				    "" as iCarrier,
				    "" as iPayment,
				    "" as sCarrierName,
				    "" as sPaymentName,
				    "" as sPaymentPrice,
				    f_first_name as sFirstName,
				    f_last_name as sLastName,
				    f_company_name as sCompanyName,
				    f_address_1 as sStreet,
				    f_postal_code as sZipCode,
				    f_city as sCity,
				    f_phone as sPhone,
				    f_memo as memo
				    FROM premium_warehouse_items_orders_data_1 WHERE id=%d',array($iOrder));
        if($aData) {
        	$aData['iTime'] = strtotime($aData['iTime']);
		if(ereg("^Language: ([a-zA-Z0-9]+)\ne-mail: ([a-zA-Z0-9@\.]+)\nIp: ([0-9\.]+)\nComment:\n([^$]*)",$aData['memo'],$reqs)) {
		    $aData['sLanguage'] = $reqs[1];
		    $aData['sEmail'] = $reqs[2];
		    $aData['sIp'] = $reqs[3];
		    $aData['sComment'] = $reqs[4];
		} else {
		    $aData['sLanguage'] = LANGUAGE; 
		    $aData['sComment'] = $_SERVER['REMOTE_ADDR'];
		}
	}
	//} epesi
//      $aData = $oFF->throwDataFromFiles( Array( DB_ORDERS, DB_ORDERS_COMMENTS ), $iOrder, 'iOrder' );
    }

    if( isset( $aData ) ){
      $aData['fPaymentCarrierPrice'] = generatePrice( $aData['fCarrierPrice'], $aData['sPaymentPrice'] );
      $aData['sPaymentCarrierPrice'] = displayPrice( $aData['fPaymentCarrierPrice'] );
      $aData['sDate'] = displayDate( $aData['iTime'] );
      $this->aOrders[$iOrder] = $aData;
      return $aData;
    }
  } // end function throwOrder

  /**
  * Return saved order
  * @return int
  * @param string $sOrder
  */
  function throwSavedOrderId( $sOrder ){
  	//{ epesi
	$ret = DB::GetOne('SELECT customer FROM premium_ecommerce_orders_temp WHERE md5(customer)=%s',array($sOrder));
	if($ret) return $ret;
	return null;
	//} epesi
	/*
    $aFile = file( DB_ORDERS_TEMP );
    $iCount= count( $aFile );
    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      if( $sOrder == md5( $aExp[0] ) )
        return $aExp[0];
    } // end for

    return null;*/
  } // end function throwSavedOrderId

  /**
  * Return status list
  * @return string
  * @param string $sFile
  * @param int    $iOrder
  */
  function listOrderStatuses( $sFile, $iOrder ){
    $aFile      = file( DB_ORDERS_STATUS );
    $iCount     = count( $aFile );
    $oTpl       =& TplParser::getInstance( );
    $content    = null;

    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      if( $aExp[0] == $iOrder ){
        $aData = orders_status( $aExp );
        $aData['sDate'] = displayDate( $aData['iTime'] );
        $aData['sStatus'] = $this->throwStatus( $aData['iStatus'] );
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'STATUS_LIST' );
      }
    } // end for

    if( isset( $content ) ){
      $oTpl->setVariables( 'aData', $aData );
      return $oTpl->tbHtml( $sFile, 'STATUS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'STATUS_FOOT' );
    }
  } // end function listOrderStatuses

  /**
  * Return payment and carrier price
  * @return string
  * @param int  $iCarrier
  * @param int  $iPayment
  */
  function throwPaymentCarrierPrice( $iCarrier, $iPayment ){
/*    $aFile = file( DB_CARRIERS_PAYMENTS );
    $iCount= count( $aFile );
    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      if( $aExp[0] == $iCarrier && $aExp[1] == $iPayment ){
        return $aExp[2];
      }
    }    */
    // { epesi
    $currency = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s',array($config['currency_symbol']));
    if($currency===false) 
	die('Currency not defined in Epesi: '.$config['currency_symbol']);

    return DB::GetOne('SELECT f_price FROM premium_ecommerce_payments_carriers WHERE f_payment=%d AND f_shipment=%d AND f_currency=%d',array($iPayment,$iCarrier,$currency));
    // } epesi
  } // end function throwPaymentCarrierPrice

  /**
  * Return list of payments and carriers
  * @return string
  * @param string $sFile
  */
  function listCarriersPayments( $sFile ){
    $oTpl       =& TplParser::getInstance( );
    $content    = null;
    $sPaymentList= null;
    $oFF        =& FlatFiles::getInstance( );

    $aPayments = $oFF->throwFileArraySmall( DB_PAYMENTS, 'iPayment', 'sName' );
    if( isset( $aPayments ) ){
      $aFile = file( DB_CARRIERS_PAYMENTS );
      $iCount= count( $aFile );
      for( $i = 1; $i < $iCount; $i++ ){
        $aExp = explode( '$', $aFile[$i] );
        $aPaymentsCarriers[$aExp[0]][$aExp[1]] = $aExp[2];
      }

      $sFunction  = LANGUAGE.'_carriers';
      $aFile      = file( DB_CARRIERS );
      $iCount     = count( $aFile );
      for( $i = 1; $i < $iCount; $i++ ){
        $aExp = explode( '$', $aFile[$i] );
        if( isset( $aPaymentsCarriers[$aExp[0]] ) )
          $aCarriers[$aExp[0]] = $sFunction( $aExp );
      }
     
      foreach( $aCarriers as $iCarrier => $aData ){
        $aData['sPayments'] = null;
        foreach( $aPayments as $iPayment => $sName ){
          if( isset( $aPaymentsCarriers[$iCarrier][$iPayment] ) ){
            if( !empty( $aPaymentsCarriers[$iCarrier][$iPayment] ) ){
              $aData['fPaymentCarrierPrice'] = generatePrice( $aData['fPrice'], $aPaymentsCarriers[$iCarrier][$iPayment] );
            }
            else{
              $aData['fPaymentCarrierPrice'] = $aData['fPrice'];
            }
            $aData['sPaymentCarrierPrice'] = displayPrice( $aData['fPaymentCarrierPrice'] );
            $aData['iPayment'] = $iPayment;
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPayments'] .= $oTpl->tbHtml( $sFile, 'ORDER_PAYMENT_CARRIERS_LIST' );
          }
          else{
            $aData['sPayments'] .= $oTpl->tbHtml( $sFile, 'ORDER_PAYMENT_CARRIERS_EMPTY' );
          }
        } // end foreach
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'ORDER_CARRIERS' );
      } // end foreach

      foreach( $aPayments as $aData['iPayment'] => $aData['sName'] ){
        $oTpl->setVariables( 'aData', $aData );
        $sPaymentList .= $oTpl->tbHtml( $sFile, 'ORDER_PAYMENTS' );
      }

      if( isset( $content ) ){
        $oTpl->setVariables( 'aData', Array( 'sPaymentList' => $sPaymentList ) );
        return $oTpl->tbHtml( $sFile, 'ORDER_PAYMENT_CARRIERS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'ORDER_PAYMENT_CARRIERS_FOOT' );
      }
    }
  } // end function listCarriersPayments

  /**
  * Return carrier data
  * @return array
  * @param int  $iCarrier
  */
/*  function throwCarrier( $iCarrier ){
    $oFF =& FlatFiles::getInstance( );
    return $oFF->throwData( DB_CARRIERS, $iCarrier, 'iCarrier' );
  } // end function throwCarrier
  // not used in epesi
*/

  /**
  * Return payment data
  * @return array
  * @param int  $iPayment
  */
/*  function throwPayment( $iPayment ){
    $oFF =& FlatFiles::getInstance( );
    return $oFF->throwData( DB_PAYMENTS, $iPayment, 'iPayment' );
  } // end function throwPayment
  // not used in epesi
*/

  /**
  * Send email to admin with order details
  * @return void
  * @param string $sFile
  * @param int    $iOrder
  */
  function sendEmailWithOrderDetails( $sFile, $iOrder ){
    $oTpl     =& TplParser::getInstance( );
    $content  = null;
    $aData    = $this->throwOrder( $iOrder );

    $aData['sProducts'] = $this->listProducts( $sFile, $iOrder, 'ORDER_EMAIL_' );
    $aData['sOrderSummary'] = $this->aOrders[$iOrder]['sOrderSummary'];
    
    $oTpl->setVariables( 'aData', $aData );
    $aSend['sMailContent'] = ereg_replace( '\|n\|', "\n", $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_BODY' ) );
    $aSend['sTopic'] = $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_TITLE' );
    $aSend['sSender']= $GLOBALS['config']['orders_email'];
    sendEmail( $aSend, null, $GLOBALS['config']['orders_email'] );
  } // end function sendEmailWithOrderDetails

};
?>