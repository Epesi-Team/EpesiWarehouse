<?php
$aFieldsNames = Array( 'iCarrier' => 0, 'sName' => 1, 'fPrice' => 2, 'sWeightRange' => 3 );

function en_carriers( $aExp ){
  return Array( 'iCarrier' => $aExp[0], 'sName' => $aExp[1], 'fPrice' => $aExp[2], 'sWeightRange' => $aExp[3] );
}
?>