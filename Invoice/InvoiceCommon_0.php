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
 * @subpackage warehouse-items-invoice
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_InvoiceCommon extends ModuleCommon {
	private static $rb_obj=null;
	
	public static function invoice_addon_parameters($record) {
		if (isset($record['id']) && (!isset($record['payment']) || $record['payment'])) {
			$href = 'target="_blank" href="modules/Premium/Warehouse/Invoice/print_invoice.php?'.http_build_query(array('record_id'=>$record['id'], 'cid'=>CID)).'"';
			if (!$record['invoice_number'] && $record['transaction_type']==1) {
				$href .= ' '.Utils_TooltipCommon::open_tag_attrs(__('Number is not defined').'<br>'.__('It will be assigned automatically upon print'), false);
			}
			if (!$record['receipt']) Base_ActionBarCommon::add('print', __('Print'), $href);

			if(isset($_GET['receipt_printed']) && $_GET['receipt_printed']==$record['id']) {
				Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $record['id'], array('invoice_print_date'=>date('Y-m-d')));
			} elseif(defined('ENABLE_RECEIPT_PRINTING') && ENABLE_RECEIPT_PRINTING) {
				load_js('modules/Premium/Warehouse/Invoice/receipt.js');
				Base_ActionBarCommon::add('print', __('Print Receipt'), 'onClick="print_receipt('.$record['id'].')" href="javascript:void(0)"');
			} elseif ($record['receipt']) {
				Base_ActionBarCommon::add('print', __('Print Receipt'), $href);
			}
		}
		return array('show'=>false);
	}

	public static function submit_warehouse_order($values, $mode) {
		if (($mode=='edit' || $mode=='add') && $values['status']==4 && $values['transaction_type']==1 && (!isset($values['invoice_number']) || !$values['invoice_number'])) {
			$values['invoice_number'] = self::generate_invoice_number($values);
			return $values;
		}
		return false;
	}

	public static function generate_invoice_number($order) {
		if (!Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit', $order)) return '';
		if (!$order['warehouse']) return '';
		if ($order['status']<2) return '';
		$t = strtotime(date('Y-m-d'));//strtotime($order['transaction_date']);
		$field = 'CONVERT(f_invoice_number,UNSIGNED)';
		if (DATABASE_DRIVER=='postgres') $field = 'f_invoice_number::integer';
		$invoice_number = DB::GetOne('SELECT MAX('.$field.') FROM premium_warehouse_items_orders_data_1 WHERE f_warehouse=%d AND f_transaction_type=%d AND f_receipt=%d AND f_invoice_print_date>=%D AND f_invoice_print_date<=%D AND active=1', array($order['warehouse'], $order['transaction_type'], $order['receipt']?1:0, date('Y-m-01',$t), date('Y-m-t',$t)));
		if (!is_numeric($invoice_number)) $invoice_number = 0;
		$order['invoice_number'] = $invoice_number+1;
		Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $order['id'], array('invoice_number'=>$order['invoice_number'],'invoice_print_date'=>$t));
		return $order['invoice_number'];
	}
	
	public static function format_invoice_number($num, $order) {
		if ($order['transaction_type']!=1) return $num;
		if ($order['receipt']==1) return $num;
		if (!$num || !is_numeric($num)) return $num;
		return date('Y/n/',strtotime($order['transaction_date'])).$num.'-'.self::get_warehouse_code($order);
	}

	public static function get_warehouse_code($order) {
		$w = Premium_WarehouseCommon::get_warehouse($order['warehouse']);
		if (isset($w['invoice_number_code']) && $w['invoice_number_code']) return $w['invoice_number_code'];
		return mb_strtoupper($w['warehouse'], 'UTF-8');
	}

	public static function display_invoice_number($r, $nolink) {
		$postfix = '';
		if ($r['transaction_type']==1 && $r['invoice_number']) {
			$conflicts = self::get_conflict_invoices($r);
			if (!empty($conflicts)) {
				$postfix = '<img src="'.Base_ThemeCommon::get_template_file('Premium_Warehouse_Invoice','conflict.png').'">';
				$msg = '';
				foreach ($conflicts as $v) {
					if ($msg) $msg .= ', ';
					$msg .= $v['transaction_id'];
				} 
				$msg = __('Warning: Found duplicate number, transaction: ').$msg;
				$postfix = Utils_TooltipCommon::create($postfix, $msg, false);
			}
		}
		return self::format_invoice_number($r['invoice_number'],$r).$postfix;
	}
	
	public static function get_conflict_invoices($order) {
		if (!trim($order['invoice_number']) || $order['transaction_type']!=1) return array();
		$t = strtotime($order['transaction_date']);
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders',array('transaction_type'=>$order['transaction_type'], '!id'=>$order['id'], 'invoice_number'=>$order['invoice_number'], 'warehouse'=>$order['warehouse'], 'receipt'=>$order['receipt'], '>=transaction_date'=>date('Y-m-01',$t), '<=transaction_date'=>date('Y-m-t',$t)));
		return $recs;
	}
	
	public static function check_number($data) {
		if (Utils_RecordBrowser::$last_record['transaction_type']!=1) return true;
		if (isset(Utils_RecordBrowser::$last_record['invoice_number']) && $data['invoice_number'] == Utils_RecordBrowser::$last_record['invoice_number']) return true;
		if ($data['invoice_number'] && !is_numeric($data['invoice_number'])) return array('invoice_number'=>__('Invalid format, number expected'));
		return true; // temporary solution - the whole duplicate mechanism is not used by UMT
/*		if (isset($data['invoice_number']) && ($data['invoice_number'] == Utils_RecordBrowser::$last_record['invoice_number'] || !$data['invoice_number'])) return true;
		if (!isset($data['warehouse'])) $data['warehouse'] = Utils_RecordBrowser::$last_record['warehouse'];
		$crits = array('warehouse'=>$data['warehouse'], 'invoice_number'=>$data['invoice_number']);
		if (isset(Utils_RecordBrowser::$last_record['id'])) $crits['!id'] = Utils_RecordBrowser::$last_record['id'];
		$other = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', $crits);
		$warning = self::$rb_obj->get_module_variable('premium_invoice_warning', null);
		if (!empty($other) && $warning!==$data['invoice_number']) {
			self::$rb_obj->set_module_variable('premium_invoice_warning', $data['invoice_number']);
			return array('invoice_number'=>__('Warning: duplicate number found'));
		}
		return true;*/
	}

	public static function QFfield_invoice_number(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		if ($mode!='view') {
			eval_js('update_invoice_fields_label = function(){'.
				'if($("receipt")) {'.
					'if ($("receipt").checked){'.
						'print_s="'.__( 'Receipt Print Date').'";'.
						'print_n="'.__( 'Receipt Number').'";'.
					'}else{'.
						'print_s="'.__( 'Invoice Print Date').'";'.
						'print_n="'.__( 'Invoice Number').'";'.
					'}'.
					'if($("_invoice_print_date__label"))$("_invoice_print_date__label").innerHTML=print_s;'.
					'if($("_invoice_number__label"))$("_invoice_number__label").innerHTML=print_n;'.
				'}'.
			'}');
			eval_js('update_invoice_fields_label();');
			eval_js('if($("receipt"))setTimeout(\'Event.observe("receipt", "change", update_invoice_fields_label)\', 1000);');
			self::$rb_obj = $rb_obj; 
			$form->addElement('text', $field, $label, array('id'=>$field));
			$form->addFormRule(array('Premium_Warehouse_InvoiceCommon', 'check_number'));
			$form->setDefaults(array($field=>$default));
		} else {
			if (Utils_RecordBrowser::$last_record['receipt']) {
				eval_js('if($("_invoice_print_date__label"))$("_invoice_print_date__label").innerHTML="'.__( 'Receipt Print Date').'";');
				eval_js('if($("_invoice_number__label"))$("_invoice_number__label").innerHTML="'.__( 'Receipt Number').'";');
			}
			$postfix = '';
			if (Utils_RecordBrowser::$last_record['transaction_type']==1) {
				if (!Utils_RecordBrowser::$last_record['invoice_number'] && Utils_RecordBrowserCommon::get_access('premium_warehouse_items_orders','edit', Utils_RecordBrowser::$last_record)) {
					if (isset($_REQUEST['assign_invoice_number']) &&
						$_REQUEST['assign_invoice_number'] == Utils_RecordBrowser::$last_record['id']) {
						$default = self::generate_invoice_number(Utils_RecordBrowser::$last_record);
					} else {
						$postfix = '<a '.Module::create_href(array('assign_invoice_number'=>Utils_RecordBrowser::$last_record['id'])).'>'.__('[assign automatically]').'</a>';
					}
				} else {
					$conflicts = self::get_conflict_invoices(Utils_RecordBrowser::$last_record);
					if (!empty($conflicts)) {
						$postfix = '<br><img src="'.Base_ThemeCommon::get_template_file('Premium_Warehouse_Invoice','conflict.png').'">';
						$msg = '';
						foreach ($conflicts as $v) {
							if ($msg) $msg .= ', ';
							$msg .= Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items_orders','transaction_id',$v['id']);
						} 
						$msg = __('Warning: Found duplicate invoice number, transaction: ').$msg;
						$postfix = $postfix.'&nbsp;'.$msg;
					}
				}
			}

			$rb_obj->set_module_variable('premium_invoice_warning', null);
			$form->addElement('static', $field, $label, self::format_invoice_number($default,Utils_RecordBrowser::$last_record).$postfix);
		}
	}
	
	public static function admin_caption() {
		return array('label'=>__('Invoice Printing'), 'section'=>__('Features Configuration'));
	}
}
?>
