<?php
$aFieldsNames = Array( 'iCustomer' => 0, 'iProduct' => 1, 'iQuantity' => 2, 'fPrice' => 3, 'sName' => 4 );

function orders_temp( $aExp ){
  return Array( 'iCustomer' => $aExp[0], 'iProduct' => $aExp[1], 'iQuantity' => $aExp[2], 'fPrice' => $aExp[3], 'sName' => $aExp[4] );
}
?>