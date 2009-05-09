<?php
$aFieldsNames   = Array( 'iBanner' => 0, 'iViews' => 1, 'iClicks' => 2 );

function banners_stats( $aExp ){
  return Array( 'iBanner' => $aExp[0], 'iViews' => $aExp[1], 'iClicks' => $aExp[2] );
}
?>