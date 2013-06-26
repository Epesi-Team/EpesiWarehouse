<?php

class Premium_Warehouse_SalesReport_CalculatorDB {

    public function clean_up_data() {
        DB::Execute('DELETE FROM premium_warehouse_sales_report_purchase_fifo_tmp');
        DB::Execute('DELETE FROM premium_warehouse_sales_report_purchase_lifo_tmp');
        DB::Execute('DELETE FROM premium_warehouse_sales_report_earning');
    }

    public function next_purchase() {
        static $data;
        if ($data === null) {
            $data = $this->_get_purchase_transactions();
        }
        return $data->FetchRow();
    }

    public function next_sale() {
        static $data;
        if ($data === null) {
            $data = $this->_get_sale_transactions();
        }
        return $data->FetchRow();
    }

    private function _check_mode($mode) {
        if ($mode != 'fifo' && $mode != 'lifo')
            throw new Exception('Unknown mode: ' . $mode);
    }

    public function temp($mode, $item_id, $warehouse) {
        $this->_check_mode($mode);
        $order = ($mode == 'fifo' ? 'ASC' : 'DESC');
        $sql = "SELECT * FROM premium_warehouse_sales_report_purchase_{$mode}_tmp " .
            "WHERE item_id=%d AND warehouse=%d ORDER BY id $order";
        return DB::GetAll($sql, array($item_id, $warehouse));
    }

    public function store_temp_purchase($mode, $id, $item_id, $quantity, $warehouse,
                                        $net_value_int, $gross_value_int, $order_details_id) {
        $sql = "INSERT INTO premium_warehouse_sales_report_purchase_{$mode}_tmp VALUES (%d, %d, %d, %d, %d, %d, %d)";
        DB::Execute($sql, array($id, $item_id, $quantity, $warehouse,
            $net_value_int, $gross_value_int, $order_details_id));
    }

    public function update_temp_quantity($mode, $temp_id, $quantity) {
        $this->_check_mode($mode);
        $sql = "UPDATE premium_warehouse_sales_report_purchase_{$mode}_tmp SET quantity=%d WHERE id=%d";
        DB::Execute($sql, array($quantity, $temp_id));
    }

    public function update_temp_warehouse($mode, $temp_id, $warehouse) {
        $this->_check_mode($mode);
        $sql = "UPDATE premium_warehouse_sales_report_purchase_{$mode}_tmp SET warehouse=%d WHERE id=%d";
        DB::Execute($sql, array($warehouse, $temp_id));
    }

    public function increase_temp_ids($mode, $temp_id) {
        $sql = "UPDATE premium_warehouse_sales_report_purchase_{$mode}_tmp SET id=id+1 WHERE id>%d";
        DB::Execute($sql, array($temp_id));
    }

    public function delete_temp($mode, $temp_id) {
        $this->_check_mode($mode);
        $sql = "DELETE FROM premium_warehouse_sales_report_purchase_{$mode}_tmp WHERE id=%d";
        DB::Execute($sql, array($temp_id));
    }

    public function get_currency_rate($order_id, $currency) {
        $sql = 'SELECT exchange_rate FROM premium_warehouse_sales_report_exchange WHERE order_id=%d AND currency=%d';
        return DB::GetOne($sql, array($order_id, $currency));
    }

    public function get_tax_rate($tax_rate_id) {
        return Data_TaxRatesCommon::get_tax_rate($tax_rate_id);
    }

    public function store_earning($order_details_id, $qty_lifo, $qty_fifo,
                                  $gross_value_lifo, $gross_value_fifo,
                                  $net_value_lifo, $net_value_fifo) {
        $sql = 'INSERT INTO premium_warehouse_sales_report_earning VALUES(%d,%d,%d,%d,%d,%d,%d)';
        DB::Execute($sql, array($order_details_id, $qty_lifo, $qty_fifo,
            $gross_value_lifo, $gross_value_fifo,
            $net_value_lifo, $net_value_fifo));
    }

    public function store_related_order_details($mode, $sale_order_details_id, $purchase_order_details_ids) {
        $this->_check_mode($mode);
        $sql = "INSERT INTO premium_warehouse_sales_report_related_order_details_{$mode} VALUES(%d, %d)";
        foreach ($purchase_order_details_ids as $id) {
            DB::Execute($sql, array($sale_order_details_id, $id));
        }
    }

