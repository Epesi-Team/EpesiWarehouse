<?php
require_once DIR_CORE.'poll.php';
require_once DIR_CORE.'poll-admin.php';

if( $a == 'list' ){
  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $content .= $oTpl->tbHtml( 'poll.tpl', 'LIST_TITLE' );
  $sPollsList = listPolls( );
  $content .= !empty( $sPollsList ) ? $sPollsList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sQuestions'] ) ){
    $iPoll = savePoll( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iPoll='.$iPoll );
    exit;
  }
  elseif( isset( $sOption ) && $sOption == 'reset' ){
    resetPollAnswers( $iPoll );
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iPoll='.$iPoll );
    exit;
  }
  else{
    if( isset( $sOption ) )
      $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

    if( !isset( $iPoll ) || !is_numeric( $iPoll ) )
      $iPoll = null;
    $content .= throwPoll( 'poll.tpl', $iPoll, 'admin' );
  }
}
elseif( $a == 'delete' && isset( $iPoll ) && is_numeric( $iPoll ) ){
  delPoll( $iPoll );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p=poll-list&sOption=del' );
  exit;
}
?>