<?php
require_once DIR_CORE.'newsletter.php';
require_once DIR_CORE.'newsletter-admin.php';

if( $a == 'form' ){
  $sNewsletterList = throwNewsletterEmails( );
  $content .= $oTpl->tbHtml( 'newsletter.tpl', 'NEWSLETTER_FORM' );
}
elseif( $a == 'list' ){
  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $content .= $oTpl->tbHtml( 'newsletter.tpl', 'NEWSLETTER_TITLE' );
  $sEmailsList = listNewsletterEmails( 'newsletter.tpl' );
  $content .= !empty( $sEmailsList ) ? $sEmailsList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'delete' && isset( $sEmail ) && !empty( $sEmail ) ){
  deleteNewsletter( $sEmail );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>