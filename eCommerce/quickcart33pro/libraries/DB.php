<?php
/**
 * Database file
 * 
 * This file defines class that maintains database connection.
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @version 1.0
 * @license SPL
 * @package epesi-base
 */
define('_VALID_ACCESS',1);

$d = getcwd();
//$sess_bak = $_SESSION;
define('SET_SESSION',false);
define('FORCE_LANG_CODE',LANGUAGE);
require_once(EPESI_DATA_DIR.'/../include.php');
ModuleManager::load_modules();
Acl::set_user(1);
Base_RegionalSettingsCommon::restore_locale();
// $_SESSION = $sess_bak;
chdir($d);
ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.EPESI_DATA_DIR.'/../');

error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

?>
