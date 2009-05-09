<?php
$aFieldsNames   = Array( 'iBanner' => 0, 'sFile' => 1, 'sLink' => 2, 'iType' => 3, 'iWidth' => 4, 'iHeight' => 5, 'sColor' => 6, 'iMax' => 7, 'iStatus' => 8 );

function banners( $aExp ){
  return Array( 'iBanner' => $aExp[0], 'sFile' => $aExp[1], 'sLink' => $aExp[2], 'iType' => $aExp[3], 'iWidth' => $aExp[4], 'iHeight' => $aExp[5], 'sColor' => $aExp[6], 'iMax' => $aExp[7], 'iStatus' => $aExp[8] );
}
?>