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

class Premium_Warehouse_Wholesale__Plugin_action implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Action';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array(
			'ID'=>'text',
			'Login'=>'text',
			'Password'=>'password'
		);
	}

	/**
	 * Returns whether plugin supports auto-download feature
	 * 
	 * @return bool support enabled
	 */
	public function is_auto_download() {
		return true;
	}

	/**
	 * This method is called when user selects auto-update from the interface
	 * It should download new file and return path and filename to downloaded file that is ready for parsing
	 * (i.e. filename that would be a valid argument for update_from_file method)
	 * 
	 * @param array array of parameters for current distributor, with format {parameter name}=>{value} 
	 * @param array distributor record (with necessary fields like 'id' and 'add_new_items'
	 * @return string filename with its location
	 */
	public function download_file($parameters, $distributor) {
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');

	    $c = curl_init();
		$url = 'https://i-serwis2.action.pl/Login.aspx';

	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($c, CURLOPT_COOKIEFILE, 'D:\cookiefile.cf');
		curl_setopt($c, CURLOPT_COOKIEJAR, 'D:\cookiefile.cf');

		$output = curl_exec($c);
	    
	    preg_match('/id=\"\_\_VIEWSTATE\" value=\"(.*?)\"/', $output, $viewstate);	    

	    if (empty($viewstate)) {
			$output = curl_exec($c);
		    preg_match('/id=\"\_\_VIEWSTATE\" value=\"(.*?)\"/', $output, $viewstate);	    
	    }
		preg_match('/id=\"\_\_EVENTVALIDATION\" value=\"(.*?)\"/', $output, $eventvalidation);	    

		if (!isset($eventvalidation[1]) || !isset($viewstate[1])) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Authentication failure, aborting.'), 2, true);
			return false;
		}

	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array(
			'txtCustomerID'=>$parameters['ID'], 
			'txtLogin'=>$parameters['Login'], 
			'txtPassword'=>$parameters['Password'],
			'__EVENTVALIDATION'=>$eventvalidation[1],
			'__VIEWSTATE'=>$viewstate[1],
			'ButtonLogIn'=>'Zaloguj'
		)));
		$output = curl_exec($c);

		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);

		$output = curl_exec($c);

		$url = 'http://i-serwis2.action.pl/ExportProxy.aspx?type=csv';
	    curl_setopt($c, CURLOPT_URL, $url);

		$output = curl_exec($c);
		
		if (!$output || strlen($output)<20000) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Authentication failure, aborting.'), 2, true);
			return false;
		}
	    $time = time();

		$filename = $dir.'ab_data_'.$time.'.tmp';
		file_put_contents($filename, $output);

	    curl_close($c);

		Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','File downloaded.'), 1, true);
	    
	    return $filename;
	}

	/**
	 * This method is called when a new file is given either via upload or auto-download method
	 * It should parse the file and manipulate `premium_warehouse_wholesale_items` table to store results of the parsing
	 * If distributor has set 'add_new_items' to true, this mthod can add items without match to the system (premium_warehouse_items recordSet)
	 * 
	 * @param string filename that should be parsed with its location 
	 * @param array distributor record (with necessary fields like 'id' and 'add_new_items'
	 * @return bool true if the update was successful, false otherwise
	 */
	public function update_from_file($filename, $distributor) {

		$f = fopen($filename,'r');
		$delimiter = ',';

		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		
		$keys = array(
			'Grupa towarowa',
			'Podgrupa towarowa',
			'Producent',
			'Nazwa produktu',
			'Cena netto',
			'Cena brutto',
			'Kod produktu',
			'Gwarancja',
			'Stan mag',
			'Kod producenta',
			'Cena sugerowana',
			'Oplata wielkogabarytowa'
		);

		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		if ($pln_id===false || $pln_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to find required currency (%s), aborting.', array('PLN')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));

		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scanning...'));
		while (!feof($f)) {
			$row = fgetcsv($f,0,$delimiter);
			if ($row===false) break;
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items);
			$scanned++;
			
			foreach ($row as $k=>$v) $row[$keys[$k]] = $v;
			
			if ($row['Stan mag']!=0) {
				$available++;
				/*** determine quantity and quantity info ***/
				if (is_numeric($row['Stan mag'])) {
					$quantity = $row['Stan mag'];
					$quantity_info = '';
				} else {
					$quantity_info = $row['Stan mag'];
					$quantity = 30;
				}

				/*** check for exact match ***/
				$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['Kod produktu'], $distributor['id']));
				if (($internal_key===false || $internal_key===null) && $row['Kod producenta']) {
					$w_item = null;
					/*** exact match not found, looking for candidates ***/
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
						'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row['Nazwa produktu']),DB::qstr('%')),
						'|manufacturer_part_number'=>$row['Kod producenta']
					));
					if (!empty($matches))
						if (count($matches)==1) {
							/*** one candidate found, if product code is empty or matches, it's ok ***/
							$v = array_pop($matches);
							if ($v['manufacturer_part_number']==$row['Kod producenta'] || $v['manufacturer_part_number']=='')
								$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['manufacturer_part_number']==$row['Kod producenta']) {
									$w_item = $v['id'];
									break;
								}
						}
					if ($w_item===null) {
						/*** no item was found matching this entry ***/
						$new_items++;
						if ($distributor['add_new_items']) {
							$vendor = Utils_RecordBrowserCommon::get_id('company', 'company_name', $row['Producent']);
							$w_item = Utils_RecordBrowserCommon::new_record('premium_warehouse_items', array('item_name'=>$row['Nazwa produktu'], 'item_type'=>1, 'manufacturer_part_number'=>$row['Kod producenta'], 'vendor'=>$vendor));
						}
					} else {
						/*** found match ***/
						$item_exist++;
					}
					if ($w_item!==null) {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency) VALUES (%d, %s, %s, %d, %d, %s, %f, %d)', array($w_item, $row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $quantity, $quantity_info, $row['Cena netto'], $pln_id));
					} else {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency) VALUES (%s, %s, %d, %d, %s, %f, %d)', array($row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $quantity, $quantity_info, $row['Cena netto'], $pln_id));
					}
				} else {
					/*** there's an exact match in the system already ***/
					$link_exist++;
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row['Cena netto'], $pln_id, $row['Kod produktu'], $distributor['id']));
				}
			} 
		}
		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scan complete.'), 1);
		Premium_Warehouse_WholesaleCommon::update_scan_status($scanned, $scanned, $available, $item_exist, $link_exist, $new_items);
		fclose($f);
		return true;
	}
}

?>
