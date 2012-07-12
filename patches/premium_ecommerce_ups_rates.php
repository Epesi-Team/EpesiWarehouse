<?php
if (ModuleManager::is_installed('Premium_Warehouse_eCommerce')>=0) {
		$areas = array(
				"EU"=>_M('Europe'),
				"US"=>_M('USA'),
				"CA"=>_M('Canada'));
		Utils_CommonDataCommon::new_array('UPS_Area',$areas);

		$eu_services = array(
			'07' => _M('UPS Express'),
        	'08' => _M('UPS Expedited'),
        	'11' => _M('UPS Standard'),
        	'54' => _M('UPS Worldwide Express Plus'),
        	'64' => _M('UPS Express NA1'),
        	'65' => _M('UPS Express Saver'));
		Utils_CommonDataCommon::new_array('UPS_Area/EU',$eu_services,false,true);

		$ca_services = array(
        	'01' => _M('UPS Express'),
           	'02' => _M('UPS Expedited'),
        	'07' => _M('UPS Worldwide Express'),
        	'08' => _M('UPS Worldwide Expedited'),
        	'11' => _M('UPS Standard'),
        	'12' => _M('UPS 3 Day Select'),
        	'13' => _M('UPS Express Saver'),
        	'14' => _M('UPS Express Early A.M.'),
        	'54' => _M('UPS Worldwide Express Plus'));
		Utils_CommonDataCommon::new_array('UPS_Area/CA',$ca_services,false,true);

		$us_services = array(
        	'01' => _M('UPS Next Day Air'),
            '02' => _M('UPS 2nd Day Air'),
        	'03' => _M('UPS Ground'),
        	'07' => _M('UPS Worldwide Express'),
        	'08' => _M('UPS Worldwide Expedited'),
        	'11' => _M('UPS Standard'),
        	'12' => _M('UPS 3 Day Select'),
        	'13' => _M('UPS Next Day Air Saver'),
        	'14' => _M('UPS Next Day Air Early A.M.'),
        	'54' => _M('UPS Worldwide Express Plus'),
        	'59' => _M('UPS 2nd Day Air A.M.'),
        	'65' => _M('UPS Express Saver'));
		Utils_CommonDataCommon::new_array('UPS_Area/US',$us_services,false,true);
}
?>