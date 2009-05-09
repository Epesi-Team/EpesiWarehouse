<?php
if( empty( $sDateFrom ) || ( !empty( $sDateFrom ) && !is_date( $sDateFrom ) ) )
  $sDateFrom = '1970-01-01';
if( empty( $sDateTo ) || ( !empty( $sDateTo ) && !is_date( $sDateTo ) ) )
  $sDateTo = date( 'Y-m-d' );
if( !isset( $iStatus1 ) )
  $iStatus1 = 1;
if( !isset( $iStatus2 ) )
  $iStatus2 = count( $oOrder->throwStatus( ) );

if( $a == 'orders' ){
  $sStatusSelect1 = throwSelectFromArray( $oOrder->throwStatus( ), $iStatus1 );
  $sStatusSelect2 = throwSelectFromArray( $oOrder->throwStatus( ), $iStatus2 );
}

if( !isset( $aActions['o1'] ) )
  $aActions['o1'] = null;
require_once DIR_CORE.'stats-admin.php';

if( $a == 'pages' ){
  if( isset( $sOption ) && $sOption == 'delete' ){
    deleteAllPagesStats( );
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }

  if( isset( $iPage ) && is_numeric( $iPage ) ){
    $sPageName = isset( $oPage->aPages[$iPage]['sName'] ) ? $oPage->aPages[$iPage]['sName'] : null;
    $sList = listPageStats( 'stats.tpl', $iPage );
  }
  else{
    $sList = listPagesStats( 'stats.tpl', $sDateFrom, $sDateTo );
  }
  $content .= $oTpl->tbHtml( 'stats.tpl', 'PAGES_LIST_TITLE' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );

}
elseif( $a == 'products' && $aActions['o1'] == 'phrases' ){
  if( isset( $sOption ) && $sOption == 'delete' ){
    deleteAllSearchWords( );
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }

  $content .= $oTpl->tbHtml( 'stats.tpl', 'PHRASES_LIST_TITLE' );
  $sList = listSearchedPhrases( 'stats.tpl', $sDateFrom, $sDateTo );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'products' && !isset( $aActions['o1'] ) ){
  if( isset( $sOption ) && $sOption == 'delete' ){
    deleteAllProductsStats( );
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }

  if( isset( $iProduct ) && is_numeric( $iProduct ) ){
    $sProductName = isset( $oProduct->aProducts[$iProduct]['sName'] ) ? $oProduct->aProducts[$iProduct]['sName'] : null;
    $sList = listProductStats( 'stats.tpl', $iProduct );
  }
  else{
    $sList = listProductsStats( 'stats.tpl', $sDateFrom, $sDateTo );
  }
  $content .= $oTpl->tbHtml( 'stats.tpl', 'PRODUCTS_LIST_TITLE' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'orders' && $aActions['o1'] == 'products' ){
  $content .= $oTpl->tbHtml( 'stats.tpl', 'ORDERS_PRODUCTS_LIST_TITLE' );
  $sList = listOrderedProductsStats( 'stats.tpl', $sDateFrom, $sDateTo, $iStatus1, $iStatus2 );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
elseif( $a == 'orders' && $aActions['o1'] == 'customers' ){
  $content .= $oTpl->tbHtml( 'stats.tpl', 'CUSTOMERS_LIST_TITLE' );
  $sList = listCustomersStats( 'stats.tpl', $sDateFrom, $sDateTo, $iStatus1, $iStatus2 );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
?>