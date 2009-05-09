<?php
$aFieldsNames   = Array( 'iProduct' => 0, 'iTime' => 1 );

function pl_products_stats( $aExp ){
  return Array( 'iProduct' => $aExp[0], 'iTime' => $aExp[1] );
}
?>