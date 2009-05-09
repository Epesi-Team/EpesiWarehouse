<?php
if( !empty( $a ) ){
  require_once DIR_CORE.'compare.php';
  $aUrl = parse_url( 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
  header('Content-Type: text/xml');
  echo listProductsCompare( 'compare.tpl', $a );
  ob_end_flush( );
  exit;
}
?>