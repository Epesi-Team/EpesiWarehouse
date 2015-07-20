<?php
/**
 * 
 * @author bukowski@crazyit.pl
 * @copyright Telaxus LLC
 * @license Commercial
 * @version 0.1
 * @package epesi-Premium/Warehouse/DrupalCommerce
 * @subpackage Allegro
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_DrupalCommerce_AllegroCommon extends ModuleCommon {
	public static function warehouse_item_addon_label($r) {
	if(!isset($r['id']))
	    return array('show'=>false);
        $x = Utils_RecordBrowserCommon::get_records_count('premium_ecommerce_products',array('item_name'=>$r['id']));
        if (!$x || !Variable::get('allegro_login')) return array('show'=>false);
        return array('label'=>'Allegro', 'show'=>true);
    }
    
    public static function update_cats() {
    	$country = Variable::get('allegro_country');

    	/* @var $a Allegro */
    	$a = self::get_lib();
    	if(!$a) return array();
    	
    	$fields = $a->get_sell_form_fields();
    	if(!isset($fields['sellFormFields']->item)) return array();
    	$cats = array();
    	$stan = array();
    	$stan_old = DB::GetAssoc('SELECT cat_id,field_id FROM premium_ecommerce_allegro_stan WHERE country=%d',array($country));
    	
    	foreach($fields['sellFormFields']->item as $f) {
    	    if($f->{'sellFormTitle'} == 'Stan') {
    		if(!isset($stan_old[$f->{'sellFormCat'}]) || $stan_old[$f->{'sellFormCat'}]!=$f->{'sellFormId'})
			DB::Replace('premium_ecommerce_allegro_stan', array('field_id'=>$f->{'sellFormId'},'cat_id'=>$f->{'sellFormCat'},'country'=>$country), array('cat_id','country'));
	    	$stan[$f->{'sellFormCat'}] = $f->{'sellFormId'};
	    	unset($stan_old[$f->{'sellFormCat'}]);
	    }
    	}

    	$ret = $a->get_categories();
    	if(!is_array($ret['catsList']->item)) return array();
    	$cats = array();
    	$cats_with_ch = array();
    	foreach($ret['catsList']->item as $c) {
    		if($c->{'catParent'}==0) {
    			$cats[$c->{'catId'}] = $c->{'catName'};
    		} else {
    		    if(!isset($stan[$c->{'catId'}]) && isset($stan[$c->{'catParent'}])) {
		    	DB::Replace('premium_ecommerce_allegro_stan', array('field_id'=>$stan[$c->{'catParent'}],'cat_id'=>$c->{'catId'},'country'=>$country), array('cat_id','country'));
		    	$stan[$c->{'catId'}] = $stan[$c->{'catParent'}];
		    	unset($stan_old[$c->{'catId'}]);
    		    }
    		    if(isset($cats[$c->{'catParent'}])) {
    			$cats[$c->{'catId'}] = $cats[$c->{'catParent'}].'&rarr;'.$c->{'catName'};
    			$cats_with_ch[$c->{'catParent'}] = $cats[$c->{'catParent'}];
    			unset($cats[$c->{'catParent'}]);
    		    } elseif(isset($cats_with_ch[$c->{'catParent'}])) {
    			$cats[$c->{'catId'}] = $cats_with_ch[$c->{'catParent'}].'&rarr;'.$c->{'catName'};
    		    } else {
    			$ret['catsList']->item[] = $c;
    		    }
    		}
    	}
    	if($stan_old)
    		DB::Execute('DELETE FROM premium_ecommerce_allegro_stan WHERE country=%d AND cat_id IN ('.implode(',',array_keys($stan_old)).')',array($country));
	DB::Execute('DELETE FROM premium_ecommerce_allegro_cats WHERE country=%d',array($country));
    	foreach($cats as $k=>$v)
	    	DB::Replace('premium_ecommerce_allegro_cats', array('id'=>$k,'name'=>DB::qstr($v),'country'=>$country), array('id','country'));
    	return $cats;
    }
    
    public static function update_statuses() {
    	$a = self::get_lib();
    	if(!$a) return;
    	
    	DB::Execute('DELETE FROM premium_ecommerce_allegro_cross WHERE created_on<%T',array(time()-3600*12));
    	
    	$ids = DB::GetCol('SELECT auction_id FROM premium_ecommerce_allegro_auctions WHERE active=1');
    	$ids = array_map(create_function('$a','return (float)$a;'),$ids);
    	$ret = $a->get_auctions_info($ids);
    	$close = array();
    	$bids = array();
    	$auctions_info = array();
    	foreach($ret['arrayItemListInfo'] as $it) {
    		if($it->{'itemInfo'}->{'itEndingInfo'}>1) {
    			$close[] = $it->{'itemInfo'}->{'itId'};
    			$auctions_info[(string)$it->{'itemInfo'}->{'itId'}] = $it;
    		}
    		if($it->{'itemInfo'}->itBidCount>0) {
    			$bids[(string)$it->{'itemInfo'}->{'itId'}] = $it->{'itemInfo'}->itBidCount; //float cannot be as array key
    		}
    	}
    	if($bids) {
    		$bids_db = DB::GetAssoc('SELECT auction_id,bids FROM premium_ecommerce_allegro_auctions WHERE auction_id IN ('.implode(',',array_keys($bids)).')');
    		$bids_ids = array();
    		foreach($bids_db as $auction_id=>$bid) {
    			if($bid!=$bids[$auction_id]) {
    				$bids_ids[] = (float)$auction_id; //need float to pass to webapi
    			}
    		}
    		$todo = array_combine($bids_ids,array_fill(0,count($bids_ids),1));
//    		print_r($todo);
		$transactions = $a->get_transactions($bids_ids);
		$bids_data = $a->get_bids_user_data($bids_ids);
		$currency = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		$shipments = $a->get_shipments();
		foreach($transactions as $trans) {
			$t = time();

			if( isset( $trans->postBuyFormItems->item ) ){
			    if(!is_array($trans->postBuyFormItems->item)) $trans->postBuyFormItems->item = array($trans->postBuyFormItems->item);
			    $keys = array_keys($trans->postBuyFormItems->item);
			    if(!isset($trans->postBuyFormItems->item[$keys[0]]->postBuyFormItId)) continue;
			    $bid_id = (string)$trans->postBuyFormItems->item[$keys[0]]->postBuyFormItId;
			} else {
			    continue;
			}
			unset($todo[$bid_id]);

			if(DB::GetOne('SELECT 1 FROM premium_warehouse_items_orders_data_1 WHERE f_allegro_order=%s AND active=1',array($trans->postBuyFormId))) continue;

			$bid = $bids_data[$bid_id];
			$buyer = null;
			if(!is_array($bid->usersPostBuyData->item)) $bid->usersPostBuyData->item = array($bid->usersPostBuyData->item);
			foreach($bid->usersPostBuyData->item as $user_key=>$user) {
			    if($user->userData->userId==$trans->postBuyFormBuyerId) {
				$buyer = $user;
				unset($bids_data[$bid_id]->usersPostBuyData->item[$user_key]);
				break;
			    }
			}
			if($buyer===null) continue;
			
			$company = DB::GetOne('SELECT id FROM company_data_1 WHERE f_email=%s AND active=1',array($buyer->userData->userEmail));
			if(!$company) {
				if(isset($trans->postBuyFormInvoiceData->postBuyFormAdrCompany) && $trans->postBuyFormInvoiceData->postBuyFormAdrCompany) {
					$company = Utils_RecordBrowserCommon::new_record('company',array('company_name'=>$trans->postBuyFormInvoiceData->postBuyFormAdrCompany,
					    'tax_id'=>$trans->postBuyFormInvoiceData->postBuyFormAdrNip,
					    'address_1'=>$trans->postBuyFormInvoiceData->postBuyFormAdrStreet,
					    'postal_code'=>$trans->postBuyFormInvoiceData->postBuyFormAdrPostcode,
					    'city'=>$trans->postBuyFormInvoiceData->postBuyFormAdrCity,
					    'country'=>'PL',
					    'phone'=>$buyer->userData->userPhone,
					    'email'=>$buyer->userData->userEmail,
					    'group'=>'customer'
					    ));
				} else $company = null;
			}
			$contact = DB::GetOne('SELECT id FROM contact_data_1 WHERE f_email=%s AND active=1',array($buyer->userData->userEmail));
			if(!$contact) {
				$contact = Utils_RecordBrowserCommon::new_record('contact',array('first_name'=>$buyer->userData->userFirstName,
					    'last_name'=>$buyer->userData->userLastName,
					    'address_1'=>$buyer->userData->userAddress,
					    'postal_code'=>$buyer->userData->userPostcode,
					    'city'=>$buyer->userData->userCity,
					    'country'=>'PL',
					    'mobile_phone'=>$buyer->userData->userPhone,
					    'work_phone'=>$buyer->userData->userPhone2,
					    'email'=>$buyer->userData->userEmail,
					    'group'=>'customer'
					    ));
				
			}

			$shipping_name = explode(" ",$trans->postBuyFormShipmentAddress->postBuyFormAdrFullName,2);

			$pay_type = 'Inna';
			if($trans->postBuyFormPayType == 'm') $pay_type = 'mTransfer - mBank';
			if($trans->postBuyFormPayType == 'n') $pay_type = 'MultiTransfer - MultiBank';
			if($trans->postBuyFormPayType == 'w') $pay_type = 'BZWBK - Przelew24';
			if($trans->postBuyFormPayType == 'o') $pay_type = 'Pekao24Przelew - Bank Pekao';
			if($trans->postBuyFormPayType == 'i') $pay_type = 'Płacę z Inteligo';
			if($trans->postBuyFormPayType == 'd') $pay_type = 'Płać z Nordea';
			if($trans->postBuyFormPayType == 'p') $pay_type = 'Płać z iPKO';
			if($trans->postBuyFormPayType == 'h') $pay_type = 'Płać z BPH';
			if($trans->postBuyFormPayType == 'g') $pay_type = 'Płać z ING ';
			if($trans->postBuyFormPayType == 'l') $pay_type = 'LUKAS e-przelew';
			if($trans->postBuyFormPayType == 'u') $pay_type = 'Eurobank';
			if($trans->postBuyFormPayType == 'me') $pay_type = 'Meritum Bank';
			if($trans->postBuyFormPayType == 'ab') $pay_type = 'Płacę z Alior Bankiem';
			if($trans->postBuyFormPayType == 'wp') $pay_type = 'Przelew z Polbank';
			if($trans->postBuyFormPayType == 'wm') $pay_type = 'Przelew z Millennium';
			if($trans->postBuyFormPayType == 'wk') $pay_type = 'Przelew z Kredyt Bank';
			if($trans->postBuyFormPayType == 'wg') $pay_type = 'Przelew z BGŻ ';
			if($trans->postBuyFormPayType == 'wd') $pay_type = 'Przelew z Deutsche Bank';
			if($trans->postBuyFormPayType == 'wr') $pay_type = 'Przelew z Raiffeisen Bank';
			if($trans->postBuyFormPayType == 'wc') $pay_type = 'Przelew z Citibank';
			if($trans->postBuyFormPayType == 'wn') $pay_type = 'Przelew z Invest Bank';
			if($trans->postBuyFormPayType == 'wi') $pay_type = 'Przelew z Getin Bank ';
			if($trans->postBuyFormPayType == 'wy') $pay_type = 'Przelew z Bankiem Pocztowym';
			if($trans->postBuyFormPayType == 'c') $pay_type = 'Karta kredytowa';
			if($trans->postBuyFormPayType == 'b') $pay_type = 'Przelew bankowy';
			if($trans->postBuyFormPayType == 't') $pay_type = 'Płatność testowa';
			if($trans->postBuyFormPayType == 'co') $pay_type = 'Checkout PayU';
			if($trans->postBuyFormPayType == 'ai') $pay_type = 'Raty PayU';
			if($trans->postBuyFormPayType == 'collect_on_delivery') $pay_type = 'Płacę przy odbiorze';
			if($trans->postBuyFormPayType == 'wire_transfer') $pay_type = 'Zwykły przelew - poza systemem Płacę z Allegro';
			if($trans->postBuyFormPayType == 'not_specified') $pay_type = 'nie określona';
			
			$id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders',array(
			    'transaction_type'=>1,
			    'transaction_date'=>$t,
			    'allegro_order'=>$trans->postBuyFormId,
			    'allegro_auction'=>$bid_id,
			    'company_name'=>$trans->postBuyFormInvoiceData->postBuyFormAdrCompany?$trans->postBuyFormInvoiceData->postBuyFormAdrCompany:$buyer->userData->userCompany,
			    'last_name'=>$buyer->userData->userLastName,
			    'first_name'=>$buyer->userData->userFirstName,
			    'address_1'=>$trans->postBuyFormInvoiceData->postBuyFormAdrStreet?$trans->postBuyFormInvoiceData->postBuyFormAdrStreet:$buyer->userData->userAddress,
			    'city'=>$trans->postBuyFormInvoiceData->postBuyFormAdrCity?$trans->postBuyFormInvoiceData->postBuyFormAdrCity:$buyer->userData->userCity,
			    'postal_code'=>$trans->postBuyFormInvoiceData->postBuyFormAdrPostcode?$trans->postBuyFormInvoiceData->postBuyFormAdrPostcode:$buyer->userData->userPostcode,
			    'phone'=>$trans->postBuyFormInvoiceData->postBuyFormAdrPhone?$trans->postBuyFormInvoiceData->postBuyFormAdrPhone:$buyer->userData->userPhone,
			    'country'=>'PL',
			    'zone'=>'',
			    'memo'=>'Użytkownik: '.$buyer->userData->userLogin."\nMetoda płatności: ".$pay_type."\nMetoda wysyłki: ".$shipments[$trans->postBuyFormShipmentId]."\n".$trans->postBuyFormMsgToSeller,
			    'created_on'=>$t,
			    'shipment_type'=>($trans->postBuyFormShipmentId<9?1:($trans->postBuyFormShipmentId==10 || $trans->postBuyFormShipmentId==13?0:($trans->postBuyFormShipmentId==9 || $trans->postBuyFormShipmentId==11?5:6))),
			    'shipment_cost'=>$trans->postBuyFormPostageAmount.'__'.$currency,
			    'payment'=>1,
			    'payment_type'=>($trans->postBuyFormPayType=='wire_transfer'?2:($trans->postBuyFormPayType=='collect_on_delivery'?9:5)),
			    'tax_id'=>$trans->postBuyFormInvoiceData->postBuyFormAdrNip,
			    'warehouse'=>null,
			    'online_order'=>1,
			    'status'=>-1,
			    'contact'=>$contact,
			    'company'=>$company,
			    'terms'=>null,
			    'receipt'=>$trans->postBuyFormInvoiceOption?0:1,
			    'handling_cost'=>null,
			    'shipping_company_name'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrCompany,
			    'shipping_last_name'=>isset($shipping_name[1])?$shipping_name[1]:'',
			    'shipping_first_name'=>$shipping_name[0],
			    'shipping_address_1'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrStreet,
			    'shipping_city'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrCity,
			    'shipping_postal_code'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrPostcode,
			    'shipping_phone'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrPhone,
			    'shipping_country'=>'PL'
			));
    	

			if( isset( $trans->postBuyFormItems->item ) ){
			  foreach( $trans->postBuyFormItems->item as $aData ){
				$item_id = DB::GetOne('SELECT item_id FROM premium_ecommerce_allegro_auctions WHERE auction_id=%s',array($aData->postBuyFormItId));
				$pr = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$item_id);
				$net = $aData->postBuyFormItPrice*100/(100+Data_TaxRatesCommon::get_tax_rate($pr['tax_rate']));
				ob_start();
				Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details',array('transaction_id'=>$id,'item_name'=>$item_id,'quantity'=>$aData->postBuyFormItQuantity,'description'=>$aData->postBuyFormItTitle,'tax_rate'=>$pr['tax_rate'],'net_price'=>$net.'__'.$currency,'gross_price'=>$aData->postBuyFormItPrice.'__'.$currency));
				ob_end_clean();
		          }
			}

		}
		
		//print_r($todo);
