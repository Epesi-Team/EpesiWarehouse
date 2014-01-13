<?php
unset( $config, $aMenuTypes, $aPay, $lang, $aBanners, $aBoxes, $aUser );

if(!include_once('epesi.php'))
    die('Please configure ecommerce module in epesi');

mb_internal_encoding('UTF-8');

$config['display_all_published_products'] = true;
/*
* Directories
*/
$config['dir_core']     = 'core/';
$config['dir_libraries']= 'libraries/';
$config['dir_lang']     = 'lang/';
$config['dir_templates']= 'templates/';
$config['dir_themes']   = $config['dir_templates'].'themes/';
$config['dir_files']    = 'files/';

/*
* Rewrite
*/
$config['friendly_links'] = null;
define( 'FRIENDLY_LINKS', $config['friendly_links'] );
if( FRIENDLY_LINKS == true ){
  $config['index'] = 'index.html';
  $config['redirect'] = null;
  $config['search_amp'] = isset( $sPhrase ) ? '&amp;' : '?';
}
else{
  $config['index'] = '?';
  $config['redirect'] = $_SERVER['PHP_SELF'];
  $config['search_amp'] = '&amp;';
}
define( 'INDEX', $config['index'] );
define( 'REDIRECT', $config['redirect'] );

/*
* If You want embed PHP code in template files
* set this variable true but it is not recommended and
* script will be slower
*/
$config['embed_php'] = false;

/*
* If should be language parameter added to url
*/
/*
* Language separator in url
*/
$config['language_separator'] = '_';

define( 'LANGUAGE_SEPARATOR', $config['language_separator'] );

/*
* Leave code below!
*/
require_once $config['dir_core'].'common.php';

if( defined( 'CUSTOMER_PAGE' ) && !isset( $sLang ) )
  $sLang = getLanguageFromUrl( );
if( isset( $sLang ) && preg_match('/^[a-z0-9]{2}$/i',$sLang ) && is_file( 'config/'.$sLang.'.php' ) && is_file( 'config/epesi_'.$sLang.'.php' ) && in_array($sLang,$config['available_lang'])){
  setCookie( 'sLanguage', $sLang, time( ) + 86400 );
  define( 'LANGUAGE_CONFIG', $sLang );
}
else{
  if( !empty( $_COOKIE['sLanguage'] ) && preg_match('/^[a-z0-9]{2}$/i', $_COOKIE['sLanguage'] ) && is_file( 'config/'.$_COOKIE['sLanguage'].'.php' ) && is_file( 'config/epesi_'.$_COOKIE['sLanguage'].'.php' )  && in_array($_COOKIE['sLanguage'],$config['available_lang']))
    define( 'LANGUAGE_CONFIG', $_COOKIE['sLanguage'] );
  else {
    require_once($config['dir_libraries'].'GeoIP.php');
    if(is_file( 'config/'.GEOIP_COUNTRY.'.php' ) && is_file( 'config/epesi_'.GEOIP_COUNTRY.'.php' ) && in_array(GEOIP_COUNTRY,$config['available_lang']))
	define('LANGUAGE_CONFIG', GEOIP_COUNTRY);
    else
	define('LANGUAGE_CONFIG', $config['default_lang']);
  }
}

require_once(LANGUAGE_CONFIG.'.php');
require_once('epesi_'.LANGUAGE_CONFIG.'.php');
if(!defined('LANGUAGE')) {
	if(isset($config['language']))
		define('LANGUAGE',$config['language']);
	else
		define('LANGUAGE',LANGUAGE_CONFIG);
}

$config['poll_max_answers'] = 7;
$config['language']	= LANGUAGE;
$config['version']  = '3.3';

$config['newsletter'] = true;
$config['cross_sell'] = true;
$config['pages_images_sizes'] = Array( 0 => 100, 1 => 200 );
$config['max_dimension_of_image'] = 900;
$config['display_all_files'] = true;
$config['display_expanded_menu'] = false;
$config['display_subcategory_products'] = true;
$config['change_files_names'] = false;

$aOuterPaymentOption = Array( 1 => 'DotPay', 2 => 'Przelewy24', 3 => 'PayPal', 4 => 'Platnosci.pl', 5 => 'Żagiel', 6=>'Credit Card (basic)' );
define( 'DIR_CORE',       $config['dir_core'] );
define( 'DIR_FILES',      $config['dir_files'] );
define( 'DIR_BACKUP', DIR_FILES.'backup/' );
define( 'DIR_LIBRARIES',  $config['dir_libraries'] );
define( 'DIR_LANG',       $config['dir_lang'] );
define( 'DIR_TEMPLATES',  $config['dir_templates'] );
define( 'DIR_THEMES',     $config['dir_themes'] );

define( 'MAX_DIMENSION_OF_IMAGE', $config['max_dimension_of_image'] );

define( 'POLL_MAX_ANSWERS',       $config['poll_max_answers'] );
define( 'POLL_COOKIE_NAME',  'iPollAnswer' );
define( 'DISPLAY_EXPANDED_MENU', $config['display_expanded_menu'] );
define( 'DISPLAY_SUBCATEGORY_PRODUCTS', $config['display_subcategory_products'] );
define( 'VERSION',  $config['version'] );
define( 'TIME_DIFF', $config['time_diff'] );

//moved from lang_XX.php
$config['template'] = "default.css";
$config['default_theme'] = 'default.php';
$config['default_pages_template'] = 'pages_default.tpl';
$config['default_products_template'] = 'products_default.tpl';

$config['inherit_from_parents'] = false;

/*
* Start page
*/
//{ epesi variables - don't change
$config['start_page'] = 11;
$config['basket_page'] = 3;
$config['order_page'] = 7;
$config['rules_page'] = 15;
$config['page_search'] = 19;
$config['contact_page']	= 31; //epesi required id
$config['site_map'] = 27; //epesi required id
//} epesi variables - don't change
?>