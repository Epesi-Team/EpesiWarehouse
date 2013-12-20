<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Reorder Point Applet and view
 *
 * @author Adam Bukowski <abukowski@telaxus.com>
 * @copyright Copyright &copy; 2013, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_ReorderPoint extends Module
{
    public function body()
    {
        if (!Premium_Warehouse_Items_ReorderPointCommon::has_reorder_access()) {
            print __('You don\'t have permission to see this report');
        }

        $rb = $this->init_module('Utils/RecordBrowser',
                                 'premium_warehouse_items',
                                 'premium_warehouse_items');
        $crits = array();
        $crits['id'] = Premium_Warehouse_Items_ReorderPointCommon::get_items_to_reorder();
        $cols = array(
            'reorder_point' => true,
            'quantity_sold' => false,
            'last_purchase_price' => true,
            'gross_price' => false,
        );
        $args = array(
            array(),  // order
            $crits,
            $cols,
        );

        $hp = array(
            'quantity_on_hand'=>array('name'=>__('On Hand'), 'width'=>4, 'wrapmode'=>'nowrap'),
            'quantity_en_route'=>array('name'=>__('En Route'), 'width'=>4, 'wrapmode'=>'nowrap'),
            'available_qty'=>array('name'=>__('Avail. Qty'), 'width'=>4, 'wrapmode'=>'nowrap'),
            'dist_qty'=>array('name'=>__('Dist Qty'), 'width'=>4, 'wrapmode'=>'nowrap'),
            'reserved_qty'=>array('name'=>__('Res. Qty'), 'width'=>4, 'wrapmode'=>'nowrap'),
            'manufacturer_part_number'=>array('name'=>__('Part Number'), 'width'=>10, 'wrapmode'=>'nowrap'),
            'item_type'=>array('width'=>10, 'wrapmode'=>'nowrap'),
            'gross_price'=>array('name'=>__('Price'),'width'=>8, 'wrapmode'=>'nowrap'),
            'item_name'=>array('width'=>20,'wrapmode'=>'nowrap'),
            'sku'=>array('width'=>6, 'wrapmode'=>'nowrap'),
            'upc'=>array('width'=>8, 'wrapmode'=>'nowrap'),
            'product_code'=>array('width'=>8, 'wrapmode'=>'nowrap'),
            'manufacturer'=>array('width'=>8)
        );
        $rb->set_header_properties($hp);

        $this->display_module($rb, $args);
    }

    /**
     * Applet "Items to order" - based on reorder point
     * @param $conf
     * @param $opts
     */
    public function applet($conf, & $opts)
    {
        $opts['go'] = true;  // enable fullscreen
        $rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items','premium_warehouse_items');
        $limit = null;
        $crits = array();
        $crits['id'] = Premium_Warehouse_Items_ReorderPointCommon::get_items_to_reorder();

        $sorting = array();
        $cols = array(
            array('field'=>'item_name', 'width'=>10),
            array('field'=>'quantity_on_hand', 'width'=>10),
            array('field'=>'reorder_point', 'width'=>10)
        );

        $conds = array(
            $cols,
            $crits,
            $sorting,
            array('Premium_Warehouse_ItemsCommon', 'applet_info_format'),
            $limit,
            $conf,
            & $opts
        );
        $this->display_module($rb, $conds, 'mini_view');
    }

    public function caption() {
        return __('Items to Order');
    }

}
