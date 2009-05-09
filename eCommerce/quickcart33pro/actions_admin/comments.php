<?php
if( $a == 'list' ){
  if( isset( $_POST['sOption'] ) ){
    saveComments( $_POST, $sFileDb );
    $sOption = 'save';
  }

  if( isset( $sOption ) ){
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }

  $content .= $oTpl->tbHtml( 'comments.tpl', 'COMMENTS_TITLE' );

  if( isset( $iPage ) && is_numeric( $iPage ) ){
    $iLink = $iPage;
    $sFileDb = null;
  }

  if( isset( $iProduct ) && is_numeric( $iProduct ) ){
    $iLink = $iProduct;
    $sFileDb = DB_PRODUCTS_COMMENTS;
  }

  if( !isset( $iLink ) )
    $iLink = null;
  if( empty( $sFileDb ) )
    $sFileDb = null;

  $sCommentsList = listComments( 'comments.tpl', $iLink, $sFileDb );
  $content .= !empty( $sCommentsList ) ? $sCommentsList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'delete' && isset( $iComment ) && is_numeric( $iComment ) ){
  $sDb = null;
  $sLink = 'iPage';

  if( !isset( $iPage ) )
    $iPage = null;
  
  if( isset( $iProduct ) ){
    $sLink = 'iProduct';
    $sDb = DB_PRODUCTS_COMMENTS;
  }
  else
    $iProduct = null;

  deleteComment( $iComment, $sDb );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del&'.$sLink.'='.$iPage.$iProduct );
  exit;
}
?>