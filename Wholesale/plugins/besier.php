<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Pawel Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2011, crazyIT
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Wholesale__Plugin_besier implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Besier Oehling GmbH';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array('POP3 server'=>'text','User'=>'text','Password'=>'password');
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

		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale').'/besier';
		@mkdir($dir);

        $ret = false;
        foreach($l as $msgl) {
            $headers = imap_fetchheader($in,$msgl->uid,FT_UID | FT_PREFETCHTEXT);
            $body = imap_body($in,$msgl->uid,FT_UID | FT_PEEK);
            file_put_contents($dir.'/tmp.mime',$headers.$body);
            ob_start();
            passthru('cd "'.$dir.'" && munpack -q tmp.mime');
            ob_end_clean();
            @unlink($dir.'/tmp.mime');
            foreach(scandir($dir) as $f) {
                if($f=='.' || $f=='..') continue;
                if(!$ret && preg_match('/^Stockpricelist/i',$f) && preg_match('/xls$/i',$f)) {
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

	    $time = time();
	    $filename = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale').'/besier_'.$time.'.tmp';
	    rename($dir.'/'.$ret,$filename);
	    @unlink(ModuleManager::get_data_dir('Premium_Warehouse_Wholesale').'/besier.xls'); //backup
	    @copy($filename,ModuleManager::get_data_dir('Premium_Warehouse_Wholesale').'/besier.xls'); //backup
	    recursive_rmdir($dir);

		Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','File downloaded.'), 1, true);
	    
	    return $filename;
	}

	public function update_from_file($filename, $distributor, $params) {
		try {
			$old_err = error_reporting(0);
			$xls = Libs_PHPExcelCommon::load($filename);
			error_reporting($old_err);
		} catch(Exception $e) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to parse uploaded file, invalid XLS: '.$e), 2, true);
			return false;
		}
		if(!$xls) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to parse uploaded file, invalid XLS.'), 2, true);
			return false;
		}

		$uploaded_data = array();
		$map = array('Product Type'=>'Grupa towarowa',
		            'Producer'=>'Producent',
		            'Product Name'=>'Nazwa produktu',
		            'Net Price €'=>'Cena netto',
		            'Stock'=>'Stan mag',
		            'Code'=>'Kod produktu',
		            'Prod. Code'=>'Kod producenta');
		
		foreach($xls->getAllSheets() as $sheet) {
			$cols = null;
			foreach($sheet->getRowIterator() as $row) {
				$cellIterator = $row->getCellIterator();
			  	$cellIterator->setIterateOnlyExistingCells(false);
				if($cols===null) {
					$cols = array();
					foreach ($cellIterator as $j=>$cell) {
						$cols[$j] = $cell->getValue();
					}
				} else {
					$tmp = array();
					foreach ($cellIterator as $j=>$cell) {
						if(!isset($map[$cols[$j]]))
							continue;
						$tmp[$map[$cols[$j]]] = trim($cell->getValue());
					}
					$uploaded_data[] = $tmp;
				}
			}
		}
		unset($xls);
		
		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		$euro_id = Utils_CurrencyFieldCommon::get_id_by_code('EUR');
		if ($euro_id===false || $euro_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to find required currency (%s), aborting.', array('PLN')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));
		
		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distr_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scanning...'));
		foreach ($uploaded_data as $row) {
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			if (strlen($row['Nazwa produktu'])>127) $row['Nazwa produktu'] = substr($row['Nazwa produktu'],0,127);
			
			$row['Stan mag'] = trim($row['Stan mag']);
			if(!is_numeric($row['Stan mag'])) $row['Stan mag'] = 0;
			if ($row['Stan mag']!=0) {
				$available++;
			}
				
			if($row['Grupa towarowa']) {
				if(!isset($categories[$row['Grupa towarowa']])) {
					$categories[$row['Grupa towarowa']] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distr_categories',array('foreign_category_name'=>$row['Grupa towarowa'],'distributor'=>$distributor['id']));
					$new_categories++;
				} elseif(isset($categories_to_del[$row['Grupa towarowa']]))
					unset($categories_to_del[$row['Grupa towarowa']]);
				$category = $categories[$row['Grupa towarowa']];
			} else $category = null;

			$manufacturer = null;
			if($row['Producent']) {
				$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$row['Producent']),array('group'));
				$producent = explode(' ',$row['Producent']);
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
				$w_item = null;
				/*** exact match not found, looking for candidates ***/
				$matches = array();
				if($row['Kod producenta'])
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
						'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row['Nazwa produktu']),DB::qstr('%')),
						'|manufacturer_part_number'=>$row['Kod producenta'],
						'|product_code'=>$row['Kod producenta']
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
				} else {
					/*** found match ***/
					$item_exist++;
				}
				if (!is_numeric($row['Cena netto'])) $row['Cena netto'] = 0;
				if ($w_item!==null) {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer,manufacturer_part_number) VALUES (%d, %s, %s, %d, %d, %s, %f, %d,%d,%d, %s)', array($w_item, $row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $row['Stan mag'], '', $row['Cena netto'], $euro_id,$category,$manufacturer, substr($row['Kod producenta'],0,32)));
				} else {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer,manufacturer_part_number) VALUES (%s, %s, %d, %d, %s, %f, %d,%d,%d, %s)', array($row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $row['Stan mag'], '', $row['Cena netto'], $euro_id,$category,$manufacturer, substr($row['Kod producenta'],0,32)));
				}
			} else {
				if (!is_numeric($row['Cena netto'])) $row['Cena netto'] = 0;
				/*** there's an exact match in the system already ***/
				$link_exist++;
				DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d,distributor_category=%d,manufacturer=%d,manufacturer_part_number=%s WHERE internal_key=%s AND distributor_id=%d', array($row['Stan mag'], '', $row['Cena netto'], $euro_id, $category,$manufacturer, substr($row['Kod producenta'],0,32), $row['Kod produktu'], $distributor['id']));
			}
		} 
		foreach($categories_to_del as $name=>$id) {
			Utils_RecordBrowserCommon::delete_record('premium_warehouse_distr_categories',$id);
		}
		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scan complete.'), 1);
		Premium_Warehouse_WholesaleCommon::update_scan_status($scanned, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
		return true;
	}
}

?>
