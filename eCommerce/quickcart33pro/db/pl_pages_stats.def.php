<?php
$aFieldsNames   = Array( 'iPage' => 0, 'iTime' => 1 );

function pl_pages_stats( $aExp ){
  return Array( 'iPage' => $aExp[0], 'iTime' => $aExp[1] );
}
?>