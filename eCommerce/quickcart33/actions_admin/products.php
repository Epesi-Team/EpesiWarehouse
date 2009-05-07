<?php
if( $a == 'list' ){
  if( isset( $_POST['sOption'] ) ){
    $oProduct->saveProducts( $_POST );
    $sOption = 'save';
  }

  if( isset( $sOption ) ){
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }
  
  $content .= $oTpl->tbHtml( 'products.tpl', 'LIST_TITLE' );
  $sList = $oProduct->listProductsAdmin( 'products.tpl' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sName'] ) ){
    $iProduct = $oProduct->saveProduct( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iProduct='.$iProduct );
    exit;
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  if( isset( $iProduct ) && is_numeric( $iProduct ) ){
    $aData = $oProduct->throwProduct( $iProduct );
  }

  if( isset( $aData ) && is_array( $aData ) ){
    $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
    $aData['sDescriptionFull']  = changeTxt( $aData['sDescriptionFull'], 'nlNds' );
    $sFilesList                 = $oFile->listAllFilesAdmin( 'files.tpl', $iProduct, 2 );
  }
  else{
    $aData['iStatus']       = 1;
    $aData['sTemplate']     = null;
    $aData['sTheme']        = null;
    $aData['iPageParent']   = null;
    $aData['iPosition']     = 0;
    $aData['aCategories']   = null;
    $iProduct               = null;
    $aData['sDescriptionFull'] = null;
    $aData['sDescriptionShort']= null;
  }

  $sStatusBox           = throwYesNoBox( 'iStatus', $aData['iStatus'] );
  $sTemplatesSelect     = throwTemplatesSelect( 'products_', $aData['sTemplate'] );
  $sThemesSelect        = throwThemesSelect( $aData['sTheme'] );
  $sPagesSelect         = $oPage->throwProductsPagesSelectAdmin( $aData['aCategories'] );
  $sSize1Select         = throwSelectFromArray( $config['pages_images_sizes'], 0 );
  $sSize2Select         = throwSelectFromArray( $config['pages_images_sizes'], 0 );
  $sPhotoTypesSelect    = throwSelectFromArray( $aPhotoTypes, 1 );
  $sDisplayAllFiles     = var_export( $config['display_all_files'], true );
  $sFilesDir            = ( $config['display_all_files'] === true ) ? $oFile->listFilesInDir( 'files.tpl', $iProduct, 2 ) : null;
  $oTpl->unsetVariables( );

  $sDescriptionShort  = htmlEditor ( 'sDescriptionShort', '120', '100%', $aData['sDescriptionShort'], Array( 'aOptions' => Array( 'ToolbarStartExpanded' => false ), 'ToolbarSet' => 'Basic' ) ) ;
  $sDescriptionFull   = htmlEditor ( 'sDescriptionFull', '280', '100%', $aData['sDescriptionFull'], Array( 'ToolbarSet' => 'DescriptionFull' ) ) ;
  
  $sFilesForm  = $oTpl->tbHtml( 'files.tpl', 'FILES_FORM' ); 
  $sFormTabs   = $oTpl->tbHtml( 'products.tpl', 'FORM_TABS' );
  $content    .= $oTpl->tbHtml( 'products.tpl', 'FORM_MAIN' );
}
elseif( $a == 'delete' && isset( $iProduct ) && is_numeric( $iProduct ) ){
  $oProduct->deleteProduct( $iProduct );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>