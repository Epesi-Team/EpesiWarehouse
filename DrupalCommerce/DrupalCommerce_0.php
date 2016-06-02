<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * DrupalCommerce
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_DrupalCommerce extends Module {
	private $rb;
	private $recordset;
	private $caption;

	public function admin() {
		if($this->is_back()) {
			if($this->parent->get_type()=='Base_Admin')
				$this->parent->reset();
			else
				location(array());
			return;
		}
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$buttons = array();
//		$icon = Base_ThemeCommon::get_template_file($name,'icon.png');
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'setup_3rd_party_plugins')).'>'.__('3rd party info plugins').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'prices')).'>'.__('Automatic prices').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'availability')).'>'.__('Availability').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'features')).'>'.__('Features').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'order_status_change_email_page')).'>'.__('Order status change e-mails').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'parameters')).'>'.__('Parameters').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'parameter_groups')).'>'.__('Parameter Groups').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'drupal')).'>'.__('Drupal').'</a>',
						'icon'=>null);
		$theme = $this->pack_module('Base/Theme');
		$theme->assign('header', __('eCommerce settings'));
		$theme->assign('buttons', $buttons);
		$theme->display();
	}


	public function body() {
		$this->recordset = 'products';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_products');
		$this->rb->set_defaults(array('publish'=>1,'status'=>1));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));

		$opts = Premium_Warehouse_DrupalCommerceCommon::get_categories();
		$this->rb->set_custom_filter('items',array('type'=>'select','label'=>__('Category'),'args'=>$opts,'trans_callback'=>array('Premium_Warehouse_DrupalCommerceCommon', 'category_filter')));

		$this->display_module($this->rb);//,array(array('position'=>'ASC'),array(),$cols));
	}

	public function parameters() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$this->recordset = 'parameters';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameters');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function parameter_groups() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$this->recordset = 'parameter_groups';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameter_groups');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function availability() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$this->recordset = 'availability';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_availability');
		$this->display_module($this->rb);

		return true;
	}

	public function actions_for_position($r, $gb_row) {
		$tab = 'premium_ecommerce_'.$this->recordset;
		if(isset($_REQUEST['pos_action']) && $r['id']==$_REQUEST['pos_action'] && is_numeric($_REQUEST['old']) && is_numeric($_REQUEST['new'])) {
		    $crits = $this->rb->get_module_variable('crits_stuff',array());
		    if($_REQUEST['new']>0) {
			    $pos = Utils_RecordBrowserCommon::get_records($tab,array_merge($crits,array('>position'=>$_REQUEST['old'])),array('position'), array('position'=>'ASC'),1);
		    } else {
			    $pos = Utils_RecordBrowserCommon::get_records($tab,array_merge($crits,array('<position'=>$_REQUEST['old'])),array('position'), array('position'=>'DESC'),1);
		    }
		    if($pos) {
		    	$pos = array_shift($pos);
		    	$pos = $pos['position'];
		    	$recs = Utils_RecordBrowserCommon::get_records($tab,array('position'=>$pos), array('id'));
		    	foreach($recs as $rr)
				Utils_RecordBrowserCommon::update_record($tab,$rr['id'],array('position'=>$_REQUEST['old']));
    		    	Utils_RecordBrowserCommon::update_record($tab,$r['id'],array('position'=>$pos));
		    	location(array());
		    } else {
		    	Epesi::alert(__('This item is already on top/bottom'));
		    }
		}
		if($r['position']>0)
		    $gb_row->add_action(Module::create_href(array('pos_action'=>$r['id'],'old'=>$r['position'],'new'=>0)),'move-up');
		static $max;
		if(!isset($max))
		    $max = Utils_RecordBrowserCommon::get_records_count($tab);
		if($r['position']<$max-1)
    		    $gb_row->add_action(Module::create_href(array('pos_action'=>$r['id'],'old'=>$r['position'],'new'=>1)),'move-down');
	}

	public function parameter_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameter_labels');
		$order = array(array('parameter'=>$arg['id']), array('parameter'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('parameter'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function parameter_group_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_param_group_labels');
		$order = array(array('group'=>$arg['id']), array('group'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('group'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function availability_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_availability_labels');
		$order = array(array('availability'=>$arg['id']), array('availability'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('availability'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function descriptions_addon($arg) {
		$this->_descriptions_addon($arg['id']);
	}

	public function descriptions_addon_item($arg) {
		$pid = Utils_RecordBrowserCommon::get_id('premium_ecommerce_products', array('items'), array($arg['id']));
		$this->_descriptions_addon($pid);
	}

	private function _descriptions_addon($id) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_descriptions');
		$order = array(array('product'=>$id), array('product'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('product'=>$id,'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function cat_descriptions_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_cat_descriptions');
		$order = array(array('category'=>$arg['id']), array('category'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('category'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function parameters_addon($arg) {
		$this->_parameters_addon($arg['id']);
	}
	public function parameters_addon_item($arg) {
		$pid = Utils_RecordBrowserCommon::get_id('premium_ecommerce_products', array('items'), array($arg['id']));
		$this->_parameters_addon($pid);
	}
	private function _parameters_addon($id) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_products_parameters');
		$order = array(array('product'=>$id), array('product'=>false), array('language'=>'ASC','parameter'=>'ASC'));
		$rb->set_defaults(array('product'=>$id,'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'parameter'=>array('wrapmode'=>'nowrap'),
			'value'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function prices_addon($arg) {
		$this->_prices_addon($arg['id']);
	}
	public function prices_addon_item($arg) {
		$pid = Utils_RecordBrowserCommon::get_id('premium_ecommerce_products', array('items'), array($arg['id']));
		$this->_prices_addon($pid);
	}
	private function _prices_addon($id) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_prices');
		$order = array(array('product'=>$id), array('product'=>false), array('currency'=>'ASC'));
		$rb->set_defaults(array('product'=>$id));
		$rb->set_header_properties(array(
			'currency'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'price'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function associations_addon($arg) {
		$this->_associations_addon($arg['id']);
	}
	public function associations_addon_item($arg) {
		$pid = Utils_RecordBrowserCommon::get_id('premium_ecommerce_products', array('items'), array($arg['id']));
		$this->_associations_addon($pid);
	}
	private function _associations_addon($id) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_associations');
		$order = array(array('product'=>$id), array('product'=>false), array('associated_item'=>'ASC'));
		$rb->set_defaults(array('product'=>$id,'associated_item_price_change____'=>0,'associated_item_quantity'=>1));
		$this->display_module($rb,$order,'show_data');
	}

	public function orders_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_orders');
		//$order = array(array('transaction_id'=>$arg['id']), array('transaction_id'=>false));
		$ord_id = Premium_Warehouse_DrupalCommerceCommon::orders_get_record();
		$this->display_module($rb,array('view',$ord_id,null,false),'view_entry');
		if(Base_AclCommon::i_am_admin())
    		Base_ActionBarCommon::add('edit', __('Edit ecommerce'), $this->create_callback_href(array($this,'edit_ecommerce_order'),$ord_id));
	}

	public function edit_ecommerce_order($id) {
        $x = ModuleManager::get_instance('/Base_Box|0');
        if (!$x) trigger_error('There is no base box module instance',E_USER_ERROR);
	    $x->push_main('Utils/RecordBrowser','view_entry',array('edit', $id, array(), true),array('premium_ecommerce_orders'));
	}

	public function order_status_change_email_page() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_emails');
		$this->display_module($this->rb);

        return true;
	}

	public function edit_variable($header, $v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$f = $this->init_module('Libs/QuickForm');

		$f->addElement('header',null,$header);

		$fck = & $f->addElement('ckeditor', 'content', __('Content'));
		$fck->setFCKProps('800','300',true);

		$f->setDefaults(array('content'=>Variable::get($v,false)));

		Base_ActionBarCommon::add('save',__('Save'),$f->get_submit_form_href());

		if($f->validate()) {
			$ret = $f->exportValues();
			$content = str_replace("\n",'',$ret['content']);
			Variable::set($v,$content);
			Base_StatusBarCommon::message(__('Page saved'));
			return false;
		}
		$f->display();
		return true;
	}

	public function edit_variable_mail($header, $v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$f = $this->init_module('Libs/QuickForm');

		$f->addElement('header',null,$header);

		$f->addElement('text', 'subject', __('Subject'),array('maxlength'=>64));

		$fck = & $f->addElement('ckeditor', 'content', __('Content'));
		$fck->setFCKProps('800','300',true);

		$f->setDefaults(array('content'=>Variable::get($v,false),'subject'=>Variable::get($v.'S',false)));

		Base_ActionBarCommon::add('save',__('Save'),$f->get_submit_form_href());

		if($f->validate()) {
			$ret = $f->exportValues();
			$content = str_replace("\n",'',$ret['content']);
			Variable::set($v,$content);
			Variable::set($v.'S',strip_tags($ret['subject']));
			Base_StatusBarCommon::message(__('Page saved'));
			return false;
		}
		$f->display();
		return true;
	}

	public function drupal() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$this->recordset = 'drupal';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_drupal');
		$this->rb->set_defaults(array('endpoint'=>'epesi','update_products_every__minutes_'=>'360'));
		$this->display_module($this->rb);

		print('<div style="text-align:left;padding-left:20px;font-size:16px;"><h2>Initial Drupal setup</h2><ol>
		<li><a href="http://drupal.epe.si/pkg/epesi_commerce_kickstart.tar.gz" target="_blank">'.__('Download Drupal Commerce Kickstart package with Epesi custom modules').'</a></li>
		<li>'.__('Upload it to your server (it can be different server than Epesi) and begin drupal installation').'</li>
		<li><u>'.__('Always use different database than Epesi one').'</u></li>
		<li>'.__('On "Configure store" screen disable "demo store" installation and enable translations').'<br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/0.png"></li>
		<li>'.__('Log in to your new drupal commerce installation and go to modules').'<br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/1.png"></li>
		<li>'.__('Search for the phrase "epesi"').'<br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/2.png"></li>
		<li>'.__('Check "Epesi Commerce Connector"').'<br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/3.png"></li>
		<li>'.__('Save').'<br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/4.png"></li>
		<li>'.__('Apply dependencies installation').'<br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/5.png"></li>
		<li>'.__('Click "New" button on Epesi toolbar above and enter your drupal installation URL, login and password').'<br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/e1.png"></li>
		<li><a href="http://www.epesi.org/Cron" target="_blank">'.__('Make sure you have added Epesi cron jobs').'</a></li>
		</ol><h2>Recommended Drupal setup</h2><ol>
		<li>'.__('Enable other currencies and enable dedicated price fields for Epesi Products. Product in Epesi should have price set in each supported currency to be exported to Drupal.').'<br /><img style="padding:10px 0px 0px" src="'.$this->get_module_dir().'manual/currency1.png"><br /><img style="padding:10px 0px 0px" src="'.$this->get_module_dir().'manual/currency2.png"><br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/currency3.png"></li>
		<li>'.__('Enable currency selector block').'</li>
		<li>'.__('Enable translations in "Basic Page" content type, menus...').'</li>
		<li>'.__('Edit customer <u>billing</u> and <u>shipping</u> profiles (each one) - enable "Name (First name, Last name)", "Organisation (single line)" and disable "Name (single line)"').'<br /><img style="padding:10px 0px 0px" src="'.$this->get_module_dir().'manual/profiles1.png"><br /><img style="padding:10px 0px 0px" src="'.$this->get_module_dir().'manual/profiles2.png"><br /><img style="padding:10px 0px 0px" src="'.$this->get_module_dir().'manual/profiles3.png"><br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/profiles4.png"></li>
		<li>'.__('Add phone field to customer billing profile: text field with machine name "field_phone" and custom label, "required" is optional').'<br /><img style="padding:10px 0px 0px" src="'.$this->get_module_dir().'manual/phone1.png"><br /><img style="padding:10px 0px 0px" src="'.$this->get_module_dir().'manual/phone2.png"><br /><img style="padding:10px 0px 20px" src="'.$this->get_module_dir().'manual/phone3.png"></li>
		</ol></div>');

		return true;
	}

	private $manufacturers;
	public function fast_fill() {
		$qf = $this->init_module('Libs/QuickForm');
		$qf->addElement('hidden','id',null,array('id'=>'icecat_prod_id'));
		$qf->addElement('hidden','item_name',null,array('id'=>'icecat_prod_nameh'));
		$qf->addElement('static',null,__('Item Name'),'<div id="icecat_prod_name" />');
		$qf->addElement('text','upc',__('UPC'),array('id'=>'icecat_prod_upc'));
		$qf->addElement('text','product_code',__('Product Code'),array('id'=>'icecat_prod_code'));
		$qf->addElement('text','manufacturer_part_number',__('Part Number'),array('id'=>'icecat_prod_part_num'));

		$companies = CRM_ContactsCommon::get_companies(array('group'=>array('manufacturer')),array('company_name'),array('company_name'=>'ASC'));
		$this->manufacturers = array(''=>'---');
		foreach($companies as $c) {
			$this->manufacturers[$c['id']] = $c['company_name'];
		}
		$qf->addElement('select','manufacturer',__('Manufacturer'),$this->manufacturers,array('id'=>'icecat_prod_manuf'));

		$qf->addElement('checkbox','skip',__('Publish without getting information data'),'',array('id'=>'icecat_prod_skip'));
        $qf->addElement('static', '3rd party', __('Available data'),'<iframe id="3rdp_info_frame" style="width:300px; height:100px;border:0px"></iframe>');

		$qf->addElement('submit',null,__('Zapisz'));
		$qf->addFormRule(array($this,'check_fast_fill'));

		if($qf->validate()) {
			eval_js('leightbox_deactivate(\'fast_fill_lb\');');
			$vals = $qf->exportValues();
			Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$vals['id'],array('upc'=>$vals['upc'],'product_code'=>$vals['product_code'],'manufacturer_part_number'=>$vals['manufacturer_part_number'],'manufacturer'=>isset($vals['manufacturer'])?$vals['manufacturer']:''));
		   	Premium_Warehouse_DrupalCommerceCommon::publish_warehouse_item($vals['id'],!(isset($vals['skip']) && $vals['skip']));
		}

		Libs_LeightboxCommon::display('fast_fill_lb',$this->get_html_of_module($qf),'Express fill');

		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items');
		$this->rb->set_default_order(array('item_name'=>'ASC'));

		$this->rb->set_button(false);
		$this->rb->disable_watchdog();
		$this->rb->disable_actions(array('delete'));

		$cols = array('quantity_on_hand'=>false,'quantity_en_route'=>false,'available_qty'=>false,'reserved_qty'=>false,'dist_qty'=>false,
				'quantity_sold'=>false,'vendor'=>false,'manufacturer'=>true,'product_code'=>true,'upc'=>true,'gross_price'=>false,'manufacturer_part_number'=>true);

		$this->rb->set_header_properties(array(
						'manufacturer'=>array('width'=>25, 'wrapmode'=>'nowrap'),
						'manufacturer_part_number'=>array('name'=>__('Part Number'), 'width'=>15, 'wrapmode'=>'nowrap'),
						'product_code'=>array('width'=>15, 'wrapmode'=>'nowrap'),
						'upc'=>array('width'=>20, 'wrapmode'=>'nowrap'),
						'item_type'=>array('width'=>5, 'wrapmode'=>'nowrap'),
//						'gross_price'=>array('name'=>'Price','width'=>10, 'wrapmode'=>'nowrap'),
						'item_name'=>array('wrapmode'=>'nowrap'),
						'sku'=>array('width'=>10, 'wrapmode'=>'nowrap')
						));

  		$this->rb->set_additional_actions_method(array($this,'fast_fill_actions'));
		$items_already_added = Utils_RecordBrowserCommon::get_possible_values('premium_ecommerce_products','items');
		$ignore_items = array();
		foreach($items_already_added as $iad) $ignore_items = array_merge($ignore_items,array_filter(explode('__',$iad)));
		$crits = array('!id'=>array_unique($ignore_items));
		$this->display_module($this->rb, array(array(),$crits,$cols));
//		Utils_RecordBrowserCommon::merge_crits(array('upc'=>'','(manufacturer_part_number'=>'', '|manufacturer'=>''),array('(product_code'=>'', '|manufacturer'=>''))
	}

	public function check_fast_fill($arg) {
		if(isset($arg['skip']) && $arg['skip']) return array();
		if(!isset($arg['upc'])) $arg['upc'] = '';
		if(!isset($arg['manufacturer'])) $arg['manufacturer'] = '';
		if(!isset($arg['product_code'])) $arg['product_code'] = '';
		if(!isset($arg['item_name'])) $arg['item_name'] = '';
		if(!isset($arg['manufacturer_part_number'])) $arg['manufacturer_part_number'] = '';
		if(!isset($arg['id']) || !is_numeric($arg['id'])) return array('upc'=>__('Invalid request without ID. Hacker?'));
		if(empty($arg['upc']) &&
		    (empty($arg['manufacturer']) || empty($arg['product_code'])) &&
		    (empty($arg['manufacturer']) || empty($arg['manufacturer_part_number']))
		    ) {
		    	eval_js('$(\'icecat_prod_id\').value=\''.$arg['id'].'\';'.
					'$(\'icecat_prod_name\').innerHTML=\''.addcslashes($arg['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_nameh\').value=\''.addcslashes($arg['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_upc\').value=\''.addcslashes($arg['upc'],'\'\\').'\';'.
					'$(\'icecat_prod_code\').value=\''.addcslashes($arg['product_code'],'\'\\').'\';'.
					'$(\'icecat_prod_part_num\').value=\''.addcslashes($arg['manufacturer_part_number'],'\'\\').'\';'.
					'$(\'icecat_prod_manuf\').value=\''.addcslashes($arg['manufacturer'],'\'\\').'\';');

			return array('upc'=>'<span id="icecat_prod_err">'.__('Please fill manufacturer and product code, or manufacturer and part number, or UPC, or skip gettin information data.').'</span>');
		}
		return array();
	}

	public function fast_fill_actions($r, $gb_row) {
		$gb_row->add_action(Libs_LeightboxCommon::get_open_href('fast_fill_lb').' id="icecat_button_'.$r['id'].'"','edit',__('Click here to fill required data'));
		$gb_row->add_js('jq(\'#icecat_button_'.$r['id'].'\').click(function() {'.
					'$(\'icecat_prod_id\').value=\''.$r['id'].'\';'.
					'$(\'icecat_prod_name\').innerHTML=\''.addcslashes($r['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_nameh\').value=\''.addcslashes($r['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_upc\').value=\''.addcslashes($r['upc'],'\'\\').'\';'.
					'$(\'icecat_prod_code\').value=\''.addcslashes($r['product_code'],'\'\\').'\';'.
					'$(\'icecat_prod_part_num\').value=\''.addcslashes($r['manufacturer_part_number'],'\'\\').'\';'.
					'$(\'icecat_prod_manuf\').value=\''.addcslashes($r['manufacturer'],'\'\\').'\';'.
					'$(\'icecat_prod_skip\').checked=false;'.
					'$(\'3rdp_info_frame\').src=\'modules/Premium/Warehouse/DrupalCommerce/3rdp.php?'.http_build_query(array('upc'=>$r['upc'],'mpn'=>$r['manufacturer_part_number'],'man'=>isset($this->manufacturers[$r['manufacturer']])?$this->manufacturers[$r['manufacturer']]:'')).'\';'.
					'var err=$(\'icecat_prod_err\');if(err!=null)err.parentNode.parentNode.removeChild(err.parentNode);'.
					'})');
	}

	public function features() {
		if($this->is_back()) return false;

		$form = $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, __('eCommerce item tabs'));

		$form->setDefaults(array('prices'=>Variable::get('ecommerce_item_prices'),
					'associations'=>Variable::get('ecommerce_item_associations'),
		            'parameters'=>Variable::get('ecommerce_item_parameters')
				    ,'descriptions'=>Variable::get('ecommerce_item_descriptions')));

		$form->addElement('checkbox', 'prices', __('Prices'),'');
		$form->addElement('checkbox', 'parameters', __('Parameters'),'');
		$form->addElement('checkbox', 'descriptions', __('Descriptions'),'');
		$form->addElement('checkbox', 'associations', __('Kits / Associations'),'');

		if($form->validate()) {
			$vals = $form->exportValues();
			Variable::set('ecommerce_item_prices',(isset($vals['prices']) && $vals['prices'])?true:false);
			Variable::set('ecommerce_item_associations',(isset($vals['associations']) && $vals['associations'])?true:false);
			Variable::set('ecommerce_item_descriptions',(isset($vals['descriptions']) && $vals['descriptions'])?true:false);
			Variable::set('ecommerce_item_parameters',(isset($vals['parameters']) && $vals['parameters'])?true:false);
			DB::Execute('UPDATE premium_ecommerce_products_field SET type=%s WHERE field=%s OR field=%s', array(Variable::get('ecommerce_item_descriptions')?'calculated':'hidden', 'Product Name', 'Description'));
			return false;
		} else $form->display();

		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href(true,__('creating thumbnails, please wait')));

    		return true;
	}

	public function prices() {
		if($this->is_back()) return false;

		$form = $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, __('Automatic prices'));

		eval_js_once("ecommerce_autoprices = function(val) {
			if(val) {
				$('ecommerce_minimal').enable();
				$('ecommerce_margin').enable();
			} else {
				$('ecommerce_minimal').disable();
				$('ecommerce_margin').disable();
			}
		}");

		$form->setDefaults(array('enabled'=>Variable::get('ecommerce_autoprice'),'minimal'=>Variable::get('ecommerce_minimal_profit')
				    ,'margin'=>Variable::get('ecommerce_percentage_profit')));

		$form->addElement('checkbox', 'enabled', __('Enabled'),'',array('onChange'=>'ecommerce_autoprices(this.checked)'));
		$enabled = $form->exportValue('enabled');
		eval_js('ecommerce_autoprices('.$enabled.')');

		$form->addElement('text', 'minimal', __('Minimal profit margin'),array('id'=>'ecommerce_minimal'));
		$form->addElement('text', 'margin', __('Percentage profit margin'),array('id'=>'ecommerce_margin'));

		if($enabled) {
			$form->addRule('minimal', __('This should be numeric value'),'numeric');
			$form->addRule('margin', __('This should be numeric value'),'numeric');
		}

		if($form->validate()) {
			$vals = $form->exportValues();
			Variable::set('ecommerce_autoprice',(isset($vals['enabled']) && $vals['enabled'])?true:false);
			Variable::set('ecommerce_minimal_profit',$vals['minimal']);
			Variable::set('ecommerce_percentage_profit',$vals['margin']);
			return false;
		} else $form->display();

		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href(true,__('creating thumbnails, please wait')));

    		return true;
	}

	public function check_path($p) {
	    if(!is_dir($p) || !is_dir(rtrim($p,'/').'/files') || !is_writable(rtrim($p,'/').'/files')
		|| (file_exists(rtrim($p,'/').'/files/epesi') && !is_writable(rtrim($p,'/').'/files/epesi'))
		|| (file_exists(rtrim($p,'/').'/files/100/epesi') && !is_writable(rtrim($p,'/').'/files/100/epesi'))
		|| (file_exists(rtrim($p,'/').'/files/200/epesi') && !is_writable(rtrim($p,'/').'/files/200/epesi'))
		|| !is_writable(rtrim($p,'/').'/config')
		|| (file_exists(rtrim($p,'/').'/config/epesi.php') && !is_writable(rtrim($p,'/').'/config/epesi.php'))) return false;
	    return true;
	}

	public function get_3rd_party_info_addon($arg){
	}

	public function warehouse_item_addon($arg) {
		$recs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('items'=>$arg['id']));
		if(empty($recs)) {
		    print('<h1><a '.$this->create_callback_href(array('Premium_Warehouse_DrupalCommerceCommon','publish_warehouse_item'),$arg['id']).'>'.__('Publish').'</a></h1>');

		    $plugins = Utils_RecordBrowserCommon::get_records('premium_ecommerce_3rdp_info',array(),array(),array('position'=>'ASC'));
		    if($plugins) print('<h1><a '.$this->create_callback_href(array('Premium_Warehouse_DrupalCommerceCommon','publish_warehouse_item'),array($arg['id'],false)).'>'.__('Publish without getting information data').'</a></h1>');
		    return;
		}
		$rec = array_pop($recs);

		$on = '<span class="checkbox_on" />';
		$off = '<span class="checkbox_off" />';

		print('<h1>'.Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$rec['id']).__('Go to item').Utils_RecordBrowserCommon::record_link_close_tag().'</h1>');

		//opts
 		$m = $this->init_module('Utils/GenericBrowser',null,'t0');
 		$m->set_table_columns(array(
				array('name'=>__('Option')),
				array('name'=>__('Value')),
				array('name'=>__('Actions'))
					    ));
 		$m->add_row(__('Published'),($rec['publish']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_DrupalCommerceCommon','toggle_publish'),array($rec['id'],!$rec['publish'])).'>'.__('Toggle').'</a>');
 		$m->add_row(__('Recommended'),($rec['recommended']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_DrupalCommerceCommon','toggle_recommended'),array($rec['id'],!$rec['recommended'])).'>'.__('Toggle').'</a>');
 		$m->add_row(__('Always in stock'),($rec['always_in_stock']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_DrupalCommerceCommon','toggle_always_in_stock'),array($rec['id'],!$rec['always_in_stock'])).'>'.__('Toggle').'</a>');
 		$m->add_row(__('Assigned category'),($arg['category']?$on:$off),'');
		$quantity = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$arg['id'],'>quantity'=>0));
 		$m->add_row(__('Available in warehouse'),(empty($quantity)?$off:$on),'');
 		$m->add_row(__('Common attachments'),Utils_AttachmentCommon::count('premium_ecommerce_products/'.$arg['id']),'');
//		$m->add_row('Related,recommended',Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$rec['id'],false,'edit').__('Edit item').Utils_RecordBrowserCommon::record_link_close_tag());

 		$this->display_module($m);

		//langs
        if(Variable::get('ecommerce_item_descriptions')) {
     		$m = $this->init_module('Utils/GenericBrowser',null,'t1');
 	    	$m->set_table_columns(array(
				array('name'=>__('Language')),
				array('name'=>__('Name')),
				array('name'=>__('Description')),
				array('name'=>__('Parameters')),
				array('name'=>__('Attachments')),
				array('name'=>__('Actions'))
					    ));
    		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
	    	foreach($langs as $code=>$name) {
		        $descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('product'=>$rec['id'],'language'=>$code),array('display_name','short_description'));
		        $descs = array_pop($descs);
    		    $params = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters',array('product'=>$rec['id'],'language'=>$code));
	    	    $attachments = Utils_AttachmentCommon::count('premium_ecommerce_descriptions/'.$descs['id']);
 		        $m->add_row($name,($descs && isset($descs['display_name']) && $descs['display_name'])?$on:$off,($descs && isset($descs['short_description']) && $descs['short_description'])?$on:$off,empty($params)?$off:$on,$attachments,
 		        		$descs?Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_descriptions',$descs['id'],false,'edit').__('Edit').Utils_RecordBrowserCommon::record_link_close_tag():'<a '.Utils_RecordBrowserCommon::create_new_record_href('premium_ecommerce_descriptions',array('language'=>$code,'product'=>$rec['id'])).'>'.__('Add').'</a>');
    		}
 	    	$this->display_module($m);
        }

		//currencies
        if(Variable::get('ecommerce_item_prices')) {
     		$m = $this->init_module('Utils/GenericBrowser',null,'t2');
 	    	$m->set_table_columns(array(
				array('name'=>__('Currency')),
				array('name'=>__('Gross Price')),
				array('name'=>__('Tax Rate')),
				array('name'=>__('Actions'))
					    ));
    		$curr_opts = Premium_Warehouse_DrupalCommerceCommon::get_currencies();
	    	foreach($curr_opts as $id=>$code) {
		        $prices = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('product'=>$rec['id'],'currency'=>$id),array('gross_price','tax_rate'));
		        $prices = array_pop($prices);
    		    if($prices && isset($prices['gross_price'])) {
    			    $tax = Utils_RecordBrowserCommon::get_record('data_tax_rates',$prices['tax_rate']);
    			    $m->add_row($code,$prices['gross_price'],$tax['name'],
 		    		Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_prices',$prices['id'],false,'edit').__('Edit').Utils_RecordBrowserCommon::record_link_close_tag());
	    	    } else {
         		    $m->add_row($code,$off,$off,
         		    	'<a '.Utils_RecordBrowserCommon::create_new_record_href('premium_ecommerce_prices',array('currency'=>$id,'product'=>$rec['id'])).'>'.__('Add').'</a>');
    		    }
	    	}
 		    $this->display_module($m);
 		}
	}

	public function caption(){
		if (isset($this->caption)) return $this->caption;
		if (isset($this->rb)) return $this->rb->caption();
		return __('eCommerce administration');
	}

	public function applet($conf, & $opts) {
		//available applet options: toggle,href,title,go,go_function,go_arguments,go_contruct_arguments
		$opts['go'] = false; // enable/disable full screen
		$xxx = Premium_Warehouse_DrupalCommerceCommon::$order_statuses;
		$xxx['active'] = __('Active');
		$opts['title'] = __('eCommerce - %s',array($xxx[$conf['status']]));

		$crits = array('online_order'=>1);
		if($conf['status']=='active')
			$crits['status'] = array(2,3,4,5,6);
		else
			$crits['status'] = $conf['status'];
		if($conf['my']) {
			$my_rec = CRM_ContactsCommon::get_my_record();
			$crits['employee'] = array('',$my_rec['id']);
		}
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$conds = array(
									array(	array('field'=>'transaction_id', 'width'=>10),
										array('field'=>'transaction_date', 'width'=>10),
										array('field'=>'warehouse', 'width'=>10)
									),
									$crits,
									array('transaction_date'=>'DESC','transaction_id'=>'DESC'),
									array('Premium_Warehouse_DrupalCommerceCommon','applet_info_format'),
									15,
									$conf,
									& $opts
				);
		$this->display_module($rb, $conds, 'mini_view');

	}

	public function setup_3rd_party_plugins() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

	    Base_ActionBarCommon::add('search',__('Scan plugins'), $this->create_callback_href(array('Premium_Warehouse_DrupalCommerceCommon','scan_for_3rdp_info_plugins')));
        $this->recordset = '3rdp_info';
        $this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_3rdp_info','premium_ecommerce_3rdp_info');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
        $this->display_module($this->rb);

        return true;
	}

	public function attachment_product_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_products/'.$arg['id']));
		$this->display_module($a);
	}

	public function attachment_categories_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_warehouse_items_categories/'.$arg['id']));
		$this->display_module($a);
	}

	public function attachment_categories_desc_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_cat_descriptions/'.$arg['language'].'/'.$arg['category']));
		$this->display_module($a);
	}

	public function attachment_product_addon_item($arg){
		$pid = Utils_RecordBrowserCommon::get_id('premium_ecommerce_products', array('items'), array($arg['id']));
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_products/'.$pid));
		$this->display_module($a);
	}

	public function attachment_product_desc_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_descriptions/'.$arg['id']));
		$this->display_module($a);
	}

    public function reset_cron() {
        $ret = ModuleManager::reset_cron($this->get_type(),'cron_categories');
        if($ret) print(__('Categories and products update has been successfully queued as first cron job to run.'));
        else print(__('Categories and products update is already running - please come back here later.'));
    }

}

?>
