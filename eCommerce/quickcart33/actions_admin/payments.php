<?php
if( $a == 'list' ){
  if( isset( $sOption ) ){
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }
  
  $content .= $oTpl->tbHtml( 'payments.tpl', 'LIST_TITLE' );
  $sList = $oOrder->listPaymentsAdmin( 'payments.tpl' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sName'] ) ){
    $iPayment = $oOrder->savePayment( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iPayment='.$iPayment );
    exit;
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  if( isset( $iPayment ) && is_numeric( $iPayment ) ){
    $aData = $oOrder->throwPayment( $iPayment );
  }
  else
    $iPayment = null;

  $sCarriersList = $oOrder->listCarriersPaymentsAdmin( 'payments.tpl', $iPayment );
  $oTpl->unsetVariables( );
  $sFormTabs = $oTpl->tbHtml( 'payments.tpl', 'FORM_TABS' );
  $content .= $oTpl->tbHtml( 'payments.tpl', 'FORM' );
}
elseif( $a == 'delete' && isset( $iPayment ) && is_numeric( $iPayment ) ){
  $oOrder->deletePayment( $iPayment );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>