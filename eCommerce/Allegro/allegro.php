<?php

class Allegro {
        private $client;
        private $version;

        private $error_code;
        private $error_msg;
        
        private $tabsession;
        private $session_id;
        private $key;
        private $country;
        
        public function __construct($login,$password,$country,$key) {
		try {
			$this->client = new SoapClient('https://webapi.allegro.pl/service.php?wsdl');
			$doquerysysstatus_request = array(
			   'sysvar' => 1,
			   'countryId' => $country,
			   'webapiKey' => $key
			);
			$this->version = get_object_vars($this->client->doQuerySysStatus($doquerysysstatus_request));
			$doLogin_request = array(
			    'userLogin' => $login, 
			    'userPassword' => $password, 
			    'countryCode'=>$country, 
			    'webapiKey'=>$key, 
			    'localVersion'=>$this->version['verKey']);
			$this->tabsession = get_object_vars($this->client->doLogin($doLogin_request));
			$this->session_id = $this->tabsession['sessionHandlePart'];
			$this->key = $key;
			$this->country = $country;
		} catch (SoapFault $soapFault) {
			$this->error_code = $soapFault->faultcode;
			$this->error_msg = $soapFault->faultstring;
		}
	}
	
	public function error() {
	        return $this->error_code?$this->error_code.' '.$this->error_msg:false;
	}
	
	public function version() {
	        return $this->version;
	}
	
	public function new_auction($fields,$local_id = 9999999999999) {
	    $req = array('sessionHandle'=>$this->session_id,'fields'=>$fields,'itemTemplateId'=>0,'localId'=>$local_id);
	    return $this->call('doNewAuctionExt',0,$req);
	}

	public function check_new_auction_price($fields) {
	    $req = array('sessionHandle'=>$this->session_id,'fields'=>$fields);
	    return $this->call('doCheckNewAuctionExt',0,$req);
	}
	
	public function verify_new_auction($local_id) {
	    $req = array('sessionHandle'=>$this->session_id,'localId'=>$local_id);
	    return $this->call('doVerifyItem',0,$req);
	}
	
	public function get_sell_form_fields() {
	    $req = array('countryCode'=>$this->country,'localVersion'=>0,'webapiKey'=>$this->key);
	    return $this->call('doGetSellFormFieldsExt',3600*24,$req);
	}
	
	public function get_sell_form_fields_for_category($cat) {
	    $req = array('countryId'=>$this->country,'categoryId'=>$cat,'webapiKey'=>$this->key);
	    return $this->call('doGetSellFormFieldsForCategory',3600*24,$req);
	}

	public function get_auctions_info($ids) {
		$ret = array('arrayItemListInfo'=>array(), 'arrayItemsNotFound'=>array(),'arrayItemsAdminKilled'=>array());
		foreach(array_chunk($ids,25) as $a) {
			$req = array('sessionHandle'=>$this->session_id,'itemsIdArray'=>$a);
			$r2 = $this->call('doGetItemsInfo',0,$req);
			foreach($ret as $key=>$val) {
				if(isset($r2[$key]->item)) {
					if(!is_array($r2[$key]->item)) $r2[$key]->item = array($r2[$key]->item);
					$ret[$key] = array_merge($ret[$key],$r2[$key]->item);
				}
			}
		}
		return $ret; 
	}

	public function get_auction_info($id,$desc=1,$image=1,$attr=1,$postage=0,$company=0) {
		$req = array('sessionHandle'=>$this->session_id,'itemId'=>(float)$id, 'getDesc'=>$desc,
		   'getImageUrl' => $image,
		      'getAttribs' => $attr,
		         'getPostageOptions' => $postage,
		            'getCompanyInfo' => $company);
		return $this->call('doShowItemInfoExt',0,$req);
	}

	public function search($string,$category) {
		$req = array('sessionHandle'=>$this->session_id,'searchQuery'=>array('searchString'=>$string,'searchOptions'=>32768,'searchCountry'=>1,'searchCategory'=>$category));
		$ret = $this->call('doSearch',3600,$req);
		if($ret['searchCount']>0) return is_object($ret['searchArray']->item)?array($ret['searchArray']->item):$ret['searchArray']->item;
		
		$req = array('sessionHandle'=>$this->session_id,'searchQuery'=>array('searchString'=>$string,'searchOptions'=>32768+16384,'searchCountry'=>1,'searchCategory'=>$category));
		$ret = $this->call('doSearch',3600,$req);
		if($ret['searchCount']>0) return is_object($ret['searchArray']->item)?array($ret['searchArray']->item):$ret['searchArray']->item;
		
		$req = array('sessionHandle'=>$this->session_id,'searchQuery'=>array('searchString'=>$string,'searchOptions'=>32768,'searchCountry'=>1));
		$ret = $this->call('doSearch',3600,$req);
		if($ret['searchCount']>0) return is_object($ret['searchArray']->item)?array($ret['searchArray']->item):$ret['searchArray']->item;

		$req = array('sessionHandle'=>$this->session_id,'searchQuery'=>array('searchString'=>$string,'searchOptions'=>32768+16384,'searchCountry'=>1));
		$ret = $this->call('doSearch',3600,$req);
		if($ret['searchCount']>0) return is_object($ret['searchArray']->item)?array($ret['searchArray']->item):$ret['searchArray']->item;

		return array();
	}
	
