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

class Premium_Warehouse_SoldItemsReportCommon extends ModuleCommon {

    public static function menu() {
        if (!Base_AclCommon::i_am_admin()) return;
        return array(_M('Reports') => array('__submenu__' => 1,
            _M('Sold Items') => array()
        ));
    }

}

?>