<?php
class  Premium_Warehouse_eCommerce_CompareService_skapiec extends Premium_Warehouse_eCommerce_CompareService {
	public function fetch($url,$tax) {
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_eCommerce_CompareUpdatePrices');
		
		$c = curl_init();
		
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_POST, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.01; Windows NT 5.0)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($c, CURLOPT_COOKIEFILE, $dir.'cookiefile.cf');
		curl_setopt($c, CURLOPT_COOKIEJAR, $dir.'cookiefile.cf');
		
		$output = curl_exec($c);
		
		curl_close($c);
		
		if(preg_match('/w[\t\n\s]+cenie[\t\n\s]+<em>od[\t\n\s]+([0-9]+([,\.][0-9]+)?)[\t\n\s]+PLN[\t\n\s]+do/i',$output,$ret)) {
			$this->currency = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
			$this->price = str_replace(',','.',$ret[1]);
			return true;
		}
		
		return false;
	}
}
?>