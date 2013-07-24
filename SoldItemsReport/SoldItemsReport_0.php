<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Adam Bukowski <abukowski@telaxus.com>
 * @copyright Copyright &copy; 2013, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-solditemsreport
 */

class Premium_Warehouse_SoldItemsReport extends Module {

    /** @var string $format Date format in columns */
    private $format;
    /** @var array $columns Columns in report */
    private $columns;
    /** @var Utils_RecordBrowser_Reports */
    private $rbr;
    /** @var Libs_QuickForm */
    private $form;

    private $group_template = array();

    public function construct() {
        $this->rbr = $this->init_module('Utils/RecordBrowser/Reports');
        $this->form = $this->init_module('Libs/QuickForm');
    }

    private function add_customer_select() {
        $customer_field = 'customer';
        $callback = array('CRM_ContactsCommon', 'display_company');
        $company_crits = array();
        $this->form->addElement('autoselect', $customer_field, __('Customer'), array(),
            array(
                array('CRM_ContactsCommon', 'autoselect_company_suggestbox'),
                array($company_crits, $callback)
            ), $callback, array('id' => $customer_field));
    }

    private function add_warehouse_select() {
        $warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse', array(), array(), array('warehouse' => 'ASC'));
        $warehouse_choice = array('' => '[' . __('All') . ']');
        $my_warehouse = Base_User_SettingsCommon::get('Premium_Warehouse', 'my_warehouse');
        if (!$my_warehouse) $my_warehouse = '';
        foreach ($warehouses as $v)
            $warehouse_choice[$v['id']] = $v['warehouse'];
        $this->form->addElement('select', 'warehouse', __('Warehouse'), $warehouse_choice);
        $this->form->setDefaults(array('warehouse' => $my_warehouse));
    }

    private function calculate_data($transaction_crits) {
        // necessary recordsets
        $transaction_rs = new RBO_RecordsetAccessor('premium_warehouse_items_orders');
        $items_sold_rs = new RBO_RecordsetAccessor('premium_warehouse_items_orders_details');
        $items_rs = new RBO_RecordsetAccessor('premium_warehouse_items');

        // calculations
        $transactions = $transaction_rs->get_records($transaction_crits);
        $transactions_ids = array_keys($transactions);
        // items sold for transactions
        $items_sold = $items_sold_rs->get_records(array('transaction_id' => $transactions_ids));

        $items_summary = array();
        foreach ($items_sold as $item_sold) {
            $item_details = & $items_summary[$item_sold->item_name];
            if (!is_array($item_details))
                $item_details = array('item' => $items_rs->get_record($item_sold->item_name),
                    'groups' => $this->group_template, 'total_quantity' => 0);

            $item_details['total_quantity'] += $item_sold->quantity;
            $item_groups = & $item_details['groups'];

            $date = $transactions[$item_sold->transaction_id]->transaction_date;
            $group = date($this->format, strtotime($date));
            $item_details = & $item_groups[$group];
            $item_details['Quantity'] += $item_sold->quantity;
            $item_details['Transactions'] += 1;
        }

        usort($items_summary, array($this, 'cmp_items_summary'));

        return $items_summary;
    }

    public function cmp_items_summary($a, $b) {
        return $a['total_quantity'] < $b['total_quantity'];
    }

    private function get_data() {
        $values = $this->rbr->display_date_picker(array(), $this->form);

        // Prepare transactions crits: sale, finalized, specific range, specific warehouse
        $transaction_crits = array(
            'transaction_type' => 1, // sale transactions
            'transaction_status' => 20, // finalized transactions
            '>=transaction_date' => $values['start'],
            '<=transaction_date' => $values['end']);

        $customer_id = & $values['other']['customer'];
        if ($customer_id) {
            $transaction_crits['company'] = $customer_id;
        }

        $warehouse = & $values['other']['warehouse'];
        if ($warehouse) {
            $transaction_crits['warehouse'] = $warehouse;
        }

        // HEADERS
        $header = array('Item');
        switch ($values['type']) {
            case 'day':
                $this->format = 'd M Y';
                break;
            case 'week':
                $this->format = 'W Y';
                break;
            case 'month':
                $this->format = 'M Y';
                break;
            case 'year':
                $this->format = 'Y';
                break;
        }
        foreach ($values['dates'] as $v) {
            $date = date($this->format, $v);
            $header[] = $date;
            $this->group_template[$date] = array('Quantity' => 0, 'Transactions' => 0);
        }
        $this->rbr->set_table_header($header);

        return $this->calculate_data($transaction_crits);
    }

    public function body() {
        $this->add_customer_select();
        $this->add_warehouse_select();

        $data = $this->get_data();

        $categories = array('Quantity'); //, 'Transactions');
        $format = array($categories[0] => 'numeric'); //, $categories[1] => 'numeric');

        $this->rbr->set_reference_records($data);
        $this->rbr->set_reference_record_display_callback(array($this, 'display_item'));
        $this->rbr->set_categories($categories);
        $this->rbr->set_summary('col', array('label'=>__('Total')));
        $this->rbr->set_summary('row', array('label'=>__('Total')));
        $this->rbr->set_format($format);

        $this->rbr->set_display_cell_callback(array($this, 'display_cells'));
        $this->rbr->set_pdf_title(__('Sold Items, %s', array(date('Y-m-d H:i:s'))));
        $this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
        $this->rbr->set_pdf_filename(__('Sold_Items_%s', array(date('Y_m_d__H_i_s'))));
        $this->rbr->set_bonus_width(100);
        $this->display_module($this->rbr);
    }

    public function display_item($data_row, $nolink) {
        $r = $data_row['item']->to_array();
        return Premium_Warehouse_ItemsCommon::display_sku($r, $nolink) . ' - ' .
            Premium_Warehouse_ItemsCommon::display_item_name($r, $nolink);
    }

    public function display_cells($data_row) {
        return $data_row['groups'];
    }
}

?>