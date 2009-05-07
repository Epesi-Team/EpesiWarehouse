<?php
if( $a == 'list' ){
  if( isset( $sOption ) ){
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }
  
  $content .= $oTpl->tbHtml( 'carriers.tpl', 'LIST_TITLE' );
  $sList = $oOrder->listCarriersAdmin( 'carriers.tpl' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sName'] ) ){
    $iCarrier = $oOrder->saveCarrier( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iCarrier='.$iCarrier );
    exit;
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  if( isset( $iCarrier ) && is_numeric( $iCarrier ) ){
    $aData = $oOrder->throwCarrier( $iCarrier );
  }

  if( !isset( $aData ) ){
    $iCarrier = null;
  }

  $sPaymentList = $oOrder->listPaymentsCarriersAdmin( 'carriers.tpl', $iCarrier );
  $oTpl->unsetVariables( );
  $sFormTabs = $oTpl->tbHtml( 'carriers.tpl', 'FORM_TABS' );
  $content .= $oTpl->tbHtml( 'carriers.tpl', 'FORM_MAIN' );
}
elseif( $a == 'delete' && isset( $iCarrier ) && is_numeric( $iCarrier ) ){
  $oOrder->deleteCarrier( $iCarrier );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>