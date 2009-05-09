<?php
$aFieldsNames = Array( 'iPayment' => 0, 'sName' => 1, 'iOuterSystem' => 2, 'sDescription' => 3 );

function en_payments( $aExp ){
  return Array( 'iPayment' => $aExp[0], 'sName' => $aExp[1], 'iOuterSystem' => $aExp[2], 'sDescription' => $aExp[3] );
}
?>