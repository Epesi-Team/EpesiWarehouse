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
  else{
    $aData['sDescription'] = null;
    $iPayment = null;
  }

  $sCarriersList = $oOrder->listCarriersPaymentsAdmin( 'payments.tpl', $iPayment );
  $oTpl->unsetVariables( );
  $sFormTabs = $oTpl->tbHtml( 'payments.tpl', 'FORM_TABS' );

  if( !isset( $aData['iOuterSystem'] ) )
    $aData['iOuterSystem'] = null;

  $sOuterPaymentOption = throwSelectFromArray( $aOuterPaymentOption, $aData['iOuterSystem'] );
  $sDescription   = htmlEditor ( 'sDescription', '150', '100%', $aData['sDescription'], Array( 'aOptions' => Array( 'ToolbarStartExpanded' => false ), 'ToolbarSet' => 'Basic' ) ) ;

  $content .= $oTpl->tbHtml( 'payments.tpl', 'FORM' );
}
elseif( $a == 'delete' && isset( $iPayment ) && is_numeric( $iPayment ) ){
  $oOrder->deletePayment( $iPayment );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>