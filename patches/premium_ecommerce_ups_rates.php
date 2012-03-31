<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
		$areas = array(
				"EU"=>'Europe',
				"US"=>'USA',
				"CA"=>'Canada');
		Utils_CommonDataCommon::new_array('UPS_Area',$areas);

		$eu_services = array(
			'07' => 'UPS Express',
        	'08' => 'UPS Expedited',
        	'11' => 'UPS Standard',
        	'54' => 'UPS Worldwide Express Plus',
        	'64' => 'UPS Express NA1',
        	'65' => 'UPS Express Saver');
		Utils_CommonDataCommon::new_array('UPS_Area/EU',$eu_services,false,true);

		$ca_services = array(
        	'01' => 'UPS Express',
           	'02' => 'UPS Expedited',
        	'07' => 'UPS Worldwide Express',
        	'08' => 'UPS Worldwide Expedited',
        	'11' => 'UPS Standard',
        	'12' => 'UPS 3 Day Select',
        	'13' => 'UPS Express Saver',
        	'14' => 'UPS Express Early A.M.',
        	'54' => 'UPS Worldwide Express Plus');
		Utils_CommonDataCommon::new_array('UPS_Area/CA',$ca_services,false,true);

		$us_services = array(
        	'01' => 'UPS Next Day Air',
            '02' => 'UPS 2nd Day Air',
        	'03' => 'UPS Ground',
        	'07' => 'UPS Worldwide Express',
        	'08' => 'UPS Worldwide Expedited',
        	'11' => 'UPS Standard',
        	'12' => 'UPS 3 Day Select',
        	'13' => 'UPS Next Day Air Saver',
        	'14' => 'UPS Next Day Air Early A.M.',
        	'54' => 'UPS Worldwide Express Plus',
        	'59' => 'UPS 2nd Day Air A.M.',
        	'65' => 'UPS Express Saver');
		Utils_CommonDataCommon::new_array('UPS_Area/US',$us_services,false,true);
}
?>