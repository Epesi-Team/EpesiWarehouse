<?php
require_once DIR_CORE.'boxes.php';
require_once DIR_CORE.'boxes-admin.php';

if( $a == 'list' ){
  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $content .= $oTpl->tbHtml( 'boxes.tpl', 'LIST_TITLE' );
  $sBoxesList = listBoxes( );
  $content .= !empty( $sBoxesList ) ? $sBoxesList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sName'] ) ){
    $iBox = saveBox( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iBox='.$iBox );
    exit;
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  if( isset( $iBox ) && is_numeric( $iBox ) ){
    $aData = throwBox( $iBox );
  }
  else{
    $aData['sContent'] = null;
  }

  $sContent   = htmlEditor ( 'sContent', '280', '100%', $aData['sContent'], Array( 'aOptions' => Array( 'ToolbarStartExpanded' => false ), 'ToolbarSet' => 'Basic' ) ) ;

  $content .= $oTpl->tbHtml( 'boxes.tpl', 'FORM' );
}
elseif( $a == 'delete' && isset( $iBox ) && is_numeric( $iBox ) ){
  deleteBox( $iBox );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>