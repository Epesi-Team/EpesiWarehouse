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

interface Premium_Warehouse_DrupalCommerce_3rdp__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name();
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type (TEXT or PASSWORD)
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters();

	public function download($parameters,$item,$langs,$verbose);
	public function check($parameters,$upc,$man,$mpn,$langs);
}

?>
