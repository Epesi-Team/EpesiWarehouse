<?php
$aFieldsNames = Array( 'iOrder' => 0, 'iStatus' => 1, 'iTime' => 2 );
$aFieldsSort  = Array( 'iTime', 'iStatus', 'iOrder' );

function orders_status( $aExp ){
  return Array( 'iOrder' => $aExp[0], 'iStatus' => $aExp[1], 'iTime' => $aExp[2] );
}
?>