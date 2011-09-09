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

class Premium_Warehouse_Wholesale__Plugin_incom implements Premium_Warehouse_Wholesale__Plugin {

	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Incom';
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
	 * @param array distributor record
	 * @return string filename with its location
	 */
	public function download_file($parameters, $distributor) {

        try {
    	    $client = new SoapClient('https://nbweb.incom.pl/NBWebServicePHP/service.svc?wsdl');
        	$result = $client->GetTowaryInfoList(array('UserName'=>$parameters['ID'],'Password'=>$parameters['Password']));
        } catch (Exception $e) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Error: '.$e -> getMessage ()), 2, true);
            return false;
        }

		if (!$result) {
			Premium_Warehouse_WholesaleCommon::file_download_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Authentication failure, aborting.'), 2, true);
			return false;
		}
		
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');
	    $time = time();
		$filename = $dir.'incom_'.$time.'.tmp';
		file_put_contents($filename, serialize($result->GetTowaryInfoListResult->TowarLista->TowarInfoTypePHP));

		
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
	    $result = @unserialize(file_get_contents($filename));
	    if(!$result || !is_array($result)) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Invalid file, aborting.', array('PLN')), 2, true);
			return false;
	    }
	
		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		if ($pln_id===false || $pln_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Unable to find required currency (%s), aborting.', array('PLN')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));

		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distr_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(Base_LangCommon::ts('Premium_Warehouse_Wholesale','Scanning...'));
		foreach($result as $row) {
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			if (strlen($row->Nazwa)>127) $row->Nazwa = substr($row->Nazwa,0,127);
			
			if (is_numeric($row->Stan) && $row->Stan>=0) {
				$available++;
				/*** determine quantity and quantity info ***/
				$quantity = $row->Stan;
				$quantity_info = '';

				if($row->NazwaTypu) {
					if(!isset($categories[$row->NazwaTypu])) {
						$categories[$row->NazwaTypu] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distr_categories',array('foreign_category_name'=>$row->NazwaTypu,'distributor'=>$distributor['id']));
						$new_categories++;
					} elseif(isset($categories_to_del[$row->NazwaTypu]))
						unset($categories_to_del[$row->NazwaTypu]);
					$category = $categories[$row->NazwaTypu];
				} else $category = null;

				$manufacturer = null;
				if($row->NazwaProducenta) {
					$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$row->NazwaProducenta),array('group'));
					$producent = explode(' ',$row->NazwaProducenta);
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
				$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row->Symbol, $distributor['id']));
				if (($internal_key===false || $internal_key===null)) {
					$w_item = null;
					$matches = array();
					if($row->SymbolProducenta)
					/*** exact match not found, looking for candidates ***/
						$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
							'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row->Nazwa),DB::qstr('%')),
							'|manufacturer_part_number'=>$row->SymbolProducenta
						));
					if (!empty($matches))
						if (count($matches)==1) {
							/*** one candidate found, if product code is empty or matches, it's ok ***/
							$v = array_pop($matches);
							if ($v['manufacturer_part_number']==$row->SymbolProducenta || $v['manufacturer_part_number']=='')
								$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['manufacturer_part_number']==$row->SymbolProducenta) {
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
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency, distributor_category,manufacturer,manufacturer_part_number) VALUES (%d, %s, %s, %d, %d, %s, %f, %d, %d, %d, %s)', array($w_item, $row->Symbol, $row->Nazwa, $distributor['id'], $quantity, $quantity_info, $row->CenaNetto, $pln_id,$category, $manufacturer,$row->SymbolProducenta));
					} else {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency, distributor_category,manufacturer,manufacturer_part_number) VALUES (%s, %s, %d, %d, %s, %f, %d, %d, %d, %s)', array($row->Symbol, $row->Nazwa, $distributor['id'], $quantity, $quantity_info, $row->CenaNetto, $pln_id, $category, $manufacturer,$row->SymbolProducenta));
					}
				} else {
					/*** there's an exact match in the system already ***/
					$link_exist++;
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d, distributor_category=%d, manufacturer=%d, manufacturer_part_number=%s WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row->CenaNetto, $pln_id, $category, $manufacturer, $row->SymbolProducenta, $row->Symbol, $distributor['id']));
				}
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
