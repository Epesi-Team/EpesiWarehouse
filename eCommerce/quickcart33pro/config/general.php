<?php
unset( $config, $aMenuTypes, $aPay, $lang, $aBanners, $aBoxes, $aUser );

require_once 'epesi.php';

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
if( isset( $sLang ) && eregi('^[a-z0-9]{2}$',$sLang ) && is_file( $config['dir_lang'].$sLang.'.php' ) ){
  setCookie( 'sLanguage', $sLang, time( ) + 86400 );
  define( 'LANGUAGE', $sLang );
}
else{
  if( !empty( $_COOKIE['sLanguage'] ) && eregi('^[a-z0-9]{2}$', $_COOKIE['sLanguage'] ) && is_file( $config['dir_lang'].$_COOKIE['sLanguage'].'.php' ) )
    define( 'LANGUAGE', $_COOKIE['sLanguage'] );
  else
    define( 'LANGUAGE', $config['default_lang'] );
}

$config['config']       = 'config/general.php';
require_once(LANGUAGE.'.php');
require_once('epesi_'.LANGUAGE.'.php');

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

$aOuterPaymentOption = Array( 1 => 'DotPay', 2 => 'Przelewy24', 3 => 'PayPal', 4 => 'Platnosci.pl', 5 => 'Żagiel' );
define( 'DIR_CORE',       $config['dir_core'] );
define( 'DIR_FILES',      $config['dir_files'] );
define( 'DIR_BACKUP', DIR_FILES.'backup/' );
define( 'DIR_LIBRARIES',  $config['dir_libraries'] );
define( 'DIR_LANG',       $config['dir_lang'] );
define( 'DIR_TEMPLATES',  $config['dir_templates'] );
define( 'DIR_THEMES',     $config['dir_themes'] );

define( 'DB_PAGES',       $config_db['pages'] );
define( 'DB_PAGES_EXT',   $config_db['pages_ext'] );
define( 'DB_PAGES_FILES', $config_db['pages_files'] );
define( 'DB_CATEGORIES_NOKAUT_NAMES', $config_db['categories_nokaut_names'] );
define( 'DB_POLL_QUESTIONS', $config_db['poll_questions'] );
define( 'DB_POLL_ANSWERS',   $config_db['poll_answers'] );
define( 'DB_POLL_VOTES',     $config_db['poll_votes'] );
define( 'DB_PRODUCTS_RELATED', $config_db['products_related'] );
define( 'DB_BANNERS', $config_db['banners'] );
define( 'DB_BANNERS_STATS', $config_db['banners_stats'] );
define( 'DB_BOXES', $config_db['boxes'] );
define( 'DB_PAGES_STATS',     $config_db['pages_stats'] );
define( 'DB_SEARCHED_WORDS',  $config_db['searched_words'] );
define( 'DB_PRODUCTS_STATS',  $config_db['products_stats'] );
define( 'DB_FEATURES', $config_db['features'] );
define( 'DB_FEATURES_PRODUCTS', $config_db['features_products'] );
define( 'DB_PAGES_COMMENTS', $config_db['pages_comments'] );
define( 'DB_PRODUCTS_COMMENTS', $config_db['products_comments'] );
define( 'DB_NEWSLETTER', $config_db['newsletter'] );
define( 'DB_PRODUCTS',       $config_db['products'] );
define( 'DB_PRODUCTS_EXT',   $config_db['products_ext'] );
define( 'DB_PRODUCTS_FILES', $config_db['products_files'] );
define( 'DB_PRODUCTS_PAGES', $config_db['products_pages'] );
define( 'DB_ORDERS_TEMP', $config_db['orders_temp'] );
define( 'DB_ORDERS', $config_db['orders'] );
define( 'DB_ORDERS_PRODUCTS', $config_db['orders_products'] );
define( 'DB_ORDERS_COMMENTS', $config_db['orders_comments'] );
define( 'DB_ORDERS_STATUS', $config_db['orders_status'] );
define( 'DB_PAYMENTS', $config_db['payments'] );
define( 'DB_CARRIERS', $config_db['carriers'] );
define( 'DB_CARRIERS_PAYMENTS', $config_db['carriers_payments'] );

define( 'DB_CONFIG',      $config['config'] );

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