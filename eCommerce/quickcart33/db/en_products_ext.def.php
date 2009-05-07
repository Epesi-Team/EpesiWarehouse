<?php
$aFieldsNames   = Array( 'iProduct' => 0, 'sNameTitle' => 1, 'sTemplate' => 2, 'sTheme' => 3, 'sMetaKeywords' => 4, 'sMetaDescription' => 5, 'sDescriptionFull' => 6 );

function en_products_ext( $aExp ){
  return Array( 'iProduct' => $aExp[0], 'sNameTitle' => $aExp[1], 'sTemplate' => $aExp[2], 'sTheme' => $aExp[3], 'sMetaKeywords' => $aExp[4], 'sMetaDescription' => $aExp[5], 'sDescriptionFull' => $aExp[6] );
}
?>