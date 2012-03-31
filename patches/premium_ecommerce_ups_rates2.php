<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
		Utils_CommonDataCommon::remove('UPS_Area');

		$areas = array(
				"EU,07"=>'EU, UPS Express',
				"EU,08"=>'EU, UPS Expedited',
				"EU,11"=>'EU, UPS Standard',
				"EU,54"=>'EU, UPS Worldwide Express Plus',
				"EU,64"=>'EU, UPS Express NA1',
				"EU,65"=>'EU, UPS Express Saver',
				"US,01"=>'USA, UPS Next Day Air',
				"US,02"=>'USA, UPS 2nd Day Air',
				"US,03"=>'USA, UPS Ground',
				"US,07"=>'USA, UPS Worldwide Express',
				"US,08"=>'USA, UPS Worldwide Expedited',
				"US,11"=>'USA, UPS Standard',
				"US,12"=>'USA, UPS 3 Day Select',
				"US,13"=>'USA, UPS Next Day Air Saver',
				"US,14"=>'USA, UPS Next Day Early A.M.',
				"US,54"=>'USA, UPS Worldwide Express Plus',
				"US,59"=>'USA, UPS 2nd Day Air A.M.',
				"US,65"=>'USA, UPS Express Saver',
				"CA,01"=>'Canada, UPS Express',
				"CA,02"=>'Canada, UPS Expedited',
				"CA,07"=>'Canada, UPS Worldwide Express',
				"CA,08"=>'Canada, UPS Worldwide Expedited',
				"CA,11"=>'Canada, UPS Standard',
				"CA,12"=>'Canada, UPS 3 Day Select',
				"CA,13"=>'Canada, UPS Express Saver',
				"CA,14"=>'Canada, UPS Express Early A.M.',
				"CA,54"=>'Canada, UPS Worldwide Express Plus'
				);
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Shipment_Types/2',$areas,false,true);
}
?>