<!-- BEGIN HEAD -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="$config[language]" lang="$config[language]">
<head>
  <title>$lang['Admin'] - $config[title]</title>
  <meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
  <meta name="Description" content="" />
  <meta name="Keywords" content="" />
  <meta name="Author" content="OpenSolution.org" />
  <script type="text/javascript" src="$config[dir_core]common.js"></script>
  <script type="text/javascript" src="$config[dir_core]common-admin.js"></script>
  <script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
  <link rel="stylesheet" href="$config[dir_templates]admin/style.css" type="text/css" />
  <script type="text/javascript">
    <!--
    var cfBorderColor     = "#666666";
    var cfLangNoWord      = "$lang[cf_no_word]";
    var cfLangMail        = "$lang[cf_mail]";
    var cfWrongValue      = "$lang[cf_wrong_value]";
    var cfToSmallValue    = "$lang[cf_to_small_value]";
    var cfTxtToShort      = "$lang[cf_txt_to_short]";

    var delShure = "$lang['Operation_sure']";
    var yes = "$lang[yes]";
    var no = "$lang[no]";
    //-->
  </script>

<!-- END HEAD -->
<!-- BEGIN BODY -->
</head>

<body>
  <div id="container">
    $sMsg
    <div id="header">
      <div id="menuTop">
        <div id="links"><a href="?p=">$lang['homepage']</a>|<a href="http://opensolution.org/?p=support">$lang['support']</a>|<a href="?p=settings-config">$lang['configuration']</a>|<a href="?p=logout">$lang['log_out']</a><!-- menu top end --></div>
        <div id="lang">$lang['select_language']: <select name="" onchange="redirectToUrl( '$_SERVER[PHP_SELF]?sLang='+this.value )">$sLangSelect</select></div>
      </div>
      <div id="logoOs">
        <a href="http://opensolution.org/"><img src="$config[dir_templates]admin/img/logo_os.jpg" alt="OpenSolution.org" /></a>
      </div>
      <div class="clear"></div>

      <!-- menu under_logo start -->
      <ul id="menuBar">
        <li onmouseover="return buttonClick( event, 'p' ); buttonMouseover( event, 'p' );"><a href="?p=p-list"><span class="pages">$lang['Pages']</span></a></li>
        <li onmouseover="return buttonClick( event, 'languages' ); buttonMouseover( event, 'languages' );"><a href="?p=lang-list"><span class="lang">$lang['Languages']</span></a></li>
        <li onmouseover="return buttonClick( event, 'files' ); buttonMouseover( event, 'files' );"><a href="?p=files-list"><span class="files">$lang['Files']</span></a></li>
        <li onmouseover="return buttonClick( event, 'products' ); buttonMouseover( event, 'products' );"><a href="?p=products-list"><span class="products">$lang['Products']</span></a></li>
        <li onmouseover="return buttonClick( event, 'orders' ); buttonMouseover( event, 'orders' );"><a href="?p=orders-list"><span class="orders">$lang['Orders']</span></a></li>
        <!-- menu under_logo bar end -->
      </ul>

      <!-- submenu under_logo start -->
      <div id="p" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=p-form">$lang['New_page']</a>
      </div>
      <div id="languages" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=lang-form">$lang['New_language']</a>
      </div>
      <div id="files" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=files-list&amp;iLinkType=1">$lang['Pages']</a>
        <a href="?p=files-list&amp;iLinkType=2">$lang['Products']</a>
      </div>
      <div id="products" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=products-form">$lang['New_product']</a>
      </div>
      <div id="orders" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=orders-list&amp;iStatus=1">$lang['Orders_pending']</a>
        <span class="sep"></span>
        <a href="?p=payments-list">$lang['Payment_methods']</a>
        <a href="?p=payments-form">$lang['New_payment_method']</a>
        <span class="sep"></span>
        <a href="?p=carriers-list">$lang['Carriers']</a>
        <a href="?p=carriers-form">$lang['New_carrier']</a>
      </div>
      <!-- menu under_logo end -->

    </div>
    <div class="clear"></div>
    <div id="body">
<!-- END BODY -->

<!-- BEGIN FOOT -->
      <div id="back">
        &laquo; <a href="javascript:history.back();">$lang[back]</a>
      </div>
    </div>
  </div>
</body>
</html>
<!-- END FOOT -->

<!-- BEGIN HOME -->
<h1>$lang['Homepage']</h1>
<div id="news">
  <iframe src="http://opensolution.org/news,$config[language],$config[version].html"></iframe>
</div>
<!-- END HOME -->