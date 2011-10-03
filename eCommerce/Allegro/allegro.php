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
			$this->client = new SoapClient('http://webapi.allegro.pl/uploader.php?wsdl');
			$this->version = $this->client->doQuerySysStatus(1, $country, $key);
			$this->tabsession = $this->client->doLogin($login, $password, $country, $key, $this->version['ver-key']);
			$this->session_id = $this->tabsession['session-handle-part'];
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
	
	public function new_auction($fields) {
	    return $this->call('doNewAuctionExt',$this->session_id,$fields);
	}

	public function check_new_auction_price($fields) {
		return $this->call('doCheckNewAuctionExt',$this->session_id,$fields);
	}
	
	public function get_sell_form_fields() {
	    return $this->call('doGetSellFormFieldsExt',$this->country,0,$this->key);
	}
	
	public function get_countries() {
	    return $this->call('doGetCountries',$this->country,$this->key);
	}
	
	public function get_categories() {
	    return $this->call('doGetCatsData',$this->country,0,$this->key);
	}

	private function call($name) {
	        $args = func_get_args();
	        array_shift($args);
	        try {
        		$ret = call_user_func_array(array($this->client,$name), $args);
        	} catch (SoapFault $soapFault) {
			$this->error_code = $soapFault->faultcode;
			$this->error_msg = $soapFault->faultstring;
			return false;
		}
		return $ret;
	}

}

/*$a = new Allegro('shackyt','warszawa1',1,'838df04db0');
if($a->error())
    print($a->error());
print_r($a->version());
$ret = $a->get_countries();
foreach($ret as $row) {
    print('$countries['.$row->{'country-id'}.'] = \''.$row->{'country-name'}.'\';'."\n");
}
print_r($a->get_sell_form_fields(1));
print($a->error());*/
?>