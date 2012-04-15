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

class Premium_Warehouse_Wholesale__Plugin_foxfoto implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Foxfoto';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array(
			'URL'=>'text',
			'Cennik'=>'text'
			
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
	 * @param array distributor record
	 * @return string filename with its location
	 */
	public function download_file($params, $distributor) {
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');

	    $c = curl_init();
	    $url = trim(html_entity_decode($params['URL']));
	    if ($url==""){
			$url = 'http://new.foxfoto.pl/export.php?auth=b7a02433123475fb74598506ecd0f125&format=csv';
		}
	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.01; Windows NT 5.0)");
		//curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
//		curl_setopt($c, CURLOPT_COOKIEFILE, $dir.'cookiefile.cf');
	//	curl_setopt($c, CURLOPT_COOKIEJAR, $dir.'cookiefile.cf');
		$output = curl_exec($c);
		
		if (!$output || strlen($output)<10000) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Authentication failure, aborting.'), 2, true);
			return false;
		}
	    $time = time();

		$filename = $dir.'foxfoto_'.$time.'.tmp';
		file_put_contents($filename, $output);

	    curl_close($c);

		Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','File downloaded.'), 1, true);
	    
	    return $filename;
	}

	/**
	 * This method is called when a new file is given either via upload or auto-download method
	 * It should parse the file and manipulate `premium_warehouse_wholesale_items` table to store results of the parsing
	 * 
	 * @param string filename that should be parsed with its location 
	 * @param array distributor record
	 * @return bool true if the update was successful, false otherwise
	 */
	public function update_from_file($filename, $distributor, $params) {

		$f = fopen($filename,'r');
		$delimiter = ';';

		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		$keys = fgetcsv($f,0,$delimiter,'"');

		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		if ($pln_id===false || $pln_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to find required currency (%s), aborting.', array('PLN')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));
		//DB::Execute('DELETE FROM premium_warehouse_wholesale_items WHERE distributor_id=%d', array($distributor['id']));
		
		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distr_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scanning...'));
		while (!feof($f)) {
			$row = fgetcsv($f,0,$delimiter,'"');
			if (!$row) break;
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			if(count($row)<count($keys)) continue;
			foreach ($row as $k=>$v) {
				if(!isset($keys[$k]))break;
				$row[$keys[$k]] = $v;				
			}
			if (strlen($row['Nazwa produktu'])>127) $row['Nazwa produktu'] = substr($row['Nazwa produktu'],0,127);
			
			$cena = str_replace(',','.',$row['Cena netto '.$params['Cennik'].' - PLN']);
			if(!is_numeric($cena)) continue;
			
			if ($row['Dostępność']!=0) {
				$available++;
			}
				/*** determine quantity and quantity info ***/
				
			if (trim($row['Dostępność'])=== 'W magazynie'){
				$quantity_info = '';
				$quantity=1;
			} else { 
				$quantity_info = $row['Dostępność'];
				$quantity=0;
			}
				
			if($row['Kategoria'] ) {
				if(!isset($categories[$row['Kategoria']])) {
					$categories[$row['Kategoria']] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distr_categories',array('foreign_category_name'=>$row['Kategoria'],'distributor'=>$distributor['id']));
					$new_categories++;
				} elseif(isset($categories_to_del[$row['Kategoria']]))
					unset($categories_to_del[$row['Kategoria']]);
				$category = $categories[$row['Kategoria']];
			} else $category = null;

			$manufacturer = null;
			if($row['producent']) {
				$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$row['producent']),array('group'));
				$producent = explode(' ',$row['producent']);
				if(!$cc && count($producent)>1) 
					$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$producent[0]),array('group'));
				if($cc) {
					$cc2 = array_shift($cc);
					$manufacturer = $cc2['id'];
			    		if(!in_array('manufacturer', $cc2['group'])) {
			    			$cc2['group']['manufacturer'] = 'manufacturer';
				    		Utils_RecordBrowserCommon::update_record('company',$cc2['id'],array('group'=>$cc2['group']));
			    		}
				}
			}

			/*** check for exact match ***/
			$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['Kod produktu'], $distributor['id']));
			if ($internal_key===false || $internal_key===null) {
				$new_items++;
				DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer) VALUES (%s, %s, %d, %d, %s, %f, %d,%d,%d)', array($row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $quantity, $quantity_info, $cena, $pln_id,$category,$manufacturer));
			} else {
				$link_exist++;
				DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d,distributor_category=%d,manufacturer=%d WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $cena, $pln_id, $category,$manufacturer, $row['Kod produktu'], $distributor['id']));
			}
		} 
		foreach($categories_to_del as $name=>$id) {
			Utils_RecordBrowserCommon::delete_record('premium_warehouse_distr_categories',$id);
		}
		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scan complete.'), 1);
		Premium_Warehouse_WholesaleCommon::update_scan_status($scanned, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
		fclose($f);
		return true;
	}
}

?>
