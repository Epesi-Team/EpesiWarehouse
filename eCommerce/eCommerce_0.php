<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * eCommerce
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce extends Module {
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
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());

		$buttons = array();
//		$icon = Base_ThemeCommon::get_template_file($name,'icon.png');
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'setup_3rd_party_plugins')).'>'.$this->t('3rd party info plugins').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'prices')).'>'.$this->t('Automatic prices').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'availability')).'>'.$this->t('Availability').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'banners')).'>'.$this->t('Banners').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'boxes')).'>'.$this->t('Boxes').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'contactus_page')).'>'.$this->t('Contact us').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'features')).'>'.$this->t('Features').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'home_page')).'>'.$this->t('Home Page').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'compare_services')).'>'.$this->t('Links for compare services').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'order_status_change_email_page')).'>'.$this->t('Order status change e-mails').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'pages')).'>'.$this->t('Pages').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'parameters')).'>'.$this->t('Parameters').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'parameter_groups')).'>'.$this->t('Parameter Groups').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'payments_carriers')).'>'.$this->t('Payments & Carriers').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'polls')).'>'.$this->t('Polls').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'promotion_codes')).'>'.$this->t('Promotion Codes').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'QC_dirs')).'>'.$this->t('Quickcart settings').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'rules_page')).'>'.$this->t('Rules & Policies').'</a>',
						'icon'=>null);
		$theme =  & $this->pack_module('Base/Theme');
		$theme->assign('header', $this->t('eCommerce settings'));
		$theme->assign('buttons', $buttons);
		$theme->display();
	}
	

	public function body() {
		$this->recordset = 'products';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_products');
		$this->rb->set_defaults(array('publish'=>1,'status'=>1));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));

		$opts = Premium_Warehouse_eCommerceCommon::get_categories();
		$this->rb->set_custom_filter('item_name',array('type'=>'select','label'=>$this->t('Category'),'args'=>$opts,'trans_callback'=>array('Premium_Warehouse_eCommerceCommon', 'category_filter')));

