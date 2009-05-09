<?php
//error_reporting( E_ALL );
unset( $config, $aMenuTypes, $aPhotoTypes, $lang, $aBanners, $aBoxes, $aUser );

/*
* Directories
*/
$config['dir_core']     = 'core/';
$config['dir_db']       = 'db/';
$config['dir_libraries']= 'libraries/';
$config['dir_lang']     = 'lang/';
$config['dir_templates']= 'templates/';
$config['dir_themes']   = $config['dir_templates'].'themes/';
$config['dir_files']    = 'files/';
$config['dir_plugins']  = 'plugins/';

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
* Add minutes difference between your local time and server time
*/
$config['time_diff'] = 0;

$config['hidden_shows'] = false;

/*
* If You want embed PHP code in template files
* set this variable true but it is not recommended and
* script will be slower
*/
$config['embed_php'] = false;

/*
* If true, then script will search db/*_products_ext.php file in
* meta keywords, meta description and full description.
* We dont recommend it if shopping cart have more then 1000 products
*/
$config['search_products_description'] = false;

/*
* Administrator login and password
*/
$config['login'] = "admin";
$config['pass'] = "admin";

/*
* Add minutes difference between your local time and server time
*/
$config['time_diff'] = 0;

/*
* Default language
*/
$config['default_lang'] = "en";

/*
* If should be use wysiwyg editor or default
*/
$config['wysiwyg'] = true;

/*
* If should be text size change option on pages
*/
$config['text_size'] = true;

/*
* If should be language parameter added to url
*/
$config['language_in_url'] = false;

/*
* Language separator in url
*/
$config['language_separator'] = '_';

define( 'LANGUAGE_IN_URL',    $config['language_in_url'] );
define( 'LANGUAGE_SEPARATOR', $config['language_separator'] );

/*
* Leave code below!
*/
require_once $config['dir_core'].'common.php';

$config['cookie_admin'] = defined( 'CUSTOMER_PAGE' ) ? null : 'A';

if( defined( 'CUSTOMER_PAGE' ) && !isset( $sLang ) && LANGUAGE_IN_URL == true )
  $sLang = getLanguageFromUrl( );
if( isset( $sLang ) && is_file( $config['dir_lang'].$sLang.'.php' ) && strlen( $sLang ) == 2 ){
  setCookie( 'sLanguage'.$config['cookie_admin'], $sLang, time( ) + 86400 );
  define( 'LANGUAGE', $sLang );
}
else{
  if( !empty( $_COOKIE['sLanguage'.$config['cookie_admin']] ) && is_file( $config['dir_lang'].$_COOKIE['sLanguage'.$config['cookie_admin']].'.php' ) && strlen( $_COOKIE['sLanguage'.$config['cookie_admin']] ) == 2 )
    define( 'LANGUAGE', $_COOKIE['sLanguage'.$config['cookie_admin']] );
  else
    define( 'LANGUAGE', $config['default_lang'] );
}

$config['config']       = 'config/general.php';
$config['config_lang']  = 'config/lang_'.LANGUAGE.'.php';

$config_db['pages']       = $config['dir_db'].LANGUAGE.'_pages.php';
$config_db['pages_ext']   = $config['dir_db'].LANGUAGE.'_pages_ext.php';
$config_db['pages_files'] = $config['dir_db'].LANGUAGE.'_pages_files.php';
$config_db['categories_nokaut_names'] = $config['dir_db'].LANGUAGE.'_categories_nokaut_names.php';
$config_db['products_related'] = $config['dir_db'].LANGUAGE.'_products_related.php';
$config_db['banners'] = $config['dir_db'].'banners.php';
$config_db['banners_stats'] = $config['dir_db'].'banners_stats.php';
$config_db['boxes'] = $config['dir_db'].LANGUAGE.'_boxes.php';
$config_db['features'] = $config['dir_db'].LANGUAGE.'_features.php';
$config_db['features_products'] = $config['dir_db'].LANGUAGE.'_features_products.php';
$config_db['pages_comments'] = $config['dir_db'].LANGUAGE.'_pages_comments.php';
$config_db['products_comments'] = $config['dir_db'].LANGUAGE.'_products_comments.php';
$config_db['newsletter'] = $config['dir_db'].'newsletter.php';

$config_db['products']        = $config['dir_db'].LANGUAGE.'_products.php';
$config_db['products_ext']    = $config['dir_db'].LANGUAGE.'_products_ext.php';
$config_db['products_files']  = $config['dir_db'].LANGUAGE.'_products_files.php';
$config_db['products_pages']  = $config['dir_db'].LANGUAGE.'_products_pages.php';
$config_db['orders_temp']     = $config['dir_db'].'orders_temp.php';
$config_db['orders']          = $config['dir_db'].'orders.php';
$config_db['orders_products'] = $config['dir_db'].'orders_products.php';
$config_db['orders_comments'] = $config['dir_db'].'orders_comments.php';
$config_db['orders_status']   = $config['dir_db'].'orders_status.php';
$config_db['payments']        = $config['dir_db'].LANGUAGE.'_payments.php';
$config_db['carriers']        = $config['dir_db'].LANGUAGE.'_carriers.php';
$config_db['carriers_payments']= $config['dir_db'].LANGUAGE.'_carriers_payments.php';

$config_db['pages_stats'] =     $config['dir_db'].LANGUAGE.'_pages_stats.php';
$config_db['searched_words'] =  $config['dir_db'].LANGUAGE.'_searched_words.php';
$config_db['products_stats'] =  $config['dir_db'].LANGUAGE.'_products_stats.php';
$config_db['poll_questions'] = $config['dir_db'].LANGUAGE.'_poll_questions.php';
$config_db['poll_answers'] =   $config['dir_db'].LANGUAGE.'_poll_answers.php';
$config_db['poll_votes'] =     $config['dir_db'].LANGUAGE.'_poll_votes.php';

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
$config['skapiec_shop_id'] = '2222';
define( 'DIR_CORE',       $config['dir_core'] );
define( 'DIR_DB',         $config['dir_db'] );
define( 'DIR_FILES',      $config['dir_files'] );
define( 'DIR_BACKUP', DIR_FILES.'backup/' );
define( 'DIR_LIBRARIES',  $config['dir_libraries'] );
define( 'DIR_PLUGINS',    $config['dir_plugins'] );
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
define( 'DB_CONFIG_LANG', $config['config_lang'] );

define( 'MAX_DIMENSION_OF_IMAGE', $config['max_dimension_of_image'] );

define( 'POLL_MAX_ANSWERS',       $config['poll_max_answers'] );
define( 'POLL_COOKIE_NAME',  'iPollAnswer' );
define( 'HIDDEN_SHOWS',   $config['hidden_shows'] );
define( 'DISPLAY_EXPANDED_MENU', $config['display_expanded_menu'] );
define( 'DISPLAY_SUBCATEGORY_PRODUCTS', $config['display_subcategory_products'] );
define( 'WYSIWYG',   $config['wysiwyg'] );
define( 'VERSION',  $config['version'] );
define( 'TIME_DIFF', $config['time_diff'] );
?>