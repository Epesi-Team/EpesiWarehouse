<?php
$aFieldsNames   = Array( 'sWords' => 0, 'iTime' => 1 );

function en_searched_words( $aExp ){
  return Array( 'sWords' => $aExp[0], 'iTime' => $aExp[1] );
}
?>