//		$cols = array('item_name'=>array('name'=>'Item name')
//		        );
		$this->display_module($this->rb);//,array(array('position'=>'ASC'),array(),$cols));
	}

	public function compare_services() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
 		$m = & $this->init_module('Utils/GenericBrowser',null,'t1');
 		$m->set_table_columns(array(array('name'=>$this->t('Site'),'width'=>30),
							  array('name'=>'Link','width'=>70)));
		$site = $this->t('http://replace.with.quickcart.url/');
		$a = array('Old Ceneo.pl XML'=>'ceneo',
		        'Ceneo.pl'=>'ceneo2',
			'Nokaut.pl'=>'nokaut',
			'Skapiec.pl'=>'skapiec',
			'Handelo.pl'=>'handelo',
			'Szoker.pl'=>'szoker',
			'Cenus.pl'=>'cenus',
			'Zakupy.Onet.pl'=>'onet');
		foreach($a as $k=>$url) {
			$m->add_row($k,	$site.'?sLang=pl&p=compare-'.$url);
			$m->add_row($k.' ('.$this->t('includes out of stock items').')',	$site.'?sLang=pl&p=compare-'.$url.'&outOfStock=1');
		}
		$a = array('Froogle.com'=>'froogle',
			'Shopping.com'=>'shopping');
		foreach($a as $k=>$url) {
			$m->add_row($k,	$site.'?sLang=en&p=compare-'.$url);
			$m->add_row($k.' ('.$this->t('includes out of stock items').')',	$site.'?sLang=en&p=compare-'.$url.'&outOfStock=1');
		}
 		$this->display_module($m);

		return true;
	}

	public function parameters() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->recordset = 'parameters';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameters');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function parameter_groups() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());

		$this->recordset = 'parameter_groups';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameter_groups');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function boxes() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->recordset = 'boxes';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_boxes');
		$this->rb->set_defaults(array('publish'=>1,'language'=>Base_LangCommon::get_lang_code()));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}
	
	public function banners() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_banners');
		$this->rb->set_defaults(array('publish'=>1,'views_limit'=>0,'views'=>0,'clicks'=>0,'width'=>480,'height'=>80,'color'=>'#000000','language'=>Base_LangCommon::get_lang_code()));
		$this->display_module($this->rb);

    		return true;
	}
	
	public function polls() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		
		print('<h2>'.$this->t('Last active poll is displayed.').'</h2>');
	
		$this->recordset = 'polls';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_polls');
		$this->rb->set_defaults(array('publish'=>1,'language'=>Base_LangCommon::get_lang_code()));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}
	
	public function promotion_codes() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_promotion_codes');
		$this->rb->force_order(array('expiration'=>'DESC'));
		$this->display_module($this->rb);

		return true;
	}
	
	public function clear_votes($poll) {
		DB::Execute('UPDATE premium_ecommerce_poll_answers_data_1 SET f_votes=0 WHERE f_poll=%d',array($poll));
	}
	
	public function poll_answers_addon($arg) {
		Base_ActionBarCommon::add('delete', 'Clear votes', $this->create_callback_href(array($this,'clear_votes'),array($arg['id'])));
		
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_poll_answers');
		$order = array(array('poll'=>$arg['id']), array('poll'=>false,'answer'=>true,'votes'=>true), array('answer'=>'ASC'));
		$rb->set_defaults(array('poll'=>$arg['id'],'votes'=>0));
		$rb->set_header_properties(array(
			'answer'=>array('width'=>50, 'wrapmode'=>'nowrap'),
			'votes'=>array('width'=>10, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function availability() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->recordset = 'availability';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_availability');
		$this->display_module($this->rb);

		return true;
	}

	public function pages() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->recordset = 'pages';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages');
		$this->rb->set_defaults(array('publish'=>1,'type'=>2));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function payments_carriers() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_payments_carriers');
		$this->rb->set_defaults(array('percentage_of_amount'=>0));
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
		    	Epesi::alert($this->t('This item is already on top/bottom'));
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
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_descriptions');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function item_cat_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_item_cat_labels');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name'],'language'=>Base_LangCommon::get_lang_code()));
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
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_products_parameters');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('language'=>'ASC','parameter'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'parameter'=>array('wrapmode'=>'nowrap'),
			'value'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function subpages_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages');
		$order = array(array('parent_page'=>$arg['id']), array(), array('page_name'=>'ASC'));
		$rb->set_defaults(array('parent_page'=>$arg['id'],'publish'=>1,'type'=>2));
//		$rb->set_header_properties(array(
//			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
//			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
//									));
		$this->display_module($rb,$order,'show_data');
	}

	public function pages_info_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages_data');
		$order = array(array('page'=>$arg['id']), array('page'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('page'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'name'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function prices_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_prices');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('currency'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name']));
		$rb->set_header_properties(array(
			'currency'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'price'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function orders_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_orders');
		//$order = array(array('transaction_id'=>$arg['id']), array('transaction_id'=>false));
		$ord_id = Premium_Warehouse_eCommerceCommon::orders_get_record();
		$this->display_module($rb,array('view',$ord_id,null,false),'view_entry');
		if(Base_AclCommon::i_am_admin())
    		Base_ActionBarCommon::add('edit', 'Edit ecommerce', $this->create_callback_href(array($this,'edit_ecommerce_order'),$ord_id));		
	}
	
	public function edit_ecommerce_order($id) {
        $x = ModuleManager::get_instance('/Base_Box|0');
        if (!$x) trigger_error('There is no base box module instance',E_USER_ERROR);
	    $x->push_main('Utils/RecordBrowser','view_entry',array('edit', $id, array(), true),array('premium_ecommerce_orders'));
	}
	
	public function contactus_page() {
		return $this->edit_variable_with_lang('Contact us','ecommerce_contactus');
	}
	
	public function order_status_change_email_page() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_emails');
		$this->display_module($this->rb);		

        return true;	    
	}
	
	public function rules_page() {
		return $this->edit_variable_with_lang('Rules and policies','ecommerce_rules');
	}

	public function home_page() {
		return $this->edit_variable_with_lang('Home','ecommerce_home');
	}

	private function edit_variable_with_lang($header,$v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		
		print('<h1>'.$header.'</h1>'.$this->t('Choose language to edit:').'<ul>');

		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
		print('<li><a '.$this->create_callback_href(array($this,'edit_variable'),array($header,$v)).'>default (if translation is available)</a></li>');
		foreach($langs as $k=>$name) {
			print('<li><a '.$this->create_callback_href(array($this,'edit_variable'),array($header,$v.'_'.$k)).'>'.$name.'</a></li>');
		}
		print('</ul>');
		return true;
	}

	public function edit_variable($header, $v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$f = $this->init_module('Libs/QuickForm');
		
		$f->addElement('header',null,$this->t($header));
		
		$fck = & $f->addElement('ckeditor', 'content', $this->t('Content'));
		$fck->setFCKProps('800','300',true);
		
		$f->setDefaults(array('content'=>Variable::get($v,false)));

		Base_ActionBarCommon::add('save','Save',$f->get_submit_form_href());
		
		if($f->validate()) {
			$ret = $f->exportValues();
			$content = str_replace("\n",'',$ret['content']);
			Variable::set($v,$content);
			Base_StatusBarCommon::message($this->t('Page saved'));
			return false;
		}
		$f->display();	
		return true;
	}

	public function edit_variable_mail($header, $v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$f = $this->init_module('Libs/QuickForm');
		
		$f->addElement('header',null,$this->t($header));

		$f->addElement('text', 'subject', $this->t('Subject'),array('maxlength'=>64));
		
		$fck = & $f->addElement('ckeditor', 'content', $this->t('Content'));
		$fck->setFCKProps('800','300',true);
		
		$f->setDefaults(array('content'=>Variable::get($v,false),'subject'=>Variable::get($v.'S',false)));

		Base_ActionBarCommon::add('save','Save',$f->get_submit_form_href());
		
		if($f->validate()) {
			$ret = $f->exportValues();
			$content = str_replace("\n",'',$ret['content']);
			Variable::set($v,$content);
			Variable::set($v.'S',strip_tags($ret['subject']));
			Base_StatusBarCommon::message($this->t('Page saved'));
			return false;
		}
		$f->display();	
		return true;
	}

	public function QC_dirs() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		Base_ActionBarCommon::add('add', 'Add', $this->create_callback_href(array($this,'add_quickcart')));
	
		$gb = & $this->init_module('Utils/GenericBrowser',null,'qc_list');

		$gb->set_table_columns(array(array('name'=>$this->t('Path'), 'order'=>'path')));

		$query = 'SELECT path FROM premium_ecommerce_quickcart';
		$query_qty = 'SELECT count(*) FROM premium_ecommerce_quickcart';

		$ret = $gb->query_order_limit($query, $query_qty);
		
		if($ret)
			while(($row=$ret->FetchRow())) {
			    $r = $gb->get_new_row();
			    $r->add_data($row['path']);
			    $r->add_action($this->create_confirm_callback_href($this->t('Are you sure you want to delete this record?'),array($this,'delete_quickcart'),$row['path']),'delete');
			    $r->add_action($this->create_callback_href(array($this,'quickcart_settings'),$row['path']),'edit','Settings');
			}

		$this->display_module($gb);

		return true;
	}
	
	public function quickcart_settings($path) {
		if($this->is_back()) return false;

		if(!is_writable($path.'/config/general.php')) {
			Epesi::alert('Config file not writable: '.$path.'/config/general.php');
			return false;
		}			

		$form = & $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, $this->t('QuickCart settings: %s',array($path)));
		
		$files = scandir($path.'/config');
		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
		foreach($files as $f) {
			if(!preg_match('/^(.{2,3})\.php$/i',$f,$reqs))
				continue;
			if(in_array($reqs[1].'.gif',$files) && in_array('epesi_'.$reqs[1].'.php',$files)) {
				$code = $reqs[1];
				if(!is_writable($path.'/config/epesi_'.$code.'.php')) {
					Epesi::alert('Config file not writable: '.$path.'/config/epesi_'.$code.'.php');
					unset($langs[$code]);
					continue;
				}
				global $config;
				$config = array();
				require_once($path.'/config/'.$code.'.php');
				if(isset($config['language']) && $config['language']!=$code)
					$langs[$code] = $code;
			} else {
				unset($langs[$code]);
			}
		}

		foreach($langs as $code=>$name) {
			if(file_exists($path.'/config/'.$code.'.gif') && file_exists($path.'/config/'.$code.'.php') && file_exists($path.'/config/epesi_'.$code.'.php')) {
			} else {
			}
		}
		$form->addElement('select', 'default_lang', $this->t('Default language'),$langs);
		$form->addRule('default_lang', $this->t('Field required'), 'required');
		$form->addElement('multiselect', 'available_lang', $this->t('Available languages'),$langs);
		$form->addRule('available_lang', $this->t('At least one language must be available'), 'required');
		$form->addRule(array('default_lang','available_lang'), $this->t('Default language must be one of quickcart available languages'), 'callback',array($this,'quickcart_check_default_lang'));

		$form->addElement('text', 'email', $this->t('Shop e-mail'));
		$form->addRule('email', $this->t('This is not valid email address'), 'email');

		$form->addElement('text', 'products_list', $this->t('Number of products displayed on page'));
		$form->addRule('products_list', $this->t('This field should be numeric'), 'numeric');
		$form->addRule('products_list', $this->t('Field required'), 'required');

		$form->addElement('text', 'news_list', $this->t('Number of news (subpages) displayed on page'));
		$form->addRule('news_list', $this->t('This field should be numeric'), 'numeric');
		$form->addRule('news_list', $this->t('Field required'), 'required');

		$form->addElement('text', 'time_diff', $this->t('Difference between your local time and server time in hours'));
		$form->addRule('time_diff', $this->t('This field should be numeric'), 'numeric');
		$form->addRule('time_diff', $this->t('Field required'), 'required');

		$form->addElement('select','default_image_size',$this->t('Thumbnails size'),array(0=>$this->t('100 x 100'),1=>$this->t('200 x 200')));

		$form->addElement('checkbox', 'text_size', $this->t('Text resize buttons'));
		$form->addElement('checkbox', 'site_map_products', $this->t('Display products on sitemap page'));

		$form->addElement('header',null,$this->t('External services settings'));

		$form->addElement('text', 'skapiec_shop_id', $this->t('Skąpiec shop ID'));
		$form->addRule('skapiec_shop_id', $this->t('This field should be numeric'), 'numeric');

		$form->addElement('text', 'allpay_id', $this->t('Allpay ID'));
		$form->addRule('allpay_id', $this->t('This field should be numeric'), 'numeric');

		$form->addElement('text', 'przelewy24_id', $this->t('Przelewy24 ID'));
		$form->addRule('przelewy24_id', $this->t('This field should be numeric'), 'numeric');

		$form->addElement('text', 'platnosci_id', $this->t('Platnosci ID'));
		$form->addRule('platnosci_id', $this->t('This field should be numeric'), 'numeric');
		$form->addElement('text', 'platnosci_pos_auth_key', $this->t('Platnosci pos auth key'));
		$form->addRule('platnosci_pos_auth_key', $this->t('This field should be numeric'), 'numeric');
		$form->addElement('text', 'platnosci_key1', $this->t('Platnosci key 1'));
		$form->addElement('text', 'platnosci_key2', $this->t('Platnosci key 2'));
		$form->addElement('text', 'epesi_payments_url', $this->t('Epesi Payments module URL'));

		$form->addElement('text', 'zagiel_id', $this->t('Zagiel ID'));
		$form->addRule('zagiel_id', $this->t('This field should be numeric'), 'numeric');
		$form->addElement('text', 'zagiel_min_price', $this->t('Zagiel minimal price'));
		$form->addRule('zagiel_min_price', $this->t('This field should be numeric'), 'numeric');

		$form->addElement('text', 'paypal_email', $this->t('Paypal email'));
		$form->addRule('paypal_email', $this->t('This is not valid email address'), 'email');

        $form->addElement('header', 'ups', $this->t('UPS rates fetching (please fill all fields to enable this feature)'));
		$form->addElement('text', 'ups_accesskey', $this->t('UPS Access Key'));
		$form->addElement('text', 'ups_username', $this->t('UPS Username'));
		$form->addElement('password', 'ups_password', $this->t('UPS Password'));
		$form->addElement('text', 'ups_shipper_number', $this->t('UPS Shipper Number'));
		$form->addElement('commondata', 'ups_src_country', $this->t('Your Country'), 'Countries', array('empty_option'=>true));
		$form->addElement('text', 'ups_src_zip', $this->t('Your ZIP'));
		$form->addElement('select', 'ups_weight_unit', $this->t('Weight Unit'), array('KGS'=>'KGS','LBS'=>'LBS'));

		$config = array();
		@include_once($path.'/config/epesi.php');
		$form->setDefaults($config);
		
		$currencies = DB::GetAssoc('SELECT code, code FROM utils_currency WHERE active=1');
		foreach($langs as $code=>$l) {
			$form->addElement('header',null,$this->t('Language: %s',array($l)));
			$form->addElement('select',$code.'-currency_symbol',$this->t('Currency'),$currencies);
			$form->addRule($code.'-currency_symbol',$this->t('Field required'),'required');
			
			$form->addElement('text', $code.'-delivery_free', $this->t('Price, after which the order gets sent for free to the customer'));
			$form->addRule($code.'-delivery_free', $this->t('This field should be numeric'), 'numeric');
			$form->addRule($code.'-delivery_free',$this->t('Field required'),'required');
			
			$form->addElement('text', $code.'-title', $this->t('Title'));
			$form->addRule($code.'-title',$this->t('Field required'),'required');

			$form->addElement('text', $code.'-slogan', $this->t('Slogan'));
			$form->addElement('textarea', $code.'-description', $this->t('Description'));
			$form->addElement('textarea', $code.'-keywords', $this->t('Keywords'));
			$form->addElement('textarea', $code.'-foot_info', $this->t('Foot'));
			
			$config = array();
			$config2 = array();
			@include_once($path.'/config/epesi_'.$code.'.php');
			foreach($config as $k=>$v) {
				$config2[$code.'-'.$k] = $v;
			}
			$form->setDefaults($config2);
		}

		if($form->validate()) {
			$data_dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/'.DATA_DIR;
			$vals = $form->exportValues();
			$ccc = "<?php
define('EPESI_DATA_DIR','".str_replace('\'','\\\'',$data_dir)."');
if(!defined('_VALID_ACCESS') && !file_exists(EPESI_DATA_DIR)) die('Launch epesi, log in as administrator, go to Menu->Adminitration->eCommerce->QuickCart settings and add \''.dirname(dirname(__FILE__)).'\' directory to setup quickcart');
\$config['default_lang'] = '".$vals['default_lang']."';
\$config['available_lang'] = array('".implode('\',\'',$vals['available_lang'])."');
\$config['text_size'] = ".((isset($vals['text_size']) && $vals['text_size'])?'true':'false').";
\$config['email'] = '".$vals['email']."';
\$config['skapiec_shop_id'] = ".$vals['skapiec_shop_id'].";
\$config['products_list'] = ".$vals['products_list'].";
\$config['news_list'] = ".$vals['news_list'].";
\$config['site_map_products'] = ".((isset($vals['site_map_products']) && $vals['site_map_products'])?'true':'false').";
\$config['time_diff'] = ".$vals['time_diff'].";
\$config['allpay_id'] = ".($vals['allpay_id']!==null?$vals['allpay_id']:null).";
\$config['przelewy24_id'] = ".$vals['przelewy24_id'].";
\$config['platnosci_id']	= ".$vals['platnosci_id'].";
\$config['platnosci_pos_auth_key'] = ".$vals['platnosci_pos_auth_key'].";
\$config['platnosci_key1'] = '".$vals['platnosci_key1']."';
\$config['platnosci_key2'] = '".$vals['platnosci_key2']."';
\$config['epesi_payments_url'] = '".$vals['epesi_payments_url']."';
\$config['zagiel_id'] = ".($vals['zagiel_id']?$vals['zagiel_id']:'null').";
\$config['zagiel_min_price'] = ".($vals['zagiel_min_price']?$vals['zagiel_min_price']:'null').";
\$config['paypal_email'] = '".$vals['paypal_email']."';
\$config['default_image_size'] = ".$vals['default_image_size'].";
\$config['ups_accesskey'] = '".str_replace('\'','\\\'',$vals['ups_accesskey'])."';
\$config['ups_username'] = '".str_replace('\'','\\\'',$vals['ups_username'])."';
\$config['ups_password'] = '".str_replace('\'','\\\'',$vals['ups_password'])."';
\$config['ups_shipper_number'] = '".str_replace('\'','\\\'',$vals['ups_shipper_number'])."';
\$config['ups_src_country'] = '".str_replace('\'','\\\'',$vals['ups_src_country'])."';
\$config['ups_src_zip'] = '".str_replace('\'','\\\'',$vals['ups_src_zip'])."';
\$config['ups_weight_unit'] = '".str_replace('\'','\\\'',$vals['ups_weight_unit'])."';
?>";
			file_put_contents($path.'/config/epesi.php',$ccc);
			
			foreach($langs as $code=>$l) {
				
				$ccc = "<?php
\$config['currency_symbol'] = '".str_replace('\'','\\\'',$vals[$code.'-currency_symbol'])."';
\$config['delivery_free'] = ".$vals[$code.'-delivery_free'].";
\$config['title'] = '".str_replace('\'','\\\'',$vals[$code.'-title'])."';
\$config['description'] = '".str_replace('\'','\\\'',$vals[$code.'-description'])."';
\$config['slogan'] = '".str_replace('\'','\\\'',$vals[$code.'-slogan'])."';
\$config['keywords'] = '".str_replace('\'','\\\'',$vals[$code.'-keywords'])."';
\$config['foot_info'] = '".str_replace('\'','\\\'',$vals[$code.'-foot_info'])."';
?>";
				file_put_contents($path.'/config/epesi_'.$code.'.php',$ccc);
			}
			return false;
		} else $form->display();
	
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		Base_ActionBarCommon::add('save', 'Save', $form->get_submit_form_href());
		
    		return true;
	}
	
	public function quickcart_check_default_lang($x) {
		return strpos($x[1],$x[0])!==false;
	}
	
	public function add_quickcart() {
		if($this->is_back()) return false;
	
		$form = & $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, $this->t('Add quickcart(epesi version) binding'));

		$form->addElement('text', 'path', $this->t('Path'));
		$form->addRule('path', $this->t('A path must be between 3 and 255 chars'), 'rangelength', array(3,255));
		$form->registerRule('check_path','callback','check_path','Premium_Warehouse_eCommerce');
		$form->addRule('path', $this->t('Invalid path or files directory not writable'), 'check_path');
		$form->addRule('path', $this->t('Field required'), 'required');

		if($form->validate()) {
		    $p = rtrim($form->exportValue('path'),'/');
		    DB::Execute('INSERT INTO premium_ecommerce_quickcart(path) VALUES(%s)',array($p));
		    @set_time_limit(0);
		    @mkdir($p.'/files/epesi');
		    @mkdir($p.'/files/100/epesi');
		    @mkdir($p.'/files/200/epesi');
		    Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products',array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		    Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions',array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		    @mkdir($p.'/files/epesi/banners');
		    $banners = DB::GetCol('SELECT f_file FROM premium_ecommerce_banners_data_1 WHERE active=1');
		    foreach($banners as $b)
			Premium_Warehouse_eCommerceCommon::copy_banner($b);
		    return false;
		} else $form->display();

		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		Base_ActionBarCommon::add('save', 'Save', $form->get_submit_form_href(true,$this->t('creating thumbnails, please wait')));
		
    		return true;
	}
	
	private $manufacturers;
	public function fast_fill() {
		$qf = $this->init_module('Libs/QuickForm');
		$qf->addElement('hidden','id',null,array('id'=>'icecat_prod_id'));
		$qf->addElement('hidden','item_name',null,array('id'=>'icecat_prod_nameh'));
		$qf->addElement('static',null,$this->t('Item Name'),'<div id="icecat_prod_name" />');
		$qf->addElement('text','upc',$this->t('UPC'),array('id'=>'icecat_prod_upc'));
		$qf->addElement('text','product_code',$this->t('Product code'),array('id'=>'icecat_prod_code'));
		$qf->addElement('text','manufacturer_part_number',$this->t('Part number'),array('id'=>'icecat_prod_part_num'));

		$companies = CRM_ContactsCommon::get_companies(array('group'=>array('manufacturer')),array('company_name'),array('company_name'=>'ASC'));
		$this->manufacturers = array(''=>'---');
		foreach($companies as $c) {
			$this->manufacturers[$c['id']] = $c['company_name'];
		}
		$qf->addElement('select','manufacturer',$this->t('Manufacturer'),$this->manufacturers,array('id'=>'icecat_prod_manuf'));

		$qf->addElement('checkbox','skip',$this->t('Publish without getting information data'),'',array('id'=>'icecat_prod_skip'));
        $qf->addElement('static', '3rd party', $this->t('Available data'),'<iframe id="3rdp_info_frame" style="width:300px; height:100px;border:0px"></iframe>');
		
		$qf->addElement('submit',null,$this->t('Zapisz'));
		$qf->addFormRule(array($this,'check_fast_fill'));
		
		if($qf->validate()) {
			eval_js('leightbox_deactivate(\'fast_fill_lb\');');
			$vals = $qf->exportValues();
			Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$vals['id'],array('upc'=>$vals['upc'],'product_code'=>$vals['product_code'],'manufacturer_part_number'=>$vals['manufacturer_part_number'],'manufacturer'=>$vals['manufacturer']));
		   	Premium_Warehouse_eCommerceCommon::publish_warehouse_item($vals['id'],!(isset($vals['skip']) && $vals['skip']));
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
						'manufacturer_part_number'=>array('name'=>'Part Number', 'width'=>15, 'wrapmode'=>'nowrap'),
						'product_code'=>array('width'=>15, 'wrapmode'=>'nowrap'),
						'upc'=>array('width'=>20, 'wrapmode'=>'nowrap'),
						'item_type'=>array('width'=>5, 'wrapmode'=>'nowrap'),
//						'gross_price'=>array('name'=>'Price','width'=>10, 'wrapmode'=>'nowrap'),
						'item_name'=>array('wrapmode'=>'nowrap'),
						'sku'=>array('width'=>10, 'wrapmode'=>'nowrap')
						));

  		$this->rb->set_additional_actions_method(array($this,'fast_fill_actions'));
		
		$crits = array('!id'=>Utils_RecordBrowserCommon::get_possible_values('premium_ecommerce_products','item_name'));
		$this->display_module($this->rb, array(array(),$crits,$cols));
