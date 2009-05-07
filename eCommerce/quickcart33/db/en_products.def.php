<?php
$aFieldsNames   = Array( 'iProduct' => 0, 'sName' => 1, 'fPrice' => 2, 'iStatus' => 3, 'iPosition' => 4,  'sAvailable' => 5, 'sDescriptionShort' => 6 );
$aFieldsSort    = Array( 'iPosition', 'sName', 'iProduct', 'sAvailable', 'fPrice', 'sDescriptionShort', 'iStatus' );

function en_products( $aExp ){
  return Array( 'iProduct' => $aExp[0], 'sName' => $aExp[1], 'fPrice' => $aExp[2], 'iStatus' => $aExp[3], 'iPosition' => $aExp[4], 'sAvailable' => $aExp[5], 'sDescriptionShort' => $aExp[6] );
}
?>