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
			'Client number'=>'text',
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
	
	public function download_file($parameters, $distributor) {
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');

	    $c = curl_init();
	    $url = 'https://intouch.techdata.com/services/centrAuthentication.asp';

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
			'id'=>$parameters['Client number'],'name'=>$parameters['Login'],'pwd'=>$parameters['Password'],
			'local_url'=>'http://www.techdata.pl/logon.aspx?OPAGE=%2fDefault.aspx%3fOID%3d301%26NOROT%3d1&screen=1280x1024')));

	    $output = curl_exec($c);

		preg_match('/action=\"(.*?)\"/',$output,$match);
		if (!isset($match[1])) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Authentication failure, aborting.'), 2, true);
			return false;
		}
	    $url=$match[1];
		preg_match('/session\" value=\"([a-zA-Z0-9]*?)\"/',$output,$match);
	    curl_setopt($c, CURLOPT_URL, $url);
	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array('session'=>$match[1])));
	    $output = curl_exec($c);
	    
		$url = 'http://www.techdata.pl/pliki/cenniki/?OID=3385';
		curl_setopt($c, CURLOPT_URL, $url);
		$output = curl_exec($c);

		preg_match('/\"(\/download\.aspx.*?)\"/', $output, $match);
		if (!isset($match[1])) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to locate file to download.'), 2, true);
			return false;
		}
	    
		$url=htmlspecialchars_decode('http://www.techdata.pl'.$match[1]);
		curl_setopt($c, CURLOPT_URL, $url);
	    $output = curl_exec($c);
	    $time = time();	    

		$zip_filename = $dir.'zip_techdata_'.$time.'.tmp';
		$zip_extract_path = $dir.'zip_techdata_'.$time.'/';
		file_put_contents($zip_filename, $output);

		$zip = new ZipArchive;
		if ($zip->open($zip_filename) == 1) {
			$zip->extractTo($zip_extract_path);
		} else {
			return false;
		}
		
		$zip->close();

		@unlink($zip_filename);
		
		$sdir = scandir($zip_extract_path);
		$filename = '';
		foreach ($sdir as $file)
			if ($file != basename($file, '.DBF')) {
				$filename = $file;
			}
		copy($zip_extract_path.$filename, $dir.$filename);
		recursive_rmdir($zip_extract_path);
		
			
	    curl_close($c);

		Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','File downloaded.'), 1, true);
	    
	    return $dir.$filename;
	}

	public function update_from_file($filename, $distributor) {
		$d = dbase_open($filename, 0);

		$total = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		static $parts = array(
			'VENDOR'=>24, 
			'KOD_TD'=>7, 
			'NAZWA'=>65, 
			'SYMBOLPROD'=>21, 
			'CENA_C'=>20, 
			'W_MAG'=>7, 
			'NOWOSC'=>1, 
			'GRUPA'=>60, 
			'TEL'=>1, 
			'OPIS1'=>170,
			'OPIS2'=>170);
			
		$index = 1;
		$limit = dbase_numrecords($d);

		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		if ($pln_id===false || $pln_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to find required currency (%s), aborting.', array('PLN')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));

		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distributor_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scanning...'));
		while ($index <= $limit) {
			Premium_Warehouse_WholesaleCommon::update_scan_status($limit, $total, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$row_parts = dbase_get_record_with_names($d, $index);
			$index++;

			foreach ($row_parts as $k=>$v)
				$row_parts[$k] = trim($v);
			$row_parts['NAZWA'] = mb_convert_encoding($row_parts['NAZWA'],"ISO-8859-1","UTF-8");
			if (strlen($row_parts['NAZWA'])>127) $row_parts['NAZWA'] = substr($row_parts['NAZWA'],0,127);

			$total++;
			$row_parts['W_MAG'] = strtolower(str_replace(' ','_',$row_parts['W_MAG']));
			if ($row_parts['W_MAG']!='nie_ma' && $row_parts['NAZWA']) {
				$available++;
				/*** determine quantity and quantity info ***/
				if ($row_parts['W_MAG']=='jest') $quantity = 1;
				else $quantity = 0;
				$quantity_info = '';
				switch ($row_parts['W_MAG']) {
					case 'za_2tyg': $quantity_info='In 2 weeks or more';break;
					case 'za_tydz': $quantity_info='In 1-2 weeks';break;
					case 'wkrotce': $quantity_info='In less than 2 weeks';break;
					case 'jutro': $quantity_info='Tomorrow';break;
				}

				if($row_parts['GRUPA']) {
					if(!isset($categories[$row_parts['GRUPA']])) {
						$categories[$row_parts['GRUPA']] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distributor_categories',array('foreign_category_name'=>$row_parts['GRUPA'],'distributor'=>$distributor['id']));
						$new_categories++;
					} elseif(isset($categories_to_del[$row_parts['GRUPA']]))
						unset($categories_to_del[$row_parts['GRUPA']]);
					$category = $categories[$row_parts['GRUPA']];
				} else $category = null;

				$manufacturer = null;
				if($row_parts['VENDOR']) {
					$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$row_parts['VENDOR']),array('group'));
					$producent = explode(' ',$row_parts['VENDOR']);
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
				$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row_parts['KOD_TD'], $distributor['id']));
				if (($internal_key===false || $internal_key===null) && $row_parts['SYMBOLPROD']) {
					$w_item = null;
					/*** exact match not found, looking for candidates ***/
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
						'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row_parts['NAZWA']),DB::qstr('%')),
						'|manufacturer_part_number'=>$row_parts['SYMBOLPROD']
					));
					if (!empty($matches))
						if (count($matches)==1) {
							/*** one candidate found, if product code is empty or matches, it's ok ***/
							$v = array_pop($matches);
							if ($v['manufacturer_part_number']==$row_parts['SYMBOLPROD'] || $v['manufacturer_part_number']=='')
								$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['manufacturer_part_number']==$row_parts['SYMBOLPROD']) {
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
					if ($w_item!==null) {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency, distributor_category,manufacturer) VALUES (%d, %s, %s, %d, %d, %s, %f, %d, %d,%d)', array($w_item, $row_parts['KOD_TD'], $row_parts['NAZWA'], $distributor['id'], $quantity, $quantity_info, $row_parts['CENA_C'], $pln_id,$category,$manufacturer));
					} else {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency, distributor_category,manufacturer) VALUES (%s, %s, %d, %d, %s, %f, %d, %d,%d)', array($row_parts['KOD_TD'], $row_parts['NAZWA'], $distributor['id'], $quantity, $quantity_info, $row_parts['CENA_C'], $pln_id,$category,$manufacturer));
					}
				} else {
					/*** there's an exact match in the system already ***/
					$link_exist++;
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d, distributor_category=%d, manufacturer=%d WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row_parts['CENA_C'], $pln_id, $category,$manufacturer, $row_parts['KOD_TD'], $distributor['id']));
				}
			} 
		}
		foreach($categories_to_del as $name=>$id) {
			Utils_RecordBrowserCommon::delete_record('premium_warehouse_distributor_categories',$id);
		}
		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scan complete.'), 1);
		Premium_Warehouse_WholesaleCommon::update_scan_status($limit, $total, $available, $item_exist, $link_exist, $new_items, $new_categories);
		dbase_close($d);
		return true;
	}
}

?>
