<?php
$aFieldsNames   = Array( 'iFile' => 0, 'iPage' => 1, 'sFileName' => 2, 'sDescription' => 3, 'iPhoto' => 4, 'iPosition' => 5, 'iType' => 6, 'iSize1' => 7, 'iSize2' => 8 );
$aFieldsSort    = Array( 'iPosition', 'sFileName', 'iPage', 'sDescription', 'iFile', 'iPhoto', 'iType', 'iSize1', 'iSize2' );

function en_pages_files( $aExp ){
  return Array( 'iFile' => $aExp[0], 'iPage' => $aExp[1], 'sFileName' => $aExp[2], 'sDescription' => $aExp[3], 'iPhoto' => $aExp[4], 'iPosition' => $aExp[5], 'iType' => $aExp[6], 'iSize1' => $aExp[7], 'iSize2' => $aExp[8] );
}
?>