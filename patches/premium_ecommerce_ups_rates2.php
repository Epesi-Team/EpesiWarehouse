<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
		Utils_CommonDataCommon::remove('UPS_Area');

		$areas = array(
				"EU,07"=>_M('EU, UPS Express'),
				"EU,08"=>_M('EU, UPS Expedited'),
				"EU,11"=>_M('EU, UPS Standard'),
				"EU,54"=>_M('EU, UPS Worldwide Express Plus'),
				"EU,64"=>_M('EU, UPS Express NA1'),
				"EU,65"=>_M('EU, UPS Express Saver'),
				"US,01"=>_M('USA, UPS Next Day Air'),
				"US,02"=>_M('USA, UPS 2nd Day Air'),
				"US,03"=>_M('USA, UPS Ground'),
				"US,07"=>_M('USA, UPS Worldwide Express'),
				"US,08"=>_M('USA, UPS Worldwide Expedited'),
				"US,11"=>_M('USA, UPS Standard'),
				"US,12"=>_M('USA, UPS 3 Day Select'),
				"US,13"=>_M('USA, UPS Next Day Air Saver'),
				"US,14"=>_M('USA, UPS Next Day Early A.M.'),
				"US,54"=>_M('USA, UPS Worldwide Express Plus'),
				"US,59"=>_M('USA, UPS 2nd Day Air A.M.'),
				"US,65"=>_M('USA, UPS Express Saver'),
				"CA,01"=>_M('Canada, UPS Express'),
				"CA,02"=>_M('Canada, UPS Expedited'),
				"CA,07"=>_M('Canada, UPS Worldwide Express'),
				"CA,08"=>_M('Canada, UPS Worldwide Expedited'),
				"CA,11"=>_M('Canada, UPS Standard'),
				"CA,12"=>_M('Canada, UPS 3 Day Select'),
				"CA,13"=>_M('Canada, UPS Express Saver'),
				"CA,14"=>_M('Canada, UPS Express Early A.M.'),
				"CA,54"=>_M('Canada, UPS Worldwide Express Plus'));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Shipment_Types/2',$areas,false,true);
}
?>