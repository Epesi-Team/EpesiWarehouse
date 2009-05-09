<?php
$aFieldsNames = Array( 'iCategory' => 0, 'iCategoryParent' => 1, 'sName' => 2 );

function pl_categories_nokaut_names( $aExp ){
  return Array( 'iCategory' => $aExp[0], 'iCategoryParent' => $aExp[1], 'sName' => $aExp[2] );
}
?>