<?php
$aFieldsNames = Array( 'iComment' => 0, 'iLink' => 1, 'sName' => 2, 'sContent' => 3, 'iTime' => 4, 'sIp' => 5, 'iStatus' => 6 );
$aFieldsSort  = Array( 'iComment', 'iLink', 'sName', 'sContent', 'iTime', 'sIp', 'iStatus' );

function en_products_comments( $aExp ){
  return Array( 'iComment' => $aExp[0], 'iLink' => $aExp[1], 'sName' => $aExp[2], 'sContent' => $aExp[3], 'iTime' => $aExp[4], 'sIp' => $aExp[5], 'iStatus' => $aExp[6] );
}
?>