<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-wholesale
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Wholesale extends Module {
    private $rb;

    public function body() {
        if(!DB::GetOne('SELECT 1 FROM premium_warehouse_distributor_data_1 WHERE active=1'))
            return $this->dists();
        $tb = & $this->init_module('Utils/TabbedBrowser');
        $tb->set_tab($this->t('Items'), array($this,'items'));
        $tb->set_tab($this->t('Distributors'), array($this,'dists'));
        $this->display_module($tb);
    }

    public function items() {
        $gb = $this->init_module('Utils/GenericBrowser', null, 'wholesale_items_addon');
        $gb->set_table_columns(array(
            array('name'=>$this->t('Status'), 'width'=>6, 'wrapmode'=>'nowrap', 'order'=>'item_id'),
            array('name'=>$this->t('Item Name'), 'width'=>40, 'wrapmode'=>'nowrap', 'order'=>'distributor_item_name', 'search'=>'distributor_item_name'),
            array('name'=>$this->t('Distributor'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'distributor_id'),
            array('name'=>$this->t('Distributor Code'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'internal_key', 'search'=>'internal_key'),
            array('name'=>$this->t('Price'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'price'),
            array('name'=>$this->t('Quantity'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'quantity'),
            array('name'=>$this->t('Quantity Details'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'quantity_info'),
            array('name'=>$this->t('Distributor Category'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'distributor_category', 'search'=>'f_foreign_category_name'),
            array('name'=>$this->t('Manufacturer'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'manufacturer', 'search'=>'f_company_name'),
            array('name'=>$this->t('MPN'), 'width'=>3, 'wrapmode'=>'nowrap', 'order'=>'manufacturer_part_number', 'search'=>'manufacturer_part_number'),
            array('name'=>$this->t('UPC'), 'width'=>3, 'wrapmode'=>'nowrap', 'order'=>'upc', 'search'=>'upc')
        ));

        $form = $this->init_module('Libs/QuickForm');
        $form->addElement('select','link_status','Show',array('all'=>'all items','linked'=>'only linked items','unlinked'=>'only unlinked items'),array('onChange'=>$form->get_submit_form_js()));
        $form->addElement('checkbox','available','Only available','',array('onChange'=>$form->get_submit_form_js()));
        $link_status = & $this->get_module_variable('link_status','all');
        $available = & $this->get_module_variable('available',true);
        $form->setDefaults(array('link_status'=>$link_status,'available'=>$available));
        if($form->validate()) {
            $link_status = $form->exportValue('link_status');
            $available = $form->exportValue('available');
        }
        $form->display();

        $dists_tmp = Utils_RecordBrowserCommon::get_records('premium_warehouse_distributor',array(),array('Name'));
        $dists = array();
        foreach($dists_tmp as $tmp)
            $dists[$tmp['id']] = Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_distributor','Name',$tmp);
        unset($dists_tmp);

        $where = $gb->get_search_query(false,true);
        if ($where) $where = ' AND '.$where;
        if($link_status!='all') {
            if($link_status=='linked')
                $where .= ' AND item_id is not null';
            else
                $where .= ' AND item_id is null';
        }
        if($available)
            $where .= ' AND quantity>0';
        $limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_wholesale_items LEFT JOIN company_data_1 c ON c.id=manufacturer LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE distributor_id IN ('.implode(',',array_keys($dists)).') '.$where));
        $gb->set_default_order(array('Item Name'=>'ASC'));
        $order = $gb->get_query_order();

        $ret = DB::SelectLimit('SELECT *, c.f_company_name as manufacturer_name, whl.id AS id,cat.f_foreign_category_name as category FROM premium_warehouse_wholesale_items AS whl LEFT JOIN company_data_1 c ON c.id=whl.manufacturer LEFT JOIN premium_warehouse_items_data_1 AS itm ON itm.id=whl.item_id LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE distributor_id IN ('.implode(',',array_keys($dists)).') '.$where.' '.$order, $limit['numrows'], $limit['offset']);

        while ($row=$ret->FetchRow()) {
            if ($row['item_id']) {
//              $item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $row['item_id']);
                $sku = Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items', 'sku', $row['item_id']);
                $sku .= '<a '.$this->create_callback_href(array($this, 'unlink_item'), array($row['id'])).'><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','cancel.png').'" border="0" /></a>';
            } else {
                $form = $this->init_module('Libs/QuickForm');
                $field = 'link_it_'.$row['id'];
                $form->addElement('autocomplete', $field, '', array($this->get_type().'Common', 'item_match_autocomplete'), array($row['distributor_id']));
                $form->addElement('submit', 'submit', '');
                $theme = $this->init_module('Base/Theme');
                $form->assign_theme('form', $theme);
                $theme->assign('field_name', $field);
                $theme->assign('submit_button', '<a '.$form->get_submit_form_href().'><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','link.png').'" border="0" /></a>');
                $theme->assign('cancel_button', '<a href="javascript:void(0);" onclick="$(\'link_it_'.$row['id'].'_form\').style.display=\'none\';$(\'link_it_'.$row['id'].'_choice\').style.display=\'inline\'"><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','cancel.png').'" border="0" /></a>');
                ob_start();
                $theme->display('match_form');
                if ($form->validate()) {
                    $sku = $form->exportValue($field);
                    $item_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'sku', $sku);
                    DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE id=%d', array($item_id, $row['id']));
                    location(array());
                } else {
                    $sku =  '<span id="link_it_'.$row['id'].'_form" style="display:none;">'.
                                ob_get_clean().
                            '</span>'.
                            '<span id="link_it_'.$row['id'].'_choice">'.
                                '<a href="javascript:void(0);" onclick="$(\'link_it_'.$row['id'].'_form\').style.display=\'inline\';$(\'link_it_'.$row['id'].'_choice\').style.display=\'none\'"><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','link.png').'" border="0" /></a>'.
                            '</span>';
                }
            }
            $gb->add_row(
                $sku,
                $row['distributor_item_name'],
                $dists[$row['distributor_id']],
                array('value'=>$row['internal_key'], 'style'=>'text-align:right;'),
                array('value'=>Utils_CurrencyFieldCommon::format($row['price'],$row['price_currency']), 'style'=>'text-align:right;'),
                array('value'=>$row['quantity'], 'style'=>'text-align:right;'),
                $row['quantity_info'],
                $row['category'],
                $row['manufacturer_name'],
                $row['manufacturer_part_number'],
                $row['upc']
            );
        }
        $this->display_module($gb);
    }

    public function dists() {
        Base_ActionBarCommon::add('search','Scan plugins', $this->create_callback_href(array('Premium_Warehouse_WholesaleCommon','scan_for_plugins')));
        $this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_distributor','premium_warehouse_distributor_module');
        $this->display_module($this->rb);
    }

    public function attachment_addon($arg){
        $a = $this->init_module('Utils/Attachment',array('Premium/Warehouse/Wholesale/'.$arg['id']));
        $a->set_view_func(array('Premium_Warehouse_WholesaleCommon','search_format'),array($arg['id']));
        $a->additional_header('Distributor: '.$arg['name']);
        $a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
        $a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
        $this->display_module($a);
    }

    public function unlink_item($id) {
        DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=NULL WHERE id=%d', array($id));
        return false;
    }

    public function items_addon($arg) {
        $gb = $this->init_module('Utils/GenericBrowser', null, 'wholesale_items_addon');
        $gb->set_table_columns(array(
            array('name'=>$this->t('Status'), 'width'=>6, 'wrapmode'=>'nowrap', 'order'=>'item_id'),
            array('name'=>$this->t('Item Name'), 'width'=>40, 'wrapmode'=>'nowrap', 'order'=>'distributor_item_name', 'search'=>'distributor_item_name'),
            array('name'=>$this->t('Distributor Code'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'internal_key', 'search'=>'internal_key'),
            array('name'=>$this->t('Price'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'price'),
            array('name'=>$this->t('Quantity'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'quantity'),
            array('name'=>$this->t('Quantity Details'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'quantity_info'),
            array('name'=>$this->t('Distributor Category'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'distributor_category', 'search'=>'f_foreign_category_name'),
            array('name'=>$this->t('Manufacturer'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'manufacturer', 'search'=>'f_company_name'),
            array('name'=>$this->t('MPN'), 'width'=>3, 'wrapmode'=>'nowrap', 'order'=>'manufacturer_part_number', 'search'=>'manufacturer_part_number'),
            array('name'=>$this->t('UPC'), 'width'=>3, 'wrapmode'=>'nowrap', 'order'=>'upc', 'search'=>'upc')
        ));

        $form = $this->init_module('Libs/QuickForm');
        $form->addElement('select','link_status','Show',array('all'=>'all items','linked'=>'only linked items','unlinked'=>'only unlinked items'),array('onChange'=>$form->get_submit_form_js()));
        $form->addElement('checkbox','available','Only available','',array('onChange'=>$form->get_submit_form_js()));
        $link_status = & $this->get_module_variable('link_status','all');
        $available = & $this->get_module_variable('available',true);
        $form->setDefaults(array('link_status'=>$link_status,'available'=>$available));
        if($form->validate()) {
            $link_status = $form->exportValue('link_status');
            $available = $form->exportValue('available');
        }
        $form->display();

        $where = $gb->get_search_query(false,true);
        if ($where) $where = ' AND ('.$where.')';
        if($link_status!='all') {
            if($link_status=='linked')
                $where .= ' AND item_id is not null';
            else
                $where .= ' AND item_id is null';
        }
        if($available)
            $where .= ' AND quantity>0';
//      $limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_wholesale_items WHERE distributor_id=%d AND (quantity!=%d OR quantity_info!=%s) '.$where, array($arg['id'],0,'')));
        $limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_wholesale_items LEFT JOIN company_data_1 c ON c.id=manufacturer LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE distributor_id=%d '.$where, array($arg['id'])));
        $gb->set_default_order(array('Item Name'=>'ASC'));
        $order = $gb->get_query_order();

        $form2 = $this->init_module('Libs/QuickForm');
        $form2->addElement('text', 'item_name', $this->t('Item Name'),array('id'=>'add_item_name'));
        $form2->addElement('commondata', 'item_type', $this->t('Item Type'), 'Premium_Warehouse_Items_Type', array('empty_option'=>true, 'order_by_key'=>true));
        $form2->addElement('text', 'product_code', $this->t('Product Code'));
        $form2->addElement('text', 'manufacturer_part_number', $this->t('Manufacturer Part Number'));
        $form2->addElement('text', 'weight', $this->t('Weight'));
        $taxes = array(''=>'---',)+Data_TaxRatesCommon::get_tax_rates();
        $form2->addElement('select', 'tax_rate', $this->t('Tax Rate'),$taxes);
        $ecommerce_on = ModuleManager::is_installed('Premium_Warehouse_eCommerce')!=-1;
        if($ecommerce_on) {
            $form2->addElement('checkbox', 'ecommerce', $this->t('eCommerce publish'));
            $form2->addElement('static', '3rd party', $this->t('Available data'),'<iframe id="3rdp_info_frame" style="width:300px; height:100px;border:0px"></iframe>');
        }
        $form2->setDefaults(array('item_type'=>1, 'tax_rate'=>$arg['tax_rate']));
        $lp = $this->init_module('Utils_LeightboxPrompt');
        $lp->add_option('add', 'Add', '', $form2);
        $this->display_module($lp, array($this->t('Create new item'), array('internal_id','item_name')));
        $vals = $lp->export_values();
        if ($vals) {
            $validate = true;
            if (!isset($vals['form']['item_name']) || !$vals['form']['item_name']) {
                Epesi::alert($this->ht('Item name is required'));
                $validate = false;
            }
            if (!isset($vals['form']['item_type']) || $vals['form']['item_type']==='') {
                Epesi::alert($this->ht('Item type is required'));
                $validate = false;
            }
            if(!isset($vals['form']['weight']) || !is_numeric($vals['form']['weight'])) {
                Epesi::alert($this->ht('Weight is required and should be numeric'));
                $validate = false;
            }

            if ($validate) {
                list($dist_cat,$manufacturer,$mpn,$upc) = DB::GetRow('SELECT distributor_category,manufacturer,manufacturer_part_number,upc FROM premium_warehouse_wholesale_items WHERE id=%d',array($vals['params']['internal_id']));
                $categories = Utils_RecordBrowserCommon::get_record('premium_warehouse_distr_categories',$dist_cat);
                $new_vals = array('category'=>$categories['epesi_category'],'manufacturer'=>$manufacturer,'vendor'=>$arg['company']);
                if($mpn && (!isset($vals['form']['manufacturer_part_number']) || !$vals['form']['manufacturer_part_number'])) $new_vals['manufacturer_part_number']=$mpn;
                if($upc) $new_vals['upc']=$upc;
                $iid = Utils_RecordBrowserCommon::new_record('premium_warehouse_items', array_merge($vals['form'],$new_vals));
                DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE id=%d', array($iid, $vals['params']['internal_id']));
                if($ecommerce_on && isset($vals['form']['ecommerce']) && $vals['form']['ecommerce']) {
                    Premium_Warehouse_eCommerceCommon::publish_warehouse_item($iid);
                }
            }
        }

        // $ret = DB::SelectLimit('SELECT *, whl.id AS id,cat.f_foreign_category_name as category FROM premium_warehouse_wholesale_items AS whl LEFT JOIN premium_warehouse_items_data_1 AS itm ON itm.id=whl.item_id LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE distributor_id=%d AND (quantity!=%d OR quantity_info!=%s) '.$where.' '.$order, $limit['numrows'], $limit['offset'], array($arg['id'],0,''));

        $ret = DB::SelectLimit('SELECT *, c.f_company_name as manufacturer_name, whl.id AS id,cat.f_foreign_category_name as category FROM premium_warehouse_wholesale_items AS whl LEFT JOIN company_data_1 c ON c.id=whl.manufacturer LEFT JOIN premium_warehouse_items_data_1 AS itm ON itm.id=whl.item_id LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE distributor_id=%d '.$where.' '.$order, $limit['numrows'], $limit['offset'], array($arg['id']));

        while ($row=$ret->FetchRow()) {
            $row['distributor_item_name'] = strip_tags($row['distributor_item_name']);
            if ($row['item_id']) {
//              $item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $row['item_id']);
                $sku = Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items', 'sku', $row['item_id']);
                $sku .= '<a '.$this->create_callback_href(array($this, 'unlink_item'), array($row['id'])).'><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','cancel.png').'" border="0" /></a>';
            } else {
                $form = $this->init_module('Libs/QuickForm');
                $field = 'link_it_'.$row['id'];
                $form->addElement('autocomplete', $field, '', array($this->get_type().'Common', 'item_match_autocomplete'), array($row['distributor_id']));
                $form->addElement('submit', 'submit', '');
                $theme = $this->init_module('Base/Theme');
                $form->assign_theme('form', $theme);
                $theme->assign('field_name', $field);
                $theme->assign('submit_button', '<a '.$form->get_submit_form_href().'><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','link.png').'" border="0" /></a>');
                $theme->assign('cancel_button', '<a href="javascript:void(0);" onclick="$(\'link_it_'.$row['id'].'_form\').style.display=\'none\';$(\'link_it_'.$row['id'].'_choice\').style.display=\'inline\'"><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','cancel.png').'" border="0" /></a>');
                ob_start();
                $theme->display('match_form');
                if ($form->validate()) {
                    $sku = $form->exportValue($field);
                    $item_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'sku', $sku);
                    DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE id=%d', array($item_id, $row['id']));
                    location(array());
                } else {
                    $sku =  '<span id="link_it_'.$row['id'].'_form" style="display:none;">'.
                                ob_get_clean().
                            '</span>'.
                            '<span id="link_it_'.$row['id'].'_choice">'.
                                '<a href="javascript:void(0);" onclick="$(\'link_it_'.$row['id'].'_form\').style.display=\'inline\';$(\'link_it_'.$row['id'].'_choice\').style.display=\'none\'"><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','link.png').'" border="0" /></a>'.
                                '<a '.$lp->get_href(array($row['id'])).' onMouseUp="$(\'add_item_name\').value=\''.escapeJS($row['distributor_item_name']).'\';var rd=$(\'3rdp_info_frame\');if(rd)rd.src=\'modules/Premium/Warehouse/Wholesale/3rdp.php?'.http_build_query(array('upc'=>$row['upc'],'mpn'=>$row['manufacturer_part_number'],'man'=>$row['manufacturer_name'])).'\';"><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','add_item.png').'" border="0" /></a>'.
                            '</span>';
                }
            }
            $gb->add_row(
                $sku,
//              $item['item_name'],
                $row['distributor_item_name'],
                array('value'=>$row['internal_key'], 'style'=>'text-align:right;'),
                array('value'=>Utils_CurrencyFieldCommon::format($row['price'],$row['price_currency']), 'style'=>'text-align:right;'),
                array('value'=>$row['quantity'], 'style'=>'text-align:right;'),
                $row['quantity_info'],
                $row['category'],
                $row['manufacturer_name'],
                $row['manufacturer_part_number'],
                $row['upc']
            );
        }
        $this->display_module($gb);
    }

    public function distributors_addon($arg) {
        $gb = $this->init_module('Utils/GenericBrowser', null, 'wholesale_items_addon');
        $gb->set_table_columns(array(
            array('name'=>$this->t('Distributor'), 'width'=>40, 'wrapmode'=>'nowrap'),
            array('name'=>$this->t('Distributor Code'), 'width'=>7, 'wrapmode'=>'nowrap'),
            array('name'=>$this->t('Price'), 'width'=>7, 'wrapmode'=>'nowrap'),
            array('name'=>$this->t('Quantity'), 'width'=>7, 'wrapmode'=>'nowrap'),
            array('name'=>$this->t('Quantity Details'), 'width'=>7, 'wrapmode'=>'nowrap'),
            array('name'=>$this->t('Last Update'), 'width'=>7, 'wrapmode'=>'nowrap')
        ));

/*
        $limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_wholesale_items WHERE item_id=%d AND (quantity!=%d OR quantity_info!=%s)', array($arg['id'],0,'')));
        $ret = DB::SelectLimit('SELECT * FROM premium_warehouse_wholesale_items WHERE item_id=%d AND (quantity!=%d OR quantity_info!=%s)', $limit['numrows'], $limit['offset'], array($arg['id'],0,''));
*/

        $limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_wholesale_items WHERE item_id=%d', array($arg['id'])));
        $ret = DB::SelectLimit('SELECT * FROM premium_warehouse_wholesale_items WHERE item_id=%d', $limit['numrows'], $limit['offset'], array($arg['id']));
        while ($row=$ret->FetchRow()) {
            $dist = Utils_RecordBrowserCommon::get_record('premium_warehouse_distributor', $row['distributor_id']);
            $gb->add_row(
                Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_distributor', 'name', $dist['id']),
                array('value'=>$row['internal_key'], 'style'=>'text-align:right;'),
                array('value'=>Utils_CurrencyFieldCommon::format($row['price'],$row['price_currency']), 'style'=>'text-align:right;'),
                array('value'=>$row['quantity'], 'style'=>'text-align:right;'),
                $row['quantity_info'],
                Base_RegionalSettingsCommon::time2reg($dist['last_update'],'without_seconds')
            );
        }
        $this->display_module($gb);
    }

    public function categories_addon($arg) {
        $rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_distr_categories');
        $order = array(array('distributor'=>$arg['id']), array('distributor'=>false), array('foreign_category_name'=>'ASC'));
        $rb->set_defaults(array('distributor'=>$arg['id']));
        $this->display_module($rb,$order,'show_data');
    }


    public function caption(){
        if (isset($this->rb)) return $this->rb->caption();
    }
}

?>
