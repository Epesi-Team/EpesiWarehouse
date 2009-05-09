<?php
$aFieldsNames   = Array( 'iAnswer' => 0, 'iPoll' => 1, 'sAnswer' => 2 );

function en_poll_answers( $aExp ){
  return Array( 'iAnswer' => $aExp[0], 'iPoll' => $aExp[1], 'sAnswer' => $aExp[2] );
}
?>