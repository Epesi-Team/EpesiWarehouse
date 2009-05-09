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
    $sFriendlyLinksSelect = throwTrueNullSelect( $config['friendly_links'] );
    $sSiteMapPageSelect = $oPage->throwPagesSelectAdmin( $config['site_map'] );
    $sSiteMapProductsSelect = throwTrueNullSelect( $config['site_map_products'] );
    $sCrossSellSelect = throwTrueNullSelect( $config['cross_sell'] );
    $sNewsletterSelect = throwTrueFalseSelect( $config['newsletter'] );
    $sContactPageSelect = $oPage->throwPagesSelectAdmin( $config['contact_page'] );
    $sProductsDescSelect= throwTrueFalseSelect( $config['search_products_description'] );
    if( LANGUAGE == 'pl' )
      $sSkapiecId = $oTpl->tbHtml( 'settings.tpl', 'SKAPIEC_ID' );
    $sDisplayAllFilesSelect   = throwTrueFalseSelect( $config['display_all_files'] );
    $sChangeFilesNamesSelect  = throwTrueFalseSelect( $config['change_files_names'] );
    $sInheritFromParentsSelect= throwTrueFalseSelect( $config['inherit_from_parents'] );
    $sExpandedMenuSelect      = throwTrueFalseSelect( $config['display_expanded_menu'] );
    $sSubcategoryProductsSelect = throwTrueFalseSelect( $config['display_subcategory_products'] );
    $sTextSizeSelect          = throwTrueFalseSelect( $config['text_size'] );
    $sLanguageInUrl           = throwTrueFalseSelect( $config['language_in_url'] );


    $sFormTabs   = $oTpl->tbHtml( 'settings.tpl', 'CONFIG_TABS' );
    $content    .= $oTpl->tbHtml( 'settings.tpl', 'CONFIG_MAIN' );
  }
}
?>