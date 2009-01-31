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

	public function body() {
		if (isset($_REQUEST['recordset'])) 
			switch($_REQUEST['recordset']) {
				case 'products':
				case 'parameters':
				case 'availability':
				case 'pages':
					$this->set_module_variable('recordset', $_REQUEST['recordset']);
					break;
			}
		$mod = $this->get_module_variable('recordset');
		switch($mod) {
			case 'products':
				$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_products');
				$this->rb->set_defaults(array('publish'=>1,'position'=>0,'status'=>1));
				break;
			case 'parameters':
				$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameters');
				break;
			case 'availability':
				$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_availability');
				$this->rb->set_defaults(array('position'=>0));
				break;
			case 'pages':
				$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages');
				$this->rb->set_defaults(array('position'=>0,'publish'=>1));
				break;
		}
		$this->display_module($this->rb);
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
		$order = array(array('item_name'=>$arg['id']), array('item_name'=>false), array('parameter'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['id']));
		$rb->set_header_properties(array(
			'parameter'=>array('wrapmode'=>'nowrap'),
			'value'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function subpages_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages');
		$order = array(array('parent_page'=>$arg['id']), array(), array('page_name'=>'ASC'));
		$rb->set_defaults(array('parent_page'=>$arg['id']));
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
		$order = array(array('item_name'=>$arg['id']), array('item_name'=>false), array('currency'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['id']));
		$rb->set_header_properties(array(
			'currency'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'price'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function start_page() {
		$this->edit_variable('Start page','ecommerce_start_page');
	}
	
	public function rules_page() {
		$this->edit_variable('Rules and policies','ecommerce_rules');
	}
	
	private function edit_variable($header, $v) {
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
		}
		$f->display();	
	}
	
	public function caption(){
		if (isset($this->rb)) return $this->rb->caption();
	}
}

?>