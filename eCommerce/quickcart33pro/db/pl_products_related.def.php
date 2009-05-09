<?php
$aFieldsNames   = Array( 'iProduct' => 0, 'iRelated' => 1 );

function pl_products_related( $aExp ){
  return Array( 'iProduct' => $aExp[0], 'iRelated' => $aExp[1] );
}
?>