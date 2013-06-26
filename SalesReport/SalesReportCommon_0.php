<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Janusz Tylek <jtylek@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-salesreport
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_SalesReportCommon extends ModuleCommon {
    public static function admin_caption() {
        return array('label' => __('Sales Report'), 'section' => __('Features Configuration'));
    }

    public static function currency_exchange_addon_access() {
        return Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders', 'edit', Utils_RecordBrowser::$last_record);
    }

    public static function menu() {
        if (!Base_AclCommon::i_am_admin()) return;
        return array(_M('Reports') => array('__submenu__' => 1,
            _M('Sales by Warehouse') => array('mode' => 'sales_by_warehouse'),
            _M('Sales by Transaction') => array('mode' => 'sales_by_transaction'),
            _M('Sales by Item') => array('mode' => 'sales_by_item'),
            _M('Stock Value by Warehouse') => array('mode' => 'value_by_warehouse')
        ));
    }

    public static function currency_exchange($val, $cur, $order_id) {
        $rate = DB::GetOne('SELECT exchange_rate FROM premium_warehouse_sales_report_exchange WHERE order_id=%d AND currency=%d', array($order_id, $cur));
        if (!$rate) $rate = 0;
        return $val * $rate;
    }

    public static function recalculate() {
        set_time_limit(0);

        $currency = Variable::get('premium_warehouse_ex_currency');
        $currency_precision = Utils_CurrencyFieldCommon::get_precission($currency);

        $calculator = Premium_Warehouse_SalesReport_Calculator::default_instance();
        $calculator->set_currency($currency);
        $calculator->set_currency_precision($currency_precision);
        $calculator->calculate();
    }
}

?>