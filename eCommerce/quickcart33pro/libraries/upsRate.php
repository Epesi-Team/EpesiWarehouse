<?php
class upsRate {
    var $AccessLicenseNumber;  
    var $UserId;  
    var $Password;
    var $shipperNumber;
    var $credentials;

    /**********************************************
     * $access = XML Access Code
     * $user = UPS.com Username
     * $password = UPS.com Password
     * $shipper = UPS Account Number
     *
     **********************************************/
    function __construct($access="",$user="",$pass="",$shipper="") { 
        if($access=="")
                throw new Exception("Empty XML Access Code");
        elseif($user=="")
                throw new Exception("Empty Username");
        elseif($pass=="")
                throw new Exception("Empty Password");
        elseif($shipper=="")
                throw new Exception("Empty UPS Account Number");
        else {
                $this->AccessLicenseNumber = $access;
                $this->UserID = $user;
                $this->Password = $pass;        
                $this->shipperNumber = $shipper;
                $this->credentials = 1;
        };
    }

    // Define the function getRate()
    function getRate($country,$PostalCode,$dest_country,$dest_zip,$service,$weight,$weight_unit = 'LBS') {
	static $cache;
	if(!isset($cache)) $cache = array();
	$args = func_get_args();
	$cache_id = md5(serialize($args));
	if(isset($cache[$cache_id])) return $cache[$cache_id];
        $data ="<?xml version=\"1.0\"?>  
                <AccessRequest xml:lang=\"en-US\">  
                    <AccessLicenseNumber>$this->AccessLicenseNumber</AccessLicenseNumber>  
                    <UserId>$this->UserID</UserId>  
                    <Password>$this->Password</Password>  
                </AccessRequest>  
                <?xml version=\"1.0\"?>  
                <RatingServiceSelectionRequest xml:lang=\"en-US\">  
                    <Request>  
                                <TransactionReference>  
                                        <CustomerContext>Bare Bones Rate Request</CustomerContext>  
                                        <XpciVersion>1.0001</XpciVersion>  
                                </TransactionReference>  
                                <RequestAction>Rate</RequestAction>  
                                <RequestOption>Rate</RequestOption>  
                    </Request>  
                        <PickupType>  
                                <Code>01</Code>  
                        </PickupType>  
                        <Shipment>  
                                <Shipper>  
                                        <Address>  
                                                <PostalCode>$PostalCode</PostalCode>  
                                                <CountryCode>$country</CountryCode>  
                                        </Address>  
                                        <ShipperNumber>$this->shipperNumber</ShipperNumber>  
                                </Shipper>  
                                <ShipTo>  
                                        <Address>  
                                                <PostalCode>$dest_zip</PostalCode>  
                                                <CountryCode>$dest_country</CountryCode>  
                                        <ResidentialAddressIndicator/>  
                                        </Address>  
                                </ShipTo>  
                                <ShipFrom>  
                                        <Address>  
                                                <PostalCode>$PostalCode</PostalCode>  
                                                <CountryCode>$country</CountryCode>  
                                        </Address>  
                                </ShipFrom>  
                                <Service>  
                                        <Code>$service</Code>  
                                </Service>  
                                <Package>  
                                        <PackagingType>  
                                                <Code>02</Code>  
                                        </PackagingType>  
                                        <PackageWeight>  
                                                <UnitOfMeasurement>  
                                                <Code>$weight_unit</Code>  
                                                </UnitOfMeasurement>  
                                                <Weight>$weight</Weight>  
                                        </PackageWeight>  
                                </Package>  
                        </Shipment>  
                </RatingServiceSelectionRequest>";  
                $ch = curl_init("https://www.ups.com/ups.app/xml/Rate");  
                curl_setopt($ch, CURLOPT_HEADER, 0);  
                curl_setopt($ch,CURLOPT_POST,1);  
                curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
                curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
                curl_setopt($ch,CURLOPT_POSTFIELDS,$data);  
                curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);  
                $result=curl_exec ($ch);  

                // Find out if the UPS service is down
                preg_match_all('/HTTP\/1\.\d\s(\d+)/',$result,$matches);
                foreach($matches[1] as $key=>$value) {
                    if ($value != 100 && $value != 200) {
                        throw new Exception("The UPS service seems to be down with HTTP/1.1 $value");
                    }
                }
                $res = simplexml_load_string($result);
                if($res->Response->ResponseStatusCode==0) {
                    throw new Exception($res->Response->Error[0]->ErrorDescription);
                }
		
                return ($cache[$cache_id] = array((string)$res->RatedShipment->TotalCharges->MonetaryValue,(string)$res->RatedShipment->TotalCharges->CurrencyCode));  
            }  
    }


function ups_rate($country,$zip,$weight,$service) {
global $config;
if(!isset($config['ups_accesskey']) || !$config['ups_accesskey'] || 
    !isset($config['ups_username']) || !$config['ups_username'] || 
    !isset($config['ups_password']) || !$config['ups_password'] || 
    !isset($config['ups_shipper_number']) || !$config['ups_shipper_number'] || 
    !isset($config['ups_src_country']) || !$config['ups_src_country'] || 
    !isset($config['ups_src_zip']) || !$config['ups_src_zip'] || 
    !isset($config['ups_weight_unit']) || !$config['ups_weight_unit'])
    return 0;
$ups = new upsRate($config['ups_accesskey'],$config['ups_username'], $config['ups_password'],$config['ups_shipper_number']);
try {
    $ok=$ups->getRate($config['ups_src_country'],$config['ups_src_zip'],$country,$zip,substr($service,strpos($service,',')+1),$weight,$config['ups_weight_unit']);
} catch(Exception $e) {
    return 'Error: '.$e->getMessage();
}

if(!isset($ok[1]))
    return 'Error: invalid return from UPS server - '.print_r($ok,true);

if($ok[1]==$config['currency_symbol'])
    return $ok[0];

static $rates;
if(!isset($rates)) {    
    $ret = @simplexml_load_file('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');
    if(!$ret) {
	return 'Error: cannot get ECB currencies table';
    }

    $currencies = array($ok[1]=>$ok[1], $config['currency_symbol']=>$config['currency_symbol']);

    $rates = array();
    if(isset($currencies['EUR']))
	$rates[$currencies['EUR']]=1;
    foreach($ret->Cube->Cube->Cube as $r) {
	if(isset($currencies[(String)$r['currency']])) {
	        $rates[$currencies[(String)$r['currency']]] = (String)$r['rate'];
    	}
    }

    if(count($rates)!=2) return 'Error: invalid currencies - '.implode(', ',$currencies);
}
return $ok[0]/$rates[$ok[1]]*$rates[$config['currency_symbol']];
}
?>

