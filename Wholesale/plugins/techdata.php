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
	 * Returns whether plugin supports auto-update feature
	 * 
	 * @return bool support enabled
	 */
	public function is_auto_update() {
		return true;
	}
	
	public function download_file($parameters, $distributor) {
	    $c = curl_init();
	    $url = 'https://intouch.techdata.com/services/centrAuthentication.asp';

	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_COOKIEFILE, "D:/cookiefile.cf");
		curl_setopt($c, CURLOPT_COOKIEJAR, "D:/cookiefile.cf"); 

	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array(
			'id'=>$parameters['Client number'],'name'=>$parameters['Login'],'pwd'=>$parameters['Password'],
			'local_url'=>'http://www.techdata.pl/logon.aspx?OPAGE=%2fDefault.aspx%3fOID%3d301%26NOROT%3d1&screen=1280x1024')));

	    $output = curl_exec($c);

		preg_match('/action=\"(.*?)\"/',$output,$match);
	    $url=$match[1];
		preg_match('/session\" value=\"([a-zA-Z0-9]*?)\"/',$output,$match);
	    curl_setopt($c, CURLOPT_URL, $url);
	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array('session'=>$match[1])));
	    $output = curl_exec($c);
	    
		$url = 'http://www.techdata.pl/pliki/cenniki/?OID=3385';
		curl_setopt($c, CURLOPT_URL, $url);
		$output = curl_exec($c);
		
		preg_match('/\"(\/download\.aspx.*?)\"/', $output, $match);
	    
	    // TODO: file location, perhaps requires update on daily basis
		$url=htmlspecialchars_decode('http://www.techdata.pl'.$match[1]);
		curl_setopt($c, CURLOPT_URL, $url);
	    $output = curl_exec($c);
	    $time = time();	    

		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');
		$zip_filename = $dir.'zip_techdata_'.$time.'.tmp';
		$zip_extract_path = $dir.'zip_techdata_'.$time.'/';
		file_put_contents($zip_filename, $output);

		$zip = new ZipArchive;
		if ($zip->open($zip_filename) == 1)
			$zip->extractTo($zip_extract_path);
		
		$dir = scandir($zip_extract_path);
		$filename = '';
		foreach ($dir as $file)
			if ($file != basename($file, '.DBF')) {
				$filename = $file;
				break;				
			}
			
//		$this->update_from_file($zip_extract_path.$filename, $distributor);
		
		// TODO: clean up mess

	    curl_close($c);
	    
	    return $zip_extract_path.$filename;
	}

	public function update_from_file($filename, $distributor) {
		if (!is_callable('dbase_open')) {
			$f = fopen($filename, 'r');
			$header = fgets($f,387);
		} else {
			$d = dbase_open($filename, 0);
		}

		$total = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		
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
			
		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));

		$index = 1;
		$limit = dbase_numrecords($d);
		while ((isset($d) && $index <= $limit) || (isset($f) && !feof($f))) {
			Premium_Warehouse_WholesaleCommon::update_scan_status($limit, $total, $available, $item_exist, $link_exist, $new_items);
			if (isset($d)) {
				$row_parts = dbase_get_record_with_names($d, $index);
				$index++;
			} else {
				$row = fgets($f,548);
				$row_parts = array();
				$last = 0;
				foreach ($parts as $k=>$v) {
					$row_parts[$k] = substr($row, $last, $v);
					$last += $v;
				}
			}
			foreach ($row_parts as $k=>$v)
				$row_parts[$k] = trim($v);

			$total++;
			$row_parts['W_MAG'] = strtolower(str_replace(' ','_',$row_parts['W_MAG']));
			$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
			if ($pln_id===false || $pln_id===null)
				return false;
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
				/*** check for exact match ***/
				$w_item = DB::GetOne('SELECT item_id FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row_parts['KOD_TD'], $distributor['id']));
				if ($w_item===false || $w_item===null) {
					$w_item = null;
					/*** exact match not found, looking for candidates ***/
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
						'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row_parts['NAZWA']),DB::qstr('%')),
						'|product_code'=>$row_parts['SYMBOLPROD']
					));
					if (!empty($matches))
						if (count($matches)==1) {
							/*** one candidate found, if product code is empty or matches, it's ok ***/
							$v = array_pop($matches);
							if ($v['product_code']==$row_parts['SYMBOLPROD'] || $v['product_code']=='')
								$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['product_code']==$row_parts['SYMBOLPROD']) {
									$w_item = $v['id'];
									break;
								}
						}
					if ($w_item===null) {
						/*** no item was found matching this entry ***/
						$new_items++;
						if ($distributor['add_new_items']) {
							$vendor = Utils_RecordBrowserCommon::get_id('company', 'company_name', $row_parts['VENDOR']);
							$w_item = Utils_RecordBrowserCommon::new_record('premium_warehouse_items', array('item_name'=>$row_parts['NAZWA'], 'item_type'=>1, 'product_code'=>$row_parts['SYMBOLPROD'], 'vendor'=>$vendor));
						}
					} else {
						/*** found match ***/
						$item_exist++;
					}
					if ($w_item!==null) {
						DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_id, quantity, quantity_info, price, price_currency) VALUES (%d, %s, %d, %d, %s, %f, %d)', array($w_item, $row_parts['KOD_TD'], $distributor['id'], $quantity, $quantity_info, $row_parts['CENA_C'], $pln_id));
					}
				} else {
					/*** there's an exact match in the system already ***/
					$link_exist++;
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row_parts['CENA_C'], $pln_id, $row_parts['KOD_TD'], $distributor['id']));
				}
			} 
		}
		Premium_Warehouse_WholesaleCommon::update_scan_status($limit, $total, $available, $item_exist, $link_exist, $new_items);
		if (isset($d))
			dbase_close($d);
		else 
			fclose($f);
		return true;
	}
}

?>
