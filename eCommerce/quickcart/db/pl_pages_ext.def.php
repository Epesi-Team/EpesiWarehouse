<?php
$aFieldsNames   = Array( 'iPage' => 0, 'sDescriptionFull' => 1, 'sMetaDescription' => 2, 'sMetaKeywords' => 3, 'sTemplate' => 4, 'sTheme' => 5, 'sUrl' => 6, 'sBanner' => 7 );

function pl_pages_ext( $aExp ){
  return Array( 'iPage' => $aExp[0], 'sDescriptionFull' => $aExp[1], 'sMetaDescription' => $aExp[2], 'sMetaKeywords' => $aExp[3], 'sTemplate' => $aExp[4], 'sTheme' => $aExp[5], 'sUrl' => $aExp[6], 'sBanner' => $aExp[7] );
}
?>