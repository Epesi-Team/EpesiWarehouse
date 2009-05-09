<?php
if( $aActions['a'] == 'return' ){
  if( isset( $_POST['status'] ) )
    $status = $_POST['status'];
  if( isset( $status ) && $status == 'OK' )
    $content .= $oTpl->tbHtml( 'payment.tpl', 'ACCEPT' );
  elseif( isset( $status ) && $status == 'FAIL' )
    $content .= $oTpl->tbHtml( 'payment.tpl', 'DENIED' );
  else
    $content .= $oTpl->tbHtml( 'payment.tpl', 'ERROR' );
}
elseif( $aActions['a'] == 'paypal' ){
  if( isset( $_POST['payment_status'] ) ){
    if( $_POST['payment_status'] == 'Completed' || $_POST['payment_status'] == 'Pending' )
      $content .= $oTpl->tbHtml( 'payment.tpl', 'ACCEPT' );
    else
      $content .= $oTpl->tbHtml( 'payment.tpl', 'DENIED' );
  }
  else
    $content .= $oTpl->tbHtml( 'payment.tpl', 'RETURN_INFO' );
}
?>