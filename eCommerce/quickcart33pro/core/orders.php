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
      $iItems = 0;
      foreach( $this->aProducts as $aData ){
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['sSummary'] = displayPrice( normalizePrice( $aData['fSummary'] ) );
        $aData['sPrice'] = displayPrice( $aData['fPrice'] );
        $aData['sLinkDelete'] = defined( 'CUSTOMER_PAGE' ) ? $GLOBALS['aData']['sLinkName'].((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?'?':'&amp;').'iProductDelete='.$aData['iProduct'] : null;
        $oTpl->setVariables( 'aData', $aData );
        if( !empty( $GLOBALS['config']['zagiel_id'] ) && $sBlock == 'ZAGIEL_' ){
          for( $j = 0; $j < $aData['iQuantity']; $j++ ){
            $iItems++;
            $aData['iItem'] = $iItems;
            $oTpl->setVariables( 'aData', $aData );
            $content .= $oTpl->tbHtml( $sFile, $sBlock.'LIST' );
          }
        }
        else
          $content .= $oTpl->tbHtml( $sFile, $sBlock.'LIST' );
        $i++;
      }

      $aData['fProductsSummary'] = normalizePrice( $this->fProductsSummary );
      $aData['sProductsSummary'] = displayPrice( $aData['fProductsSummary'] );
      if( !empty( $GLOBALS['config']['zagiel_id'] ) && $sBlock == 'BASKET_' && $aData['sProductsSummary'] >= $GLOBALS['config']['zagiel_min_price'] ){
        $oTpl->setVariables( 'aData', $aData );
        $aData['sZagielInfo'] = $oTpl->tbHtml( $sFile, 'ZAGIEL_INFO' );
      }
      if( isset( $iId ) && isset( $this->aOrders[$iId] ) ){
        $this->aOrders[$iId]['fProductsSummary'] = $aData['fProductsSummary'];
        if( !empty( $this->aOrders[$iId]['fPaymentCarrierPrice'] ) ){
          $this->aOrders[$iId]['fOrderSummary'] = $aData['fOrderSummary'] = normalizePrice( $aData['fProductsSummary'] +  $this->aOrders[$iId]['fPaymentCarrierPrice'] );
          $this->aOrders[$iId]['sOrderSummary'] = $aData['sOrderSummary'] = displayPrice( $aData['fOrderSummary'] );
          if( !empty( $GLOBALS['config']['zagiel_id'] ) && $sBlock == 'ZAGIEL_' ){
            $iItems++;
            $aData['iItem'] = $iItems;
            $aData['iProduct'] = 'carrier';
            $aData['sName'] = $GLOBALS['lang']['Delivery_and_payment'];
            $aData['fSummary'] = $this->aOrders[$iId]['fPaymentCarrierPrice'];
            $oTpl->setVariables( 'aData', $aData );
            $content .= $oTpl->tbHtml( $sFile, $sBlock.'LIST' );
          }
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
	    $this->aProducts = null;
    	$this->fProductsSummary   = null;
	    $_SESSION['iOrderQuantity'.LANGUAGE]  = 0;
    	$_SESSION['fOrderSummary'.LANGUAGE]   = null;
		
		$ret = DB::Execute('SELECT * FROM premium_ecommerce_orders_temp WHERE customer=%s',array($_SESSION['iCustomer'.LANGUAGE]));
		while($row = $ret->FetchRow()) {
	        $this->aProducts[$row['product']] = Array( 'iCustomer' => $row['customer'], 'iProduct' => $row['product'], 'iQuantity' => $row['quantity'], 'fPrice' => $row['price'], 'sName' => $row['name'], 'tax'=>$row['tax'], 'weight'=>$row['weight'] );
	        $this->aProducts[$row['product']]['sLinkName'] = '?'.$row['product'].','.change2Url( $this->aProducts[$row['product']]['sName'] );
	        $this->aProducts[$row['product']]['fSummary'] = normalizePrice( $this->aProducts[$row['product']]['fPrice'] * $this->aProducts[$row['product']]['iQuantity']);
	        $_SESSION['iOrderQuantity'.LANGUAGE] += $row['quantity'];
    	    $_SESSION['fOrderSummary'.LANGUAGE]  += ( $row['quantity'] * $row['price'] );
		}
	    if( isset( $_SESSION['fOrderSummary'.LANGUAGE] ) )
    		$this->fProductsSummary = $_SESSION['fOrderSummary'.LANGUAGE] = normalizePrice( $_SESSION['fOrderSummary'.LANGUAGE] );
	//} epesi
  } // end function generateBasket

  /**
  * Generates variable with products in order
  * @return void
  * @param int  $iOrder
  */
  function generateProducts( $iOrder ){
    // { epesi
    $ret = DB::Execute('SELECT * FROM premium_warehouse_items_orders_details_data_1 WHERE f_transaction_id=%d',array($iOrder));
    while($row = $ret->FetchRow()) {
	$this->aProducts[$row['id']] = array('iElement' => $row['id'], 'iOrder' => $iOrder, 'iProduct' => $row['f_item_name'], 'iQuantity' => $row['f_quantity'], 'fPrice' => $row['f_gross_price'], 'sName' => $row['f_description']);
        $this->aProducts[$row['id']]['fSummary'] = normalizePrice( $this->aProducts[$row['id']]['fPrice'] * $this->aProducts[$row['id']]['iQuantity'] );
        $this->fProductsSummary += $this->aProducts[$row['id']]['fPrice'] * $this->aProducts[$row['id']]['iQuantity'];
    }
    // } epesi

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
		$qty = DB::GetAssoc('SELECT product,quantity FROM premium_ecommerce_orders_temp WHERE customer=%s',array($_SESSION['iCustomer'.LANGUAGE]));
		foreach($qty as $p=>$q) {
			if(isset( $aForm['aProducts'][$p] ) && is_numeric( $aForm['aProducts'][$p] ) && $aForm['aProducts'][$p] > 0 && $aForm['aProducts'][$p] < 10000 && $q!=$aForm['aProducts'][$p]) {
				DB::Execute('UPDATE premium_ecommerce_orders_temp SET quantity=%d WHERE customer=%s AND product=%d',array($aForm['aProducts'][$p],$_SESSION['iCustomer'.LANGUAGE],$p));
			}
		}
    }
  } // end function saveBasket

  /**
  * Delete product from basket
  * @return void
  * @param int  $iProduct
  * @param int  $iOrder
  */
  function deleteFromBasket( $iProduct, $iOrder = null ){
    if( !isset( $iOrder ) )
    	 $iOrder = $_SESSION['iCustomer'.LANGUAGE];
	DB::Execute('DELETE FROM premium_ecommerce_orders_temp WHERE product=%d AND customer=%s',array($iProduct,$iOrder));
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
		$prod = $oProduct->getProduct($iProduct);
		DB::Execute('INSERT INTO premium_ecommerce_orders_temp(customer,product,quantity,price,name,tax,weight) VALUES (%s,%d,%d,%s,%s,%s,%f)',array($iOrder,$iProduct,$iQuantity,$prod['fPrice'],$prod['sName'],$prod['tax'],$prod['sWeight']));
	}
	//} epesi
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
        $sPrice = $this->throwPaymentCarrierPrice( $aExp[0], $aExp[1]);
	if($sPrice===false) unset($sPrice);
    }
    else{
      return false;
    }

    if(
      throwStrLen( $aForm['sFirstName'] ) > 1
      && throwStrLen( $aForm['sLastName'] ) > 1
      && throwStrLen( $aForm['sStreet'] ) > 1
      && throwStrLen( $aForm['sZipCode'] ) > 1
      && throwStrLen( $aForm['sCity'] ) > 1
      && throwStrLen( $aForm['sPhone'] ) > 1
      && throwStrLen( $aForm['sCountry'] ) > 1
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
	/* 
	//'iOrder' => 0, 
	//'iTime' => 3, 
	//'iCarrier' => 4, 
	//'iPayment' => 5, 
	//'sCarrierName' => 6, 
	//'fCarrierPrice' => 7, 
	//'sPaymentName' => 8, 
	//'sPaymentPrice' => 9, 
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

    if( !isset( $aForm['iInvoice'] ) )
      $aForm['iInvoice'] = null;

    list($carrier,$payment) = explode( ';', $aForm['sPaymentCarrier'] );
    $price = $this->throwPaymentCarrierPrice($carrier,$payment);
    $currency = $this->getCurrencyId();

    $aPayment = $this->throwPayment( $payment );

    if( isset( $aPayment['iOuterSystem'] ) ){
      $aForm['iPaymentSystem'] = $aPayment['iOuterSystem'];
      if( isset( $aForm['aPaymentChannel'][$aPayment['iPayment']] ) ) {
        $aForm['mPaymentChannel'] = $aForm['aPaymentChannel'][$aPayment['iPayment']];
      } else
        $aForm['mPaymentChannel'] = null;
    }
    else{
      $aForm['iPaymentSystem'] = null;
      $aForm['mPaymentChannel'] = null;
    }
    $aForm['iPaymentRealized']  = 0;
	
    $t = time();
    //$memo = "Language: ".LANGUAGE."\ne-mail: ".$aForm['sEmail']."\nIp: ".$_SERVER['REMOTE_ADDR']."\nComment:\n".$aForm['sComment'];
    DB::Execute('INSERT INTO premium_warehouse_items_orders_data_1(f_transaction_type,f_transaction_date,f_status,
						f_company_name,f_last_name,f_first_name,f_address_1,f_city,f_postal_code,f_phone,f_country,f_zone,f_memo,created_on,
						f_shipment_type,f_shipment_cost,f_payment,f_payment_type,f_tax_id) VALUES 
						(1,%D,"-1",%s,%s,%s,%s,%s,%s,%s,%s,"",%s,%T,%s,%s,1,%s,%s)',
					array($t,$aForm['sCompanyName'],$aForm['sLastName'],$aForm['sFirstName'],$aForm['sStreet'],$aForm['sCity'],
					$aForm['sZipCode'],$aForm['sPhone'],$aForm['sCountry'],$memo,$t,$carrier,$price.'_'.$currency,$payment,$aForm['sNip']));
    $id = DB::Insert_ID('premium_warehouse_items_orders_data_1','id');
    $trans_id = '#'.str_pad($id, 6, '0', STR_PAD_LEFT);
    DB::Execute('UPDATE premium_warehouse_items_orders_data_1 SET f_transaction_id=%s WHERE id=%d',array($trans_id,$id));

	DB::Execute('INSERT INTO premium_ecommerce_orders_data_1(f_transaction_id, f_language, f_email, f_ip, f_comment, f_invoice, f_payment_system, 
						f_payment_channel,f_payment_realized,created_on) VALUES
						(%d,%s,%s,%s,%s,%b,%d,%d,%b,%T)',
					array($id,LANGUAGE,$aForm['sEmail'],$_SERVER['REMOTE_ADDR'],$aForm['sComment'],$aForm['iInvoice']?true:false,
					$aForm['iPaymentSystem'],$aForm['mPaymentChannel'],$aForm['iPaymentRealized'],time()));

    $taxes = DB::GetAssoc('SELECT id, f_percentage FROM data_tax_rates_data_1 WHERE active=1');

    if( isset( $this->aProducts ) ){
      foreach( $this->aProducts as $aData ){
	$net = $aData['fPrice']*100/(100+$taxes[$aData['tax']]);
        DB::Execute('INSERT INTO premium_warehouse_items_orders_details_data_1(f_transaction_id,f_item_name,f_quantity,f_description,f_tax_rate,created_on,f_net_price) 
							VALUES (%s,%d,%d,%s,%s,%T,%s)', array($id,$aData['iProduct'],$aData['iQuantity'],$aData['sName'],$aData['tax'],$t,$net.'__'.$currency));
      }
    }
	
  	DB::Execute('DELETE FROM premium_ecommerce_orders_temp WHERE customer=%s',array($_SESSION['iCustomer'.LANGUAGE]));

    $_SESSION['iOrderQuantity'.LANGUAGE]  = 0;
    $_SESSION['fOrderSummary'.LANGUAGE]   = null;

    return $id;
  } // end function addOrder

  /**
  * Return order data
  * @return array
  * @param int  $iOrder
  */
  function throwOrder( $iOrder ){
    if( isset( $this->aOrders[$iOrder] ) ){
      return $this->aOrders[$iOrder];
    }
    else{
	$aData = DB::GetRow('SELECT w.id as iOrder, 
				    w.created_on as iTime,
				    w.f_shipment_type as iCarrier,
				    w.f_shipment_type,
				    w.f_payment_type as iPayment,
				    w.f_shipment_cost as sPaymentPrice,
				    w.f_first_name as sFirstName,
				    w.f_last_name as sLastName,
				    w.f_company_name as sCompanyName,
				    w.f_address_1 as sStreet,
				    w.f_postal_code as sZipCode,
				    w.f_city as sCity,
				    w.f_country as sCountry,
				    w.f_phone as sPhone,
				    w.f_tax_id as sNip,
				    o.f_comment as sComment,
				    o.f_ip as sIp,
				    o.f_email as sEmail,
				    o.f_language as sLanguage,
				    o.f_payment_system as iPaymentSystem,
				    o.f_payment_channel as mPaymentChannel,
				    o.f_payment_realized as iPaymentRealized
				    FROM premium_warehouse_items_orders_data_1 w INNER JOIN premium_ecommerce_orders_data_1 o ON o.f_transaction_id=w.id WHERE w.id=%d',array($iOrder));
        if($aData) {
	        $aPayments = $this->getPayments();
	        $aShipments = $this->getShipments();
		$aData['sCarrierName'] = $aShipments[$aData['f_shipment_type']];
		$aData['sPaymentName'] = $aPayments[$aData['iPayment']];
		list($aData['sPaymentPrice']) = explode('_',$aData['sPaymentPrice']);
        	$aData['iTime'] = strtotime($aData['iTime']);
	}
    }

    if( isset( $aData ) ){
      $aData['sInvoice'] = throwYesNoTxt( $aData['iInvoice'] );
      $aData['fPaymentCarrierPrice'] = generatePrice( $aData['fCarrierPrice'], $aData['sPaymentPrice'] );
      $aData['sPaymentCarrierPrice'] = displayPrice( $aData['fPaymentCarrierPrice'] );
      $aData['sDate'] = displayDate( $aData['iTime'] );
      if( !empty( $aData['iPaymentSystem'] ) && isset( $GLOBALS['aOuterPaymentOption'][$aData['iPaymentSystem']] ) ){
        $aData['sPaymentChannel'] = $GLOBALS['aOuterPaymentOption'][$aData['iPaymentSystem']];
        if( isset( $GLOBALS['aPay'][$aData['iPaymentSystem']][$aData['mPaymentChannel']] ) )
          $aData['sPaymentChannel'] .= ' | '.$GLOBALS['aPay'][$aData['iPaymentSystem']][$aData['mPaymentChannel']];
      }
      else
        $aData['sPaymentChannel'] = '-';

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
	$ret = DB::GetOne('SELECT customer FROM premium_ecommerce_orders_temp WHERE md5(customer)=%s',array($sOrder));
	if($ret) return $ret;
	return null;
  } // end function throwSavedOrderId

  /**
  * Return payment and carrier price
  * @return string
  * @param int  $iCarrier
  * @param int  $iPayment
  */
  function throwPaymentCarrierPrice( $iCarrier, $iPayment ){
    // { epesi
    if( isset( $GLOBALS['config']['delivery_free'] ) && $_SESSION['fOrderSummary'.LANGUAGE] >= $GLOBALS['config']['delivery_free'] ){
      return 0;
    }
    $currency = $this->getCurrencyId();
    $weight = $this->getWeight();
    return DB::GetOne('SELECT f_price FROM premium_ecommerce_payments_carriers_data_1 WHERE active=1 AND f_payment=%d AND f_shipment=%d AND f_currency=%d
    			AND (
			f_max_weight=(SELECT MIN(f_max_weight) FROM premium_ecommerce_payments_carriers_data_1 WHERE active=1 AND f_currency=%s AND f_max_weight>=%f)
			OR
			(f_max_weight is null 
			    AND
			(SELECT 1 FROM premium_ecommerce_payments_carriers_data_1 WHERE active=1 AND f_currency=%s AND f_max_weight>=%f LIMIT 1) is null
			))',array($iPayment,$iCarrier,$currency,$currency,$weight,$currency,$weight));
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
    $aOuterPayments = DB::GetAssoc('SELECT f_payment,f_relate_with FROM premium_ecommerce_payments_data_1 WHERE active=1');

    $freeDelivery = false;
    if( isset( $GLOBALS['config']['delivery_free'] ) && $_SESSION['fOrderSummary'.LANGUAGE] >= $GLOBALS['config']['delivery_free'] ){
      $freeDelivery = true;
    }
     
    $aPayments = $this->getPayments();
    $aShipments = $this->getShipments();
    $weight = $this->getWeight();
    if( $aPayments && $aShipments ){
      $currency = $this->getCurrencyId();
      //get possible configurations
      $ret = DB::Execute('SELECT f_payment,f_shipment,f_price FROM premium_ecommerce_payments_carriers_data_1 
    			WHERE active=1 AND f_currency=%s AND (
			f_max_weight=(SELECT MIN(f_max_weight) FROM premium_ecommerce_payments_carriers_data_1 WHERE active=1 AND f_currency=%s AND f_max_weight>=%f)
			OR
			(f_max_weight is null 
			    AND
			(SELECT 1 FROM premium_ecommerce_payments_carriers_data_1 WHERE active=1 AND f_currency=%s AND f_max_weight>=%f LIMIT 1) is null
			))',array($currency,$currency,$weight,$currency,$weight));
      while($aExp = $ret->FetchRow()) {
        if($freeDelivery)
    	    $aPaymentsCarriers[$aExp['f_shipment']][$aExp['f_payment']] = 0;
	else
    	    $aPaymentsCarriers[$aExp['f_shipment']][$aExp['f_payment']] = $aExp['f_price'];
      }
      foreach( $aShipments as $iCarrier => $carrier_name ) {
        if(!isset($aPaymentsCarriers[$iCarrier])) continue;
        $aData = array('sName'=>$carrier_name, 'iCarrier'=>$iCarrier, 'sPayments'=>null, 'fPrice'=>0);
        foreach( $aPayments as $iPayment => $sName ){
          if( isset( $aPaymentsCarriers[$iCarrier][$iPayment] ) ){
            $aData['fPaymentCarrierPrice'] = normalizePrice( $aPaymentsCarriers[$iCarrier][$iPayment] );
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
      $aData = array();

      foreach( $aPayments as $aData['iPayment'] => $aData['sName'] ){
        if( isset( $aOuterPayments[$aData['iPayment']] ) && $aOuterPayments[$aData['iPayment']] > 0 ){
          if( isset( $GLOBALS['aPay'][$aOuterPayments[$aData['iPayment']]] ) ){
            $aData['sPaymentChannelSelect'] = throwSelectFromArray( $GLOBALS['aPay'][$aOuterPayments[$aData['iPayment']]] );
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPaymentChannel'] = $oTpl->tbHtml( $sFile, 'PAYMENT_CHANNEL' );
          }
          elseif( $aOuterPayments[$aData['iPayment']] == 5 )
            $aData['sPaymentChannel'] = $oTpl->tbHtml( $sFile, 'ZAGIEL_INFO' );
        }
        $oTpl->setVariables( 'aData', $aData );
        $sPaymentList .= $oTpl->tbHtml( $sFile, 'ORDER_PAYMENTS' );
	$aData = array(); //epesi team quickcart bug fix
      } // end foreach

      if( isset( $content ) ){
        $oTpl->setVariables( 'aData', Array( 'sPaymentList' => $sPaymentList ) );
        return $oTpl->tbHtml( $sFile, 'ORDER_PAYMENT_CARRIERS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'ORDER_PAYMENT_CARRIERS_FOOT' );
      }
    }
  } // end function listCarriersPayments

  // { epesi
  /**
  * Return currency id
  * @return array
  * @param int  $iCarrier
  */
  function getCurrencyId( ){
    global $config;

    $currency = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s',array($config['currency_symbol']));
    if($currency===false) 
    	die('Currency not defined in Epesi: '.$config['currency_symbol']);
    return $currency;
  } // end function getCurrencyId
  
  function getPayments(){
     //get possible payments
     static $payments = null;
     if(!isset($payments)) {
        $payments_id = DB::GetOne('SELECT id FROM utils_commondata_tree WHERE akey="Premium_Items_Orders_Payment_Types"');
        $currency = $this->getCurrencyId();
	if($payments_id===false)
	    die('Common data key "Premium_Items_Orders_Payment_Types" not defined.');
	$payments = DB::GetAssoc('SELECT p.akey, p.value FROM utils_commondata_tree p WHERE p.parent_id=%d AND p.akey IN (SELECT f_payment FROM premium_ecommerce_payments_carriers_data_1 WHERE f_currency=%s AND active=1) ORDER BY akey',array($payments_id,$currency));
	global $translations;
	foreach($payments as $k=>$v) {
		if(isset($translations['Utils_CommonData'][$v]) && $translations['Utils_CommonData'][$v])
			$payments[$k] = $translations['Utils_CommonData'][$v];
	}
    }
    return $payments;
  }

  function getShipments(){
    //get possible shipments
     static $shipments = null;
     if(!isset($shipments)) {
        $shipments_id = DB::GetOne('SELECT id FROM utils_commondata_tree WHERE akey="Premium_Items_Orders_Shipment_Types"');
	if($shipments_id===false)
	    die('Common data key "Premium_Items_Orders_Shipment_Types" not defined.');
	$shipments = DB::GetAssoc('SELECT akey, value FROM utils_commondata_tree WHERE parent_id=%d ORDER BY akey',array($shipments_id));
	global $translations;
	foreach($shipments as $k=>$v) {
		if(isset($translations['Utils_CommonData'][$v]) && $translations['Utils_CommonData'][$v])
			$shipments[$k] = $translations['Utils_CommonData'][$v];
	}
    }
    return $shipments;
  }

  function getWeight() {
    static $weight;
    if( !isset( $weight ) ){
	$weight = 0;
	if(isset($this->aProducts))
      foreach( $this->aProducts as $a ){
	$weight += $a['iQuantity']*$a['weight'];
      }
    }
    return $weight;
  }
  
  // } epesi

  /**
  * Return payment data
  * @return array
  * @param int  $iPayment
  */
  function throwPayment( $iPayment ){
    $payments = $this->getPayments();
    if(!isset($payments[$iPayment])) return null;
    $aData = DB::GetRow('SELECT f_relate_with as iOuterSystem, f_description as sDescription, f_payment as iPayment FROM premium_ecommerce_payments_data_1 WHERE f_payment=%s',array($iPayment));
    $aData['sName'] = $payments[$iPayment];
    if( isset( $aData ) && is_array( $aData ) ){
      $aData['sDescription'] = changeTxt( $aData['sDescription'], 'Ndsnl' );
      return $aData;
    }
    else
      return null;
  } // end function throwPayment

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
    $aPayment = $this->throwPayment( $aData['iPayment'] );
    if( !empty( $aPayment['sDescription'] ) )
      $aData['sPaymentDescription'] = $aPayment['sDescription'];

    $oTpl->setVariables( 'aData', $aData );
    $aSend['sMailContent'] = ereg_replace( '\|n\|', "\n", $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_BODY' ) );
    $aSend['sTopic'] = $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_TITLE' );
    $aSend['sSender']= $GLOBALS['config']['email'];
    sendEmail( $aSend, null, $aData['sEmail'] ); //send e-mail to client
  } // end function sendEmailWithOrderDetails
};
?>