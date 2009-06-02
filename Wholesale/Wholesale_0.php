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

	public function caption(){
		if (isset($this->rb)) return $this->rb->caption();
	}
}

?>