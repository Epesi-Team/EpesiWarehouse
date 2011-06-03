<?php
define('EPESI_DATA_DIR','/datastorage/www/epesi_prosperix_demo/data');
if(!defined('_VALID_ACCESS') && !file_exists(EPESI_DATA_DIR)) die('Launch epesi, log in as administrator, go to Menu->Adminitration->eCommerce->QuickCart settings and add \''.dirname(dirname(__FILE__)).'\' directory to setup quickcart');
if(!defined('_VALID_ACCESS') && !file_exists(EPESI_DATA_DIR)) die('Launch epesi, log in as administrator, go to Menu->Adminitration->eCommerce->QuickCart settings and add \''.dirname(dirname(__FILE__)).'\' directory to setup quickcart');
$config['default_lang'] = 'pl';
$config['available_lang'] = array('nl','en','fr','de','it','pl','us');
$config['text_size'] = true;
$config['email'] = 'warszawa@umnietaniej.com';
$config['skapiec_shop_id'] = 105;
$config['products_list'] = 10;
$config['news_list'] = 5;
$config['site_map_products'] = true;
$config['time_diff'] = 0;
$config['allpay_id'] = 1234;
$config['przelewy24_id'] = 4321;
$config['platnosci_id']	= 1001;
$config['platnosci_pos_auth_key'] = 1111;
$config['platnosci_key1'] = 'asdf786sdf65asdf78sd785fs7d6f57s';
$config['platnosci_key2'] = 'zkjxcv87sd989zxcv79sd6ds98fs7df9';
$config['zagiel_id'] = 1157620;
$config['zagiel_min_price'] = 300;
$config['paypal_email'] = 'sklep@umnietaniej.com';
$config['default_image_size'] = 0;
?>
