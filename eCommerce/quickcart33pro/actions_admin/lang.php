<?php
if( $a == 'list' ){
  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $content .= $oTpl->tbHtml( 'lang.tpl', 'LIST_TITLE' );
  $sList    = listLanguages( 'lang.tpl' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'form' ){
  if( isset( $_POST['sOption'] ) ){
    addLanguage( $_POST['language'], $_POST['language_from'] );
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=save' );
    exit;
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $sLangSelect = throwLangSelect( $config['default_lang'] );

  $content .= $oTpl->tbHtml( 'lang.tpl', 'FORM' );
}
elseif( $a == 'delete' && isset( $sLanguage ) && !empty( $sLanguage ) ){
  deleteLanguage( $sLanguage );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
elseif( $a == 'translations' && isset( $sLanguage ) && !empty( $sLanguage ) ){
  if( isset( $_POST['sOption'] ) ){
    saveVariables( $_POST, DIR_LANG.$sLanguage.'.php', 'lang' );
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&sLanguage='.$sLanguage );
    exit;
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $content .= $oTpl->tbHtml( 'lang.tpl', 'LIST_TITLE' );
  $content .= listLangVariables( 'lang.tpl', $sLanguage );
}
?>