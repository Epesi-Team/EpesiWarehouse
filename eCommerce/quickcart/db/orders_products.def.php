<?php
$aFieldsNames = Array( 'iElement' => 0, 'iOrder' => 1, 'iProduct' => 2, 'iQuantity' => 3, 'fPrice' => 4, 'sName' => 5 );

function orders_products( $aExp ){
  return Array( 'iElement' => $aExp[0], 'iOrder' => $aExp[1], 'iProduct' => $aExp[2], 'iQuantity' => $aExp[3], 'fPrice' => $aExp[4], 'sName' => $aExp[5] );
}
?>