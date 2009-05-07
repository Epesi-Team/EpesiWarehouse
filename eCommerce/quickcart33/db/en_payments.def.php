<?php
$aFieldsNames = Array( 'iPayment' => 0, 'sName' => 1 );

function en_payments( $aExp ){
  return Array( 'iPayment' => $aExp[0], 'sName' => $aExp[1] );
}
?>