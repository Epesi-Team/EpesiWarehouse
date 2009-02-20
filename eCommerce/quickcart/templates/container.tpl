<!-- BEGIN HEAD -->
<?xml version="1.0" encoding="$config[charset]"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="$config[language]">

<head>
  <title>$sTitle$config[title]</title>
  <meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
  <meta name="Description" content="$sDescription" />
  <meta name="Keywords" content="$sKeywords" />
  <meta name="Author" content="OpenSolution.org" />
  <meta http-equiv="Content-Style-Type" content="text/css" />

  <script type="text/javascript" src="$config[dir_core]common.js"></script>
  <script type="text/javascript" src="$config[dir_core]plugins.js"></script>
  <script type="text/javascript" src="$config[dir_core]prototype.lite.js"></script>
  <script type="text/javascript" src="$config[dir_core]moo.fx.js"></script>
  <script type="text/javascript" src="$config[dir_core]litebox-1.0.js"></script>
  <script type="text/javascript">
    <!--
    var cfBorderColor     = "#d1bd9d";
    var cfLangNoWord      = "$lang[cf_no_word]";
    var cfLangMail        = "$lang[cf_mail]";
    var cfWrongValue      = "$lang[cf_wrong_value]";
    var cfToSmallValue    = "$lang[cf_to_small_value]";
    var cfTxtToShort      = "$lang[cf_txt_to_short]";
    AddOnload( initLightbox );
    AddOnload( targetBlank );
    //-->
  </script>

  <style type="text/css">@import "$config[dir_templates]$config[template]";</style>
  $sBanner
<!-- END HEAD -->
<!-- BEGIN BODY --> 
</head>
<body>
<div class="skiplink"><a href="#content" accesskey="2">$lang['Skip_navigation']</a></div>
<div id="container">
  <div id="main">
    <div id="head1">
      $sMenu1
    </div>
    <div id="head2">
      <div id="logo">
        <h1><a href="?" tabindex="1"><img src="$config[dir_templates]img/logo.jpg" alt="$config[title]" /></a></h1>
        <h2>$config[slogan]</h2>
      </div>
      <div id="navipath">$sPagesTree</div>
    </div>
    <div id="head3">
      $sMenu2
    </div>
    <div id="body">
      <div id="column2">
        <!-- additional column, hidden in styles -->
      </div>
      
      <div id="column">
        $sSearchForm
        $sMenu3
        $sMenu4
      </div>
      <div id="content">
<!-- END BODY -->

<!-- BEGIN ORDER_BODY --> 
</head>
<body>
<div class="skiplink"><a href="#body" accesskey="2">$lang['Skip_navigation']</a></div>
<div id="container">
  <div id="main">
    <div id="head1">
      $sMenu1
    </div>
    <div id="head2">
      <div id="logo">
        <h1><a href="?" tabindex="1"><img src="$config[dir_templates]img/logo.jpg" alt="$config[title]" /></a></h1>
        <h2>$config[slogan]</h2>
      </div>
      <div id="navipath">$sPagesTree</div>
    </div>
    <div id="head3">
      $sMenu2
    </div>
    <div id="orderbody">
      <div id="column2">
        <!-- additional column, hidden in styles -->
      </div>
      <div id="content">
<!-- END ORDER_BODY -->

<!-- BEGIN FOOT -->
        <div id="options"><div class="print"><a href="javascript:window.print();">$lang['print']</a></div><div class="back"><a href="javascript:history.back();">&laquo; $lang['back']</a></div></div>
      </div>
    </div>
    <div id="foot">
      <div id="copy">$config[foot_info]</div>
      <!-- 
        LICENSE REQUIREMENTS - DONT DELETE/HIDE LINK "powered by Quick.Cart" TO www.OpenSolution.org
      -->
      <div class="foot" id="powered"><a href="http://opensolution.org/">Powered by <strong>Quick.Cart</strong></a></div>
    </div>

    <div class="clear">&nbsp;</div>
  </div>
</div>

</body>
</html>
<!-- END FOOT -->

<!-- BEGIN SEARCH_FORM -->
<form method="post" action="$sLinkSearch" id="searchForm">
  <fieldset>
    <span><label for="$lang[search]">$lang[search]</label><input type="text" size="20" name="sPhrase" id="$lang[search]" value="$sPhrase" class="input" maxlength="100" /></span>
    <em><input type="submit" value="$lang[search] &raquo;" class="submit" /></em>
  </fieldset>
</form>
<!-- END SEARCH_FORM -->