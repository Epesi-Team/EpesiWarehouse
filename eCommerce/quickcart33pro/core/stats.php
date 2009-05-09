<?php
/**
* Save page stat
* @param int $iPage
*/
function savePageStat( $iPage ){
    //{ epesi
    switch($iPage%4) {
	case 0: //category
		DB::Execute('INSERT INTO premium_ecommerce_categories_stats(page,visited_on) VALUES (%d,%T)',array($iPage/4,time()));
		break;
	case 2: //epesi backend page
		DB::Execute('INSERT INTO premium_ecommerce_pages_stats(page,visited_on) VALUES (%d,%T)',array(($iPage-2)/4,time()));
		break;
	case 1: //companies
	case 3: //basket,start page, etc.
		//ignore
		break;
    }
    //} epesi
    /*
  $oFF =& FlatFiles::getInstance( );
  $oFF->save( DB_PAGES_STATS, Array( 'iPage' => $iPage, 'iTime' => time( ) ) );
  */
} // end function savePageStat

/**
* Save product stat
* @param int $iProduct
*/
function saveProductStat( $iProduct ){
    //{ epesi
    DB::Execute('INSERT INTO premium_ecommerce_products_stats(product,visited_on) VALUES (%d,%T)',array($iProduct,time()));
    //} epesi
    /*
  $oFF =& FlatFiles::getInstance( );
  $oFF->save( DB_PRODUCTS_STATS, Array( 'iProduct' => $iProduct, 'iTime' => time( ) ) );
  */
} // end function saveProductStat

/**
* Saves searched words
* @param array $aWords
*/
function saveSearchedWords( $aWords ){
  if( isset( $GLOBALS['aActions']['o2'] ) )
    return null;
  if( function_exists( 'mb_strtolower' ) )
    $sWordsSorted = mb_strtolower( implode( ' ', $aWords ), 'UTF-8' );
  else
    $sWordsSorted = strtolower_utf8( implode( ' ', $aWords ) );
  $aWordsSorted = explode( ' ', $sWordsSorted );
  sort( $aWordsSorted );
  $oFF =& FlatFiles::getInstance( );
  $oFF->save( DB_SEARCHED_WORDS, Array( 'sWords' => implode( ' ', $aWordsSorted ), 'iTime' => time( ) ) );
} // end function saveSearchedWords

function strtolower_utf8( $sWords ) {
  return utf8_encode( strtolower( utf8_decode( $sWords ) ) );
}
?>