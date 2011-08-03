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

class Premium_Warehouse_Wholesale__Plugin_midwich implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Midwich';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array(
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
		$url = 'ftp://'.$parameters['Login'].':'.$parameters['Password'].'@ftp.midwich.co.uk/data.zip';
	    $time = time();

		$filename = $dir.'midwich_'.$time.'.tmp';
		$filename2 = $dir.'midwich_'.$time.'.zip';
		
	    $f = fopen($filename2,'w');
	    curl_setopt($c, CURLOPT_URL, $url);
	    curl_setopt($c, CURLOPT_FILE, $f);

		if (!curl_exec($c)) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Authentication failure, aborting.'), 2, true);
			return false;
		}

	    curl_close($c);
	    fclose($f);

		@mkdir($dir.'/midwich');
		$zip = new ZipArchive;
		if ($zip->open($filename2) == 1) {
			$zip->extractTo($dir.'/midwich');
		} else {
			return false;
		}
		
		$zip->close();

		@unlink($filename2);
		
		if(!file_exists($dir.'/midwich/Midwich.csv')) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','No CSV file in ZIP.'), 2, true);
			recursive_rmdir($dir.'/midwich');
			return false;
		}
		
		rename($dir.'/midwich/Midwich.csv',$filename);
		@unlink($dir.'/midwich.csv');
		@copy($filename,$dir.'/midwich.csv');
		recursive_rmdir($dir.'/midwich');

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

		$keys = fgetcsv($f,0,$delimiter);

		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('GBP');
		if ($pln_id===false || $pln_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to find required currency (%s), aborting.', array('GBP')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));
		
		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distr_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scanning...'));
		while (!feof($f)) {
			$row = fgetcsv($f,0,$delimiter);
			if ($row===false) break;
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			foreach ($row as $k=>$v) $row[$keys[$k]] = $v;
			$row['product_name'] = $row['product_name'];
			if (strlen($row['product_name'])>127) $row['product_name'] = substr($row['product_name'],0,127);
			
			if ($row['stock']!=0) {
				$available++;
			}
				/*** determine quantity and quantity info ***/
			if (is_numeric($row['stock'])) {
				$quantity = $row['stock'];
				$quantity_info = '';
			} else {
				$quantity_info = $row['stock'];
				$quantity = 0;
			}
				
			if($row['category']) {
				if(!isset($categories[$row['category']])) {
					$categories[$row['category']] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distr_categories',array('foreign_category_name'=>$row['category'],'distributor'=>$distributor['id']));
					$new_categories++;
				} elseif(isset($categories_to_del[$row['category']]))
					unset($categories_to_del[$row['category']]);
				$category = $categories[$row['category']];
			} else $category = null;

			$manufacturer = null;
			if($row['manu_name']) {
				$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$row['manu_name']),array('group'));
				$manu_name = explode(' ',$row['manu_name']);
				if(!$cc && count($manu_name)>1) 
					$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$manu_name[0]),array('group'));
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
			$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['midw_part_no'], $distributor['id']));
			if (($internal_key===false || $internal_key===null) && $row['manu_part_no']) {
				$w_item = null;
				/*** exact match not found, looking for candidates ***/
				$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
					'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row['product_name']),DB::qstr('%')),
					'|manufacturer_part_number'=>$row['manu_part_no']
				));
				if (!empty($matches))
					if (count($matches)==1) {
						/*** one candidate found, if product code is empty or matches, it's ok ***/
						$v = array_pop($matches);
						if ($v['manufacturer_part_number']==$row['manu_part_no'] || $v['manufacturer_part_number']=='')
							$w_item = $v['id'];
					} else {
						/*** found more candidates, only product code is important now ***/
						foreach ($matches as $v)
							if ($v['manufacturer_part_number']==$row['manu_part_no']) {
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
				if (!is_numeric($row['cost'])) $row['cost'] = 0;
				if ($w_item!==null) {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer,manufacturer_part_number) VALUES (%d, %s, %s, %d, %d, %s, %f, %d,%d,%d, %s)', array($w_item, $row['midw_part_no'], $row['product_name'], $distributor['id'], $quantity, $quantity_info, $row['cost'], $pln_id,$category,$manufacturer, substr($row['manu_part_no'],0,32)));
				} else {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer,manufacturer_part_number) VALUES (%s, %s, %d, %d, %s, %f, %d,%d,%d, %s)', array($row['midw_part_no'], $row['product_name'], $distributor['id'], $quantity, $quantity_info, $row['cost'], $pln_id,$category,$manufacturer, substr($row['manu_part_no'],0,32)));
				}
			} else {
				if (!is_numeric($row['cost'])) $row['cost'] = 0;
				/*** there's an exact match in the system already ***/
				$link_exist++;
				DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d,distributor_category=%d,manufacturer=%d,manufacturer_part_number=%s WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row['cost'], $pln_id, $category,$manufacturer, substr($row['manu_part_no'],0,32), $row['midw_part_no'], $distributor['id']));
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
