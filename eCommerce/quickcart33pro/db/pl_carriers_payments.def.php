<?php
$aFieldsNames = Array( 'iCarrier' => 0, 'iPayment' => 1, 'sPrice' => 2 );

function pl_carriers_payments( $aExp ){
  return Array( 'iCarrier' => $aExp[0], 'iPayment' => $aExp[1], 'sPrice' => $aExp[2] );
}
?>