    private function _get_purchase_transactions() {
        $sql = 'SELECT ' .
            'od.id AS order_details_id,' .
            'od.f_item_name AS item_id,' .
            'od.f_quantity AS quantity,' .
            'od.f_tax_rate AS tax_rate,' .
            'od.f_net_price AS net_price,' .
            'o.f_warehouse AS warehouse,' .
            'o.id AS order_id ' .
            'FROM premium_warehouse_items_orders_details_data_1 AS od ' .
            'INNER JOIN premium_warehouse_items_orders_data_1 AS o ' .
            'ON o.id=od.f_transaction_id WHERE od.active=1 AND o.f_transaction_type=0 AND o.f_status=20 ' .
            'ORDER BY o.f_transaction_date ASC, o.created_on ASC';
        return DB::Execute($sql);
    }

    private function _get_sale_transactions() {
        $sql = 'SELECT *, ' .
            'od.id AS order_details_id, ' .
            'od.f_item_name AS item_id, ' .
            'i.f_item_type as item_type, ' .
            'od.f_quantity AS quantity,' .
            'od.f_tax_rate AS tax_rate,' .
            'od.f_net_price AS net_price,' .
            'o.id AS order_id, ' .
            'o.f_transaction_type as transaction_type, ' .
            'o.f_warehouse AS warehouse, ' .
            'o.f_target_warehouse AS target_warehouse ' .
            'FROM premium_warehouse_items_orders_details_data_1 AS od ' .
            'INNER JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id ' .
            'INNER JOIN premium_warehouse_items_orders_edit_history AS h ON h.premium_warehouse_items_orders_id=o.id ' .
            'INNER JOIN premium_warehouse_items_data_1 AS i ON i.id=od.f_item_name ' .
            'WHERE od.active=1 AND (o.f_transaction_type=1 OR o.f_transaction_type=4) AND o.f_status=20 ' .
            'GROUP BY od.id ORDER BY MAX(h.edited_on) ASC';
        return DB::Execute($sql);
    }

}

class Premium_Warehouse_SalesReport_Calculator {

    private $currency;
    private $currency_precision;
    private $currency_multiplier;
    private $db;
    private $sale_ctr = 0;

    public static function default_instance() {
        return new self(new Premium_Warehouse_SalesReport_CalculatorDB());
    }

    public function __construct(Premium_Warehouse_SalesReport_CalculatorDB $db) {
        $this->db = $db;
    }

    public function set_currency($currency_id) {
        $this->currency = $currency_id;
    }

    public function set_currency_precision($precision) {
        $this->currency_precision = $precision;
        $this->currency_multiplier = pow(10, $precision);
    }

    private function _currency_exchange($val, $currency, $order_id) {
        $rate = $this->db->get_currency_rate($order_id, $currency);
        if (!$rate) $rate = 0;
        return $val * $rate;
    }

    private function _decode_currency_field($value) {
        return Utils_CurrencyFieldCommon::get_values($value);
    }

    private function _calculate_gross_value($net_value, $tax_rate_id) {
        return round((100 + $this->db->get_tax_rate($tax_rate_id)) * $net_value / 100, $this->currency_precision);
    }

    private function _price_to_int($value) {
        return (int)($value * $this->currency_multiplier);
    }

    private function _calculate_values_in_proper_currency($currency_field_value, $order_id, $tax_rate_id) {
        list($net_value, $net_currency) = $this->_decode_currency_field($currency_field_value);
        if ($net_currency != $this->currency)
            $net_value = $this->_currency_exchange($net_value, $net_currency, $order_id);
        $gross_value = $this->_calculate_gross_value($net_value, $tax_rate_id);
        return array($net_value, $gross_value);
    }

    private function _handle_purchase($purchase) {

        list($net_value, $gross_value) = $this->_calculate_values_in_proper_currency(
            $purchase['net_price'], $purchase['order_id'], $purchase['tax_rate']);

        $this->db->store_temp_purchase('fifo',
            $this->sale_ctr, $purchase['item_id'], $purchase['quantity'], $purchase['warehouse'],
            $this->_price_to_int($net_value), $this->_price_to_int($gross_value), $purchase['order_details_id']);

        $this->db->store_temp_purchase('lifo',
            $this->sale_ctr, $purchase['item_id'], $purchase['quantity'], $purchase['warehouse'],
            $this->_price_to_int($net_value), $this->_price_to_int($gross_value), $purchase['order_details_id']);

        $this->sale_ctr += 1;
    }

