<?php
require_once DIR_CORE.'newsletter.php';

if( $a == 'add' ){
  if( !empty( $_POST['sEmail'] ) && checkEmail( $_POST['sEmail'] ) == true ){
    saveNewsletter( $_POST['sEmail'] );
    $content .= $oTpl->tbHtml( 'messages.tpl', 'NEWSLETTER_ADDED' );
  }
  else{
    $content .= $oTpl->tbHtml( 'messages.tpl', 'REQUIRED_FIELDS' );
  }
  $bDisplayedPage = true;
}
?>