//		Utils_RecordBrowserCommon::merge_crits(array('upc'=>'','(manufacturer_part_number'=>'', '|manufacturer'=>''),array('(product_code'=>'', '|manufacturer'=>''))
	}

	public function check_fast_fill($arg) {
		if(isset($arg['skip']) && $arg['skip']) return true;
		if(!isset($arg['upc'])) $arg['upc'] = '';
		if(!isset($arg['manufacturer'])) $arg['manufacturer'] = '';
		if(!isset($arg['product_code'])) $arg['product_code'] = '';
		if(!isset($arg['item_name'])) $arg['item_name'] = '';
		if(!isset($arg['manufacturer_part_number'])) $arg['manufacturer_part_number'] = '';
		if(!isset($arg['id']) || !is_numeric($arg['id'])) return array('upc'=>$this->t('Invalid request without ID. Hacker?'));
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

			return array('upc'=>'<span id="icecat_prod_err">'.$this->t('Please fill manufacturer and product code, or manufacturer and part number, or UPC, or skip gettin information data.').'</span>');
		}
		return true;
	}
	
	public function fast_fill_actions($r, $gb_row) {
		$gb_row->add_action(Libs_LeightboxCommon::get_open_href('fast_fill_lb').' id="icecat_button_'.$r['id'].'"','edit',$this->t('Click here to fill required data'));
		$gb_row->add_js('Event.observe(\'icecat_button_'.$r['id'].'\',\'click\',function() {'.
					'$(\'icecat_prod_id\').value=\''.$r['id'].'\';'.
					'$(\'icecat_prod_name\').innerHTML=\''.addcslashes($r['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_nameh\').value=\''.addcslashes($r['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_upc\').value=\''.addcslashes($r['upc'],'\'\\').'\';'.
					'$(\'icecat_prod_code\').value=\''.addcslashes($r['product_code'],'\'\\').'\';'.
					'$(\'icecat_prod_part_num\').value=\''.addcslashes($r['manufacturer_part_number'],'\'\\').'\';'.
					'$(\'icecat_prod_manuf\').value=\''.addcslashes($r['manufacturer'],'\'\\').'\';'.
					'$(\'icecat_prod_skip\').checked=false;'.
					'$(\'3rdp_info_frame\').src=\'modules/Premium/Warehouse/eCommerce/3rdp.php?'.http_build_query(array('upc'=>$r['upc'],'mpn'=>$r['manufacturer_part_number'],'man'=>isset($this->manufacturers[$r['manufacturer']])?$this->manufacturers[$r['manufacturer']]:'')).'\';'.
					'var err=$(\'icecat_prod_err\');if(err!=null)err.parentNode.parentNode.removeChild(err.parentNode);'.
					'})');
	}
	
	public function features() {
		if($this->is_back()) return false;
	
		$form = & $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, $this->t('eCommerce item tabs'));
		
		$form->setDefaults(array('prices'=>Variable::get('ecommerce_item_prices'),
		            'parameters'=>Variable::get('ecommerce_item_parameters')
				    ,'descriptions'=>Variable::get('ecommerce_item_descriptions')));

		$form->addElement('checkbox', 'prices', $this->t('Prices'),'');
		$form->addElement('checkbox', 'parameters', $this->t('Parameters'),'');
		$form->addElement('checkbox', 'descriptions', $this->t('Descriptions'),'');

		if($form->validate()) {
			$vals = $form->exportValues();
			Variable::set('ecommerce_item_prices',(isset($vals['prices']) && $vals['prices'])?true:false);
			Variable::set('ecommerce_item_descriptions',(isset($vals['descriptions']) && $vals['descriptions'])?true:false);
			Variable::set('ecommerce_item_parameters',(isset($vals['parameters']) && $vals['parameters'])?true:false);
			return false;
		} else $form->display();

		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		Base_ActionBarCommon::add('save', 'Save', $form->get_submit_form_href(true,$this->t('creating thumbnails, please wait')));
		
    		return true;
	}
	
	public function prices() {
		if($this->is_back()) return false;
	
		$form = & $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, $this->t('Automatic prices'));
		
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

		$form->addElement('checkbox', 'enabled', $this->t('Enabled'),'',array('onChange'=>'ecommerce_autoprices(this.checked)'));
		$enabled = $form->exportValue('enabled');
		eval_js('ecommerce_autoprices('.$enabled.')');

		$form->addElement('text', 'minimal', $this->t('Minimal profit margin'),array('id'=>'ecommerce_minimal'));
		$form->addElement('text', 'margin', $this->t('Percentage profit margin'),array('id'=>'ecommerce_margin'));
		
		if($enabled) {
			$form->addRule('minimal', $this->t('This should be numeric value'),'numeric');
			$form->addRule('margin', $this->t('This should be numeric value'),'numeric');
		}

		if($form->validate()) {
			$vals = $form->exportValues();
			Variable::set('ecommerce_autoprice',(isset($vals['enabled']) && $vals['enabled'])?true:false);
			Variable::set('ecommerce_minimal_profit',$vals['minimal']);
			Variable::set('ecommerce_percentage_profit',$vals['margin']);
			return false;
		} else $form->display();

		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		Base_ActionBarCommon::add('save', 'Save', $form->get_submit_form_href(true,$this->t('creating thumbnails, please wait')));
		
    		return true;
	}
	
	public function check_path($p) {
	    if(!is_dir($p) || !is_dir(rtrim($p,'/').'/files') || !is_writable(rtrim($p,'/').'/files')
		|| (file_exists(rtrim($p,'/').'/files/epesi') && !is_writable(rtrim($p,'/').'/files/epesi'))
		|| (file_exists(rtrim($p,'/').'/files/100/epesi') && !is_writable(rtrim($p,'/').'/files/100/epesi'))
		|| (file_exists(rtrim($p,'/').'/files/200/epesi') && !is_writable(rtrim($p,'/').'/files/200/epesi'))
		|| !is_writable(rtrim($p,'/').'/config')
		|| !file_exists(rtrim($p,'/').'/config/epesi.php') || !is_writable(rtrim($p,'/').'/config/epesi.php')) return false;
	    return true;
	}
	
	public function delete_quickcart($path) {
	    DB::Execute('DELETE FROM premium_ecommerce_quickcart WHERE path=%s',array($path));
	    @recursive_rmdir($path.'/files/epesi/');
	    @recursive_rmdir($path.'/files/100/epesi/');
	    @recursive_rmdir($path.'/files/200/epesi/');
	}
	
	public function attachment_product_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_products/'.$arg['item_name']));
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$a->set_add_func(array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$a->set_persistent_delete();
		$a->set_max_file_size(1024*1024);
		$this->display_module($a);
	}

	public function attachment_product_desc_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_descriptions/'.$arg['language'].'/'.$arg['item_name']));
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$a->set_add_func(array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$a->set_persistent_delete();
		$a->set_max_file_size(1024*1024);
		$this->display_module($a);
	}

	public function attachment_page_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_pages/'.$arg['id']));
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$a->set_persistent_delete();
		$a->set_max_file_size(1024*1024);
		$this->display_module($a);
	}
	
	public function attachment_page_desc_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_pages_data/'.$arg['language'].'/'.$arg['page']));
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$a->set_persistent_delete();
		$a->set_max_file_size(1024*1024);
		$this->display_module($a);
	}

	public function get_3rd_party_info_addon($arg){
	}
	
	public function warehouse_item_addon($arg) {
		$recs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('item_name'=>$arg['id']));
		if(empty($recs)) {
		    print('<h1><a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','publish_warehouse_item'),$arg['id']).'>'.$this->t('Publish').'</a></h1>');
		    return;
		}
		$rec = array_pop($recs);

		$on = '<span class="checkbox_on" />';
		$off = '<span class="checkbox_off" />';
		
		print('<h1>'.Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$rec['id']).$this->t('Go to item').Utils_RecordBrowserCommon::record_link_close_tag().'</h1>');

		//opts
 		$m = & $this->init_module('Utils/GenericBrowser',null,'t0');
 		$m->set_table_columns(array(
				array('name'=>$this->t('Option')),
				array('name'=>$this->t('Value')),
				array('name'=>$this->t('Actions'))
					    ));
 		$m->add_row($this->t('Published'),($rec['publish']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_publish'),array($rec['id'],!$rec['publish'])).'>'.$this->t('toggle').'</a>');
 		$m->add_row($this->t('Recommended'),($rec['recommended']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_recommended'),array($rec['id'],!$rec['recommended'])).'>'.$this->t('toggle').'</a>');
 		$m->add_row($this->t('Exclude compare services'),($rec['exclude_compare_services']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_exclude_compare_services'),array($rec['id'],!$rec['exclude_compare_services'])).'>'.$this->t('toggle').'</a>');
 		$m->add_row($this->t('Always on stock'),($rec['always_on_stock']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_always_on_stock'),array($rec['id'],!$rec['always_on_stock'])).'>'.$this->t('toggle').'</a>');
 		$m->add_row($this->t('Assigned category'),($arg['category']?$on:$off),'');
		$quantity = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$arg['id'],'>quantity'=>0));
 		$m->add_row($this->t('Available in warehouse'),(empty($quantity)?$off:$on),'');
 		$m->add_row($this->t('Common attachments'),Utils_AttachmentCommon::count('premium_ecommerce_products/'.$arg['id']),'');
//		$m->add_row('Related,recommended',Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$rec['id'],false,'edit').$this->t('Edit item').Utils_RecordBrowserCommon::record_link_close_tag());
		
 		$this->display_module($m);

		//langs
        if(Variable::get('ecommerce_item_descriptions')) {
     		$m = & $this->init_module('Utils/GenericBrowser',null,'t1');
 	    	$m->set_table_columns(array(
				array('name'=>$this->t('Language')),
				array('name'=>$this->t('Name')),
				array('name'=>$this->t('Description')),
				array('name'=>$this->t('Parameters')),
				array('name'=>$this->t('Attachments')),
				array('name'=>$this->t('Actions'))
					    ));
    		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
	    	foreach($langs as $code=>$name) {
		        $descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$rec['item_name'],'language'=>$code),array('display_name','short_description'));
		        $descs = array_pop($descs);
    		    $params = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters',array('item_name'=>$rec['item_name'],'language'=>$code));
	    	    $attachments = Utils_AttachmentCommon::count('premium_ecommercedescriptions/'.$code.'/'.$arg['id']);
 		        $m->add_row($name,($descs && isset($descs['display_name']) && $descs['display_name'])?$on:$off,($descs && isset($descs['short_description']) && $descs['short_description'])?$on:$off,empty($params)?$off:$on,$attachments,
 		        		$descs?Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_descriptions',$descs['id'],false,'edit').$this->t('Edit').Utils_RecordBrowserCommon::record_link_close_tag():'<a '.Utils_RecordBrowserCommon::create_new_record_href('premium_ecommerce_descriptions',array('language'=>$code,'item_name'=>$arg['id'])).'>'.$this->t('Add').'</a>');
    		}
 	    	$this->display_module($m);
        }
        
		//currencies
        if(Variable::get('ecommerce_item_prices')) {
     		$m = & $this->init_module('Utils/GenericBrowser',null,'t2');
 	    	$m->set_table_columns(array(
				array('name'=>$this->t('Currency')),
				array('name'=>$this->t('Gross Price')),
				array('name'=>$this->t('Tax Rate')),
				array('name'=>$this->t('Actions'))
					    ));
    		$curr_opts = Premium_Warehouse_eCommerceCommon::get_currencies();
	    	foreach($curr_opts as $id=>$code) {
		        $prices = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$rec['item_name'],'currency'=>$id),array('gross_price','tax_rate'));
		        $prices = array_pop($prices);
    		    if($prices && isset($prices['gross_price'])) {
    			    $tax = Utils_RecordBrowserCommon::get_record('data_tax_rates',$prices['tax_rate']);
    			    $m->add_row($code,$prices['gross_price'],$tax['name'],
 		    		Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_prices',$prices['id'],false,'edit').$this->t('Edit').Utils_RecordBrowserCommon::record_link_close_tag());
	    	    } else {
         		    $m->add_row($code,$off,$off,
         		    	'<a '.Utils_RecordBrowserCommon::create_new_record_href('premium_ecommerce_prices',array('currency'=>$id,'item_name'=>$arg['id'])).'>'.$this->t('Add').'</a>');
    		    }
	    	}
 		    $this->display_module($m);
 		}
	}
	
	public function stats() {
		$this->caption = 'eCommerce stats';

		$t = time();
		$start = & $this->get_module_variable('stats_start',date('Y-m-d', $t - (30 * 24 * 60 * 60))); //last 30 days
		$end = & $this->get_module_variable('stats_end',date('Y-m-d',$t));

		$form = $this->init_module('Libs/QuickForm',null,'reports_frm');

		$form->addElement('datepicker', 'start', $this->t('From'));
		$form->addElement('datepicker', 'end', $this->t('To'));
		$form->addElement('submit', 'submit_button', $this->t('Show'));
		$form->addRule('start', 'Field required', 'required');
		$form->addRule('end', 'Field required', 'required');
		$form->setDefaults(array('start'=>$start,'end'=>$end));

		if($form->validate()) {
			$data = $form->exportValues();
			$start = $data['start'];
			$end = $data['end'];
			$end = date('Y-m-d',strtotime($end)+86400);
		}
		$form->display();

		$tb = & $this->init_module('Utils/TabbedBrowser');
		$tb->set_tab($this->t("Products"), array($this,'stats_tab'),array('products',$start,$end));
		$tb->set_tab($this->t("Pages"), array($this,'stats_tab'),array('pages',$start,$end));
		$tb->set_tab($this->t("Categories"), array($this,'stats_tab'),array('categories',$start,$end));
		$tb->set_tab($this->t("Searched Words"), array($this,'stats_tab'),array('searched',$start,$end));
		$this->display_module($tb);
		$this->tag();
	}
	
	public function stats_tab($tab,$start,$end) {
		$start_reg = Base_RegionalSettingsCommon::reg2time($start,false);
		$end_reg = Base_RegionalSettingsCommon::reg2time($end,false);
		
		if($tab=='searched') {
			$ret = DB::Execute('SELECT obj,count(visited_on) as num, obj as name FROM premium_ecommerce_'.$tab.'_stats WHERE visited_on>=%T AND visited_on<%T GROUP BY obj ORDER BY num DESC LIMIT 10',array($start_reg,$end_reg+3600*24));
		} else {
			$aj = '';
			switch($tab) {
			    case 'categories':
				$jf = 'j.f_category_name';
				$j = 'premium_warehouse_items_categories_data_1 j';
				break;
			    case 'pages':
				$jf = 'j.f_page_name';
				$j = 'premium_ecommerce_pages_data_1 j';
				break;
			    case 'products':
				$jf = 'j.f_item_name';
				$j = 'premium_warehouse_items_data_1 j';
				break;
			}
			$ret = DB::Execute('SELECT obj,count(visited_on) as num, '.$jf.' as name FROM premium_ecommerce_'.$tab.'_stats INNER JOIN '.$j.' ON (obj=j.id) WHERE visited_on>=%T AND visited_on<%T GROUP BY obj ORDER BY num DESC LIMIT 10',array($start_reg,$end_reg+3600*24));
		}

		$f = $this->init_module('Libs/OpenFlashChart');
		$title = new OFC_Elements_Title( $this->t($tab) );
		$f->set_title( $title );

		$av_colors = array('#339933','#999933', '#993333', '#336699', '#808080','#339999','#993399');
		$max = -1;
		$i = 0;
		while($row = $ret->FetchRow()) {
			$bar = new OFC_Charts_Bar();
			$bar->set_colour($av_colors[$i%count($av_colors)]);
			$bar->set_key($row['name'],10);
			$bar->set_values( array((int)$row['num']) );
			if($max<$row['num']) $max = $row['num'];
			$f->add_element( $bar );
			$i++;
		}
		if($max==-1) {
		    print($this->t("No stats available"));
		    return;
		}
		$y_ax = new OFC_Elements_Axis_Y();
		$y_ax->set_range(0,$max);
		$y_ax->set_steps($max/10);
		$f->set_y_axis($y_ax);

		$f->set_width(950);
		$f->set_height(400);
		$this->display_module($f);
	}
	
	public function pages_stats_addon($arg) {
		$this->stats_addon('pages',$arg['id']);
	}
	
	public function categories_stats_addon($arg) {
		$this->stats_addon('categories',$arg['id']);
	}
	
	public function products_stats_addon($arg) {
		$this->stats_addon('products',$arg['item_name']);
	}
	
	private function stats_addon($tab,$id) {
		$gb = & $this->init_module('Utils/GenericBrowser',null,'stats');

		$gb->set_table_columns(array(
			array('name'=>$this->t('Time'), 'order'=>'visited_on')));

		$query = 'SELECT visited_on FROM premium_ecommerce_'.$tab.'_stats WHERE obj='.$id;
		$query_qty = 'SELECT count(*) FROM premium_ecommerce_'.$tab.'_stats WHERE obj='.$id;

		$gb->set_default_order(array($this->t('Time')=>'DESC'));
		$ret = $gb->query_order_limit($query, $query_qty);
		
		while(($row=$ret->FetchRow())) {
			$gb->add_row($row['visited_on']);
		}

		$this->display_module($gb);
	}
	
	public function product_comments_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_product_comments');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('time'=>'DESC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'content'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function users_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_users');
		$order = array(array('contact'=>$arg['id']), array('contact'=>false), array());
		$rb->set_defaults(array('contact'=>$arg['id']));
		$ret = Utils_RecordBrowserCommon::get_records('premium_ecommerce_users',array('contact'=>$arg['id']));
		if(count($ret)) $rb->set_button(false);
		$this->display_module($rb,$order,'show_data');
	}
	
	public function newsletter() {
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_newsletter');
		$args = array(array(), array(), array('email'=>'ASC'));
		$this->display_module($this->rb,$args,'show_data');
	}

	public function comments() {
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_product_comments');
		$args = array(array('publish'=>0), array('product'=>true,'publish'=>false), array('time'=>'DESC'));
		$this->rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'content'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->rb->set_additional_actions_method(array($this, 'comments_publish_action'));
		$this->display_module($this->rb,$args,'show_data');
	
	}
	
	public function comments_publish_action($r, & $gb_row) {
		if(isset($_REQUEST['publish_action']) && $r['id']==$_REQUEST['publish_action']) {
    		    Utils_RecordBrowserCommon::update_record('premium_ecommerce_product_comments',$r['id'],array('publish'=>1));
		    location(array());
		}
		$gb_row->add_action(Module::create_href(array('publish_action'=>$r['id'])),'Publish',null,'restore');
	}
	
	public function caption(){
		if (isset($this->caption)) return $this->caption;
		if (isset($this->rb)) return $this->rb->caption();
		return 'eCommerce administration';
	}
	
	public function applet($conf,$opts) {
		//available applet options: toggle,href,title,go,go_function,go_arguments,go_contruct_arguments
		$opts['go'] = false; // enable/disable full screen
		$xxx = array(-2=>'New Online Order (with payment)', -1=>'New Online Order', 2=>'Order Received', 3=>'Payment Confirmed', 4=>'Order Confirmed', 5=>'On Hold', 6=>'Order Ready to Ship', 7=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing','active'=>'Active');
		$opts['title'] = $this->t('eCommerce - %s',array(Base_LangCommon::ts('Base_Dashboard',$xxx[$conf['status']])));
		
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
									array('Premium_Warehouse_eCommerceCommon','applet_info_format'),
									15,
									$conf,
									& $opts
				);
		$this->display_module($rb, $conds, 'mini_view');

	}
	
	public function setup_3rd_party_plugins() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
	    Base_ActionBarCommon::add('search','Scan plugins', $this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','scan_for_3rdp_info_plugins')));
        $this->recordset = '3rdp_info';
        $this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_3rdp_info','premium_ecommerce_3rdp_info');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
        $this->display_module($this->rb);
        
        return true;
	}
}

?>