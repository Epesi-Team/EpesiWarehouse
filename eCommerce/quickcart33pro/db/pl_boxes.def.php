<?php
$aFieldsNames   = Array( 'iBox' => 0, 'sName' => 1, 'sContent' => 2 );

function pl_boxes( $aExp ){
  return Array( 'iBox' => $aExp[0], 'sName' => $aExp[1], 'sContent' => $aExp[2] );
}
?>