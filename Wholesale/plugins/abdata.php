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

class Premium_Warehouse_Wholesale__Plugin_abdata implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'AB Data';
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
	    $url = 'http://dealer.ab.pl/main.php';

	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_COOKIEFILE, $dir.'cookiefile.cf');
		curl_setopt($c, CURLOPT_COOKIEJAR, $dir.'cookiefile.cf'); 

	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array(
			'lname'=>$parameters['ID'],'passwd'=>$parameters['Password'])));

	    $output = curl_exec($c);

		$url = 'http://dealer.ab.pl/cennik_gen3.php';
		curl_setopt($c, CURLOPT_URL, $url);
	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array(
			'ctype'=>'csv','filtr_brak'=>1)));
		$output = curl_exec($c);
		
	    $time = time();	    

		$filename = $dir.'ab_data_'.$time.'.tmp';
		file_put_contents($filename, $output);

	    curl_close($c);
	    
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
		$row = fgets($f);
		$delimiter = $row{0};
		$row = fgetcsv($f,0,$delimiter);

		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		
		$keys = array(
			'indeks',
			'indeks_p',
			'nazwa',
			'producent',
			'magazyn_stan',
			'cena_netto',
			'cena_brutto',
			'cena_brutto_z_marza',
			'kategoria',
			'magazyn_ilosc',
			'EAN'
		);

		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		if ($pln_id===false || $pln_id===null)
			return false;

		$mag_stan = array();

		while (!feof($f)) {
			$row = fgetcsv($f,0,$delimiter);
			if ($row===false) break;
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items);
			$scanned++;
			
			foreach ($row as $k=>$v) $row[$keys[$k]] = $v;
			
			if ($row['magazyn_stan']=='jest') {
				$available++;
				/*** determine quantity and quantity info ***/
				if (is_numeric($row['magazyn_ilosc'])) {
					$quantity = $row['magazyn_ilosc'];
					$quantity_info = '';
				} else {
					$quantity_info = $row['magazyn_ilosc'];
					$quantity = 30;
				}

				/*** check for exact match ***/
				$w_item = DB::GetOne('SELECT item_id FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['indeks'], $distributor['id']));
				if ($w_item===false || $w_item===null) {
					$w_item = null;
					/*** exact match not found, looking for candidates ***/
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
						'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row['nazwa']),DB::qstr('%')),
						'|product_code'=>$row['indeks_p']
					));
					if (!empty($matches))
						if (count($matches)==1) {
							/*** one candidate found, if product code is empty or matches, it's ok ***/
							$v = array_pop($matches);
							if ($v['product_code']==$row['indeks_p'] || $v['product_code']=='')
								$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['product_code']==$row['indeks_p']) {
									$w_item = $v['id'];
									break;
								}
						}
					if ($w_item===null) {
						/*** no item was found matching this entry ***/
						$new_items++;
						if ($distributor['add_new_items']) {
							$vendor = Utils_RecordBrowserCommon::get_id('company', 'company_name', $row['producent']);
							$w_item = Utils_RecordBrowserCommon::new_record('premium_warehouse_items', array('item_name'=>$row['nazwa'], 'item_type'=>1, 'product_code'=>$row['indeks_p'], 'vendor'=>$vendor));
						}
					} else {
						/*** found match ***/
						$item_exist++;
					}
					if ($w_item!==null) {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_id, quantity, quantity_info, price, price_currency) VALUES (%d, %s, %d, %d, %s, %f, %d)', array($w_item, $row['indeks'], $distributor['id'], $quantity, $quantity_info, $row['cena_netto'], $pln_id));
					}
				} else {
					/*** there's an exact match in the system already ***/
					$link_exist++;
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row['cena_netto'], $pln_id, $row['indeks'], $distributor['id']));
				}
			} 
		}
		Premium_Warehouse_WholesaleCommon::update_scan_status($scanned, $scanned, $available, $item_exist, $link_exist, $new_items);
		fclose($f);
		return true;
	}
}

?>
