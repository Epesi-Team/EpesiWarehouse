<?php
$aFieldsNames   = Array( 'iProduct' => 0, 'iFeature' => 1, 'sValue' => 2 );

function pl_features_products( $aExp ){
  return Array( 'iProduct' => $aExp[0], 'iFeature' => $aExp[1], 'sValue' => $aExp[2] );
}
?>