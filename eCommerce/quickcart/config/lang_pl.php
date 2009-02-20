<?php
setlocale( LC_CTYPE, 'pl_PL' );

$config['template'] = "default.css";
$config['default_theme'] = 'default.php';
$config['default_pages_template'] = 'pages_default.tpl';
$config['default_products_template'] = 'products_default.tpl';

//{ epesi variables - don't change
$config['start_page'] = 11;
$config['basket_page'] = 3;
$config['order_page'] = 7;
$config['rules_page'] = 15;
$config['page_search'] = 19;
//} epesi variables - don't change

$config['products_list'] = 3;
$config['admin_list'] = 20;

$config['currency_symbol'] = "PLN";

/*
* If you want always display pages with subpages in menu then add "true"
*/
$config['expand_menu'] = false;

/*
* Title, description and keywords to Your website 
*/
$config['title'] = "Quick.Cart - szybki i prosty sklep internetowy";
$config['description'] = "Szybki i prosty sklep internetowy. Skrypt napisany w języku PHP, oparty o plikową bazę danych i zgodny ze standardami XHTML 1.1 i WAI.";
$config['keywords'] = "Quick.Cart,Quick.Cms,cms,shopping cart,content management system,simple,flat files,fast,php,easy,best,freeware,gpl,OpenSolution,free";
$config['slogan'] = "Szybki i prosty sklep internetowy";
$config['foot_info'] = "Copyright &copy; 2008 <a href='?'>Website.com</a>";

$config['orders_email'] = "";

$aMenuTypes[1] = 'Menu górne nad logo';
$aMenuTypes[2] = 'Menu górne pod logo';
$aMenuTypes[3] = 'Kategorie';
$aMenuTypes[4] = 'Producenci';
$aMenuTypes[5] = 'Ukryte menu';

$aHiddenSubpages = Array( 3 => true, 4 => true );

$aPhotoTypes[1] = 'Lewa';
$aPhotoTypes[2] = 'Prawa';
?>