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
            array('name'=>$this->t('Actions'), 'width'=>6, 'wrapmode'=>'nowrap', 'order'=>'item_id'),
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

        if($dists)
            $where = 'distributor_id IN ('.implode(',',array_keys($dists)).')';
        else    
            $where = '1=1';
        $where2 = $gb->get_search_query(false,true);
        if($where2) $where .= ' AND ('.$where2.')';
        if($link_status!='all') {
            if($link_status=='linked')
                $where .= ' AND item_id is not null';
            else
                $where .= ' AND item_id is null';
        }
        if($available)
            $where .= ' AND quantity>0';
        $limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_wholesale_items LEFT JOIN company_data_1 c ON c.id=manufacturer LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE '.$where));
        $gb->set_default_order(array($this->t('Item Name')=>'ASC'));
        $order = $gb->get_query_order();

        $ret = DB::SelectLimit('SELECT *, c.f_company_name as manufacturer_name, whl.id AS id,cat.f_foreign_category_name as category FROM premium_warehouse_wholesale_items AS whl LEFT JOIN company_data_1 c ON c.id=whl.manufacturer LEFT JOIN premium_warehouse_items_data_1 AS itm ON itm.id=whl.item_id LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE '.$where.' '.$order, $limit['numrows'], $limit['offset']);

        while ($row=$ret->FetchRow()) {
            if ($row['item_id']) {
                $sku = Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items', 'sku', $row['item_id']);
				$sku .= '<a '.$this->create_confirm_callback_href($this->t('Are you sure you want to unlink the item?'), array($this, 'unlink_item'), array($row['id'])).'><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','cancel.png').'" border="0" /></a>';
            } else {
				$sku = '';
            }
            $sku .= '<a '.$this->create_callback_href(array($this, 'jump_to_link_item'), array($row['id'])).'><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','edit.png').'" border="0" /></a>';
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

	public function jump_to_link_item($dist_item_id) {
		$box = ModuleManager::get_instance('/Base_Box|0');
        $box->push_main('Premium_Warehouse_Wholesale','link_item',array($dist_item_id));
		return false;
	}
		
	public function link_item($dist_item_id) {
		$box = ModuleManager::get_instance('/Base_Box|0');
		if ($this->is_back()) {
			$box->pop_main();
			return false;
		}

		$dist_item = DB::GetRow('SELECT * FROM premium_warehouse_wholesale_items WHERE id=%d', array($dist_item_id));
		if (!$dist_item) return false;

		load_js('modules/Premium/Warehouse/Wholesale/js/sync_item.js');
		
		$form = $this->init_module('Libs_QuickForm');
		$theme = $this->init_module('Base_Theme');

		$manufacturers = array(''=>'---');
		$rec = CRM_ContactsCommon::get_companies(array('group'=>'manufacturer'));
		foreach ($rec as $r)
			$manufacturers[$r['id']] = $r['company_name'];

		/** dist_item fields **/
		$theme->assign('dist_item_icon', Base_ThemeCommon::get_template_filename('Premium_Warehouse_Items','icon.png'));
		$theme->assign('dist_item_caption', $this->t('Distributor Item'));

		$form->addElement('static', 'dist_item_name', $this->t('Item Name'), $dist_item['distributor_item_name']);
		$form->addElement('static', 'dist_price', $this->t('Price'), Utils_CurrencyFieldCommon::format($dist_item['price'],$dist_item['price_currency']));
		$form->addElement('static', 'dist_category', $this->t('Category'), Utils_RecordBrowserCommon::get_value('premium_warehouse_distr_categories', $dist_item['distributor_category'], 'foreign_category_name'));
		
		$form->addElement('static', 'dist_manufacturer', $this->t('Manufacturer'), $manufacturers[$dist_item['manufacturer']]);
		$form->addElement('static', 'dist_mpn', $this->t('Manufacturer Part Number'), $dist_item['manufacturer_part_number']);
		$form->addElement('static', 'dist_upc', $this->t('UPC'), $dist_item['upc']);
		
		/** item fields **/

		$theme->assign('item_icon', Base_ThemeCommon::get_template_filename('Premium_Warehouse_Items','icon.png'));
		$theme->assign('item_caption', $this->t('Item'));
		
		$form->addElement('select', 'create_new_item', $this->t('Link to'), array(0=>'Select Existing Item', 1=>'Create New Item'), array('onchange'=>'create_new_item_change(this.value);','id'=>'create_new_item'));
		eval_js('create_new_item_change($("create_new_item").value);');

		/** existing item **/

		$el = $form->addElement('autocomplete', 'add_candidates', $this->t('Search for new candidates'), array('Premium_Warehouse_WholesaleCommon', 'item_suggestbox'));
		$el->on_hide_js('add_new_candidate();');

		$form->addElement('hidden', 'selected_existing_item', '', array('id'=>'selected_existing_item'));
		$already_linked_item = DB::GetOne('SELECT item_id FROM premium_warehouse_wholesale_items WHERE id=%d', array($dist_item_id));
		if ($already_linked_item)
			$form->setDefaults(array('selected_existing_item'=>$already_linked_item));
		
		$form->addElement('button', 'change_candidate', $this->t('Change Item'), array('onclick'=>'item_was_selected(false);$("selected_existing_item").value = null;'));
		eval_js('item_was_selected($("selected_existing_item").value);');

		$form->addElement('text', 'e_item_name', $this->t('Item Name'), array('id'=>'e_item_name'));
		$form->addElement('text', 'e_price', $this->t('Price'), array('id'=>'e_price'));
		$form->addElement('text', 'e_category', $this->t('Category'), array('id'=>'e_category'));
		
		$form->addElement('text', 'e_manufacturer', $this->t('Manufacturer'), array('id'=>'e_manufacturer'));
		$form->addElement('text', 'e_mpn', $this->t('Manufacturer Part Number'), array('id'=>'e_mpn'));
		$form->addElement('text', 'e_upc', $this->t('UPC'), array('id'=>'e_upc'));
		
		$current_item = $form->exportValue('selected_existing_item');
		$current_item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $current_item);
		if ($current_item) {
			$def = Premium_Warehouse_WholesaleCommon::get_item_basic_info($current_item);
			foreach ($def as $k=>$d) eval_js('$("_'.$k.'__display").innerHTML = "'.Epesi::escapeJS($d).'";');
		}
	
		$form->freeze(array('e_item_name', 'e_price', 'e_category', 'e_manufacturer', 'e_mpn', 'e_upc'));
		
		$gb = $this->init_module('Utils_GenericBrowser',null,'item_suggestions');
		$gb->set_table_columns(array(
			'Item Name',
			'Category',
			'Suggested price',
			'Manufacturer',
			'Manufacturer Part Number',
			'UPC'
		));
		
		// find similar items
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array('(manufacturer_part_number'=>$dist_item['manufacturer_part_number'], '|upc'=>$dist_item['upc']),array(),array(),20);
		foreach ($items as $k=>$v) {
			$item_info = Premium_Warehouse_WholesaleCommon::get_item_basic_info($v);
			$gb_row = $gb->get_new_row();
			$gb_row->add_data_array(array_values($item_info));
			$gb_row->add_action('href="javascript:void(0);" onclick="select_candidate_to_use(event.target)"','Restore','Select this item');
			$gb_row->set_attrs('id="candidates_item_'.$v['id'].'" data="'.$v['id'].'__'.implode('__', $item_info).'"');
		}
		
		$gb_row = $gb->get_new_row();
		$gb_row->add_data('','','','','','');
		$gb_row->add_action('href="javascript:void(0);" onclick="select_candidate_to_use(event.target)"','Restore','Select this item');
		$gb_row->set_attrs('style="display:none;" id="add_candidates_row"');
		
		ob_start();
		$this->display_module($gb);
		$table = ob_get_clean();
		
		$theme->assign('item_suggestions_table', $table);

		/** new item fields **/

		$item_types = Utils_CommonDataCommon::get_array('Premium_Warehouse_Items_Type', true);
        $taxes = array(''=>'---',)+Data_TaxRatesCommon::get_tax_rates();

		$form->addElement('text', 'n_item_name', $this->t('Item Name'), array('id'=>'n_item_name'));
		$form->addElement('select', 'n_item_type', $this->t('Item Type'), $item_types, array('id'=>'n_item_type'));
		$form->addElement('select', 'n_tax_rate', $this->t('Tax Rate'), $taxes, array('id'=>'n_tax_rate'));

		$form->addElement('select', 'n_manufacturer', $this->t('Manufacturer'), $manufacturers, array('id'=>'n_manufacturer'));
		$form->addElement('text', 'n_create_manufacturer', $this->t('Manufacturer'), array('id'=>'n_create_manufacturer'));
		$form->addElement('checkbox', 'n_enable_create_manufacturer', $this->t('Create New Manufacturer'), '', 'id="n_enable_create_manufacturer" onchange="create_or_select_manufacturer();" '.Utils_TooltipCommon::open_tag_attrs($this->t('Create a new manufacturer company')));
		eval_js('create_or_select_manufacturer();');
		
		$form->addElement('text', 'n_mpn', $this->t('Manufacturer Part Number'), array('id'=>'n_mpn'));
		$form->addElement('text', 'n_upc', $this->t('UPC'), array('id'=>'n_upc'));

		$form->addElement('automulti', 'n_category', $this->t('Category'), array('Premium_Warehouse_ItemsCommon', 'automulti_search'), array(), array('Premium_Warehouse_ItemsCommon', 'automulti_format'));
		
		$form->setDefaults(array(
			'n_item_name'=>$dist_item['distributor_item_name'],
			'n_category'=>Utils_RecordBrowserCommon::get_value('premium_warehouse_distr_categories', $dist_item['distributor_category'], 'epesi_category'),
			'n_manufacturer'=>$dist_item['manufacturer'],
			'n_mpn'=>$dist_item['manufacturer_part_number'],
			'n_upc'=>$dist_item['upc']
		));

		/** eCommerce fields **/

        $ecommerce_on = ModuleManager::is_installed('Premium_Warehouse_eCommerce')!=-1;
		$ecommerce_on = false;
		$theme->assign('ecommerce_on', $ecommerce_on);

        if($ecommerce_on) {
			$theme->assign('ecommerce_icon', Base_ThemeCommon::get_template_filename('Premium_Warehouse_eCommerce','icon.png'));
			$theme->assign('ecommerce_caption', $this->t('eCommerce'));
        }
		
		$form->addFormRule(array($this, 'link_item_rules'));

		if ($form->validate()) {
			$vals = $form->exportValues();
			if ($vals['create_new_item']==1) {
				if (isset($vals['n_enable_create_manufacturer']) && $vals['n_enable_create_manufacturer']) {
					$vals['n_manufacturer'] = Utils_RecordBrowserCommon::new_record('company', array('company_name'=>$vals['n_create_manufacturer'], 'group'=>'manufacturer'));
				}
				$item = array();
				foreach (array(
					'n_item_name' => 'item_name',
					'n_item_type' => 'item_type',
					'n_upc' => 'upc',
					'n_category' => 'category',
					'n_mpn' => 'manufacturer_part_number',
					'n_tax_rate' => 'tax_rate',
					'n_manufacturer' => 'manufacturer'
				) as $k=>$v) $item[$v] = $vals[$k];
				$dist = Utils_RecordBrowserCommon::get_record('premium_warehouse_distributor',$dist_item['distributor_id']);
				$item['vendor'] = $dist['company'];
				$item_id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items', $item);
			} else {
				$item_id = $vals['selected_existing_item'];
//				$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item_id);
			}
			DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE id=%d', array($item_id, $dist_item_id));
			$box->pop_main();
			return false;
		}

		Base_ActionBarCommon::add('delete', 'Cancel', $this->create_back_href());
		Base_ActionBarCommon::add('save', 'Save', $form->get_submit_form_href());
		
		$form->assign_theme('form', $theme);
		$theme->display('sync_item');

		return true;
	}

	public function link_item_rules($data) {
		if ($data['create_new_item']==0) {
			if (!$data['selected_existing_item']) return array('create_new_item'=>$this->t('You need to select an item first.'));
		} else {
			if (!$data['n_item_name']) return array('n_item_name'=>$this->t('Field required.'));
		}
		return true;
	}

    public function dists() {
        Base_ActionBarCommon::add('search','Scan plugins', $this->create_callback_href(array('Premium_Warehouse_WholesaleCommon','scan_for_plugins')));
        $this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_distributor','premium_warehouse_distributor_module');
        $this->display_module($this->rb);
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
            array('name'=>$this->t('Epesi Category'), 'width'=>3, 'wrapmode'=>'nowrap'),
            array('name'=>$this->t('Distributor Category'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'distributor_category', 'search'=>'f_foreign_category_name'),
            array('name'=>$this->t('Manufacturer'), 'width'=>7, 'wrapmode'=>'nowrap', 'order'=>'manufacturer', 'search'=>'f_company_name'),
            array('name'=>$this->t('MPN'), 'width'=>3, 'wrapmode'=>'nowrap', 'order'=>'manufacturer_part_number', 'search'=>'manufacturer_part_number'),
            array('name'=>$this->t('UPC'), 'width'=>3, 'wrapmode'=>'nowrap', 'order'=>'upc', 'search'=>'upc')
        ));
        
        $e_cats = DB::GetAssoc('SELECT f_foreign_category_name,1 FROM premium_warehouse_distr_categories_data_1 WHERE f_distributor=%d AND f_epesi_category!=""',array($arg['id']));

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
        $gb->set_default_order(array($this->t('Item Name')=>'ASC'));
        $order = $gb->get_query_order();

        $form2 = $this->init_module('Libs/QuickForm');
        $form2->addElement('text', 'item_name', $this->t('Item Name'),array('id'=>'add_item_name'));
        $form2->addElement('commondata', 'item_type', $this->t('Item Type'), 'Premium_Warehouse_Items_Type', array('empty_option'=>true, 'order_by_key'=>true));
        $form2->addElement('text', 'product_code', $this->t('Product Code'));
        $form2->addElement('static', 'manufacturer', $this->t('Manufacturer'),'<span id="add_item_man"></span>');
        $form2->addElement('text', 'manufacturer_part_number', $this->t('Manufacturer Part Number'),array('id'=>'add_item_mpn'));
        $form2->addElement('text', 'upc', $this->t('UPC'),array('id'=>'add_item_upc'));
        $form2->addElement('text', 'weight', $this->t('Weight'));
        $taxes = array(''=>'---',)+Data_TaxRatesCommon::get_tax_rates();
        $form2->addElement('select', 'tax_rate', $this->t('Tax Rate'),$taxes);
        $ecommerce_on = ModuleManager::is_installed('Premium_Warehouse_eCommerce')!=-1;
        if($ecommerce_on) {
            $form2->addElement('checkbox', 'ecommerce', $this->t('eCommerce publish'));
            $form2->addElement('static', '3rd party', $this->t('Available data'),'<span id="3rdp_info_frame"></span>');
        }
        $form2->setDefaults(array('item_type'=>1, 'tax_rate'=>$arg['tax_rate']));
        $lp = $this->init_module('Utils_LeightboxPrompt');
        $lp->add_option('add', 'Add', '', $form2);
        $this->display_module($lp, array($this->t('Create new item'), array('internal_id')));
        $vals = $lp->export_values();
        if ($vals) {
            $validate = true;
            if (!isset($vals['form']['item_name']) || !$vals['form']['item_name']) {
                Epesi::alert($this->t('Item name is required'));
                $validate = false;
            }
            if (!isset($vals['form']['item_type']) || $vals['form']['item_type']==='') {
                Epesi::alert($this->t('Item type is required'));
                $validate = false;
            }
            if(!isset($vals['form']['weight']) || !is_numeric($vals['form']['weight'])) {
                Epesi::alert($this->t('Weight is required and should be numeric'));
                $validate = false;
            }

            if ($validate) {
                list($dist_cat,$manufacturer,$mpn,$upc) = DB::GetRow('SELECT distributor_category,manufacturer,manufacturer_part_number,upc FROM premium_warehouse_wholesale_items WHERE id=%d',array($vals['params']['internal_id']));
                $categories = Utils_RecordBrowserCommon::get_record('premium_warehouse_distr_categories',$dist_cat);
                $new_vals = array('category'=>$categories['epesi_category'],'manufacturer'=>$manufacturer,'vendor'=>$arg['company']);
                if($mpn && (!isset($vals['form']['manufacturer_part_number']) || !$vals['form']['manufacturer_part_number'])) $new_vals['manufacturer_part_number']=$mpn;
                if($upc && (!isset($vals['form']['upc']) || !$vals['form']['upc'])) $new_vals['upc']=$upc;
                $iid = Utils_RecordBrowserCommon::new_record('premium_warehouse_items', array_merge($vals['form'],$new_vals));
                DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE id=%d', array($iid, $vals['params']['internal_id']));
                if($ecommerce_on && isset($vals['form']['ecommerce']) && $vals['form']['ecommerce']) {
                    load_js($this->get_module_dir().'/add_item.js');
                    eval_js('wholesale_add_item('.$iid.')');
                }
            }
        }

        // $ret = DB::SelectLimit('SELECT *, whl.id AS id,cat.f_foreign_category_name as category FROM premium_warehouse_wholesale_items AS whl LEFT JOIN premium_warehouse_items_data_1 AS itm ON itm.id=whl.item_id LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE distributor_id=%d AND (quantity!=%d OR quantity_info!=%s) '.$where.' '.$order, $limit['numrows'], $limit['offset'], array($arg['id'],0,''));

        $ret = DB::SelectLimit('SELECT *, c.f_company_name as manufacturer_name, whl.id AS id,cat.f_foreign_category_name as category FROM premium_warehouse_wholesale_items AS whl LEFT JOIN company_data_1 c ON c.id=whl.manufacturer LEFT JOIN premium_warehouse_items_data_1 AS itm ON itm.id=whl.item_id LEFT JOIN premium_warehouse_distr_categories_data_1 cat ON (cat.f_distributor=distributor_id AND cat.id=distributor_category) WHERE distributor_id=%d '.$where.' '.$order, $limit['numrows'], $limit['offset'], array($arg['id']));

        while ($row=$ret->FetchRow()) {
            $row['distributor_item_name'] = strip_tags($row['distributor_item_name']);
            if ($row['item_id']) {
                $sku = Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items', 'sku', $row['item_id']);
                $sku .= '<a '.$this->create_callback_href(array($this, 'unlink_item'), array($row['id'])).'><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','cancel.png').'" border="0" /></a>';
            } else {
				$sku =  '';
            }
			$sku .= '<a '.$this->create_callback_href(array($this, 'jump_to_link_item'), array($row['id'])).'><img src="'.Base_ThemeCommon::get_template_file('Premium/Warehouse/Wholesale','edit.png').'" border="0" /></a>';
            $gb->add_row(
                $sku,
//              $item['item_name'],
                $row['distributor_item_name'],
                array('value'=>$row['internal_key'], 'style'=>'text-align:right;'),
                array('value'=>Utils_CurrencyFieldCommon::format($row['price'],$row['price_currency']), 'style'=>'text-align:right;'),
                array('value'=>$row['quantity'], 'style'=>'text-align:right;'),
                $row['quantity_info'],
                isset($e_cats[$row['category']])?$this->t('Yes'):$this->t('No'),
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
            array('name'=>$this->t('Category'), 'width'=>7, 'wrapmode'=>'nowrap'),
            array('name'=>$this->t('Manufacturer'), 'width'=>7, 'wrapmode'=>'nowrap'),
            array('name'=>$this->t('MPN'), 'width'=>7, 'wrapmode'=>'nowrap'),
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
				Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_distr_categories', 'foreign_category_name', $row['distributor_category']),
				$row['manufacturer'],
				$row['manufacturer_part_number'],
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
