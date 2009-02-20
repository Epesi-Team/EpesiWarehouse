<?php
if( $a == 'config' ){
  if( isset( $_POST['sOption'] ) ){
    saveVariables( $_POST, DB_CONFIG );
    saveVariables( $_POST, DB_CONFIG_LANG );
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save' );
    exit;
  }
  else{
    if( isset( $sOption ) ){
      $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
    }

    $sCssSelect         = throwCssSelect( $config['template'] );
    $sLangSelect        = throwLangSelect( $config['default_lang'] );
    $sStartPageSelect   = $oPage->throwPagesSelectAdmin( $config['start_page'] );
    $sBasketPageSelect  = $oPage->throwPagesSelectAdmin( $config['basket_page'] );
    $sOrderPageSelect   = $oPage->throwPagesSelectAdmin( $config['order_page'] );
    $sRulesPageSelect   = $oPage->throwPagesSelectAdmin( $config['rules_page'] );
    $sPageSearchSelect  = $oPage->throwPagesSelectAdmin( $config['page_search'] );
    $sHiddenShowsSelect = throwTrueFalseSelect( $config['hidden_shows'] );
    $sWysiwygSelect     = throwTrueFalseSelect( $config['wysiwyg'] );
    $sProductsDescSelect= throwTrueFalseSelect( $config['search_products_description'] );
    $sDisplayAllFilesSelect   = throwTrueFalseSelect( $config['display_all_files'] );
    $sChangeFilesNamesSelect  = throwTrueFalseSelect( $config['change_files_names'] );
    $sTextSizeSelect          = throwTrueFalseSelect( $config['text_size'] );
    $sExpandMenuSelect        = throwTrueFalseSelect( $config['expand_menu'] );

    $sFormTabs   = $oTpl->tbHtml( 'settings.tpl', 'CONFIG_TABS' );
    $content    .= $oTpl->tbHtml( 'settings.tpl', 'CONFIG_MAIN' );
  }
}
?>