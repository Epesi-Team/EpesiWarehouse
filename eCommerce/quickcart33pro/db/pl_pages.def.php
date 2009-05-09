<?php
$aFieldsNames   = Array( 'iPage' => 0, 'iPageParent' => 1, 'sName' => 2, 'sNameTitle' => 3, 'sDescriptionShort' => 4, 'iStatus' => 5, 'iPosition' => 6, 'iType' => 7, 'iSubpagesShow' => 8, 'iProducts' => 9, 'sTags' => 10, 'iTime' => 11, 'iRss' => 12, 'iAdmin' => 13, 'iAuthorized' => 14, 'iCategoryNokaut' => 15 );
$aFieldsSort    = Array( 'iPosition', 'sName', 'sNameTitle', 'iPage', 'iPageParent', 'sDescriptionShort', 'iStatus', 'iType', 'iSubpagesShow', 'iProducts', 'sTags', 'iTime', 'iRss', 'iAdmin', 'iAuthorized', 'iCategoryNokaut' );

function pl_pages( $aExp ){
  return Array( 'iPage' => $aExp[0], 'iPageParent' => $aExp[1], 'sName' => $aExp[2], 'sNameTitle' => $aExp[3], 'sDescriptionShort' => $aExp[4], 'iStatus' => $aExp[5], 'iPosition' => $aExp[6], 'iType' => $aExp[7], 'iSubpagesShow' => $aExp[8], 'iProducts' => $aExp[9], 'sTags' => $aExp[10], 'iTime' => $aExp[11], 'iRss' => $aExp[12], 'iAdmin' => $aExp[13], 'iAuthorized' => $aExp[14], 'iCategoryNokaut' => $aExp[15] );
}
?>