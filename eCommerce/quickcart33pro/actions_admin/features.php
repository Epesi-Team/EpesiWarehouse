<?php
require_once DIR_CORE.'features.php';
require_once DIR_CORE.'features-admin.php';

if( $a == 'list' ){
  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $content .= $oTpl->tbHtml( 'features.tpl', 'LIST_TITLE' );
  $sList = listFeatures( );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sName'] ) ){
    $iFeature = saveFeature( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iFeature='.$iFeature );
    exit;
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  if( isset( $iFeature ) && is_numeric( $iFeature ) ){
    $aData = throwFeature( $iFeature );
  }
  else{
    $aData['iPosition'] = 0;
  }

  $content .= $oTpl->tbHtml( 'features.tpl', 'FORM' );
}
elseif( $a == 'delete' && isset( $iFeature ) && is_numeric( $iFeature ) ){
  deleteFeature( $iFeature );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>