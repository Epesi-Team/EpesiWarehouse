<?php
$aFieldsNames   = Array( 'iOrder' => 0, 'sLanguage' => 1, 'iStatus' => 2, 'iTime' => 3, 'iCarrier' => 4, 'iPayment' => 5, 'sCarrierName' => 6, 'fCarrierPrice' => 7, 'sPaymentName' => 8, 'sPaymentPrice' => 9, 'sFirstName' => 10, 'sLastName' => 11, 'sCompanyName' => 12, 'sStreet' => 13, 'sZipCode' => 14, 'sCity' => 15, 'sPhone' => 16, 'sEmail' => 17, 'sIp' => 18 );

$aFieldsSort    = Array( 'iOrder', 'sLanguage', 'iStatus', 'iTime', 'iCarrier', 'iPayment', 'sCarrierName', 'fCarrierPrice', 'sPaymentName', 'sPaymentPrice', 'sFirstName', 'sLastName', 'sCompanyName', 'sStreet', 'sZipCode', 'sCity', 'sPhone', 'sEmail', 'sIp' );

function orders( $aExp ){
  return Array( 'iOrder' => $aExp[0], 'sLanguage' => $aExp[1], 'iStatus' => $aExp[2], 'iTime' => $aExp[3], 'iCarrier' => $aExp[4], 'iPayment' => $aExp[5], 'sCarrierName' => $aExp[6], 'fCarrierPrice' => $aExp[7], 'sPaymentName' => $aExp[8], 'sPaymentPrice' => $aExp[9], 'sFirstName' => $aExp[10], 'sLastName' => $aExp[11], 'sCompanyName' => $aExp[12], 'sStreet' => $aExp[13], 'sZipCode' => $aExp[14], 'sCity' => $aExp[15], 'sPhone' => $aExp[16], 'sEmail' => $aExp[17], 'sIp' => $aExp[18] );
}
?>