//		print_r($auctions_info);
		foreach($todo as $bid_id=>$xxxx) {
			if(!isset($auctions_info[$bid_id])) continue;
			
			$bid = $bids_data[$bid_id];
//			print_r($bid);
			$buyer = null;
			if(!is_array($bid->usersPostBuyData->item)) $bid->usersPostBuyData->item = array($bid->usersPostBuyData->item);
			$buyer = array_shift($bid->usersPostBuyData->item);
			if(!$buyer) continue;
//			print_r($buyer);

			$contact = DB::GetOne('SELECT id FROM contact_data_1 WHERE f_email=%s AND active=1',array($buyer->userData->userEmail));
			if(!$contact) {
				$contact = Utils_RecordBrowserCommon::new_record('contact',array('first_name'=>$buyer->userData->userFirstName,
					    'last_name'=>$buyer->userData->userLastName,
					    'address_1'=>$buyer->userData->userAddress,
					    'postal_code'=>$buyer->userData->userPostcode,
					    'city'=>$buyer->userData->userCity,
					    'country'=>'PL',
					    'mobile_phone'=>$buyer->userData->userPhone,
					    'work_phone'=>$buyer->userData->userPhone2,
					    'email'=>$buyer->userData->userEmail,
					    'group'=>'customer'
					    ));
				
			}

			$t = time();
			$id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders',array(
			    'transaction_type'=>1,
			    'transaction_date'=>$t,
			    'allegro_auction'=>$bid_id,
			    'company_name'=>$buyer->userData->userCompany,
			    'last_name'=>$buyer->userData->userLastName,
			    'first_name'=>$buyer->userData->userFirstName,
			    'address_1'=>$buyer->userData->userAddress,
			    'city'=>$buyer->userData->userCity,
			    'postal_code'=>$buyer->userData->userPostcode,
			    'phone'=>$buyer->userData->userPhone,
			    'country'=>'PL',
			    'zone'=>'',
			    'payment'=>1,
			    'memo'=>'Użytkownik: '.$buyer->userData->userLogin."\nZamówienie bez uzupełnionego formularza dostawy - sprawdź allegro oraz pocztę e-mail.\nJeżeli formularz zostanie uzupełniony przez klienta i zamówienie to nie będzie jeszcze przetwarzane, to zostanie ono automatycznie zaktualizowane.",
			    'created_on'=>$t,
			    'warehouse'=>null,
			    'online_order'=>1,
			    'status'=>-1,
			    'contact'=>$contact,
			    'terms'=>null,
			    'handling_cost'=>null,
			));
		    
	/*		$item_id = DB::GetOne('SELECT item_id FROM premium_ecommerce_allegro_auctions WHERE auction_id=%s',array($bid_id));
			$pr = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$item_id);
			$net = $auctions_info[$bid_id]->{'itemInfo'}->{'itPrice'}*100/(100+Data_TaxRatesCommon::get_tax_rate($pr['tax_rate']));
			ob_start();
			Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details',array('transaction_id'=>$id,'item_name'=>$item_id,'quantity'=>1,'description'=>$auctions_info[$bid_id]->{'itemInfo'}->{'itName'},'tax_rate'=>$pr['tax_rate'],'net_price'=>$net.'__'.$currency,'gross_price'=>$auctions_info[$bid_id]->{'itemInfo'}->{'itPrice'}.'__'.$currency));
			ob_end_clean();*/
		}
