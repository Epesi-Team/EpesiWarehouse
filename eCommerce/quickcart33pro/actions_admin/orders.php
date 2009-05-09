<?php
if( $a == 'list' ){
  if( isset( $_POST['sOption'] ) ){
    $oOrder->saveOrders( $_POST );
    $sOption = 'save';
  }

  if( isset( $sOption ) ){
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }

  if( !isset( $iStatus ) )
    $iStatus = null;
  if( !isset( $iProducts ) )
    $iProducts = null;
  $sStatusSelect = throwSelectFromArray( $oOrder->throwStatus( ), $iStatus );
  $sProductsBox = throwYesNoBox( 'iProducts', $iProducts );

  $content .= $oTpl->tbHtml( 'orders.tpl', 'LIST_TITLE' );
  $sList = $oOrder->listOrdersAdmin( 'orders.tpl' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' && isset( $iOrder ) && is_numeric( $iOrder ) ){
  $aData = $oOrder->throwOrder( $iOrder );

  if( isset( $aData ) && is_array( $aData ) ){
    if( isset( $_POST['sFirstName'] ) ){
      $oOrder->saveOrder( $_POST );
      if( isset( $_POST['sOptionList'] ) )
        header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
      else
        header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iOrder='.$iOrder );
      exit;
    }

    if( isset( $sOption ) )
      $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

    if( isset( $aData['sComment'] ) ){
      $aData['sComment'] = changeTxt( $aData['sComment'], 'nlNds' );
    }

    $sStatusSelect = throwSelectFromArray( $oOrder->throwStatus( ), $aData['iStatus'] );
    $sPaymentRealizedBox = throwYesNoBox( 'iPaymentRealized', $aData['iPaymentRealized'] );
    $sStatusList = $oOrder->listOrderStatuses( 'orders.tpl', $iOrder );
    require 'config/lang_'.$aData['sLanguage'].'.php';
    $sProductsList = $oOrder->listProducts( 'orders.tpl', $iOrder, 'PRODUCTS_' );
    $fOrderSummary = $oOrder->aOrders[$iOrder]['fOrderSummary'];
    $sOrderSummary = $oOrder->aOrders[$iOrder]['sOrderSummary'];
    $sInvoice      = throwYesNoBox( 'iInvoice', $aData['iInvoice'] );

    $oTpl->unsetVariables( );
    $sFormTabs = $oTpl->tbHtml( 'orders.tpl', 'FORM_TABS' );
    $content .= $oTpl->tbHtml( 'orders.tpl', 'FORM_MAIN' );
    require DB_CONFIG_LANG;
  }
  else{
    $content .= $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
  }
}
elseif( $a == 'delete' && isset( $iOrder ) && is_numeric( $iOrder ) ){
  $oOrder->deleteOrder( $iOrder );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>