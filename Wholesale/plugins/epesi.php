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

class Premium_Warehouse_Wholesale__Plugin_epesi implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Epesi';
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
	 * @param array distributor record
	 * @return string filename with its location
	 */
	public function download_file($parameters, $distributor) {
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');

	    $c = curl_init();
	    $url = rtrim($parameters['URL'],'/').'/modules/Premium/Warehouse/Items/dist.php?'.http_build_query(array(
			'user'=>$parameters['Login'],'pass'=>md5($parameters['Password'])));

	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_COOKIEFILE, $dir.'cookiefile.cf');
		curl_setopt($c, CURLOPT_COOKIEJAR, $dir.'cookiefile.cf'); 

	    $output = curl_exec($c);

		if ($output=='not installed') {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Epesi distributor disabled.'), 2, true);
			return false;
		} elseif($output=='auth failed') {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Authentication failure, aborting.'), 2, true);
			return false;
		} elseif($output=='ban') {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Host banned, aborting.'), 2, true);
			return false;
		} elseif($output=='permission denied') {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Permission denied, aborting.'), 2, true);
			return false;
		}

		
	    $time = time();

		$filename = $dir.'epesi_'.$time.'.tmp';
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
	public function update_from_file($filename, $distributor) {

		$f = fopen($filename,'r');
		$delimiter = ',';

		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		$keys = array(
			'Category',
			'Name',
			'Price',
			'Currency',
			'Quantity',
			'UPC',
			'Manufacturer',
			'MPN',
			'SKU'
		);


		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));
		
		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distributor_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scanning...'));
		while (!feof($f)) {
			$row = fgetcsv($f,0,$delimiter);
			if (!$row) break;
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			foreach ($row as $k=>$v) $row[$keys[$k]] = $v;
			if (strlen($row['Name'])>127) $row['Name'] = substr($row['Name'],0,127);

			$pln_id = Utils_CurrencyFieldCommon::get_id_by_code($row['Currency']);
			if ($pln_id===false || $pln_id===null) {
				continue; //no price
			}
			
			if ($row['Quantity']!=0) {
				$available++;
				/*** determine quantity and quantity info ***/
				if (is_numeric($row['Quantity'])) {
					$quantity = $row['Quantity'];
					$quantity_info = '';
				} else {
					$quantity = 0;
					$quantity_info = 'Invalid quantity';
				}
				
				if(!isset($categories[$row['Category']])) {
					$categories[$row['Category']] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distributor_categories',array('foreign_category_name'=>$row['Category'],'distributor'=>$distributor['id']));
					$new_categories++;
				} elseif(isset($categories_to_del[$row['Category']]))
					unset($categories_to_del[$row['Category']]);
				$category = $categories[$row['Category']];

				/*** check for exact match ***/
				$old = DB::GetRow('SELECT internal_key,item_id FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['SKU'], $distributor['id']));
				$internal_key = $old['internal_key'];
				$w_item = null;
				if (($internal_key===false || $internal_key===null || $old['item_id']===null) && $row['SKU']) {
					$marr = array();
					if($row['Manufacturer']) {
						$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$row['Manufacturer']),array());
						if($cc) {
							$cc2 = array_shift($cc);
							$marr['manufacturer'] = $cc2['id'];
						}
					}
					if(isset($marr['manufacturer'])) {
						$marr['(~"item_name']=DB::Concat(DB::qstr('%'),DB::qstr($row['Name']),DB::qstr('%'));
						if($row['MPN'])
							$marr['|manufacturer_part_number']=$row['MPN'];
						$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', $marr);
					} else {
						$matches = array();
					}
					if($row['UPC'])
						$matches = array_merge($matches,Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array('upc'=>$row['UPC'])));
					/*** exact match not found, looking for candidates ***/
					if (!empty($matches))
						if (count($matches)==1) {
							$v = array_pop($matches);
							$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['manufacturer_part_number']==$row['MPN'] || $v['upc']==$row['UPC']) {
									$w_item = $v['id'];
									break;
								}
						}
					if ($w_item===null) {
						/*** no item was found matching this entry ***/
						$new_items++;
					} else {
						/*** found match ***/
						$item_exist++;
					}
				}
				if($internal_key===false || $internal_key===null) {
					if ($w_item!==null) {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category) VALUES (%d, %s, %s, %d, %d, %s, %f, %d,%d)', array($w_item, $row['SKU'], $row['Name'], $distributor['id'], $quantity, $quantity_info, $row['Price'], $pln_id,$category));
					} else {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category) VALUES (%s, %s, %d, %d, %s, %f, %d,%d)', array($row['SKU'], $row['Name'], $distributor['id'], $quantity, $quantity_info, $row['Price'], $pln_id,$category));
					}
				} elseif($internal_key) {
					/*** there's an exact match in the system already ***/
					$link_exist++;
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d,distributor_category=%d WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row['Price'], $pln_id, $category, $row['SKU'], $distributor['id']));
					if ($w_item!==null) 
						DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE internal_key=%s AND distributor_id=%d', array($w_item, $row['SKU'], $distributor['id']));
				}
			} 
		}
		foreach($categories_to_del as $name=>$id) {
			Utils_RecordBrowserCommon::delete_record('premium_warehouse_distributor_categories',$id);
		}
		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scan complete.'), 1);
		Premium_Warehouse_WholesaleCommon::update_scan_status($scanned, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
		fclose($f);
		return true;
	}
}

?>
