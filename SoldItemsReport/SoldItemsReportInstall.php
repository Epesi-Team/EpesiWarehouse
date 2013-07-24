<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Adam Bukowski <abukowski@telaxus.com>
 * @copyright Copyright &copy; 2013, Telaxus LLC
 * @license Commercial
 * @version 1.5.0
 * @package epesi-premium
 * @subpackage warehouse-solditemsreport
 */

class Premium_Warehouse_SoldItemsReportInstall extends ModuleInstall {

    /**
     * version of module
     */
    const version = '1.5.0';

    /**
     * Module installation function.
     * @return true if installation success, false otherwise
     */
    public function install() {
        return true;
    }

    /**
     * Module uninstallation function.
     * @return true if installation success, false otherwise
     */
    public function uninstall() {
        return true;
    }

    /**
     * Returns array that contains information about modules required by this module.
     * The array should be determined by the version number that is given as parameter.
     *
     * @param string $v Version of module to install
     * @return array Array constructed as following: array(array('name'=>$ModuleName,'version'=>$ModuleVersion),...)
     */
    public function requires($v) {
        return array(
            array('name' => 'Utils/RecordBrowser/Reports', 'version' => 0),
            array('name' => 'Premium/Warehouse', 'version' => 0),
            array('name' => 'Libs/OpenFlashChart', 'version' => 0));
    }

    public static function info() {
        return array(
            'Description' => 'Sold items report',
            'Author' => 'Adam Bukowski <abukowski@telaxus.com>',
            'License' => '<a href="modules/Premium/Warehouse/SalesReport/license.html" TARGET="_blank">Commercial</a>');
    }

    public static function simple_setup() {
        return array('package' => __('Inventory Management'), 'option' => __('Sold Items Report'));
    }

    public function version() {
        return array(self::version);
    }
}

?>