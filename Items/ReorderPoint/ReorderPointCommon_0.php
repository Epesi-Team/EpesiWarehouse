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
 * @version    1.0
 * @package    epesi-premium
 * @subpackage warehouse-items
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_ReorderPointCommon extends ModuleCommon
{
    public static function menu()
    {
        if (!self::has_reorder_access()) {
            return;
        }
        return array(_M('Reports') => array('__submenu__'    => 1,
                                            _M('Items to Order') => array()
        ));
    }

    public static function has_reorder_access() {
        return Base_AclCommon::check_permission('Warehouse - see items to reorder');
    }

    public static function applet_access() {
        return self::has_reorder_access();
    }

    public static function applet_caption() {
        if (self::applet_access())
            return __('Items to Order');
    }

    public static function applet_info() {
        return __('Show items that need to be ordered to keep items quantity on specified level');
    }

    public static function get_items_to_reorder() {
        $sql = 'SELECT id FROM premium_warehouse_items_data_1 AS x WHERE' .
               // serialized and inventory item only
               ' (f_item_type = \'1\' OR f_item_type = \'0\') ' .
               ' AND f_reorder_point > COALESCE((SELECT SUM(f_quantity)' .
               ' FROM premium_warehouse_location_data_1' .
               ' WHERE active=1 AND f_item_sku=x.id),0)';
        return DB::GetCol($sql);
    }

}
