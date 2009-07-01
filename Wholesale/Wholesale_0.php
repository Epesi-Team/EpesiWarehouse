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
	
	public function items_addon($arg) {
		$gb = $this->init_module('Utils/GenericBrowser', null, 'wholesale_items_addon');
		$gb->set_table_columns(array(
			array('name'=>$this->t('SKU'), 'width'=>6, 'wrapmode'=>'nowrap'),
			array('name'=>$this->t('Item Name'), 'width'=>40, 'wrapmode'=>'nowrap'),
			array('name'=>$this->t('Distributor Code'), 'width'=>7, 'wrapmode'=>'nowrap'),
			array('name'=>$this->t('Price'), 'width'=>7, 'wrapmode'=>'nowrap'),
			array('name'=>$this->t('Quantity'), 'width'=>7, 'wrapmode'=>'nowrap'),
			array('name'=>$this->t('Quantity Details'), 'width'=>7, 'wrapmode'=>'nowrap')
		));
		$limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_wholesale_items WHERE distributor_id=%d AND (quantity!=%d OR quantity_info!=%s)', array($arg['id'],0,'')));
		$ret = DB::SelectLimit('SELECT * FROM premium_warehouse_wholesale_items WHERE distributor_id=%d AND (quantity!=%d OR quantity_info!=%s) ORDER BY item_id', $limit['numrows'], $limit['offset'], array($arg['id'],0,''));
		while ($row=$ret->FetchRow()) {
			if ($row['item_id']) {
//				$item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $row['item_id']);
				$sku = Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items', 'sku', $row['item_id']); 
			} else {
				$form = $this->init_module('Libs/QuickForm');
				$field = 'link_it_'.$row['id'];
				$form->addElement('autocomplete', $field, '', array($this->get_type().'Common', 'item_match_autocomplete'), array($row['distributor_id']));
				$form->addElement('submit', 'submit', '');
				$theme = $this->init_module('Base/Theme');
				$form->assign_theme('form', $theme);
				$theme->assign('field_name', $field);
				ob_start();
				$theme->display('match_form');
				if ($form->validate()) {
					$sku = $form->exportValue($field);
					$item_id = Utils_RecordBrowserCommon::get_id('premium_warehouse_items', 'sku', $sku);
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE id=%d', array($item_id, $row['id']));
					location(array());
				} else {
					$sku = ob_get_clean();
				}
			}
			$gb->add_row(
				$sku,
//				$item['item_name'],
				$row['distributor_item_name'],
				array('value'=>$row['internal_key'], 'style'=>'text-align:right;'),
				array('value'=>Utils_CurrencyFieldCommon::format($row['price'],$row['price_currency']), 'style'=>'text-align:right;'),
				array('value'=>$row['quantity'], 'style'=>'text-align:right;'),
				$row['quantity_info']
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
		$limit = $gb->get_limit(DB::GetOne('SELECT COUNT(*) FROM premium_warehouse_wholesale_items WHERE item_id=%d AND (quantity!=%d OR quantity_info!=%s)', array($arg['id'],0,'')));
		$ret = DB::SelectLimit('SELECT * FROM premium_warehouse_wholesale_items WHERE item_id=%d AND (quantity!=%d OR quantity_info!=%s)', $limit['numrows'], $limit['offset'], array($arg['id'],0,''));
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

	public function caption(){
		if (isset($this->rb)) return $this->rb->caption();
	}
}

?>