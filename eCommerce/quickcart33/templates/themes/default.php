<?php
if( empty( $content ) )
  $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );

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

echo $oTpl->tbHtml( 'container.tpl', 'HEAD' ).$oTpl->tbHtml( 'container.tpl', 'BODY' ).$content.$oTpl->tbHtml( 'container.tpl', 'FOOT' );
?>