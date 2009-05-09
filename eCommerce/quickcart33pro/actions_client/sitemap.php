<?php
if( $a == 'xml' ){
  header( 'Content-Type: text/xml' );
  $sDateTime = date( 'Y-m-d' );
  echo generateSiteMap2Xml( 'xml.tpl' );
  exit;
}
?>