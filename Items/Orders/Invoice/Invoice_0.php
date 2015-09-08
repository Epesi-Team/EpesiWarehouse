<?php
/**
 * Warehouse Orders Invoicing Module
 * @author pbukowski@telaxus.com
 * @copyright Telaxus LLC
 * @license Commercial
 * @version 0.1
 * @package epesi-Premium/Warehouse/Items/Orders
 * @subpackage Invoice
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_Orders_Invoice extends Module {
        private $rb;

	public function body() {
	}

        public function addon() {
        
        }
        
/*        public function record_picker() {
                $rp = $this->init_module('Utils/RecordBrowser/RecordPickerFS',array('premium_timesheet',(isset($browse_params['crits']) && is_array($browse_params['crits']))?Utils_RecordBrowserCommon::merge_crits(array('customer_billed'=>0),$browse_params['crits']):array('customer_billed'=>0),array(),array('Start'=>'DESC')));
		Base_ActionBarCommon::add('save',__('Custom customer billing'),$rp->create_open_href());
		$this->display_module($rp);
		$sel = $rp->get_selected();
		if($sel) {
			$rp->clear_selected();
			$this->bill_customer(array('id'=>$sel));
			eval_js('_chj(\'\',\'\',\'queue\')');
		}
		
		$rp2 = $this->init_module('Utils/RecordBrowser/RecordPickerFS',array('premium_timesheet',Utils_RecordBrowserCommon::merge_crits(array('employee_billed'=>0),$browse_params['crits']),array(),array('Start'=>'DESC')));
		Base_ActionBarCommon::add('save',__('Custom employee billing'),$rp2->create_open_href());
		$this->display_module($rp2);
		$sel = $rp2->get_selected();
		if($sel) {
			$rp2->clear_selected();
			$recs = Utils_RecordBrowserCommon::get_records('premium_timesheet',array('id'=>$sel),array('employee_amount','id'));
			foreach($recs as $r) {
				$amount = Utils_CurrencyFieldCommon::get_values($r['employee_amount']);
				if($amount[0]=='') continue;
				Utils_RecordBrowserCommon::update_record('premium_timesheet',$r['id'],array('employee_billed'=>1));
			}
			eval_js('_chj(\'\',\'\',\'queue\')');
		}

        }
        
        public function push_record_picker($crits) {
                print_r($crits);
        }
*/
}

?>