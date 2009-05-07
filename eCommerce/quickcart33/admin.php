<?php
/*
* Quick.Cart by OpenSolution.org
* www.OpenSolution.org
*/
extract( $_GET );

require 'config/general.php';
require DB_CONFIG_LANG;

session_start( );

require_once DIR_LANG.LANGUAGE.'.php';
header( 'Content-Type: text/html; charset='.$config['charset'] );
require_once DIR_LIBRARIES.'TplParser.php';
require_once DIR_LIBRARIES.'FileJobs.php';
require_once DIR_LIBRARIES.'FlatFiles.php';
require_once DIR_LIBRARIES.'DB.php';
require_once DIR_LIBRARIES.'FotoJobs.php';
require_once DIR_LIBRARIES.'Trash.php';
require_once DIR_PLUGINS.'plugins.php';

require_once DIR_CORE.'common.php';
require_once DIR_CORE.'common-admin.php';
require_once DIR_CORE.'pages.php';
require_once DIR_CORE.'pages-admin.php';
require_once DIR_CORE.'lang-admin.php';
require_once DIR_CORE.'files.php';
require_once DIR_CORE.'files-admin.php';
require_once DIR_CORE.'products.php';
require_once DIR_CORE.'products-admin.php';
require_once DIR_CORE.'orders.php';
require_once DIR_CORE.'orders-admin.php';

require_once DIR_PLUGINS.'edit/htmleditor.php';

if( !isset( $p ) || empty( $p ) )
  $p  = 'news';

if( isset( $sPhrase ) && !empty( $sPhrase ) ){
  $sPhrase = trim( changeSpecialChars( htmlspecialchars( stripslashes( $sPhrase ) ) ) );
}

$aActions = getAction( $p );

$oFF    =& FlatFiles::getInstance( );
$oFoto  =& FotoJobs::getInstance( );
$oTpl   =& TplParser::getInstance( DIR_TEMPLATES.'admin/', $config['embed_php'] );
$oFF->cacheFilesIndexes( $config_db );

$oFile    =& FilesAdmin::getInstance( );
$oPage    =& PagesAdmin::getInstance( );
$oProduct =& ProductsAdmin::getInstance( );
$oOrder   = new OrdersAdmin( );
$content  = null;

loginActions( $p, 'bUserQCMS', 'container.tpl' );

if( $p == 'news' || $p == 'login' )
  $content .= $oTpl->tbHtml( 'container.tpl', 'HOME' );
elseif( isset( $aActions ) && is_file( 'actions_admin/'.$aActions['f'].'.php' ) )
  require 'actions_admin/'.$aActions['f'].'.php';

if( empty( $content ) )
  $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );

$sLangSelect = throwLangSelect( $config['language'] );

if( isset( $config['login'] ) && isset( $config['pass'] ) && $config['login'] == $config['pass'] )
  $sMsg .= $oTpl->tbHtml( 'messages.tpl', 'CHANGE_LOGIN_PASSWORD' );

echo $oTpl->tbHtml( 'container.tpl', 'HEAD' ).$oTpl->tbHtml( 'container.tpl', 'BODY' ).$content.$oTpl->tbHtml( 'container.tpl', 'FOOT' );
?>