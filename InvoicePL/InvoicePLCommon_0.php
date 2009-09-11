<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Location
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-invoicepl
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_InvoicePLCommon extends ModuleCommon {
	private static $rb_obj=null;
	
	public static function user_settings() {
		return array(	
			'Invoice'=>array(
				array('name'=>'number_format','label'=>'Invoice Number Format','type'=>'text','default'=>'%n/%Y')
			));
	}

	public static function invoice_pl_addon_parameters($record) {
		if ($record['transaction_type']<=1) {
			$href = 'href="modules/Premium/Warehouse/InvoicePL/print_invoice.php?'.http_build_query(array('record_id'=>$record['id'], 'cid'=>CID)).'"';
			if (!$record['invoice_number'] && $record['transaction_type']==1) {
				$href .= ' '.Utils_TooltipCommon::open_tag_attrs(Base_LangCommon::ts('Premium_Warehouse_InvoicePL','Invoice number is not defined<br>It will be assgined automatically upon print'), false);
			}
			Base_ActionBarCommon::add('print', 'Print Invoice', $href);
		}
		return array('show'=>false);
	}

	public static function submit_order($values, $mode) {
		if (($mode=='edit' || $mode=='add') && $values['status']==4 && $values['transaction_type']==1 && (!isset($values['invoice_number']) || !$values['invoice_number'])) {
			$values['invoice_number'] = self::generate_invoice_number($values);
		}
		return Premium_Warehouse_Items_OrdersCommon::submit_order($values, $mode);
	}

	public static function generate_invoice_number($order) {
		$order['invoice_number'] = DB::GetOne('SELECT MAX(f_invoice_number) FROM premium_warehouse_items_orders_data_1 WHERE f_warehouse=%d AND f_transaction_type=%d AND f_receipt=%d', array($order['warehouse'], $order['transaction_type'], $order['receipt']));
		$order['invoice_number']++;
		Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $order['id'], array('invoice_number'=>$order['invoice_number']));
		return $order['invoice_number'];
	}

	public static function display_invoice_number($r, $nolink) {
		$postfix = '';
		if ($r['transaction_type']==1 && $r['invoice_number']) {
			$conflicts = self::get_conflict_invoices($r);
			if (!empty($conflicts)) {
				$postfix = '<img src="'.Base_ThemeCommon::get_template_file('Premium_Warehouse_InvoicePL','conflict.png').'">';
				$msg = '';
				foreach ($conflicts as $v) {
					if ($msg) $msg .= ', ';
					$msg .= $v['transaction_id'];
				} 
				$msg = Base_LangCommon::ts('Premium_Warehouse_InvoicePL','Warning: Found duplicate invoice number, transaction'.(count($conflicts)==1?'':'s').': ').$msg;
				$postfix = Utils_TooltipCommon::create($postfix, $msg, false);
			}
		}
		return $r['invoice_number'].$postfix;
	}
	
	public static function get_conflict_invoices($order) {
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',array('transaction_type'=>1, '!id'=>$order['id'], 'invoice_number'=>$order['invoice_number']));
		return $recs;
	}
	
	public static function check_number($data) {
		if ($data['invoice_number'] == Utils_RecordBrowser::$last_record['invoice_number'] || !$data['invoice_number']) return true;
		if (!isset($data['warehouse'])) $data['warehouse'] = Utils_RecordBrowser::$last_record['warehouse'];
		$crits = array('warehouse'=>$data['warehouse'], 'invoice_number'=>$data['invoice_number']);
		if (isset(Utils_RecordBrowser::$last_record['id'])) $crits['!id'] = Utils_RecordBrowser::$last_record['id'];
		$other = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', $crits);
		$warning = self::$rb_obj->get_module_variable('premium_invoice_pl_warning', null);
		if (!empty($other) && $warning!==$data['invoice_number']) {
			self::$rb_obj->set_module_variable('premium_invoice_pl_warning', $data['invoice_number']);
			return array('invoice_number'=>Base_LangCommon::ts('Premium_Warehouse_InvoicePL','Warning: duplicate number found'));
		}
		return true;
	}

	public static function QFfield_invoice_number(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		if ($mode!='view') {
			self::$rb_obj = $rb_obj; 
			$form->addFormRule(array('Premium_Warehouse_InvoicePLCommon', 'check_number'));
			$form->addElement('text', $field, $label, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			$postfix = '';
			if (Utils_RecordBrowser::$last_record['transaction_type']==1) {
				if (!Utils_RecordBrowser::$last_record['invoice_number']) {
					if (isset($_REQUEST['assign_invoice_number']) &&
						$_REQUEST['assign_invoice_number'] == Utils_RecordBrowser::$last_record['id']) {
						$default = self::generate_invoice_number(Utils_RecordBrowser::$last_record);
					} else {
						$postfix = '<a '.Module::create_href(array('assign_invoice_number'=>Utils_RecordBrowser::$last_record['id'])).'>'.Base_LangCommon::ts('Premium_Warehouse_InvoicePL','[assign automatically]').'</a>';
					}
				} else {
					$conflicts = self::get_conflict_invoices(Utils_RecordBrowser::$last_record);
					if (!empty($conflicts)) {
						$postfix = '<br><img src="'.Base_ThemeCommon::get_template_file('Premium_Warehouse_InvoicePL','conflict.png').'">';
						$msg = '';
						foreach ($conflicts as $v) {
							if ($msg) $msg .= ', ';
							$msg .= Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items_orders','transaction_id',$v['id']);
						} 
						$msg = Base_LangCommon::ts('Premium_Warehouse_InvoicePL','Warning: Found duplicate invoice number, transaction'.(count($conflicts)==1?'':'s').': ').$msg;
						$postfix = $postfix.'&nbsp;'.$msg;
					}
				}
			}

			$rb_obj->set_module_variable('premium_invoice_pl_warning', null);
			$form->addElement('static', $field, $label, $default.$postfix);
		}
	}
}
?>
