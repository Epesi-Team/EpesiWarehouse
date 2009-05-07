<?php
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

$config['currency_symbol'] = "USD";

$config['inherit_from_parents'] = false;

/*
* Title, description and keywords to Your website 
*/
$config['title'] = "Quick.Cart - fast and simple shopping cart";
$config['description'] = "Freeware, fast, simple, and multilingual shopping cart system. It is based on Flat Files, uses templates system, valid XHTML 1.1 and WAI";
$config['keywords'] = "Quick.Cart,Quick.Cms,cms,shopping cart,content management system,simple,flat files,fast,php,easy,best,freeware,gpl,OpenSolution,free";
$config['slogan'] = "Fast and simple shopping cart";
$config['foot_info'] = "Copyright &copy; 2009 <a href='?'>Website.com</a>";

$config['orders_email'] = "";

$aMenuTypes[1] = 'Top menu above logo';
$aMenuTypes[2] = 'Top menu under logo';
$aMenuTypes[3] = 'Categories';
$aMenuTypes[4] = 'Producers';
$aMenuTypes[5] = 'Hidden page';

$aHiddenSubpages = Array( 3 => true, 4 => true );

$aPhotoTypes[1] = 'Left';
$aPhotoTypes[2] = 'Right';
?>