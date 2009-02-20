<?php
$aFieldsNames   = Array( 'iProduct' => 0, 'iPage' => 1 );

function en_products_pages( $aExp ){
  return Array( 'iProduct' => $aExp[0], 'iPage' => $aExp[1] );
}
?>