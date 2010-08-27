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
          $this->aOrders[$iId]['fOrderSummary'] = $aData['fOrderSummary'] = normalizePrice( $aData['fProductsSummary'] +  $this->aOrders[$iId]['fPaymentCarrierPrice'] +  $this->aOrders[$iId]['fShipmentDiscount'] );
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
	if($_SESSION['stock_exceeded']) {
		print('<script type="text/javascript">alert(\''.addcslashes($GLOBALS['lang']['Stock_exceeded'],'\\\'').'\')</script>');
		$_SESSION['stock_exceeded'] = false;
	}
	
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
    $taxes = DB::GetAssoc('SELECT id, f_percentage FROM data_tax_rates_data_1 WHERE active=1');
    $ret = DB::Execute('SELECT * FROM premium_warehouse_items_orders_details_data_1 WHERE f_transaction_id=%d',array($iOrder));
    $currency = $this->getCurrencyId();
    while($row = $ret->FetchRow()) {
    	$rr = explode('__',$row['f_net_price']);
	if($rr && isset($rr[0]) && $rr[1]==$currency) {
		$netto = $rr[0];
		$row['f_gross_price'] = round(((float)$netto)*(100+$taxes[$row['f_tax_rate']])/100,2);
	}

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
    global $lang;
    if( isset( $aForm['aProducts'] ) && is_array( $aForm['aProducts'] ) ){
		$qty = DB::GetAssoc('SELECT product,quantity FROM premium_ecommerce_orders_temp WHERE customer=%s',array($_SESSION['iCustomer'.LANGUAGE]));
		$oProduct =& Products::getInstance( );
		foreach($qty as $p=>$q) {
			if(isset( $aForm['aProducts'][$p] ) && is_numeric( $aForm['aProducts'][$p] ) && $aForm['aProducts'][$p] > 0 && $aForm['aProducts'][$p] < 10000 && $q!=$aForm['aProducts'][$p]) {
				$iQuantity = $aForm['aProducts'][$p];
				$prod = $oProduct->getProduct($p);
				if($iQuantity>$prod['iQuantity']) {
					$iQuantity = $prod['iQuantity'];
					$_SESSION['stock_exceeded'] = true;
				}
				DB::Execute('UPDATE premium_ecommerce_orders_temp SET quantity=%d WHERE customer=%s AND product=%d',array($iQuantity,$_SESSION['iCustomer'.LANGUAGE],$p));
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
		$oProduct =& Products::getInstance( );
		$prod = $oProduct->getProduct($iProduct);
		if($iQuantity>$prod['iQuantity']) {
			$iQuantity = $prod['iQuantity'];
			$_SESSION['stock_exceeded'] = true;
		}
		DB::Execute('UPDATE premium_ecommerce_orders_temp SET quantity=%d WHERE product=%d AND customer=%s',array($iQuantity,$iProduct,$iOrder));
	} else {
		$oProduct =& Products::getInstance( );
		$prod = $oProduct->getProduct($iProduct);
		DB::Execute('INSERT INTO premium_ecommerce_orders_temp(customer,product,quantity,price,name,tax,weight) VALUES (%s,%d,%d,%s,%s,%s,%f)',array($iOrder,$iProduct,$iQuantity,$prod['fPrice'],$prod['sName'],$prod['tax'],$prod['sWeight']?$prod['sWeight']:0));
	}
	//} epesi
  } // end function addToBasket

  /**
  * Check order fields
  * @return bool
  * @param array  $aForm
  */
  function checkFields( $aForm ){
    $carrier = null;
    if( isset( $aForm['sPaymentCarrier'] ) ){
      $aExp = explode( ';', $aForm['sPaymentCarrier'] );
      if( isset( $aExp[0] ) && isset( $aExp[1] ) )
      	$carrier = $aExp[0];
        $sPrice = $this->throwPaymentCarrier( $aExp[0], $aExp[1]);
	if($sPrice===false) unset($sPrice);
    }
    else{
      return false;
    }

    $shops = DB::GetAssoc('SELECT id,1 FROM premium_warehouse_data_1 WHERE active=1 AND f_pickup_place=1');

    if($aForm['sPromotionCode']) {
	$promotion = $this->throwPromotions($aForm['sPromotionCode']);
	if(!$promotion) {
   		$c = DB::GetOne('SELECT 1 FROM premium_ecommerce_promotion_codes_data_1 WHERE f_promotion_code LIKE %s AND active=1',array($aForm['sPromotionCode']));
   		if($c)
			return 'promotion_expired';
		return 'promotion_invalid';
	}
    } 
    
    if($aForm['sPassword']) {
    	if($aForm['sPassword']!=$aForm['sPassword2'])
	    	return 'password_mismatch';
    }

        $qty = DB::GetAssoc('SELECT product,quantity FROM premium_ecommerce_orders_temp WHERE customer=%s',array($_SESSION['iCustomer'.LANGUAGE]));
	$oProduct =& Products::getInstance( );
	foreach($qty as $p=>$q) {
		$iQuantity = $q;
		$prod = $oProduct->getProduct($p);
		if($iQuantity>$prod['iQuantity']) {
			$iQuantity = $prod['iQuantity'];
			if($iQuantity<=0) {
				DB::Execute('DELETE FROM premium_ecommerce_orders_temp WHERE customer=%s AND product=%d',array($_SESSION['iCustomer'.LANGUAGE],$p));
				if(count($qty)==1)
					return 'basket_empty';
			} else
				DB::Execute('UPDATE premium_ecommerce_orders_temp SET quantity=%d WHERE customer=%s AND product=%d',array($iQuantity,$_SESSION['iCustomer'.LANGUAGE],$p));
			return 'stock_exceeded';
		}
	}
    
    if(throwStrLen( $aForm['sFirstName'] ) > 1
      && throwStrLen( $aForm['sLastName'] ) > 1
      && throwStrLen( $aForm['sStreet'] ) > 1
      && throwStrLen( $aForm['sZipCode'] ) > 1
      && throwStrLen( $aForm['sCity'] ) > 1
      && throwStrLen( $aForm['sPhone'] ) > 1
      && throwStrLen( $aForm['sCountry'] ) > 1
      && ((throwStrLen( $aForm['iPickupShop'] ) > 0 && $shops[$aForm['iPickupShop']]) || empty($shops) || $carrier!=0)
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
    $aPayment = $this->throwPaymentCarrier($carrier,$payment);
    $price = $aPayment['fPrice'];
    $order_terms = $aPayment['sTerms'];
    $currency = $this->getCurrencyId();

    $promo_employee = null;
    $promo_discount = 0;
    if($aForm['sPromotionCode']) {
	    $promotions = $this->throwPromotions($aForm['sPromotionCode']);
	    if($promotions) {
	    	foreach($promotions as $promo) {
			$rr = explode('__',$promo['discount']);
			if($rr && $rr[0] && $rr[1]==$currency) {
				$promo_discount = $rr[0];
				if($price<$promo_discount) $promo_discount = $price;
				$promo_employee = $promo['employee'];
				break;
			}
	    	}
	    }
    }
    $price = $aPayment['shipment'];
    $price -= $promo_discount;
    $handling = $aPayment['handling'];
    if($price<0) {
        $handling += $price;
        $price=0;
    }

    if( isset( self::$payments_to_qcpayments[$payment] ) ){
      if( isset( $aForm['aPaymentChannel'][$aPayment['iPayment']] ) ) {
        $aForm['mPaymentChannel'] = $aForm['aPaymentChannel'][$aPayment['iPayment']];
      } else
        $aForm['mPaymentChannel'] = null;
    }
    else{
      $aForm['mPaymentChannel'] = null;
    }
    $aForm['iPaymentRealized']  = 0;

    $t = time();
    
    $contact = null;
    $company = null;
    if(Users::logged()) {
    	$contact = $_SESSION['contact'];
    	$company = $_SESSION['company'];
    	global $aUser;
    	$colst = array('contact'=>array('email'=>'sEmail', 'last_name'=>'sLastName', 'first_name'=>'sFirstName', 'address_1'=>'sStreet', 'postal_code'=>'sZipCode', 'city'=>'sCity', 'country'=>'sCountry', 'work_phone'=>'sPhone'));
    	if($company!==null)
    		$colst['company']=array('email'=>'sEmail', 'company_name'=>'sCompanyName', 'tax_id'=>'sNip', 'address_1'=>'sStreet', 'postal_code'=>'sZipCode', 'city'=>'sCity', 'country'=>'sCountry', 'phone'=>'sPhone');
	    foreach($colst as $tab=>$cols) {    			
	    	$modified = false;
    		foreach($cols as $epesi=>$local)
    			if($aUser[$local]!=$aForm[$local]) {
    				$modified = true;
	    			break;
    			}
	    	if($modified) {
			DB::Execute('INSERT INTO '.$tab.'_edit_history(edited_on, '.$tab.'_id) VALUES (%T,%d)', array($t, ${$tab}));
			$edit_id = DB::Insert_ID(''.$tab.'_edit_history','id');
	    	    	foreach($cols as $epesi=>$local)
		    		if($aUser[$local]!=$aForm[$local]) {
	    				DB::Execute('UPDATE '.$tab.'_data_1 SET f_'.$epesi.'=%s WHERE id=%d', array($aForm[$local],${$tab}));
	    				DB::Execute('INSERT INTO '.$tab.'_edit_history_data(edit_id, field, old_value) VALUES (%d,%s,%s)', array($edit_id, $epesi, $aUser[$local]!==null?$aUser[$local]:''));
		    		}
    		}
    	}
    } else {
    	$company = DB::GetOne('SELECT id FROM company_data_1 WHERE f_email=%s AND active=1',array($aForm['sEmail']));
    	if(!$company) $company = null;
    	$contact = DB::GetOne('SELECT id FROM contact_data_1 WHERE f_email=%s AND active=1',array($aForm['sEmail']));
    	if(!$contact) {
    		$contact = null;
    	} elseif($company) { //jest kontakt i firma - sprawdz czy kontakt jest pod ta firma, jezeli nie to dodaj kontakt do firmy
	    	$companies = DB::GetOne('SELECT f_company_name FROM contact_data_1 WHERE id=%d',array($contact));
	    	if(strstr($companies,'__'.$company.'__')===false) {
	    		if($companies) $companies .= $company.'__';
	    		else $companies = '__'.$company.'__';
	    		DB::Execute('UPDATE contact_data_1 SET f_company_name=%s WHERE id=%d',array($companies,$contact));
	    	}
    	}
        //add user
       	$new_company = false;
		if(!$contact || !$company) { // jezeli nie ma kontaktu, lub jest kontakt ale nie ma firmy, to dodaj firme
	    		if($aForm['sCompanyName'] && !$company) {
	    			$new_company = true;
			    	DB::Execute('INSERT INTO company_data_1(created_on,f_company_name,f_tax_id,f_address_1,f_postal_code,f_city,f_country,f_phone,f_email,f_group,f_permission) VALUES (%T,%s,%s,%s,%s,%s,%s,%s,%s,\'__customer__\',0)',
    					array($t,$aForm['sCompanyName'],$aForm['sNip'],$aForm['sStreet'],$aForm['sZipCode'],$aForm['sCity'],$aForm['sCountry'],$aForm['sPhone'],$aForm['sEmail']));
				$company = DB::Insert_ID('company_data_1','id');
			}
		}
		if(!$contact) { //jezeli nie ma kontaktu stworz go
			if($company)
				$company2 = '__'.$company.'__';
			else
				$company2 = null;
		    	DB::Execute('INSERT INTO contact_data_1(created_on,f_first_name,f_last_name,f_address_1,f_postal_code,f_city,f_country,f_work_phone,f_email,f_company_name,f_group,f_permission) VALUES (%T,%s,%s,%s,%s,%s,%s,%s,%s,%s,\'__custm__\',0)',
    				array($t,$aForm['sFirstName'],$aForm['sLastName'],$aForm['sStreet'],$aForm['sZipCode'],$aForm['sCity'],$aForm['sCountry'],$aForm['sPhone'],$aForm['sEmail'],$company2));
			$contact = DB::Insert_ID('contact_data_1','id');
		} elseif($new_company) { //a jezeli jest kontakt o tym mailu, ale nie bylo firmy i zostala stworzona to dodaj ta firme do kontaktu
		    	$companies = DB::GetOne('SELECT f_company_name FROM contact_data_1 WHERE id=%d',array($contact));
		    	if(strstr($companies,'__'.$company.'__')===false) {
	    			if($companies) $companies .= $company.'__';
	    			else $companies = '__'.$company.'__';
		    		DB::Execute('UPDATE contact_data_1 SET f_company_name=%s WHERE id=%d',array($companies,$contact));
		    	}
		}
		//add ecommerce user
		if($aForm['sPassword']) {
    		$mdpass = md5($aForm['sPassword']);
	    	$oldpass = DB::GetOne('SELECT f_password FROM premium_ecommerce_users_data_1 WHERE f_contact=%d',array($contact));
		    if(!$oldpass) {
		    	DB::Execute('INSERT INTO premium_ecommerce_users_data_1(created_on,f_contact,f_password) VALUES (%T,%d,%s)',
    				array($t,$contact,$mdpass));
    			$oldpass = $mdpass;
    		}
    		if($oldpass==$mdpass) {
	   	    	//mark logged in
	    		$_SESSION['user'] = DB::Insert_ID('premium_ecomerce_users_data_1','id');
	    		$_SESSION['contact'] = $contact;
	    		$_SESSION['company'] = $company;
		      	if(!$_SESSION['company'])
      				$_SESSION['company'] = null;
		    } 
    	} else {
	    	$uid = DB::GetOne('SELECT id FROM premium_ecommerce_users_data_1 WHERE f_contact=%d',array($contact));
	    	if(!$uid) {
        	    $mdpass = md5(time());
	        	DB::Execute('INSERT INTO premium_ecommerce_users_data_1(created_on,f_contact,f_password) VALUES (%T,%d,%s)',
    				array($t,$contact,$mdpass));
    			$uid = DB::Insert_ID('premium_ecomerce_users_data_1','id');
    	    }
    	}
    }
    
    //$memo = "Language: ".LANGUAGE."\ne-mail: ".$aForm['sEmail']."\nIp: ".$_SERVER['REMOTE_ADDR']."\nComment:\n".$aForm['sComment'];
    DB::Execute('INSERT INTO premium_warehouse_items_orders_data_1(f_transaction_type,f_transaction_date,f_status,
						f_company_name,f_last_name,f_first_name,f_address_1,f_city,f_postal_code,f_phone,f_country,f_zone,f_memo,created_on,
						f_shipment_type,f_shipment_cost,f_payment,f_payment_type,f_tax_id,f_warehouse,f_online_order,f_contact,f_company,f_terms,f_receipt,f_handling_cost) VALUES 
						(1,%D,"-1",%s,%s,%s,%s,%s,%s,%s,%s,"",%s,%T,%s,%s,1,%s,%s,%d,1,%d,%d,%s,%b,%s)',
					array($t,$aForm['sCompanyName'],$aForm['sLastName'],$aForm['sFirstName'],$aForm['sStreet'],$aForm['sCity'],
					$aForm['sZipCode'],$aForm['sPhone'],$aForm['sCountry'],$memo,$t,$carrier,$price.'__'.$currency,$payment,$aForm['sNip'],$carrier==0?$aForm['iPickupShop']:null,$contact,$company,$order_terms,$aForm['iInvoice']?false:true,$handling.'__'.$currency));
    $id = DB::Insert_ID('premium_warehouse_items_orders_data_1','id');
    $trans_id = '#'.str_pad($id, 6, '0', STR_PAD_LEFT);
    DB::Execute('UPDATE premium_warehouse_items_orders_data_1 SET f_transaction_id=%s WHERE id=%d',array($trans_id,$id));

	DB::Execute('INSERT INTO premium_ecommerce_orders_data_1(f_transaction_id, f_language, f_email, f_ip, f_comment, f_invoice, 
						f_payment_channel,f_payment_realized,created_on,f_promotion_employee,f_promotion_shipment_discount) VALUES
						(%d,%s,%s,%s,%s,%b,%s,%b,%T,%d,%d)',
					array($id,LANGUAGE,$aForm['sEmail'],$_SERVER['REMOTE_ADDR'],$aForm['sComment'],$aForm['iInvoice']?true:false,
					$aForm['mPaymentChannel'],$aForm['iPaymentRealized'],time(),$promo_employee,$promo_discount));

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
				    o.f_payment_channel as mPaymentChannel,
				    o.f_payment_realized as iPaymentRealized,
				    o.f_invoice as iInvoice,
				    o.f_promotion_shipment_discount as iShipmentDiscount,
				    w.f_status as iStatus,
				    w.f_shipment_no as sShipmentNo,
				    w.f_tracking_info as sTrackingInfo
				    FROM premium_warehouse_items_orders_data_1 w LEFT JOIN premium_ecommerce_orders_data_1 o ON o.f_transaction_id=w.id WHERE w.id=%d',array($iOrder));

    if( isset( $aData ) ){
      $aPayments = $this->getPayments();
      $aShipments = $this->getShipments();
      
      $countries_id = DB::GetOne('SELECT id FROM utils_commondata_tree WHERE akey="Countries"');
      if($countries_id===false)
	    die('Common data key "Countries" not defined.');
      $aData['sCountry'] = DB::GetOne('SELECT p.value FROM utils_commondata_tree p WHERE p.parent_id=%d AND p.akey=%s ORDER BY p.akey',array($countries_id,$aData['sCountry']));
      global $translations;
      if(isset($translations['Utils_CommonData'][$aData['sCountry']]) && $translations['Utils_CommonData'][$aData['sCountry']])
	$aData['sCountry'] = $translations['Utils_CommonData'][$aData['sCountry']];
      
      $aData['sCarrierName'] = $aShipments[$aData['f_shipment_type']];
      $aData['sPaymentName'] = $aPayments[$aData['iPayment']];
      list($aData['sPaymentPrice']) = explode('_',$aData['sPaymentPrice']);
      $aData['iTime'] = strtotime($aData['iTime']);
      $aData['sInvoice'] = throwYesNoTxt( $aData['iInvoice'] );
      $aData['fPaymentCarrierPrice'] = generatePrice( $aData['sPaymentPrice'], $aData['iShipmentDiscount'] );
      $aData['sPaymentCarrierPrice'] = displayPrice( $aData['fPaymentCarrierPrice'] );
      if(!$aData['iShipmentDiscount']) {
	      $aData['fShipmentDiscount'] = 0;
	      $aData['sShipmentDiscount'] = '-';
      } else {
	      $aData['fShipmentDiscount'] = generatePrice( -$aData['iShipmentDiscount'] );
	      $aData['sShipmentDiscount'] = displayPrice( $aData['fShipmentDiscount'] );
      }
      $aData['sDate'] = displayDate( $aData['iTime'] );
      $aData['sPaymentSystem'] = '';
      if( isset( self::$payments_to_qcpayments[$aData['iPayment']] ) && isset( $GLOBALS['aOuterPaymentOption'][self::$payments_to_qcpayments[$aData['iPayment']]] ) ){
        $aData['iPaymentSystem'] = self::$payments_to_qcpayments[$aData['iPayment']];
        $aData['sPaymentChannel'] = $GLOBALS['aOuterPaymentOption'][self::$payments_to_qcpayments[$aData['iPayment']]];
        if( isset( $GLOBALS['aPay'][self::$payments_to_qcpayments[$aData['iPayment']]][$aData['mPaymentChannel']] ) )
          $aData['sPaymentChannel'] .= ' | '.$GLOBALS['aPay'][self::$payments_to_qcpayments[$aData['iPayment']]][$aData['mPaymentChannel']];
      }
      else
        $aData['sPaymentChannel'] = '-';

      $statusOpts = array(''=>'New', -1=>'New Online Order', 1=>'Sales Quote', 2=>'Order Received', 3=>'Payment Confirmed', 4=>'Order Confirmed', 5=>'On Hold', 6=>'Order Ready to Ship', 7=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing');
      $aData['sStatus'] = $statusOpts[$aData['iStatus']];
      if(isset($translations['Premium_Warehouse_Items_Orders'][$aData['sStatus']]) && $translations['Premium_Warehouse_Items_Orders'][$aData['sStatus']])
		$aData['sStatus'] = $translations['Premium_Warehouse_Items_Orders'][$aData['sStatus']];

      if(!$aData['sTrackingInfo'])
	      $aData['sTrackingInfo'] = '-';
      if(!$aData['sShipmentNo'])
	      $aData['sShipmentNo'] = '-';
      if(!$aData['sCompanyName'])
	      $aData['sCompanyName'] = '-';
      if(!$aData['sNip'])
	      $aData['sNip'] = '-';
      if(!$aData['sComment'])
	      $aData['sComment'] = '-';


      $this->aOrders[$iOrder] = $aData;
      return $aData;
    }
  } // end function throwOrder
  
  function throwPromotions($code) {
   	static $c;
   	if(!isset($c)) {
   		$c = DB::GetAll('SELECT f_employee as employee, f_discount as discount FROM premium_ecommerce_promotion_codes_data_1 WHERE f_expiration>%D AND f_promotion_code LIKE %s AND active=1',array(date('Y-m-d'),$code));
   	}
  	return $c;
  }

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
  function throwPaymentCarrier( $iCarrier, $iPayment){
    // { epesi
    if( isset( $GLOBALS['config']['delivery_free'] ) && $_SESSION['fOrderSummary'.LANGUAGE] >= $GLOBALS['config']['delivery_free'] ){
      return 0;
    }
    $currency = $this->getCurrencyId();
    $weight = $this->getWeight();
    $row = DB::GetRow('SELECT f_price,f_description,f_percentage_of_amount,f_order_terms FROM premium_ecommerce_payments_carriers_data_1 WHERE active=1 AND f_payment=%s AND f_shipment=%d AND f_currency=%d
    			AND (f_max_weight>=%f OR f_max_weight is null) ORDER BY (f_price+%f*f_percentage_of_amount/100)',array($iPayment,$iCarrier,$currency,$weight,$_SESSION['fOrderSummary'.LANGUAGE]));
    if($row) {
	    return array('fPrice'=>$row['f_price']+$_SESSION['fOrderSummary'.LANGUAGE]*$row['f_percentage_of_amount']/100,'sDescription'=>str_replace("\n",'<br>',$row['f_description']), 'iPayment'=>$iPayment, 'iCarrier'=>$iCarrier, 'sTerms'=>$row['f_order_terms'],'shipment'=>$row['f_price'],'handling'=>$_SESSION['fOrderSummary'.LANGUAGE]*$row['f_percentage_of_amount']/100);
    }
    return false;
  } // end function throwPaymentCarrier
  
  static $payments_to_qcpayments = array('DotPay'=>1,'Przelewy24'=>2,'PayPal'=>3, 'Platnosci.pl'=>4, 'Zagiel'=>5);
  private $is_pickup = false;

  /**
  * Return list of payments and carriers
  * @return string
  * @param string $sFile
  */
  function listCarriersPayments( $sFile ){
    $oTpl       =& TplParser::getInstance( );
    $content    = null;
    $sPaymentList= null;

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
      $ret = DB::Execute('SELECT f_payment,f_shipment,MIN(f_price+%f*f_percentage_of_amount/100) as f_price FROM premium_ecommerce_payments_carriers_data_1 
    			WHERE active=1 AND f_currency=%s AND (f_max_weight>=%f OR f_max_weight is null) GROUP BY f_payment,f_shipment',array($_SESSION['fOrderSummary'.LANGUAGE],$currency,$weight));
      $aOuterPayments = array();
      while($aExp = $ret->FetchRow()) {
        if($freeDelivery)
    	    $aPaymentsCarriers[$aExp['f_shipment']][$aExp['f_payment']] = 0;
	else
    	    $aPaymentsCarriers[$aExp['f_shipment']][$aExp['f_payment']] = $aExp['f_price'];
	if(isset(self::$payments_to_qcpayments[$aExp['f_payment']]))
		$aOuterPayments[$aExp['f_payment']] = self::$payments_to_qcpayments[$aExp['f_payment']];
      }
      foreach( $aShipments as $iCarrier => $carrier_name ) {
        if(!isset($aPaymentsCarriers[$iCarrier])) continue;
        $aData = array('sName'=>$carrier_name, 'iCarrier'=>$iCarrier, 'sPayments'=>null, 'fPrice'=>0);
        if($iCarrier==0) $this->is_pickup = true; //pickup
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

  function listPickupShops($sFile) {
    $oTpl       =& TplParser::getInstance( );
    $content = null;
    if($this->is_pickup) {
          $shops = DB::GetAll('SELECT id,f_address_1,f_address_2,f_city FROM premium_warehouse_data_1 WHERE active=1 AND f_pickup_place=1');
	  if( $shops ) {
	        $content .= $oTpl->tbHtml( $sFile, 'ORDER_PICKUP_SHOP_HEAD' );
		foreach($shops as $sh) {
			$aData = array();
			$aData['iShop'] = $sh['id'];
			$aData['sName'] = $sh['f_address_1'].($sh['f_address_2']?', '.$sh['f_address_2']:'').', '.$sh['f_city'];
          		$oTpl->setVariables( 'aData', $aData );
		        $content .= $oTpl->tbHtml( $sFile, 'ORDER_PICKUP_SHOP_LIST' );
          	}
	        $content .= $oTpl->tbHtml( $sFile, 'ORDER_PICKUP_SHOP_FOOT' );
          }
      }
      return $content;
  }
  
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
/*  function throwPayment( $iPayment ){
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
    $aPayment = $this->throwPaymentCarrier( $aData['iCarrier'], $aData['iPayment'] );
    if( !empty( $aPayment['sDescription'] ) )
      $aData['sPaymentDescription'] = $aPayment['sDescription'];

    $aData['contactus'] = getVariable('ecommerce_contactus_'.LANGUAGE);
    if(!$aData['contactus'])
	$aData['contactus'] = getVariable('ecommerce_contactus');

    $aData['sCustomHello'] = getVariable('ecommerce_order_email_'.LANGUAGE);
    if(!$aData['sCustomHello'])
	$aData['sCustomHello'] = getVariable('ecommerce_order_email');

    $oTpl->setVariables( 'aData', $aData );
	
    if($aData['sCompanyName']!='-' || $aData['sNip']!='-')
	    $aData['sCompanyInfo'] = $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_COMPANY' );
    if($aData['sShipmentDiscount']!='-')
	    $aData['sShipmentDiscountInfo'] = $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_SHIPMENT_DISCOUNT' );
    if($aData['sPaymentChannel']!='-')
	    $aData['sPaymentChannelInfo'] = $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_PAYMENT_CHANNEL' );

    $oTpl->setVariables( 'aData', $aData );

    $aSend['sMailContent'] = $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_BODY' );
    $aSend['sTopic'] = $oTpl->tbHtml( $sFile, 'ORDER_EMAIL_TITLE' );
    $aSend['sSender']= $GLOBALS['config']['email'];

    sendEmail( $aSend, null, $aData['sEmail'], true ); //send e-mail to client
  } // end function sendEmailWithOrderDetails
};
?>