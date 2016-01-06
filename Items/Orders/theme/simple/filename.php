<?php

$filename = 'Print';
if ($order['transaction_type'] == 0) {
    $filename = $order['status'] < 2 ? __('Purchase Quote') : __('Purchase Order');
} else {
    if ($order['status'] == 4) $filename = __('Packing List');
    else if ($order['status'] > 2) $filename = __('Invoice');
    else $filename = __('Sales Quote');
}
return $filename;