    private function _handle_sale($sale) {
        if ($sale['transaction_type'] == 1) {
            // regular sale
            $this->_handle_regular_sale($sale);
        } else {
            // inventory adjustment
            $this->_handle_inventory_adjustment($sale);
        }
    }

    /**
     * Add int values of earnings into sale transaction.
     * @param $sale
     */
    private function _add_int_values($sale) {
        list($net_value, $gross_value) = $this->_calculate_values_in_proper_currency(
            $sale['net_price'], $sale['order_id'], $sale['tax_rate']);
        $sale['net_value_int'] = $this->_price_to_int($net_value);
        $sale['gross_value_int'] = $this->_price_to_int($gross_value);
        return $sale;
    }

    private function _handle_regular_sale($sale) {
        $sale = $this->_add_int_values($sale);

        if ($sale['item_type'] > 1) { // non-inventory item - whole price is our earning
            $this->db->store_earning($sale['order_details_id'],
                $sale['quantity'], $sale['quantity'],
                $sale['gross_value_int'], $sale['gross_value_int'],
                $sale['net_value_int'], $sale['net_value_int']);
            return;
        }

        list($earning_net_fifo, $earning_gross_fifo, $qty_fifo) = $this->_calculate_earnings('fifo', $sale);
        list($earning_net_lifo, $earning_gross_lifo, $qty_lifo) = $this->_calculate_earnings('lifo', $sale);

        $this->db->store_earning(
            $sale['order_details_id'], $qty_lifo, $qty_fifo,
            $earning_gross_lifo, $earning_gross_fifo,
            $earning_net_lifo, $earning_net_fifo);
    }

    private function _calculate_earnings($mode, $sale) {
        $earning_gross = $earning_net = 0;

        $sold_qty = $sale['quantity'];
        $items = $this->db->temp($mode, $sale['item_id'], $sale['warehouse']);
        $purchase_orders_details_ids = array();
        foreach ($items as $item) {
            if ($sold_qty > 0) {
                $qty = min($sold_qty, $item['quantity']);
                $sold_qty -= $qty;
                $earning_gross += ($sale['gross_value_int'] - $item['gross_price']) * $qty;
                $earning_net += ($sale['net_value_int'] - $item['net_price']) * $qty;
                $purchase_orders_details_ids[] = $item['order_details_id'];
                if ($item['quantity'] > $qty)
                    $this->db->update_temp_quantity($mode, $item['id'], $item['quantity'] - $qty);
                else
                    $this->db->delete_temp($mode, $item['id']);
            } else break;
        }
        $qty = $sale['quantity'] - $sold_qty;
        $this->db->store_related_order_details($mode, $sale['order_details_id'], $purchase_orders_details_ids);
        return array($earning_net, $earning_gross, $qty);
    }

    private function _handle_inventory_adjustment($sale) {
        foreach (array('fifo', 'lifo') as $mode) {
            $trans_qty = $sale['quantity'];
            $items = $this->db->temp($mode, $sale['item_id'], $sale['warehouse']);
            foreach ($items as $item) {
                if ($trans_qty > 0) {
                    $qty = min($trans_qty, $item['quantity']);
                    $trans_qty -= $qty;
                    if ($item['quantity'] > $qty) {
                        // move part of items to another warehouse
                        $this->db->update_temp_quantity($mode, $item['id'], $item['quantity'] - $qty);
                        $this->db->increase_temp_ids($mode, $item['id']);
                        $this->db->store_temp_purchase($mode, $item['id'] + 1, $item['item_id'], $qty,
                            $sale['target_warehouse'], $item['net_price'], $item['gross_price'],
                            $item['order_details_id']);
                    } else
                        $this->db->update_temp_warehouse($mode, $item['id'], $sale['target_warehouse']);
                } else break;
            }
        }
    }

    public function calculate() {

        // init values and perform cleanup
        $this->sale_ctr = 0;
        $this->db->clean_up_data();

        // at first process all purchase transactions
        while ($purchase = $this->db->next_purchase()) {
            $this->_handle_purchase($purchase);
        }

        while ($sale = $this->db->next_sale()) {
            $this->_handle_sale($sale);
        }
    }

}