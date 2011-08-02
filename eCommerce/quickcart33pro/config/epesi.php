<?php
define('EPESI_DATA_DIR','/var/www/viking/live/data');
if(!defined('_VALID_ACCESS') && !file_exists(EPESI_DATA_DIR)) die('Launch epesi, log in as administrator, go to Menu->Adminitration->eCommerce->QuickCart settings and add \''.dirname(dirname(__FILE__)).'\' directory to setup quickcart');
$config['default_lang'] = 'us';
$config['available_lang'] = array('us');
$config['text_size'] = true;
$config['email'] = 'jtylek@telaxus.com';
$config['skapiec_shop_id'] = 2222;
$config['products_list'] = 6;
$config['news_list'] = 5;
$config['site_map_products'] = false;
$config['time_diff'] = 0;
$config['allpay_id'] = 1234;
$config['przelewy24_id'] = 4321;
$config['platnosci_id']	= 1001;
$config['platnosci_pos_auth_key'] = 1111;
$config['platnosci_key1'] = 'asdf786sdf65asdf78sd785fs7d6f57s';
$config['platnosci_key2'] = 'zkjxcv87sd989zxcv79sd6ds98fs7df9';
$config['epesi_payments_url'] = 'http://viking.epesibim.com/live/modules/Premium/Payments/';
$config['zagiel_id'] = null;
$config['zagiel_min_price'] = null;
$config['paypal_email'] = 'test@test.com';
$config['default_image_size'] = 0;
?>