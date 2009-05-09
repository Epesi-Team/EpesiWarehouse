<?php
$aFieldsNames   = Array( 'iAnswer' => 0, 'iVotes' => 1 );

function en_poll_votes( $aExp ){
  return Array( 'iAnswer' => $aExp[0], 'iVotes' => $aExp[1] );
}
?>