	public function get_transactions($ids) {
	    $req = array('sessionHandle'=>$this->session_id,'itemsIdArray'=>$ids,'userRole'=>'seller');
	    $trans = $this->call('doGetTransactionsIDs',0,$req);
	    if(!isset($trans['transactionsIdsArray']->item)) return array();
	    $trans_ids = $trans['transactionsIdsArray']->item;
	    $ret = array();
	    if($trans_ids) {
		if(!is_array($trans_ids)) $trans_ids = array($trans_ids);
		foreach(array_chunk($trans_ids,25) as $a) {
		    $req = array('sessionId'=>$this->session_id,'transactionsIdsArray'=>$a);
		    $r2 = $this->call('doGetPostBuyFormsDataForSellers',0,$req);
		    if(isset($r2['postBuyFormData']->item) && $r2['postBuyFormData']->item) {
			if(!is_array($r2['postBuyFormData']->item)) $r2['postBuyFormData']->item = array($r2['postBuyFormData']->item);
			foreach($r2['postBuyFormData']->item as $rec) {
			    $ret[(string)$rec->postBuyFormId] = $rec;
			}
		    }
		}
	    }
	    return $ret; 
	}
	public function get_bids_user_data($ids) {
		$ret = array();
		foreach(array_chunk($ids,25) as $a) {
			$req = array('sessionHandle'=>$this->session_id,'itemsArray'=>$a);
			$r2 = $this->call('doGetPostBuyData',0,$req);
			if($r2['itemsPostBuyData']->item) {
			    if(!is_array($r2['itemsPostBuyData']->item)) $r2['itemsPostBuyData']->item = array($r2['itemsPostBuyData']->item);
			    foreach($r2['itemsPostBuyData']->item as $rec) {
				$ret[(string)$rec->itemId] = $rec;
			    }
			}
		}
		return $ret; 
	}
	
	public function get_countries() {
	    $req = array('countryCode'=>$this->country,'webapiKey'=>$this->key);
	    return $this->call('doGetCountries',3600*24,$req);
	}
	
	public function get_categories() {
	    $req = array('countryId'=>$this->country,'localVersion'=>0,'webapiKey'=>$this->key);
	    return $this->call('doGetCatsData',3600,$req);
	}
	
	public function get_shipments() {
	    $ret = $this->call('doGetShipmentData',3600,array('countryId'=>$this->country,'webapiKey'=>$this->key));
	    $ret2 = array();
	    foreach($ret['shipmentDataList']->item as $r) {
		$ret2[$r->shipmentId] = $r->shipmentName;
	    }
	    return $ret2;
	}

	private function call($name,$cache) {
	        $args = func_get_args();
	        $key = str_replace(array($this->session_id,$this->key),'',serialize($args));
		$cache_obj = 'data/cache/allegro/'.md5($key);
		if($cache && file_exists($cache_obj) && filemtime($cache_obj)>time()-$cache) return unserialize(file_get_contents($cache_obj));
	        array_shift($args);
	        array_shift($args);
	        try {
        		$ret = get_object_vars(call_user_func_array(array($this->client,$name), $args));
        	} catch (SoapFault $soapFault) {
			$this->error_code = $soapFault->faultcode;
			$this->error_msg = $soapFault->faultstring;
			return false;
		}
		if($cache) {
		    @mkdir('data/cache/allegro/',0777);
		    file_put_contents($cache_obj,serialize($ret));
		}
		return $ret;
	}

}
/*
$a = new Allegro('shackyt','warszawa1',228,'838df04db0');
if($a->error())
    print($a->error());
//print_r($a->version());
print_r($ret);*/
/*
foreach($ret as $row) {
    print('$countries['.$row->{'country-id'}.'] = \''.$row->{'country-name'}.'\';'."\n");
}
print_r($a->get_sell_form_fields(1));
print($a->error());*/
?>