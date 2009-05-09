<?php
$aFieldsNames   = Array( 'iPoll' => 0, 'sQuestions' => 1, 'sCookieName' => 2, 'iStatus' => 3 );

function pl_poll_questions( $aExp ){
  return Array( 'iPoll' => $aExp[0], 'sQuestions' => $aExp[1], 'sCookieName' => $aExp[2], 'iStatus' => $aExp[3] );
}
?>