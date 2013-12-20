<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Reorder Point Applet and view
 *
 * @author     Adam Bukowski <abukowski@telaxus.com>
 * @copyright  Copyright &copy; 2013, Telaxus LLC
 * @license    Commercial
 * @version    1.5.0
 * @package    epesi-premium
 * @subpackage warehouse-items
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_ReorderPointInstall extends ModuleInstall
{
    const version = '1.5.0';

    public function install()
    {
        Base_LangCommon::install_translations($this->get_type());

        Base_AclCommon::add_permission(_M('Warehouse - see items to reorder'),
                                       array('ACCESS:manager'));
        return true;
    }

    public function uninstall()
    {
        Base_AclCommon::delete_permission('Warehouse - see items to reorder');
        return true;
    }

    public function version()
    {
        return array(self::version);
    }

    public function requires($v)
    {
        return array(
            array('name' => 'Base/Lang', 'version' => 0),
            array('name' => 'Base/Acl', 'version' => 0),
            array('name' => 'Premium/Warehouse', 'version' => 0),
            array('name' => 'Premium/Warehouse/Items', 'version' => 0),
            array('name' => 'Premium/Warehouse/Items/Location', 'version' => 0)
        );
    }

    public static function info()
    {
        return array(
            'Description' => 'Items Reorder point - Premium Module',
            'Author'      => 'Adam Bukowski <abukowski@telaxus.com>',
            'License'     => 'Commercial'
        );
    }

    public static function simple_setup()
    {
        return array('package' => __('Inventory Management'));
    }

}
