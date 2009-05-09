<?php
require_once DIR_CORE.'banners.php';
require_once DIR_CORE.'banners-admin.php';

if( $a == 'list' ){
  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $content .= $oTpl->tbHtml( 'banners.tpl', 'LIST_TITLE' );
  $sBannersList = listBanners( 'banners.tpl' );
  $content .= !empty( $sBannersList ) ? $sBannersList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sLink'] ) ){
    $iBanner = saveBanner( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iBanner='.$iBanner );
    exit;
  }
  else{
    if( isset( $iBanner ) && is_numeric( $iBanner ) )
      $aData  = throwBanner( $iBanner );

    if( isset( $sOption ) )
      $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

    if( isset( $aData ) && is_array( $aData ) ){
      $sFile  = $oTpl->tbHtml( 'banners.tpl', 'FILE' );
    }
    else{
      $aData['iType']   = null;
      $aData['iStatus'] = 1;
      $aData['iMax']    = 0;
      $aData['iViews']  = 0;
      $aData['iClicks'] = 0;
      $aData['sColor']  = '#ffffff';
      $sFile = $oTpl->tbHtml( 'banners.tpl', 'FORM_FILE' );
    }

    $sBannersTypes  = throwSelectFromArray( $aBannersTypes, $aData['iType'] );
    $sBannersStatus = throwYesNoBox( 'iStatus',  $aData['iStatus'] );

    $content .= $oTpl->tbHtml( 'banners.tpl', 'FORM' );
  }
}
elseif( $a == 'delete' && isset( $iBanner ) && is_numeric( $iBanner ) ){
  deleteBanner( $iBanner );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>