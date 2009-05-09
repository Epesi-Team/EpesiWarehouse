<?php
$aFieldsNames   = Array( 'iFeature' => 0, 'sName' => 1, 'iPosition' => 2 );
$aFieldsSort    = Array( 'iPosition', 'sName', 'iFeature' );

function en_features( $aExp ){
  return Array( 'iFeature' => $aExp[0], 'sName' => $aExp[1], 'iPosition' => $aExp[2] );
}
?>