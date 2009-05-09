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
  require_once DIR_CORE.'productsRelated.php';
  require_once DIR_CORE.'features.php';
  require_once DIR_CORE.'features-admin.php';
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
    $iRecommended              = 0;
    $aData['iComments'] = 0;
  }

  $sStatusBox           = throwYesNoBox( 'iStatus', $aData['iStatus'] );
  $sTemplatesSelect     = throwTemplatesSelect( 'products_', $aData['sTemplate'] );
  $sThemesSelect        = throwThemesSelect( $aData['sTheme'] );
  $sPagesSelect         = $oPage->throwProductsPagesSelectAdmin( $aData['aCategories'] );
  $sSize1Select         = throwSelectFromArray( $config['pages_images_sizes'], 0 );
  $sSize2Select         = throwSelectFromArray( $config['pages_images_sizes'], 0 );
  $sPhotoTypesSelect    = throwSelectFromArray( $aPhotoTypes, 1 );
  $sFeatures            = listProductFeaturesAdmin( 'features.tpl', $iProduct );
  $sCommentsBox         = throwYesNoBox( 'iComments', $aData['iComments'] );
  $sDisplayAllFiles     = var_export( $config['display_all_files'], true );
  $sProductRecommendedBox  = throwYesNoBox( 'iRecommended', isset( $aProductsRecommended[$iProduct] ) ? 1 : 0 );
  $sFilesDir            = ( $config['display_all_files'] === true ) ? $oFile->listFilesInDir( 'files.tpl', $iProduct, 2 ) : null;
  $sProductsRelatedSelect = listProductsRelatedSelect( 'productsRelated_select.tpl', $iProduct );
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
elseif( $a == 'compare' ){
  $aUrl = parse_url( 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
  if( !empty( $aUrl['path'] ) && dirname( $aUrl['path'] ) != '/' )
    $aUrl['path'] = dirname( $aUrl['path'] ).'/';
  if( LANGUAGE == 'pl' )
    $sCompareLinksPl = $oTpl->tbHtml( 'products.tpl', 'COMPARE_LINKS_PL' );
  $content .= $oTpl->tbHtml( 'products.tpl', 'COMPARE_LINKS' );
}
?>