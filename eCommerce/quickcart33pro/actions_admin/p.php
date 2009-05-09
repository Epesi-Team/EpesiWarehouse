<?php
if( $a == 'list' ){
  if( isset( $_POST['sOption'] ) ){
    $oPage->savePages( $_POST );
    $sOption = 'save';
  }

  if( isset( $sOption ) ){
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }

  $content .= $oTpl->tbHtml( 'pages.tpl', 'LIST_TITLE' );
  $sList = $oPage->listPagesAdmin( 'pages.tpl' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sName'] ) ){
    $iPage = $oPage->savePage( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&iPage='.$iPage );
    exit;
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  if( isset( $iPage ) && is_numeric( $iPage ) ){
    $aData = $oPage->throwPage( $iPage );
  }

  if( isset( $aData ) && is_array( $aData ) ){
    $aData['sDate']             = empty( $aData['iTime'] ) ? date( 'Y-m-d H:i' ) : date( 'Y-m-d H:i', $aData['iTime'] );
    $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
    $aData['sDescriptionFull']  = changeTxt( $aData['sDescriptionFull'], 'nlNds' );
    $sFilesList                 = $oFile->listAllFilesAdmin( 'files.tpl', $iPage, 1 );
  }
  else{
    $aData['sDate']         = date( 'Y-m-d H:i' );
    $aData['iStatus']       = 1;
    $aData['iType']         = 1;
    $aData['iProducts']     = 0;
    $aData['sTemplate']     = null;
    $aData['sTheme']        = null;
    $aData['iSubpagesShow'] = 1;
    $aData['iPageParent']   = null;
    $aData['iPosition']     = 0;
    $iPage                  = null;
    $aData['sDescriptionFull'] = null;
    $aData['sDescriptionShort']= null;
    $aData['iCategoryNokaut'] = null;
    $aData['iComments'] = 0;
    $aData['iRss'] = 0;
  }

  $sStatusBox           = throwYesNoBox( 'iStatus', $aData['iStatus'] );
  $sProductsBox         = throwYesNoBox( 'iProducts', $aData['iProducts'] );
  $sTypesSelect         = throwSelectFromArray( $aMenuTypes, $aData['iType'] );
  $sTemplatesSelect     = throwTemplatesSelect( 'pages_', $aData['sTemplate'] );
  $sThemesSelect        = throwThemesSelect( $aData['sTheme'] );
  $sSubpagesShowSelect  = throwSubpagesShowSelect( $aData['iSubpagesShow'] );
  $sRssBox              = throwYesNoBox( 'iRss', $aData['iRss'] );
  $sPagesSelect         = $oPage->throwPagesSelectAdmin( $aData['iPageParent'] );
  $sSize1Select         = throwSelectFromArray( $config['pages_images_sizes'], 0 );
  $sSize2Select         = throwSelectFromArray( $config['pages_images_sizes'], 0 );
  $sPhotoTypesSelect    = throwSelectFromArray( $aPhotoTypes, 1 );
  $sCommentsBox         = throwYesNoBox( 'iComments', $aData['iComments'] );
  $sDisplayAllFiles     = var_export( $config['display_all_files'], true );
  $sFilesDir            = ( $config['display_all_files'] === true ) ? $oFile->listFilesInDir( 'files.tpl', $iPage, 1 ) : null;

  if( LANGUAGE == 'pl' ){
    $sCategoriesNokaut = $oFF->throwFileSelect( DB_CATEGORIES_NOKAUT_NAMES, $aData['iCategoryNokaut'], 'iCategory', 'iCategory', 'sName' );
    $sNokautForm = $oTpl->tbHtml( 'pages.tpl', 'NOKAUT_CATEGORY' );
  }
  $oTpl->unsetVariables( );

  if( !empty( $aData['sBanner'] ) )
    $sBannerForm = $oTpl->tbHtml( 'pages.tpl', 'FORM_BANNER' );

  $sDescriptionShort  = htmlEditor ( 'sDescriptionShort', '120', '100%', $aData['sDescriptionShort'], Array( 'aOptions' => Array( 'ToolbarStartExpanded' => false ), 'ToolbarSet' => 'Basic' ) ) ;
  $sDescriptionFull   = htmlEditor ( 'sDescriptionFull', '280', '100%', $aData['sDescriptionFull'], Array( 'ToolbarSet' => 'DescriptionFull' ) ) ;

  $sFilesForm  = $oTpl->tbHtml( 'files.tpl', 'FILES_FORM' );
  $sFormTabs   = $oTpl->tbHtml( 'pages.tpl', 'FORM_TABS' );
  $content    .= $oTpl->tbHtml( 'pages.tpl', 'FORM_MAIN' );
}
elseif( $a == 'delete' && isset( $iPage ) && is_numeric( $iPage ) ){
  $oPage->deletePage( $iPage );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>