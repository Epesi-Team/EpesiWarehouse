<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * eCommerce
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
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
		$buttons = array();
//		$icon = Base_ThemeCommon::get_template_file($name,'icon.png');
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'availability')).'>'.$this->ht('Availability').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'icecat')).'>'.$this->ht('Icecat').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'pages')).'>'.$this->ht('Pages').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'parameters')).'>'.$this->ht('Parameters').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'parameter_groups')).'>'.$this->ht('Parameter Groups').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'payments_carriers')).'>'.$this->ht('Payments & Carriers').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'polls')).'>'.$this->ht('Polls').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'QC_dirs')).'>'.$this->ht('Quickcart settings').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'rules_page')).'>'.$this->ht('Rules & Policies').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'start_page')).'>'.$this->ht('Start Page').'</a>',
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
		$this->rb->set_additional_actions_method($this, 'actions_for_position');
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);
	}

	public function parameters() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->recordset = 'parameters';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameters');
		$this->rb->set_additional_actions_method($this, 'actions_for_position');
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function parameter_groups() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());

		$this->recordset = 'parameter_groups';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameter_groups');
		$this->rb->set_additional_actions_method($this, 'actions_for_position');
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function polls() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->recordset = 'polls';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_polls');
		$this->rb->set_defaults(array('publish'=>1));
		$this->rb->set_additional_actions_method($this, 'actions_for_position');
		$this->rb->force_order(array('position'=>'ASC'));
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
			'votes'=>array('width'=>1, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function availability() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->recordset = 'availability';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_availability');
		$this->rb->set_additional_actions_method($this, 'actions_for_position');
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function pages() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->recordset = 'pages';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages');
		$this->rb->set_defaults(array('publish'=>1,'type'=>2));
		$this->rb->set_additional_actions_method($this, 'actions_for_position');
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function payments_carriers() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_payments_carriers');
		$this->display_module($this->rb);

		return true;
	}

	public function actions_for_position($r, & $gb_row) {
		$tab = 'premium_ecommerce_'.$this->recordset;
		if(isset($_REQUEST['pos_action']) && $r['id']==$_REQUEST['pos_action'] && is_numeric($_REQUEST['old']) && is_numeric($_REQUEST['new'])) {
		    $recs = Utils_RecordBrowserCommon::get_records($tab,array('position'=>$_REQUEST['new']), array('id'));
		    foreach($recs as $rr)
			Utils_RecordBrowserCommon::update_record($tab,$rr['id'],array('position'=>$_REQUEST['old']));
    		    Utils_RecordBrowserCommon::update_record($tab,$r['id'],array('position'=>$_REQUEST['new']));
		    location(array());
		}
		if($r['position']>0)
		    $gb_row->add_action(Module::create_href(array('pos_action'=>$r['id'],'old'=>$r['position'],'new'=>$r['position']-1)),'move-up');
		static $max;
		if(!isset($max))
		    $max = Utils_RecordBrowserCommon::get_records_limit($tab);
		if($r['position']<$max-1)
    		    $gb_row->add_action(Module::create_href(array('pos_action'=>$r['id'],'old'=>$r['position'],'new'=>$r['position']+1)),'move-down');
	}

	public function parameter_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameter_labels');
		$order = array(array('parameter'=>$arg['id']), array('parameter'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('parameter'=>$arg['id']));
		$rb->set_header_properties(array(
			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function parameter_group_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameter_group_labels');
		$order = array(array('group'=>$arg['id']), array('group'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('group'=>$arg['id']));
		$rb->set_header_properties(array(
			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function availability_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_availability_labels');
		$order = array(array('availability'=>$arg['id']), array('availability'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('availability'=>$arg['id']));
		$rb->set_header_properties(array(
			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function descriptions_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_descriptions');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name']));
		$rb->set_header_properties(array(
			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function cat_descriptions_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_cat_descriptions');
		$order = array(array('category'=>$arg['id']), array('category'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('category'=>$arg['id']));
		$rb->set_header_properties(array(
			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function parameters_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_products_parameters');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('parameter'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name']));
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
//			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
//			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
//									));
		$this->display_module($rb,$order,'show_data');
	}

	public function pages_info_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages_data');
		$order = array(array('page'=>$arg['id']), array('page'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('page'=>$arg['id']));
		$rb->set_header_properties(array(
			'language'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'name'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function prices_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_prices');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('currency'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name']));
		$rb->set_header_properties(array(
			'currency'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'price'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function start_page() {
		return $this->edit_variable('Start page','ecommerce_start_page');
	}
	
	public function rules_page() {
		return $this->edit_variable('Rules and policies','ecommerce_rules');
	}

	public function QC_dirs() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$gb = & $this->init_module('Utils/GenericBrowser',null,'qc_list');

		$gb->set_table_columns(array(array('name'=>$this->t('Path'), 'order'=>'path')));

		$query = 'SELECT path FROM premium_ecommerce_quickcart';
		$query_qty = 'SELECT count(*) FROM premium_ecommerce_quickcart';

		$ret = $gb->query_order_limit($query, $query_qty);
		
		if($ret)
			while(($row=$ret->FetchRow())) {
			    $r = $gb->get_new_row();
			    $r->add_data($row['path']);
			    $r->add_action($this->create_confirm_callback_href($this->ht('Are you sure you want to delete this record?'),array($this,'delete_quickcart'),$row['path']),'delete');
			}

		$this->display_module($gb);

		$qf = $this->init_module('Libs/QuickForm',null,'th_size');
		$qf->addElement('select','quickcart_thumbnail_size',$this->t('Thumbnails size'),array(0=>$this->ht('100 x 100'),1=>$this->ht('200 x 200')),array('onChange'=>$qf->get_submit_form_js()));
		$qf->setDefaults(array('quickcart_thumbnail_size'=>Variable::get('quickcart_thumbnail_size')));
		if($qf->validate()) {
			Variable::set('quickcart_thumbnail_size',$qf->exportValue('quickcart_thumbnail_size'));
		}
		$qf->display();

		Base_ActionBarCommon::add('add','Add',$this->create_callback_href(array($this,'add_quickcart')));
		
		return true;
	}
	
	public function add_quickcart() {
		if($this->is_back()) return false;
	
		$form = & $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, $this->t('Add quickcart binding'));

		$form->addElement('text', 'path', $this->t('Path'));
		$form->addRule('path', $this->t('A path must be between 3 and 255 chars'), 'rangelength', array(3,255));
		$form->registerRule('check_path','callback','check_path','Premium_Warehouse_eCommerce');
		$form->addRule('path', $this->t('Invalid path'), 'check_path');
		$form->addRule('path', $this->t('Field required'), 'required');

		if($form->validate()) {
		    $p = rtrim($form->exportValue('path'),'/');
		    DB::Execute('INSERT INTO premium_ecommerce_quickcart(path) VALUES(%s)',array($p));
		    @set_time_limit(0);
		    @mkdir($p.'/files/epesi');
		    @mkdir($p.'/files/100/epesi');
		    @mkdir($p.'/files/200/epesi');
		    Utils_AttachmentCommon::call_user_func_on_file('Premium/Warehouse/eCommerce',array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		    return false;
		} else $form->display();

		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		Base_ActionBarCommon::add('save', 'Save', $form->get_submit_form_href(true,$this->t('creating thumbnails, please wait')));
		
    		return true;
	}
	
	public function check_path($p) {
	    if(!is_dir($p) || !is_dir(rtrim($p,'/').'/files') || !is_writable(rtrim($p,'/').'/files')) return false;
	    return true;
	}
	
	public function delete_quickcart($path) {
	    DB::Execute('DELETE FROM premium_ecommerce_quickcart WHERE path=%s',array($path));
	    @recursive_rmdir($path.'/files/epesi/');
	    @recursive_rmdir($path.'/files/100/epesi/');
	    @recursive_rmdir($path.'/files/200/epesi/');
	}
	
	public function icecat() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$form = & $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, $this->t('Ice cat settings'));
		
		eval_js_once('icecat_enabled = function(v) {'.
			    'if(v==1){$("icecat_user").enable();$("icecat_pass").enable();}'.
			    'else{$("icecat_user").disable();$("icecat_pass").disable();}'.
			    '};');
		
		$form->addElement('select', 'enabled', $this->t('Enabled'), array($this->ht('No'),$this->ht('Yes')), array('onChange'=>'icecat_enabled(this.value)'));
		// require a username and password
		$form->addElement('text', 'user', $this->t('Username'), array('id'=>'icecat_user'));
		$form->addElement('password', 'pass', $this->t('Password'), array('id'=>'icecat_pass'));

		$user = Variable::get('icecat_user');
		$pass = Variable::get('icecat_pass');
		if($user && $pass)
		    $enabled = 1;
		else
		    $enabled = 0;
		$form->setDefaults(array('enabled'=>$enabled,'user'=>$user,'pass'=>$pass));

		$enabled = $form->exportValue('enabled');
		eval_js('icecat_enabled('.$enabled.')');
		if($enabled) {
		    $form->addRule('user',$this->t('Field required'),'required');
		    $form->addRule('pass',$this->t('Field required'),'required');
    		    $form->registerRule('check_icecat','callback','check_icecat_pass','Premium_Warehouse_eCommerce');
		    $form->addRule(array('user','pass'), $this->t('Invalid username or password.'), 'check_icecat');
		}

		if($form->validate()) {
		    $vals = $form->exportValues();
		    if(!$vals['enabled']) {
			$vals['user'] = '';
			$vals['pass'] = '';
		    }
		    Variable::set('icecat_user',$vals['user']);
		    Variable::set('icecat_pass',$vals['pass']);
    		    Base_StatusBarCommon::message($this->t('Settings saved'));
		    return false;
		}
		$form->display();

		Base_ActionBarCommon::add('save', 'Save', $form->get_submit_form_href(true));
		
		return true;
	}
	
	public function check_icecat_pass($user) {
	    $url = 'http://data.icecat.biz/xml_s3/xml_server3.cgi?prod_id=RJ459AV;vendor=hp;lang=pl;output=productxml';
	    $c = curl_init();
	    curl_setopt($c, CURLOPT_URL, $url);
	    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($c, CURLOPT_USERPWD,$user[0].':'.$user[1]);
	    $output = curl_exec($c);
	    $response_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	    return $response_code!=401;
	}
	
	private function edit_variable($header, $v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
	
		$f = $this->init_module('Libs/QuickForm');
		
		$f->addElement('header',null,$this->t($header));

		$fck = & $f->addElement('fckeditor', 'content', $this->t('Content'));
		$fck->setFCKProps('800','300',true);
		
		$f->setDefaults(array('content'=>Variable::get($v)));

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

	public function attachment_product_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Warehouse/eCommerce/Products/'.$arg['item_name']));
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$a->set_add_func(array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$this->display_module($a);
	}

	public function attachment_product_desc_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Warehouse/eCommerce/ProductsDesc/'.$arg['language'].'/'.$arg['item_name']));
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$a->set_add_func(array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$this->display_module($a);
	}

	public function attachment_page_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Warehouse/eCommerce/Pages/'.$arg['id']));
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$a->set_add_func(array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$this->display_module($a);
	}
	
	public function attachment_page_desc_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Warehouse/eCommerce/PagesDesc/'.$arg['id']));
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$a->set_add_func(array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$this->display_module($a);
	}

	public function icecat_addon($arg){
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
					    ));
 		$m->add_row($this->t('Published'),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_publish'),array($rec['id'],!$rec['publish'])).'>'.($rec['publish']?$on:$off).'</a>');
 		$m->add_row($this->t('Assigned category'),($arg['category']?$on:$off));
		$quantity = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$arg['id'],'>quantity'=>0));
 		$m->add_row($this->t('Available in warehouse'),(empty($quantity)?$off:$on));
 		$this->display_module($m);

		//langs
 		$m = & $this->init_module('Utils/GenericBrowser',null,'t1');
 		$m->set_table_columns(array(
				array('name'=>$this->t('Language')),
				array('name'=>$this->t('Name')),
				array('name'=>$this->t('Description')),
				array('name'=>$this->t('Parameters')),
					    ));
		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
		foreach($langs as $code=>$name) {
		    $descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$rec['item_name'],'language'=>$code),array('display_name','short_description'));
		    $descs = array_pop($descs);
		    $params = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters',array('item_name'=>$rec['item_name'],'language'=>$code));
 		    $m->add_row($name,($descs && isset($descs['display_name']) && $descs['display_name'])?$on:$off,($descs && isset($descs['short_description']) && $descs['short_description'])?$on:$off,empty($params)?$off:$on);
		}
 		$this->display_module($m);

		//currencies
 		$m = & $this->init_module('Utils/GenericBrowser',null,'t2');
 		$m->set_table_columns(array(
				array('name'=>$this->t('Currency')),
				array('name'=>$this->t('Gross Price')),
				array('name'=>$this->t('Tax Rate')),
					    ));
		$curr_opts = Premium_Warehouse_eCommerceCommon::get_currencies();
		foreach($curr_opts as $id=>$code) {
		    $prices = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$rec['item_name'],'currency'=>$id),array('gross_price','tax_rate'));
		    $prices = array_pop($prices);
		    if($prices && isset($prices['gross_price'])) {
    			    $tax = Utils_RecordBrowserCommon::get_record('data_tax_rates',$prices['tax_rate']);
    			    $m->add_row($code,$prices['gross_price'],$tax['name']);
		    } else {
         		    $m->add_row($code,$off,$off);
		    }
		}
 		$this->display_module($m);
	}
	
	public function stats() {
		$this->caption = 'eCommerce stats';

		$t = time();
		$start = & $this->get_module_variable('stats_start',date('Y-m-d', $t - (30 * 24 * 60 * 60))); //last 30 days
		$end = & $this->get_module_variable('stats_end',date('Y-m-d',$t));

		$form = $this->init_module('Libs/QuickForm',null,'reports_frm');

		$form->addElement('datepicker', 'start', $this->t('From'));
		$form->addElement('datepicker', 'end', $this->t('To'));
		$form->addElement('submit', 'submit_button', $this->ht('Show'));
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
		$this->display_module($tb);
		$this->tag();
	}
	
	public function stats_tab($tab,$start,$end) {
		$start_reg = Base_RegionalSettingsCommon::reg2time($start);
		$end_reg = Base_RegionalSettingsCommon::reg2time($end);
		
		$ret = DB::Execute('SELECT obj,count(visited_on) as num FROM premium_ecommerce_'.$tab.'_stats WHERE visited_on>=%d AND visited_on<%d GROUP BY obj',array($start_reg,$end_reg));

		$f = $this->init_module('Libs/OpenFlashChart');
		$title = new title( $this->ht($tab) );
		$f->set_title( $title );

		$av_colors = array('#339933','#339933','#999933', '#993333', '#336699', '#808080','#339999','#993399');
		$max = -1;
		while($row = $ret->FetchRow()) {
			$bar = new bar_glass();
			$bar->set_colour($av_colors[$row['obj']%count($av_colors)]);
			$bar->set_key($row['obj'],3);
			$bar->set_values( array($row['num']) );
			if($max<$row['num']) $max = $row['num'];
			$f->add_element( $bar );
		}
		if($max==-1) {
		    print($this->t("No stats available"));
		    return;
		}
		$y_ax = new y_axis();
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
		$this->stats_addon('products',$arg['product_name']);
	}
	
	private function stats_addon($tab,$id) {
		$gb = & $this->init_module('Utils/GenericBrowser',null,'stats');

		$gb->set_table_columns(array(
			array('name'=>$this->t('Time'), 'order'=>'visited_on')));

		$query = 'SELECT visited_on FROM premium_ecommerce_'.$tab.'_stats WHERE obj='.$id;
		$query_qty = 'SELECT count(*) FROM premium_ecommerce_'.$tab.'_stats WHERE obj='.$id;

		$ret = $gb->query_order_limit($query, $query_qty);
		
		while(($row=$ret->FetchRow())) {
			$gb->add_row($row['visited_on']);
		}

		$this->display_module($gb);
	}
	
	public function caption(){
		if (isset($this->caption)) return $this->caption;
		if (isset($this->rb)) return $this->rb->caption();
		return 'eCommerce administration';
	}
}

?>