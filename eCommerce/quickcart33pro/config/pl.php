<?php
setlocale( LC_CTYPE, 'pl_PL' );

$config['template'] = "default.css";
$config['default_theme'] = 'default.php';
$config['default_pages_template'] = 'pages_default.tpl';
$config['default_products_template'] = 'products_default.tpl';

/*
* Start page
*/
//{ epesi variables - don't change
$config['start_page'] = 11;
$config['basket_page'] = 3;
$config['order_page'] = 7;
$config['rules_page'] = 15;
$config['page_search'] = 19;
//} epesi variables - don't change
$config['products_list'] = 3;
$config['admin_list'] = 20;

$config['delivery_free'] = '10000.00';
$config['currency_symbol'] = "PLN";

$config['inherit_from_parents'] = false;

/*
* Title, description and keywords to Your website
*/
$config['title'] = "Quick.Cart - szybki i prosty sklep internetowy";
$config['description'] = "Szybki i prosty sklep internetowy. Skrypt napisany w języku PHP, oparty o plikową bazę danych i zgodny ze standardami XHTML 1.1 i WAI.";
$config['keywords'] = "Quick.Cart,Quick.Cms,cms,shopping cart,content management system,simple,flat files,fast,php,easy,best,freeware,gpl,OpenSolution,free";
$config['slogan'] = "Szybki i prosty sklep internetowy";
$config['foot_info'] = "Copyright &copy; 2009 <a href='?'>Website.com</a>";

$config['orders_email'] = "";

$config['news_list'] = 5;

$config['contact_page']	= 31; //epesi required id
$config['email'] = "";

$config['site_map'] = 27; //epesi required id
$config['site_map_products'] = null;

$aMenuTypes[1] = 'Menu górne nad logo';
$aMenuTypes[2] = 'Menu górne pod logo';
$aMenuTypes[3] = 'Kategorie';
$aMenuTypes[4] = 'Producenci';
$aMenuTypes[5] = 'Ukryte menu';

$aHiddenSubpages = Array( 3 => true, 4 => true );


$aBannersTypes[0] = 'Góra';
$aBannersTypes[1] = 'Menu lewe';

$aPhotoTypes[1] = 'Lewa';
$aPhotoTypes[2] = 'Prawa';

$aProductsRecommended = Array( 7 => 7, 5 => 5 );
$aPhotoTypes[3] = 'Góra';
$aPhotoTypes[4] = 'Dół';

$config['allpay_id']		= 1234;
$config['przelewy24_id']		= 4321;
$config['platnosci_id']		= 1001;
$config['platnosci_pos_auth_key']		= 1111;
$config['platnosci_key1']		= "asdf786sdf65asdf78sd785fs7d6f57s";
$config['platnosci_key2']		= "zkjxcv87sd989zxcv79sd6ds98fs7df9";
$config['zagiel_id']		= 28011111;
$config['zagiel_min_price'] = 100;
$config['paypal_email']		= "test@test.pl";

$aPay[1][0] = 'Karta płatnicza';
$aPay[1][1] = 'mTransfer (mBank)';
$aPay[1][2] = 'Płacę z Inteligo (PKO BP Inteligo)';
$aPay[1][3] = 'Multitransfer (MultiBank)';
$aPay[1][4] = 'DotPay Transfer (DotPay.pl)';
$aPay[1][6] = 'Przelew24 (Bank Zachodni WBK)';
$aPay[1][7] = 'ING OnLine (ING Bank Śląski)';
$aPay[1][8] = 'Sez@m (Bank Przemysłowo-Handlowy S.A.)';
$aPay[1][9] = 'Pekao24 (Bank Pekao S.A.)';
$aPay[1][10] = 'MilleNet (Millennium Bank)';
$aPay[1][12] = 'Serwis PayPal';
$aPay[1][13] = 'Deutsche Bank PBC S.A.';
$aPay[1][14] = 'Kredyt Bank S.A. - KB24 Bankowość Elektroniczna';
$aPay[1][15] = 'PKO BP (konto Inteligo)';
$aPay[1][16] = 'Lukas Bank';
$aPay[1][17] = 'Nordea Bank Polska';
$aPay[1][18] = 'Bank BPH (usługa Przelew z BPH)';
$aPay[1][19] = 'Citibank Handlowy';
$aPay[4]['m'] = 'mTransfer - mBank';
$aPay[4]['n'] = 'MultiTransfer - MultiBank';
$aPay[4]['w'] = 'BZWBK - Przelew24';
$aPay[4]['o'] = 'Pekao24Przelew - BankPekao';
$aPay[4]['i'] = 'Płace z Inteligo';
$aPay[4]['d'] = 'Płac z Nordea';
$aPay[4]['p'] = 'Płac z PKO BP';
$aPay[4]['h'] = 'Płac z BPH';
$aPay[4]['g'] = 'Płac z ING';
$aPay[4]['c'] = 'Karta kredytowa';
?>