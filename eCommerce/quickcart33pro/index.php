<?php
/*
* Quick.Cart by OpenSolution.org
* www.OpenSolution.org
*/
extract( $_GET );
session_start( );
define( 'CUSTOMER_PAGE', true );

require 'config/general.php';
require DB_CONFIG_LANG;

if( isset( $_POST['sPhrase'] ) ){
  header( 'Location: '.$_SERVER['REQUEST_URI'].((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?'?':'&').'sPhrase='.urlencode( $_POST['sPhrase'] ) );
  exit;
}

require_once DIR_LANG.LANGUAGE.'.php';
header( 'Content-Type: text/html; charset='.$config['charset'] );
require_once DIR_LIBRARIES.'TplParser.php';
require_once DIR_LIBRARIES.'FileJobs.php';
//require_once DIR_LIBRARIES.'FlatFiles.php';
require_once DIR_LIBRARIES.'DB.php';
require_once DIR_LIBRARIES.'Trash.php';
require_once DIR_PLUGINS.'plugins.php';

require_once DIR_CORE.'pages.php';
require_once DIR_CORE.'files.php';
require_once DIR_CORE.'poll.php';
require_once DIR_CORE.'banners.php';
require_once DIR_CORE.'boxes.php';
require_once DIR_CORE.'stats.php';
require_once DIR_CORE.'comments.php';
require_once DIR_CORE.'products.php';
require_once DIR_CORE.'orders.php';

if( isset( $sPhrase ) && !empty( $sPhrase ) ){
  $sPhrase = trim( changeSpecialChars( htmlspecialchars( stripslashes( urldecode( $sPhrase ) ) ) ) );
}

$aActions = isset( $p ) ? getAction( $p ) : getUrlFromGet( );
if( isset( $aActions['f'] ) && $aActions['f'] == 'p' )
  $iContent = ( isset( $aActions['a'] ) && is_numeric( $aActions['a'] ) ) ? $aActions['a'] : $config['start_page'];
else
  $iContent = null;

//$oFF  =& FlatFiles::getInstance( );
$oTpl =& TplParser::getInstance( DIR_TEMPLATES, $config['embed_php'] );
//$oFF->cacheFilesIndexes( $config_db );

$oFile    =& Files::getInstance( );
$oPage    =& Pages::getInstance( );
$oProduct =& Products::getInstance( );
$oOrder   = new Orders( );
$content  = null;
$sTheme   = null;
$sBanner  = null;
$sRssMeta = null;

if( !isset( $_SESSION['iCustomer'.LANGUAGE] ) ){
  if( isset( $_COOKIE['sCustomer'.LANGUAGE] ) && !empty( $_COOKIE['sCustomer'.LANGUAGE] ) ){
    $iOrder = $oOrder->throwSavedOrderId( $_COOKIE['sCustomer'.LANGUAGE] );
    if( isset( $iOrder ) && is_numeric( $iOrder ) ){
      $_SESSION['iCustomer'.LANGUAGE] = $iOrder;
      $oOrder->generateBasket( );
    }
  }
  if( !isset( $_SESSION['iCustomer'.LANGUAGE] ) )
    $_SESSION['iCustomer'.LANGUAGE] = time( ).rand( 100, 999 );
}

$sKeywords    = $config['keywords'];
$sDescription = $config['description'];
ob_start( 'changeCharset' );

if( isset( $aActions ) && is_file( 'actions_client/'.$aActions['f'].'.php' ) )
  require 'actions_client/'.$aActions['f'].'.php';

$iOrderProducts = isset( $_SESSION['iOrderQuantity'.LANGUAGE] ) ? $_SESSION['iOrderQuantity'.LANGUAGE] : 0;
$fOrderSummary  = isset( $_SESSION['fOrderSummary'.LANGUAGE] ) ? displayPrice( $_SESSION['fOrderSummary'.LANGUAGE] ) : displayPrice( 0 );

if( $aActions['f'] == 'banners' && isset( $a ) && is_numeric( $a ) ){
  goToBannerLink( $a );
}
if( isset( $sTheme ) && !empty( $sTheme ) && is_file( DIR_THEMES.$sTheme ) ){
  require DIR_THEMES.$sTheme;
}
else{
  if( is_file( DIR_THEMES.$aActions['f'].'-'.$aActions['a'].'.php' ) ){
    require DIR_THEMES.$aActions['f'].'-'.$aActions['a'].'.php';
  }
  else{
    require DIR_THEMES.$config['default_theme'];
  }
}
ob_end_flush( );
?>