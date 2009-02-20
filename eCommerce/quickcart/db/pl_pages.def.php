<?php
$aFieldsNames   = Array( 'iPage' => 0, 'iPageParent' => 1, 'sName' => 2, 'sNameTitle' => 3, 'sDescriptionShort' => 4, 'iStatus' => 5, 'iPosition' => 6, 'iType' => 7, 'iSubpagesShow' => 8, 'iProducts' => 9 );
$aFieldsSort    = Array( 'iPosition', 'sName', 'sNameTitle', 'iPage', 'iPageParent', 'sDescriptionShort', 'iStatus', 'iType', 'iSubpagesShow', 'iProducts' );

function pl_pages( $aExp ){
  return Array( 'iPage' => $aExp[0], 'iPageParent' => $aExp[1], 'sName' => $aExp[2], 'sNameTitle' => $aExp[3], 'sDescriptionShort' => $aExp[4], 'iStatus' => $aExp[5], 'iPosition' => $aExp[6], 'iType' => $aExp[7], 'iSubpagesShow' => $aExp[8], 'iProducts' => $aExp[9] );
}
?>