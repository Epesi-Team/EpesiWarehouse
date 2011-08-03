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
		return array('POP3 server'=>'text','User'=>'text','Password'=>'password');
/*		return array(
			'ID'=>'text',
			'Password'=>'password'
		);*/
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
        //setup
        $host = $parameters['POP3 server'];
        $user = $parameters['User'];
        $pass = $parameters['Password'];
        $pop3 = true;//if not then imap
        $ssl = true;
        $port = null;

        //rest
        if(!is_numeric($port)) {
            if($pop3) {
                if($ssl)
                    $port = 995;
                else
                    $port = 110;    
            } else {
                if($ssl)
                    $port = 993;
                else
                    $port = 143;
            }
        }
        $ref = '{'.$host.':'.$port.'/'.($pop3?'pop3':'imap').'/novalidate-cert'.($ssl?'/ssl':'').'}';
        $in = @imap_open($ref, $user,$pass);
        if(!$in) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Connection failed: '.implode(', ',imap_errors())), 2, true);
			return false;
        }

        $new_name = "Inbox";
        $iname = $ref.$new_name;
        $iname = mb_convert_encoding( $iname, "UTF7-IMAP", "UTF-8" );
        
        $err = imap_errors();
        $st = imap_status($in,$iname,SA_UIDNEXT);
        $last_uid = $st->uidnext;
        $l=imap_fetch_overview($in,'1:'.$last_uid,FT_UID); //list of new messages

		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale').'/ab';
		@mkdir($dir);

        $ret = false;
        foreach($l as $msgl) {
            $headers = imap_fetchheader($in,$msgl->uid,FT_UID | FT_PREFETCHTEXT);
            $body = imap_body($in,$msgl->uid,FT_UID | FT_PEEK);
            file_put_contents($dir.'/tmp.mime',$headers.$body);
            file_put_contents('/tmp/dupa.txt',$headers.$body);
            ob_start();
            passthru('cd "'.$dir.'" && munpack -q tmp.mime');
            ob_end_clean();
            @unlink($dir.'/tmp.mime');
            foreach(scandir($dir) as $f) {
                if($f=='.' || $f=='..') continue;
                if(!$ret && preg_match('/^cennik_AB.zip$/i',$f)) {
                    $ret = $f;
                    continue;
                }
                @unlink($dir.'/'.$f);
            }
            imap_delete($in,$msgl->uid,FT_UID);
            if($ret) break;
        }
        imap_expunge($in);

        imap_close($in);
        
		if (!$ret) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','No new file on mailbox.'), 2, true);
			return false;
		}

		$zip = new ZipArchive;
		if ($zip->open($dir.'/'.$ret) == 1) {
			$zip->extractTo($dir);
		} else {
			return false;
		}
		
		$zip->close();

		@unlink($ret);
		
		if(!file_exists($dir.'/cennik_AB.csv')) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','No CSV file in ZIP.'), 2, true);
			return false;
		}

	    $time = time();
	    $filename = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale').'/ab_'.$time.'.tmp';
	    $output = file_get_contents($dir.'/cennik_AB.csv');
		file_put_contents($filename, iconv("cp1250","UTF-8",$output));
	    @unlink(ModuleManager::get_data_dir('Premium_Warehouse_Wholesale').'/ab.csv');
	    @copy($filename,ModuleManager::get_data_dir('Premium_Warehouse_Wholesale').'/ab.csv'); //backup
	    recursive_rmdir($dir);

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
		$row = fgetcsv($f);

		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		$keys = array(
			'indeks',
			'indeks p.',
			'nazwa',
			'producent',
			'magazyn stan',
			'cena netto',
			'cena brutto',
			'cena brutto + marza',
			'kategoria',
			'magazyn_ilosc',
			'EAN',
			'ID produktu'
		);

		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		if ($pln_id===false || $pln_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to find required currency (%s), aborting.', array('PLN')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));
//		DB::Execute('DELETE FROM premium_warehouse_wholesale_items WHERE distributor_id=%d', array($distributor['id']));

		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distr_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scanning...'));
		while (!feof($f)) {
			$row = fgetcsv($f);
			if ($row===false) break;
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			foreach ($row as $k=>$v) $row[$keys[$k]] = $v;
			$row['nazwa'] = $row['nazwa'];
			if (strlen($row['nazwa'])>127) $row['nazwa'] = substr($row['nazwa'],0,127);
			
			if ($row['magazyn stan']=='jest') {
				$available++;
				/*** determine quantity and quantity info ***/
				if (is_numeric($row['magazyn_ilosc'])) {
					$quantity = $row['magazyn_ilosc'];
					$quantity_info = '';
				} else {
					$quantity_info = $row['magazyn_ilosc'];
					$quantity = 30;
				}

				if($row['kategoria']) {
					if(!isset($categories[$row['kategoria']])) {
						$categories[$row['kategoria']] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distr_categories',array('foreign_category_name'=>$row['kategoria'],'distributor'=>$distributor['id']));
						$new_categories++;
					} elseif(isset($categories_to_del[$row['kategoria']]))
						unset($categories_to_del[$row['kategoria']]);
					$category = $categories[$row['kategoria']];
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
				$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['indeks'], $distributor['id']));
				if (($internal_key===false || $internal_key===null) && $row['indeks p.']) {
					$w_item = null;
					/*** exact match not found, looking for candidates ***/
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
						'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row['nazwa']),DB::qstr('%')),
						'|manufacturer_part_number'=>$row['indeks p.']
					));
					if (!empty($matches))
						if (count($matches)==1) {
							/*** one candidate found, if product code is empty or matches, it's ok ***/
							$v = array_pop($matches);
							if ($v['manufacturer_part_number']==$row['indeks p.'] || $v['manufacturer_part_number']=='')
								$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['manufacturer_part_number']==$row['indeks p.']) {
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
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency, distributor_category,manufacturer,upc) VALUES (%d, %s, %s, %d, %d, %s, %f, %d, %d, %d, %s)', array($w_item, $row['indeks'], $row['nazwa'], $distributor['id'], $quantity, $quantity_info, $row['cena netto'], $pln_id,$category, $manufacturer,substr($row['EAN'],0,128)));
					} else {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency, distributor_category,manufacturer,upc) VALUES (%s, %s, %d, %d, %s, %f, %d, %d, %d, %s)', array($row['indeks'], $row['nazwa'], $distributor['id'], $quantity, $quantity_info, $row['cena netto'], $pln_id, $category, $manufacturer,substr($row['EAN'],0,128)));
					}
				} else {
					/*** there's an exact match in the system already ***/
					$link_exist++;
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d, distributor_category=%d, manufacturer=%d, upc=%s WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row['cena netto'], $pln_id, $category, $manufacturer, substr($row['EAN'],0,128), $row['indeks'], $distributor['id']));
				}
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
