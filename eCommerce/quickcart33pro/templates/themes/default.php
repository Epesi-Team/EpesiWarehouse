<?php
if( empty( $content ) )
  $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );

/* POLL START */
/* $iPoll = 0; // load last added poll */
/* $iPoll = 1; // load poll with id 1 */
$iPoll = 0;

if( isset( $_POST['iPollSelected'] ) && is_numeric( $_POST['iPollSelected'] ) && isset( $_POST['iPoll'] ) && is_numeric( $_POST['iPoll'] ) ){
  dbSavePollAnswer( $_POST['iPollSelected'], $_POST['iPoll'] );
  header( "Location: ".$_SERVER['HTTP_REFERER'] );
  exit;
}
$sPoll = throwPoll( 'poll.tpl', $iPoll );
/* POLL END */

$aBoxes = throwBoxes( );

if( isset( $config['newsletter'] ) && $config['newsletter'] === true ){
  $sNewsletterLink = ( defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true ) ? 'add,newsletter.html' : '?,add,newsletter';
  $sNewsletterForm = $oTpl->tbHtml( 'container.tpl', 'NEWSLETTER_FORM' );
}
else{
  $sNewsletterForm = null;
}

if( !isset( $bBlockPage ) ){
  $sMenu1 = $oPage->throwMenu( 'menu_1.tpl', 1, $iContent, 0 );
  $sMenu2 = $oPage->throwMenu( 'menu_2.tpl', 2, $iContent, 0 );
  $sMenu3 = $oPage->throwMenu( 'menu_3.tpl', 3, $iContent, 2 );
  $sMenu4 = $oPage->throwMenu( 'menu_4.tpl', 4, $iContent, 2 );
}

if( isset( $config['page_search'] ) && is_numeric( $config['page_search'] ) && isset( $oPage->aPages[$config['page_search']] ) ){
  $sLinkSearch = $oPage->aPages[$config['page_search']]['sLinkName'];
  $sSearchForm = $oTpl->tbHtml( 'container.tpl', 'SEARCH_FORM' );
}
else{
  $sSearchForm = null;
}

$aBanners = throwBannersRand( 'container.tpl' );
echo $oTpl->tbHtml( 'container.tpl', 'HEAD' ).$oTpl->tbHtml( 'container.tpl', 'BODY' ).$content.$oTpl->tbHtml( 'container.tpl', 'FOOT' );
?>