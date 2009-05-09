<?php
if( isset( $a ) && is_numeric( $a ) ){
  $aData = $oPage->throwPage( $a );
  if( isset( $aData ) && isset( $oPage->aPagesChildrens[$a] ) ){
    $sTitle = strip_tags( ( !empty( $aData['sNameTitle'] ) ? $aData['sNameTitle'] : $aData['sName'] ).' - ' );
    header( 'Content-Type: text/xml' );
    echo $oPage->listSubpagesRss( $a, 'xml.tpl' );
    exit;
  }
}
?>