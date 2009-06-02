<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Wholesale__Plugin_techdata implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Techdata';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array(
			'Download link'=>'text',
			'Login link'=>'text',
			'Client number'=>'text',
			'Login'=>'text',
			'Password'=>'password'
		);
	}

	/**
	 * Returns whether plugin supports auto-update feature
	 * 
	 * @return bool support enabled
	 */
	public function is_auto_update() {
		return true;
	}
	
	public function auto_update($parameters) {
	    $url = $parameters['Login link'];
	    $c = curl_init();

	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_COOKIEFILE, "D:/cookiefile.cf");
		curl_setopt($c, CURLOPT_COOKIEJAR, "D:/cookiefile.cf"); 

	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array('id'=>$parameters['Client number'],'name'=>$parameters['Login'],'pwd'=>$parameters['Password'])));

	    $output = curl_exec($c);

		preg_match('/action=\"(.*?)\"/',$output,$match);
	    $url=$match[1];
		preg_match('/session\" value=\"([a-zA-Z0-9]*?)\"/',$output,$match);
	    curl_setopt($c, CURLOPT_URL, $url);
	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array('session'=>$match[1])));
	    $output = curl_exec($c);
	    
	    // TODO: file location, perhaps requires update on daily basis
	    $url='http://www.techdata.pl/download.aspx?OID=3385&FID=15987&DID=40';
	    curl_setopt($c, CURLOPT_URL, $url);
	    $output = curl_exec($c);	    

	    curl_close($c);

	    print('<hr>'.strlen($output));
	    print('<hr>');
	}

	public function update_from_file($filename) {
		$f = fopen($filename, 'r');
		$header = fgets($f,387);
		$i=10;
		while(!feof($f)) {
			$item = fgets($f,548);
			while($i-->0) {
				print('<hr>'.str_replace(' ', '.', $item));
			}
		}
		fclose($f);
	}
}

?>
