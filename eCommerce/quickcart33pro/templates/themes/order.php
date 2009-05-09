<?php
if( empty( $content ) )
  $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );

if( !isset( $bBlockPage ) ){
  $sMenu1 = $oPage->throwMenu( 'menu_1.tpl', 1, $iContent, 0 );
  $sMenu2 = $oPage->throwMenu( 'menu_2.tpl', 2, $iContent, 0 );
}

echo $oTpl->tbHtml( 'container.tpl', 'HEAD' ).$oTpl->tbHtml( 'container.tpl', 'ORDER_BODY' ).$content.$oTpl->tbHtml( 'container.tpl', 'FOOT' );
?>