//		if($todo) Base_MailCommon::send('bukowski@crazyit.pl','aukcje bez uzupełnionego formularza',implode("\n",array_keys($todo)));
		
		foreach($bids_ids as $bid_id) {
			DB::Execute('UPDATE premium_ecommerce_allegro_auctions SET bids=%d WHERE auction_id=%d',array($bids[(string)$bid_id],$bid_id));
		}
    	}
    	if($close)    	
    		DB::Execute('UPDATE premium_ecommerce_allegro_auctions SET active=0,ended_on=%T WHERE auction_id IN ('.implode(',',$close).')',array(time()));
    	
//    	if($ret['arrayItemsNotFound'])
//    		DB::GetCol('UPDATE premium_ecommerce_allegro_auctions SET active=0 WHERE auction_id IN ('.implode(',',$ret['arrayItemsNotFound']).')');
    	if($ret['arrayItemsAdminKilled'])
    		DB::GetCol('UPDATE premium_ecommerce_allegro_auctions SET active=0,ended_on=%T WHERE auction_id IN ('.implode(',',$ret['arrayItemsAdminKilled']).')',array(time()));
    	
    	$ret = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',array('!allegro_auction'=>'','allegro_order'=>'','status'=>-1));
	$bids_ids = array();
	foreach($ret as $r) {
	    $bids_ids[] = (float)$r['allegro_auction'];
	}

	$transactions = $a->get_transactions($bids_ids);
	$bids_data = $a->get_bids_user_data($bids_ids);
	$currency = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
	$shipments = $a->get_shipments();
	foreach($transactions as $trans) {
		$t = time();

		if( isset( $trans->postBuyFormItems->item ) ){
		    if(!is_array($trans->postBuyFormItems->item)) $trans->postBuyFormItems->item = array($trans->postBuyFormItems->item);
		    $keys = array_keys($trans->postBuyFormItems->item);
		    if(!isset($trans->postBuyFormItems->item[$keys[0]]->postBuyFormItId)) continue;
		    $bid_id = (string)$trans->postBuyFormItems->item[$keys[0]]->postBuyFormItId;
		} else {
		    continue;
		}
		if(DB::GetOne('SELECT 1 FROM premium_warehouse_items_orders_data_1 WHERE f_allegro_order=%s AND active=1',array($trans->postBuyFormId))) continue;
		$bid = $bids_data[$bid_id];
		$buyer = null;
		if(!is_array($bid->usersPostBuyData->item)) $bid->usersPostBuyData->item = array($bid->usersPostBuyData->item);
		foreach($bid->usersPostBuyData->item as $user_key=>$user) {
		    if($user->userData->userId==$trans->postBuyFormBuyerId) {
			$buyer = $user;
			unset($bids_data[$bid_id]->usersPostBuyData->item[$user_key]);
			break;
		    }
		}
		if($buyer===null) continue;
		
		$company = DB::GetOne('SELECT id FROM company_data_1 WHERE f_email=%s AND active=1',array($buyer->userData->userEmail));
		if(!$company) {
			if(isset($trans->postBuyFormInvoiceData->postBuyFormAdrCompany) && $trans->postBuyFormInvoiceData->postBuyFormAdrCompany) {
					$company = Utils_RecordBrowserCommon::new_record('company',array('company_name'=>$trans->postBuyFormInvoiceData->postBuyFormAdrCompany,
					    'tax_id'=>$trans->postBuyFormInvoiceData->postBuyFormAdrNip,
					    'address_1'=>$trans->postBuyFormInvoiceData->postBuyFormAdrStreet,
					    'postal_code'=>$trans->postBuyFormInvoiceData->postBuyFormAdrPostcode,
					    'city'=>$trans->postBuyFormInvoiceData->postBuyFormAdrCity,
					    'country'=>'PL',
					    'phone'=>$buyer->userData->userPhone,
					    'email'=>$buyer->userData->userEmail,
					    'group'=>'customer'
					    ));
			} else $company = null;
		}
		$contact = DB::GetOne('SELECT id FROM contact_data_1 WHERE f_email=%s AND active=1',array($buyer->userData->userEmail));
		if(!$contact) continue;

		$shipping_name = explode(" ",$trans->postBuyFormShipmentAddress->postBuyFormAdrFullName,2);
		$pay_type = 'Inna';
		if($trans->postBuyFormPayType == 'm') $pay_type = 'mTransfer - mBank';
		if($trans->postBuyFormPayType == 'n') $pay_type = 'MultiTransfer - MultiBank';
		if($trans->postBuyFormPayType == 'w') $pay_type = 'BZWBK - Przelew24';
		if($trans->postBuyFormPayType == 'o') $pay_type = 'Pekao24Przelew - Bank Pekao';
		if($trans->postBuyFormPayType == 'i') $pay_type = 'Płacę z Inteligo';
		if($trans->postBuyFormPayType == 'd') $pay_type = 'Płać z Nordea';
		if($trans->postBuyFormPayType == 'p') $pay_type = 'Płać z iPKO';
		if($trans->postBuyFormPayType == 'h') $pay_type = 'Płać z BPH';
		if($trans->postBuyFormPayType == 'g') $pay_type = 'Płać z ING ';
		if($trans->postBuyFormPayType == 'l') $pay_type = 'LUKAS e-przelew';
		if($trans->postBuyFormPayType == 'u') $pay_type = 'Eurobank';
		if($trans->postBuyFormPayType == 'me') $pay_type = 'Meritum Bank';
		if($trans->postBuyFormPayType == 'ab') $pay_type = 'Płacę z Alior Bankiem';
		if($trans->postBuyFormPayType == 'wp') $pay_type = 'Przelew z Polbank';
		if($trans->postBuyFormPayType == 'wm') $pay_type = 'Przelew z Millennium';
		if($trans->postBuyFormPayType == 'wk') $pay_type = 'Przelew z Kredyt Bank';
		if($trans->postBuyFormPayType == 'wg') $pay_type = 'Przelew z BGŻ ';
		if($trans->postBuyFormPayType == 'wd') $pay_type = 'Przelew z Deutsche Bank';
		if($trans->postBuyFormPayType == 'wr') $pay_type = 'Przelew z Raiffeisen Bank';
		if($trans->postBuyFormPayType == 'wc') $pay_type = 'Przelew z Citibank';
		if($trans->postBuyFormPayType == 'wn') $pay_type = 'Przelew z Invest Bank';
		if($trans->postBuyFormPayType == 'wi') $pay_type = 'Przelew z Getin Bank ';
		if($trans->postBuyFormPayType == 'wy') $pay_type = 'Przelew z Bankiem Pocztowym';
		if($trans->postBuyFormPayType == 'c') $pay_type = 'Karta kredytowa';
		if($trans->postBuyFormPayType == 'b') $pay_type = 'Przelew bankowy';
		if($trans->postBuyFormPayType == 't') $pay_type = 'Płatność testowa';
		if($trans->postBuyFormPayType == 'co') $pay_type = 'Checkout PayU';
		if($trans->postBuyFormPayType == 'ai') $pay_type = 'Raty PayU';
		if($trans->postBuyFormPayType == 'collect_on_delivery') $pay_type = 'Płacę przy odbiorze';
		if($trans->postBuyFormPayType == 'wire_transfer') $pay_type = 'Zwykły przelew - poza systemem Płacę z Allegro';
		if($trans->postBuyFormPayType == 'not_specified') $pay_type = 'nie określona';
		
		$orders = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',array('contact'=>$contact,'allegro_auction'=>$bid_id,'allegro_order'=>'','status'=>-1));
		if(!$orders) continue;
		$order = array_shift($orders);
		$id = $order['id'];
		
		Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders',$id,array(
			    'transaction_type'=>1,
			    'allegro_order'=>$trans->postBuyFormId,
			    'company_name'=>$trans->postBuyFormInvoiceData->postBuyFormAdrCompany?$trans->postBuyFormInvoiceData->postBuyFormAdrCompany:$buyer->userData->userCompany,
			    'last_name'=>$buyer->userData->userLastName,
			    'first_name'=>$buyer->userData->userFirstName,
			    'address_1'=>$trans->postBuyFormInvoiceData->postBuyFormAdrStreet?$trans->postBuyFormInvoiceData->postBuyFormAdrStreet:$buyer->userData->userAddress,
			    'city'=>$trans->postBuyFormInvoiceData->postBuyFormAdrCity?$trans->postBuyFormInvoiceData->postBuyFormAdrCity:$buyer->userData->userCity,
			    'postal_code'=>$trans->postBuyFormInvoiceData->postBuyFormAdrPostcode?$trans->postBuyFormInvoiceData->postBuyFormAdrPostcode:$buyer->userData->userPostcode,
			    'phone'=>$trans->postBuyFormInvoiceData->postBuyFormAdrPhone?$trans->postBuyFormInvoiceData->postBuyFormAdrPhone:$buyer->userData->userPhone,
			    'country'=>'PL',
			    'zone'=>'',
			    'memo'=>'Użytkownik: '.$buyer->userData->userLogin."\nMetoda płatności: ".$pay_type."\nMetoda wysyłki: ".$shipments[$trans->postBuyFormShipmentId]."\n".$trans->postBuyFormMsgToSeller,
			    'created_on'=>$t,
			    'shipment_type'=>($trans->postBuyFormShipmentId<9?1:($trans->postBuyFormShipmentId==10 || $trans->postBuyFormShipmentId==13?0:($trans->postBuyFormShipmentId==9 || $trans->postBuyFormShipmentId==11?5:6))),
			    'shipment_cost'=>$trans->postBuyFormPostageAmount.'__'.$currency,
			    'payment'=>1,
			    'payment_type'=>($trans->postBuyFormPayType=='wire_transfer'?2:($trans->postBuyFormPayType=='collect_on_delivery'?9:5)),
			    'tax_id'=>$trans->postBuyFormInvoiceData->postBuyFormAdrNip,
			    'warehouse'=>null,
			    'online_order'=>1,
			    'status'=>-1,
			    'company'=>$company,
			    'terms'=>null,
			    'receipt'=>$trans->postBuyFormInvoiceOption?0:1,
			    'handling_cost'=>null,
			    'shipping_company_name'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrCompany,
			    'shipping_last_name'=>isset($shipping_name[1])?$shipping_name[1]:$shipping_name[0],
			    'shipping_first_name'=>isset($shipping_name[1])?$shipping_name[0]:'',
			    'shipping_address_1'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrStreet,
			    'shipping_city'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrCity,
			    'shipping_postal_code'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrPostcode,
			    'shipping_phone'=>$trans->postBuyFormShipmentAddress->postBuyFormAdrPhone,
			    'shipping_country'=>'PL'
			));
    	

		if( isset( $trans->postBuyFormItems->item ) ){
		  foreach( $trans->postBuyFormItems->item as $aData ){
			$item_id = DB::GetOne('SELECT item_id FROM premium_ecommerce_allegro_auctions WHERE auction_id=%s',array($aData->postBuyFormItId));
			$pr = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$item_id);
			$net = $aData->postBuyFormItPrice*100/(100+Data_TaxRatesCommon::get_tax_rate($pr['tax_rate']));
			ob_start();
			Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details',array('transaction_id'=>$id,'item_name'=>$item_id,'quantity'=>$aData->postBuyFormItQuantity,'description'=>$aData->postBuyFormItTitle,'tax_rate'=>$pr['tax_rate'],'net_price'=>$net.'__'.$currency,'gross_price'=>$aData->postBuyFormItPrice.'__'.$currency));
			ob_end_clean();
	          }
		}
	}
    }
    
    public static function cron() {
        return array('update_statuses'=>5,'update_cats'=>60*24*3);
    }
    
    public static function get_lib($trig_error=true) {
    	static $a;
    	if($a===null) {
    	    if(!Variable::get('allegro_pass')) return false;
    		require_once('modules/Premium/Warehouse/DrupalCommerce/Allegro/allegro.php');
    		$a = new Allegro(Variable::get('allegro_login'),
    			Variable::get('allegro_pass'),
    			Variable::get('allegro_country'),
    			Variable::get('allegro_key'));
    		if($a->error()) {
    		    if($trig_error) trigger_error($a->error(),E_USER_ERROR);
    		    else return false;
    		}
    	}
    	return $a;
    }
    
    public static function get_templates() {
		$templates = array(''=>'---');
		$dd = self::Instance()->get_data_dir();
		if (!is_dir($dd)) return $templates;
		foreach(scandir($dd) as $d) {
			if(!preg_match('/\.tpl$/i',$d)) continue;
			$templates[$dd.$d] = $d;
		}
		return $templates;    	
    }
    
    
    public static function admin_caption() {
        return 'Allegro';
    }
    
    public static function get_other_auctions($id,$cache = false) {
	$lock_dir = DATA_DIR.'/Premium_Warehouse_DrupalCommerce_Allegro/lock';
	@mkdir($lock_dir);
	$lock_file = $lock_dir.'/'.$id.'_'.$_SERVER['REMOTE_ADDR'];
	if(file_exists($lock_file)) {
		$fp = fopen($lock_file, "w");
		flock($fp, LOCK_EX);
		fclose($fp);
		$result = DB::GetAll('SELECT c.ret_item_id as item, c.ret_auction_id as auction,a.buy_price FROM premium_ecommerce_allegro_cross c INNER JOIN premium_ecommerce_allegro_auctions a ON a.auction_id=c.ret_auction_id AND a.item_id=c.ret_item_id  WHERE c.item_id=%d AND c.ip=%s ORDER BY c.position',array($id,$_SERVER['REMOTE_ADDR']));
		if($result) return $result;
	}
	
	$fp = fopen($lock_file, "w");
	flock($fp, LOCK_EX);

	$result = array();
	$skip_item_ids = array($id);
	$items = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('item_name'=>$id));
	foreach($items as $i) {
		foreach(array_merge($i['popup_products'],$i['related_products']) as $p) {
			$pp = Utils_RecordBrowserCommon::get_record('premium_ecommerce_products',$p,array('item_name'));
			$x = DB::GetRow('SELECT auction_id,buy_price FROM premium_ecommerce_allegro_auctions WHERE item_id=%d AND active=1',array($pp['item_name']));
			if($x) {
			    $result[] = array('auction'=>$x['auction_id'],'item'=>$pp['item_name'],'buy_price'=>$x['buy_price']);
			    $skip_item_ids[] = $pp['item_name'];
			}
		}
	}
        $products = DB::GetCol('SELECT or_det.f_item_name FROM premium_warehouse_items_orders_details_data_1 or_det 
			    WHERE or_det.f_item_name!=%d AND or_det.f_transaction_id IN 
			    (SELECT ord.f_transaction_id FROM premium_warehouse_items_orders_details_data_1 or_det2 
			    INNER JOIN premium_ecommerce_orders_data_1 ord ON ord.f_transaction_id=or_det2.f_transaction_id
			    WHERE ord.f_language="pl" AND or_det2.f_item_name=%d) GROUP BY or_det.f_item_name ORDER BY count(or_det.f_item_name) DESC LIMIT 9',array($id,$id));
	foreach($products as $p) {
		$pp = Utils_RecordBrowserCommon::get_record('premium_ecommerce_products',$p,array('item_name'));
		$x = DB::GetRow('SELECT auction_id,buy_price FROM premium_ecommerce_allegro_auctions WHERE item_id=%d AND active=1 AND auction_id>=0',array($pp['item_name']));
		if($x) {
			$result[] = array('auction'=>$x['auction_id'],'item'=>$pp['item_name'],'buy_price'=>$x['buy_price']);
			$skip_item_ids[] = $pp['item_name'];
		}
	}
	for($i=count($result); $i<9; $i++) {
	        $row = DB::GetRow('SELECT c.ret_item_id as item, c.ret_auction_id as auction,a.buy_price FROM premium_ecommerce_allegro_cross c INNER JOIN premium_ecommerce_allegro_auctions a ON a.auction_id=c.ret_auction_id AND a.item_id=c.ret_item_id  WHERE c.item_id=%d AND c.position=%d AND ret_auction_id>=0 AND c.ip=%s ORDER BY c.position',array($id,$i,$_SERVER['REMOTE_ADDR']));
		if(!$row) {
			$row = DB::GetRow('SELECT auction_id as auction,item_id as item,buy_price FROM premium_ecommerce_allegro_auctions WHERE active=1 AND auction_id>=0 AND item_id NOT IN ('.implode(',',$skip_item_ids).') ORDER BY RAND()');
			if(!$row) break;
		}
		$skip_item_ids[] = $row['item'];
		$result[] = $row;
	}
	foreach($result as $pos => $row) {
		@DB::Execute('INSERT INTO premium_ecommerce_allegro_cross(ret_item_id,ret_auction_id,item_id,position,ip) VALUES(%d,%s,%d,%d,%s)',array($row['item'],$row['auction'],$id,$pos,$_SERVER['REMOTE_ADDR']));
	}

	fclose($fp);
	@unlink($lock_file);
	return $result;
    }

	public static function allegro_filter($choice) {
		if ($choice=='__NULL__') return array();
		$ids = DB::GetCol('SELECT item_id FROM premium_ecommerce_allegro_auctions WHERE active=1');
		if($choice) return array('id'=>$ids);
		return array('!id'=>$ids);
	}


	public static function autoselect_category_search($arg=null, $id=null) {
		$country = Variable::get('allegro_country');
		$cats = DB::GetOne('SELECT 1 FROM premium_ecommerce_allegro_cats WHERE country=%d',array($country));
		if(!$cats)
			Premium_Warehouse_DrupalCommerce_AllegroCommon::update_cats();
			
		$args = explode(' ',$arg);
		$wh = '';
		foreach($args as $aaa) {
			$wh .= ' AND name LIKE CONCAT("%%",'.DB::qstr($aaa).',"%%")';
		}
		$cats = DB::GetAssoc('SELECT id,name FROM premium_ecommerce_allegro_cats WHERE country=%d'.$wh.' ORDER BY name',array($country));
		return $cats;
	}

	public static function autoselect_category_format($arg=null) {
		$country = Variable::get('allegro_country');
		$emps = DB::GetOne('SELECT name FROM premium_ecommerce_allegro_cats WHERE country=%d AND id=%d',array($country,$arg));
		return $emps;
	}

    public static function QFfield_allegro_order(&$form, $field, $label, $mode, $default) {
        $form->addElement('static', $field, $label);
        if($field=='allegro_auction') 
            $form->setDefaults(array($field=>'<a href="http://allegro.pl/show_item.php?item='.$default.'" target="_blank">'.$default.'</a>'));
        else
	    $form->setDefaults(array($field=>$default));
    }

	public static function applet_caption() {
		return "Allegro";
	}

	public static function applet_info() {
		return "Ostatnio zakończone aukcje na allegro";
	}
	
	private static $photos;
	public static function collect_photos($id,$file,$original,$args=null) {
	    if(count(self::$photos)==1) return;
	    $ext = strrchr($original,'.');
	    if(preg_match('/^\.(jpg|jpeg|gif|png|bmp)$/i',$ext)) {
                $th1 = Utils_ImageCommon::create_thumb($file,640,480);
	        self::$photos[] = $th1['thumb'];
	    }
	}
	
	public static function delete_photos() {
	    foreach(self::$photos as $ph)
		@unlink($ph);
	}
	
	public static function get_publish_array($r,$vals,$auction_cost=0) {
			$country = Variable::get('allegro_country');

			/* @var $a Allegro */
			$a = self::get_lib();
			if(!$a) {
			    Epesi::alert('Skonfiguruj moduł Allegro');
			    return array();
			}

			self::$photos = array();
			Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products/'.$r['id'],array('Premium_Warehouse_DrupalCommerce_AllegroCommon','collect_photos'));
			Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions/pl/'.$r['id'],array('Premium_Warehouse_DrupalCommerce_AllegroCommon','collect_photos'));
			$fields = array();
			$fields[] =    array(
		      			'fid' => 1,   // Tytuł
		        		'fvalueString' => $vals['title'],
				        'fvalueInt' => 0,
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
		      			'fid' => 2,   // Kategoria
		        		'fvalueString' => '',
				        'fvalueInt' => $vals['category'],
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 3,   // Data rozpoczęcia
				        'fvalueString' => '',
				        'fvalueInt' => time(),
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 4,   // Czas trwania
				        'fvalueString' => '',
				        'fvalueInt' => $vals['days'],
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 5,   // Ilość sztuk
				        'fvalueString' => '',
				        'fvalueInt' => $vals['qty'],
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 6,   // Cena wywoławcza
				        'fvalueString' => '',
				        'fvalueInt' => 0,
				        'fvalueFloat' => $vals['initial_price']?$vals['initial_price']+((isset($vals['add_auction_cost']) && $vals['add_auction_cost'] && $auction_cost)?$auction_cost:0):'',
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 7,   // Cena minimalna
				        'fvalueString' => '',
				        'fvalueInt' => 0,
				        'fvalueFloat' => $vals['minimal_price']?$vals['minimal_price']+((isset($vals['add_auction_cost']) && $vals['add_auction_cost'] && $auction_cost)?$auction_cost:0):'',
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			
			$buy_now = $vals['buy_price']?$vals['buy_price']+((isset($vals['add_auction_cost']) && $vals['add_auction_cost'] && $auction_cost)?$auction_cost:0):'';
			$fields[] = array(
				        'fid' => 8,   // Cena kup teraz
				        'fvalueString' => '',
				        'fvalueInt' => 0,
				        'fvalueFloat' => $buy_now,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 9,   // Kraj
				        'fvalueString' => '',
				        'fvalueInt' => Variable::get('allegro_country'),
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 10,   // Województwo
				        'fvalueString' => '',
				        'fvalueInt' => Variable::get('allegro_country')==1?Variable::get('allegro_state'):213,
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')			
			);
			$fields[] = array(
				        'fid' => 11,   // Miasto
				        'fvalueString' => Variable::get('allegro_city'),
				        'fvalueInt' => 0,
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')			
			);
			$fields[] = array(
				        'fid' => 32,   // Kod pocztowy
				        'fvalueString' => Variable::get('allegro_postal_code'),
				        'fvalueInt' => 0,
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')			
			);
			$fields[] = array(
				        'fid' => 12,   // Transport
				        'fvalueString' => '',
				        'fvalueInt' => $vals['transport'],
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 35,   // odbiór osobisty
				        'fvalueString' => '',
				        'fvalueInt' => 0+(isset($vals['in_shop'])?1:0)+(isset($vals['in_shop_trans'])?4:0),
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			if(isset($vals['stan']) && $vals['stan'] && $stan_id = DB::GetOne('SELECT field_id FROM premium_ecommerce_allegro_stan WHERE country=%d AND cat_id=%d',array($country,$vals['category']))) {
			    $fields[] = array(
				        'fid' => $stan_id,   // Transport
				        'fvalueString' => '',
				        'fvalueInt' => $vals['stan'],
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			    );
			}
			$fields[] = array(
				        'fid' => 13,   // Za granicę
				        'fvalueString' => '',
				        'fvalueInt' => isset($vals['abroad']) && $vals['abroad']?32:0,
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 14,   // Formy płatności
				        'fvalueString' => '',
				        'fvalueInt' => 1 + (Variable::get('allegro_fvat')?(Variable::get('allegro_country')==1?32:8):0),
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				        'fid' => 15,   // promocja
				        'fvalueString' => '',
				        'fvalueInt' => (isset($vals['pr_bold']) && $vals['pr_bold']?1:0)+(isset($vals['pr_thumbnail']) && $vals['pr_thumbnail']?2:0)
			+(isset($vals['pr_light']) && $vals['pr_light']?4:0)+(isset($vals['pr_bigger']) && $vals['pr_bigger']?8:0)
			+(isset($vals['pr_catpage']) && $vals['pr_catpage']?16:0)+(isset($vals['pr_mainpage']) && $vals['pr_mainpage']?32:0)
			+(isset($vals['pr_watermark']) && $vals['pr_watermark']?64:0),
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			foreach(self::$photos as $i=>$ph) {
				$ph_content = @file_get_contents($ph);
				if($ph_content)
				$fields[] = array(
								        'fid' => 16+$i,   // Obrazek
								        'fvalueString' => '',
								        'fvalueInt' => 0,
								        'fvalueFloat' => 0,
								        'fvalueImage' => $ph_content,
								        'fvalueDatetime' => 0,
								        'fvalueDate' => '',
								        'fvalueRangeInt' => array(
								                'fvalueRangeIntMin' => 0,
								                'fvalueRangeIntMax' => 0),
								        'fvalueRangeFloat' => array(
								                'fvalueRangeFloatMin' => 0,
								                'fvalueRangeFloatMax' => 0),
								        'fvalueRangeDate' => array(
								                'fvalueRangeDateMin' => '',
								                'fvalueRangeDateMax' => '')
				);
			}
			$carriers = array();
			if(isset($vals['post_service_price']) && $vals['post_service_price']!=='') {
				$fields[] = array(
					        'fid' => 36,   // Paczka pocztowa ekonomiczna
					        'fvalueString' => '',
					        'fvalueInt' => 0,
					        'fvalueFloat' => $vals['post_service_price'],
					        'fvalueImage' => 0,
					        'fvalueDatetime' => 0,
					        'fvalueDate' => '',
					        'fvalueRangeInt' => array(
					                'fvalueRangeIntMin' => 0,
					                'fvalueRangeIntMax' => 0),
					        'fvalueRangeFloat' => array(
					                'fvalueRangeFloatMin' => 0,
					                'fvalueRangeFloatMax' => 0),
					        'fvalueRangeDate' => array(
					                'fvalueRangeDateMin' => '',
					                'fvalueRangeDateMax' => '')
				);
				$carriers[] = 'Poczta Polska: '.$vals['post_service_price'].' zł';
			}
			if(isset($vals['post_service_price_p']) && $vals['post_service_price_p']!=='') {
				$fields[] = array(
					        'fid' => 40,   // Paczka pocztowa ekonomiczna
					        'fvalueString' => '',
					        'fvalueInt' => 0,
					        'fvalueFloat' => $vals['post_service_price_p'],
					        'fvalueImage' => 0,
					        'fvalueDatetime' => 0,
					        'fvalueDate' => '',
					        'fvalueRangeInt' => array(
					                'fvalueRangeIntMin' => 0,
					                'fvalueRangeIntMax' => 0),
					        'fvalueRangeFloat' => array(
					                'fvalueRangeFloatMin' => 0,
					                'fvalueRangeFloatMax' => 0),
					        'fvalueRangeDate' => array(
					                'fvalueRangeDateMin' => '',
					                'fvalueRangeDateMax' => '')
				);
				$carriers[] = 'Poczta Polska (pobranie): '.$vals['post_service_price_p'].' zł';
			}
			if(isset($vals['ups_price']) && $vals['ups_price']!=='') {
				$fields[] = array(
					        'fid' => 44,   // Paczka pocztowa ekonomiczna
					        'fvalueString' => '',
					        'fvalueInt' => 0,
					        'fvalueFloat' => $vals['ups_price'],
					        'fvalueImage' => 0,
					        'fvalueDatetime' => 0,
					        'fvalueDate' => '',
					        'fvalueRangeInt' => array(
					                'fvalueRangeIntMin' => 0,
					                'fvalueRangeIntMax' => 0),
					        'fvalueRangeFloat' => array(
					                'fvalueRangeFloatMin' => 0,
					                'fvalueRangeFloatMax' => 0),
					        'fvalueRangeDate' => array(
					                'fvalueRangeDateMin' => '',
					                'fvalueRangeDateMax' => '')
				);
				$carriers[] = 'Kurier: '.$vals['ups_price'].' zł';
			}
			if(isset($vals['ups_price_p']) && $vals['ups_price_p']!=='') {
				$fields[] = array(
					        'fid' => 45,   // Paczka pocztowa ekonomiczna
					        'fvalueString' => '',
					        'fvalueInt' => 0,
					        'fvalueFloat' => $vals['ups_price_p'],
					        'fvalueImage' => 0,
					        'fvalueDatetime' => 0,
					        'fvalueDate' => '',
					        'fvalueRangeInt' => array(
					                'fvalueRangeIntMin' => 0,
					                'fvalueRangeIntMax' => 0),
					        'fvalueRangeFloat' => array(
					                'fvalueRangeFloatMin' => 0,
					                'fvalueRangeFloatMax' => 0),
					        'fvalueRangeDate' => array(
					                'fvalueRangeDateMin' => '',
					                'fvalueRangeDateMax' => '')
				);
				$carriers[] = 'Kurier (pobranie): '.$vals['ups_price_p'].' zł';
			}
				
			$desc = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$r['id'],'language'=>'pl'));
			if($desc) $desc = array_shift($desc);
			$description = (isset($desc['long_description']) && $desc['long_description']?$desc['long_description']:(isset($desc['short_description']) && $desc['short_description']?$desc['short_description']:$r['description']));
			$other_auctions = '<table border=0>';
			for($wiersz=0;$wiersz<3;$wiersz++) {
			    $other_auctions .= '<tr>';
			    for($kolumna=0; $kolumna<3; $kolumna++) 
				$other_auctions .= '<td><a target="_blank" href="'.get_epesi_url().'/modules/Premium/Warehouse/DrupalCommerce/Allegro/redirect.php?id='.$r['id'].'&i='.($kolumna+$wiersz*3).'"><img src="'.get_epesi_url().'/modules/Premium/Warehouse/DrupalCommerce/Allegro/img.php?id='.$r['id'].'&i='.($kolumna+$wiersz*3).'" border=0 /></a></td>';
			    $other_auctions .= '</tr>';
			}
			$other_auctions .= '</table>';
			$image = '<img src="'.get_epesi_url().'/modules/Premium/Warehouse/DrupalCommerce/Allegro/anim.php?id='.$r['id'].'&w=240" border=0 />';
			$gallery = '';
			for($i=0; $i<5; $i++)
				$gallery .= '<img src="'.get_epesi_url().'/modules/Premium/Warehouse/DrupalCommerce/Allegro/imgs.php?id='.$r['id'].'&pos='.$i.'&w=500" border=0 /><br />';
			$features = '<table id="features" cellspacing="1"><thead><tr><td colspan="3">Cechy</td></tr></thead><tbody>';
		        $parameters = array();
			$ret2 = DB::Execute('SELECT pp.f_item_name, pp.f_value,
									p.f_parameter_code as parameter_code,
									pl.f_label as parameter_label,
									g.f_group_code as group_code,
									gl.f_label as group_label
						FROM premium_ecommerce_products_parameters_data_1 pp
						INNER JOIN (premium_ecommerce_parameters_data_1 p,premium_ecommerce_parameter_groups_data_1 g) ON (p.id=pp.f_parameter AND g.id=pp.f_group)
						LEFT JOIN premium_ecommerce_parameter_labels_data_1 pl ON (pl.f_parameter=p.id AND pl.f_language="pl" AND pl.active=1)
						LEFT JOIN premium_ecommerce_param_group_labels_data_1 gl ON (gl.f_group=g.id AND gl.f_language="pl" AND gl.active=1)
						WHERE pp.active=1 AND pp.f_language="pl" AND pp.f_item_name=%d ORDER BY g.f_position,gl.f_label,g.f_group_code,p.f_position,pl.f_label,p.f_parameter_code',array($r['id']));
			$last_group = null;
			while($bExp = $ret2->FetchRow()) {
				$parameters[] = array('sGroup'=>($last_group!=$bExp['group_code']?($bExp['group_label']?$bExp['group_label']:$bExp['group_code']):''), 'sName'=>($bExp['parameter_label']?$bExp['parameter_label']:$bExp['parameter_code']), 'sValue'=>($bExp['f_value']=='Y'?'<span class="yes">Yes</span>':($bExp['f_value']=='N'?'<span class="no">No</span>':$bExp['f_value'])));
				if($last_group != $bExp['group_code']) {
    					$last_group = $bExp['group_code'];
				}
			}
			$row = DB::GetRow('SELECT it.f_sku,
					it.f_upc,
					it.f_product_code
					FROM premium_warehouse_items_data_1 it WHERE id=%d',array($r['id']));
			$parameters[] = array('sGroup'=>'Kody','sName'=>'SKU','sValue'=>$row['f_sku']);
			$parameters[] = array('sGroup'=>'','sName'=>'UPC','sValue'=>$row['f_upc']);
			$parameters[] = array('sGroup'=>'','sName'=>'Kod producenta','sValue'=>$row['f_product_code']);
			$i2=0;
			foreach($parameters as $aData) {
				$aData['iStyle'] = ( $i2 % 2 ) ? 0: 1;
				$features .= '<tr class="l'.$aData['iStyle'].'"><th>'.$aData['sGroup'].'</th><th>'.$aData['sName'].'</th><td>'.$aData['sValue'].'</td></tr>';
				$i2++;
			} // end for
			$features .= '</tbody></table>';
			if($vals['template'])
				$description = str_replace(array('{$description}','{$title}','{$shipping_cost}','{$other_auctions}','{$image}','{$gallery}','{$features}'),array($description,$vals['title'],implode('<br />',$carriers),$other_auctions,$image,$gallery,$features),file_get_contents($vals['template']));
			$fields[] = array(
				        'fid' => 24,   // Opis
				        'fvalueString' => $description,
				        'fvalueInt' => 0,
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$transport_description = trim($vals['transport_description']);
			if($transport_description)
			$fields[] = array(
				        'fid' => 27,   // Dodatkowe info o przesyłce
				        'fvalueString' => $transport_description,
				        'fvalueInt' => 0,
				        'fvalueFloat' => 0,
				        'fvalueImage' => 0,
				        'fvalueDatetime' => 0,
				        'fvalueDate' => '',
				        'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				        'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				        'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			$fields[] = array(
				         'fid' => 29,  // Format sprzedaży [Aukcja (z licytacją) lub Kup Teraz!]
				         'fvalueString' => '',
				         'fvalueInt' => 0,
				         'fvalueFloat' => 0,
				         'fvalueImage' => 0,
				         'fvalueDatetime' => 0,
				         'fvalueDate' => '',
				         'fvalueRangeInt' => array(
				                'fvalueRangeIntMin' => 0,
				                'fvalueRangeIntMax' => 0),
				         'fvalueRangeFloat' => array(
				                'fvalueRangeFloatMin' => 0,
				                'fvalueRangeFloatMax' => 0),
				         'fvalueRangeDate' => array(
				                'fvalueRangeDateMin' => '',
				                'fvalueRangeDateMax' => '')
			);
			
			/* @var $a Allegro */
			$a = self::get_lib();
			$similar = $a->search($r['item_name'],$vals['category']);

			$cat_fields = $a->get_sell_form_fields_for_category($vals['category']);
			if(isset($cat_fields['sellFormFieldsForCategory']->sellFormFieldsList->item) && $cat_fields['sellFormFieldsForCategory']->sellFormFieldsList->item) {
				if(!is_array($cat_fields['sellFormFieldsForCategory']->sellFormFieldsList->item)) $cat_fields['sellFormFieldsForCategory']->sellFormFieldsList->item = array($cat_fields['sellFormFieldsForCategory']->sellFormFieldsList->item);
				foreach($cat_fields['sellFormFieldsForCategory']->sellFormFieldsList->item as $cat_field) {
					if($cat_field->sellFormId<700 || $cat_field->sellFormResType==7) continue;
					
					foreach($fields as $fields_done)
					    if($fields_done['fid']==$cat_field->sellFormId) continue 2;
					
					$cat_vals = explode('|',trim($cat_field->sellFormDesc));
					$cat_keys = explode('|',trim($cat_field->sellFormOptsValues));
					if(is_array($cat_vals) && $cat_vals && is_array($cat_keys) && $cat_keys)
						$cat_select = array_filter(array_combine($cat_vals,$cat_keys));
					
					$val = DB::GetOne('SELECT v.f_value FROM premium_ecommerce_parameter_labels_data_1 l INNER JOIN premium_ecommerce_products_parameters_data_1 v ON l.f_parameter=v.f_parameter AND l.f_language=v.f_language WHERE l.f_language="pl" AND l.f_label=%s AND v.f_item_name=%d',array($cat_field->sellFormTitle,$r['id']));
					if($val===null || $val===false) $val = DB::GetOne('SELECT v.f_value FROM premium_ecommerce_parameter_labels_data_1 l INNER JOIN premium_ecommerce_products_parameters_data_1 v ON l.f_parameter=v.f_parameter AND l.f_language=v.f_language WHERE l.f_language="pl" AND l.f_label LIKE %s AND v.f_item_name=%d',array('%%'.$cat_field->sellFormTitle.'%%',$r['id']));
					if($val!==null && $val!==false) {
						if(isset($cat_select) && $cat_select) {
						    $ret_val = 0;
						    foreach($cat_select as $cat_select_key=>$cat_select_val)
						        if(strcasecmp($cat_select_key,$val)===0) $ret_val |= $cat_select_val;
						    if(!$ret_val) {
						        $vals = explode(',<br />',str_replace(array(', ',",\n"),',<br />',$val));
						        foreach($vals as $cval) {
						            foreach($cat_select as $cat_select_key=>$cat_select_val)
						                if(strcasecmp($cat_select_key,$cval)===0) $ret_val |= $cat_select_val;
						        }
						    }
						    if($ret_val===0) $ret_val=null;
						    $val = $ret_val;
						}
						if($val!==null) {
							$arr = array(
						         'fid' => $cat_field->sellFormId,
						         'fvalueString' => '',
							 'fvalueInt' => 0,
						         'fvalueFloat' => 0,
						         'fvalueImage' => 0,
						         'fvalueDatetime' => 0,
						         'fvalueDate' => '',
						         'fvalueRangeInt' => array(
						                'fvalueRangeIntMin' => 0,
						                'fvalueRangeIntMax' => 0),
						         'fvalueRangeFloat' => array(
						                'fvalueRangeFloatMin' => 0,
						                'fvalueRangeFloatMax' => 0),
						         'fvalueRangeDate' => array(
						                'fvalueRangeDateMin' => '',
						                'fvalueRangeDateMax' => '')
							);
							$ok = false;
							switch($cat_field->sellFormResType) {
								case 1:
								    $arr['fvalueString'] = $val;
								    $ok = true;
								    break;
								case 2:
								    $arr['fvalueInt'] = intval($val);
								    if($arr['fvalueInt']) $ok = true;
								    break;
								case 3:
								    $arr['fvalueFloat'] = floatval($val);
								    if($arr['fvalueFloat']) $ok = true;
								    break;
							}
							if($ok) {
							    $fields[] = $arr;
							    continue;
							}
						}
					}
					
					foreach($similar as $sid=>$it) {
						if(!isset($it->parametersInfo->item)) continue;
						if(!is_array($it->parametersInfo->item)) $it->parametersInfo->item = array($it->parametersInfo->item);
						foreach($it->parametersInfo->item as $attr) {
								if($attr->parameterName==$cat_field->sellFormTitle) {
									if(is_array($attr->parameterValue->item)) $attr->parameterValue->item = array_shift($attr->parameterValue->item);
									$arr = array(
									         'fid' => $cat_field->sellFormId,
									         'fvalueString' => '',
										 'fvalueInt' => 0,
									         'fvalueFloat' => 0,
									         'fvalueImage' => 0,
									         'fvalueDatetime' => 0,
									         'fvalueDate' => '',
									         'fvalueRangeInt' => array(
									                'fvalueRangeIntMin' => 0,
									                'fvalueRangeIntMax' => 0),
									         'fvalueRangeFloat' => array(
									                'fvalueRangeFloatMin' => 0,
									                'fvalueRangeFloatMax' => 0),
									         'fvalueRangeDate' => array(
									                'fvalueRangeDateMin' => '',
									                'fvalueRangeDateMax' => '')
									);
									$param_value = '';
									switch($cat_field->sellFormResType) {
										case 1:
										    if(isset($cat_select) && isset($cat_select[$attr->parameterValue->item]))
											$arr['fvalueString'] = $cat_select[$attr->parameterValue->item];
										    else
											$arr['fvalueString'] = $attr->parameterValue->item;
										    $param_value = $arr['fvalueString'];
										    break;
										case 2:
										    if(isset($cat_select) && isset($cat_select[$attr->parameterValue->item]))
											    $arr['fvalueInt'] = intval($cat_select[$attr->parameterValue->item]);
										    else
											    $arr['fvalueInt'] = intval($attr->parameterValue->item);
										    $param_value = $arr['fvalueInt'];
										    break;
										case 3:
										    $arr['fvalueFloat'] = floatval($attr->parameterValue->item);
										    $param_value = $arr['fvalueFloat'];
										    break;
										case 9:
										    $arr['fvalueDatetime'] = $attr->parameterValue->item;
										    $param_value = $arr['fvalueDatetime'];
										    break;
										case 13:
										    $arr['fvalueDate'] = $attr->parameterValue->item;
										    $param_value = $arr['fvalueDate'];
										    break;
									}
									$param_value = substr(str_replace("\n",'<br />',strip_tags($param_value)),0,256);
									if($param_value) {
										$group = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameter_groups',array('group_code'=>'Podstawowe informacje'),array('id'));
										if($group) {
										        $group = array_shift($group);
										        $group = $group['id'];
										} else {
											$group = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_groups',array('group_code'=>'Podstawowe informacje'));
										        $parameter_group_label = array('group'=>$group,
											    'language'=>'pl',
											    'label'=>'Podstawowe informacje');
										        Utils_RecordBrowserCommon::new_record('premium_ecommerce_param_group_labels',$parameter_group_label);
										}
										$param = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameters',array('parameter_code'=>$cat_field->sellFormTitle),array('id'));
										if($param) {
											$param = array_shift($param);
											$param = $param['id'];
										} else {
											$param = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameters',array('parameter_code'=>$cat_field->sellFormTitle));
										        $parameter_label = array('parameter'=>$param,
										            'language'=>'pl',
										            'label'=>$cat_field->sellFormTitle);
											Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_labels',$parameter_label);
										}

										$item_params = array('item_name'=>$r['id'],
											'parameter'=>$param,
											'group'=>$group,
											'language'=>'pl',
											'value'=>$param_value);
										if(!Utils_RecordBrowserCommon::get_records_count('premium_ecommerce_products_parameters',array('item_name'=>$r['id'],
											'parameter'=>$param,
											'group'=>$group,
											'language'=>'pl')))
											Utils_RecordBrowserCommon::new_record('premium_ecommerce_products_parameters',$item_params);
									}
									$fields[] = $arr;
									continue;
								}
							
						}
					}
					
					$param_value = '';
					$group = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameter_groups',array('group_code'=>'Podstawowe informacje'),array('id'));
					if($group) {
					        $group = array_shift($group);
					        $group = $group['id'];
					} else {
						$group = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_groups',array('group_code'=>'Podstawowe informacje'));
					        $parameter_group_label = array('group'=>$group,
						    'language'=>'pl',
						    'label'=>'Podstawowe informacje');
					        Utils_RecordBrowserCommon::new_record('premium_ecommerce_param_group_labels',$parameter_group_label);
					}
					$param = Utils_RecordBrowserCommon::get_records('premium_ecommerce_parameters',array('parameter_code'=>$cat_field->sellFormTitle),array('id'));
					if($param) {
						$param = array_shift($param);
						$param = $param['id'];
					} else {
						$param = Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameters',array('parameter_code'=>$cat_field->sellFormTitle));
						        $parameter_label = array('parameter'=>$param,
						            'language'=>'pl',
						            'label'=>$cat_field->sellFormTitle);
						Utils_RecordBrowserCommon::new_record('premium_ecommerce_parameter_labels',$parameter_label);
					}

					$item_params = array('item_name'=>$r['id'],
						'parameter'=>$param,
						'group'=>$group,
						'language'=>'pl',
						'value'=>$param_value,
						'info'=>((isset($cat_select) && $cat_select)?implode("\n",array_keys($cat_select)):($cat_field->sellFormResType==1?'łańcuch znaków':'liczba')));
					if(!Utils_RecordBrowserCommon::get_records_count('premium_ecommerce_products_parameters',array('item_name'=>$r['id'],
						'parameter'=>$param,
						'group'=>$group,
						'language'=>'pl')))
						Utils_RecordBrowserCommon::new_record('premium_ecommerce_products_parameters',$item_params);
					
				}
			}
			
			return $fields;
	}
	
	public static function menu() {
		return array('Inventory'=>array('eCommerce'=>array('Allegro'=>array(),'__submenu__'=>1),'__submenu__'=>1));
	}
}

?>