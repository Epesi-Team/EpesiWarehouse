<?php
$aFieldsNames = Array( 'iOrder' => 0, 'sComment' => 1 );

function orders_comments( $aExp ){
  return Array( 'iOrder' => $aExp[0], 'sComment' => $aExp[1] );
}
?>