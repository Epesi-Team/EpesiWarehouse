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
  $sRssMeta

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
  <script type="text/javascript">
    <!--
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
      <div id="logo">
        <h1><a href="$config[index]" tabindex="1"><img src="$config[dir_templates]img/logo.gif" alt="$config[title]" /></a></h1>
        <h2>$config[slogan]</h2>
      </div>
      $sMenu1
      <div id="searchform">$sSearchForm</div>
    </div>
    <div id="head3">
      $sMenu2
    </div>
    <div id="head4">
      <div id="navipath">$sPagesTree</div>
    </div>
    <div id="body">
      <div id="column2">
        <!-- additional column, hidden in styles -->
      </div>

      <div id="column">
        <div id="shadow-corner"></div>
        $sMenu3
        $sMenu4
        $sPoll
        <div id="banner1">$aBanners[1]</div>
        $aBoxes[1]
        $aBoxes[2]
        $aBoxes[3]
        $aBoxes[4]
        $sNewsletterForm
        <div class="column-bottom"><div id="shadow-corner-2"></div></div>
      </div>
      <div id="content">
        <div id="shadow-top"></div>
        <div id="banner0">$aBanners[0]</div>
<!-- END BODY -->

<!-- BEGIN BANNER_NORMAL --><div class="banner"><a href="$aData[sBannerLink]"><img src="$config[dir_files]$aData[sFile]" alt="$aData[sLink]" title="$aData[sLink]" style="width:$aData[iWidth]px;height:$aData[iHeight]px;" /></a></div><!-- END BANNER_NORMAL -->

<!-- BEGIN BANNER_NORMAL_NO_LINK --><div class="banner"><img src="$config[dir_files]$aData[sFile]" style="width:$aData[iWidth]px;height:$aData[iHeight]px;" alt="" /></div><!-- END BANNER_NORMAL_NO_LINK -->

<!-- BEGIN BANNER_FLASH --><div class="bannerFlash"><object type="application/x-shockwave-flash" data="$config[dir_files]$aData[sFile]" width="$aData[iWidth]" height="$aData[iHeight]"><param name="bgcolor" value="$aData[sColor]" /><param name="movie" value="$config[dir_files]$aData[sFile]" /></object></div><!-- END BANNER_FLASH -->
<!-- BEGIN BOX --><div class="box" id="box$aData[iBox]">
  <div class="name">$aData[sName]</div>
  <div class="content">
    $aData[sContent]
  </div>
</div><!-- END BOX -->
<!-- BEGIN NEWSLETTER_FORM -->
<form action="$sNewsletterLink" method="post" id="newsletter">
  <fieldset>
    <div><label for="newsletterEmail">$lang[Newsletter_info]</label><input type="text" name="sEmail" id="newsletterEmail" value="" class="input" onfocus="this.value=''" onclick="this.value=''" /></div>
    <p><input type="submit" value="$lang[email_add]" class="submit" /></p>
  </fieldset>
</form>
<script type="text/javascript">
<!--
  gEBI( 'newsletterEmail' ).value = "$lang[Newsletter_info]";
//-->
</script>
<!-- END NEWSLETTER_FORM -->

<!-- BEGIN ORDER_BODY -->
</head>
<body>
<div class="skiplink"><a href="#body" accesskey="2">$lang['Skip_navigation']</a></div>
<div id="container">
  <div id="main">
    <div id="head1">
      <div id="logo">
        <h1><a href="$config[index]" tabindex="1"><img src="$config[dir_templates]img/logo.gif" alt="$config[title]" /></a></h1>
        <h2>$config[slogan]</h2>
      </div>
      $sMenu1
    </div>
    <div id="head3">
      $sMenu2
    </div>
    <div id="head4">
      <div id="navipath">$sPagesTree</div>
    </div>
    <div id="orderbody">
      <div id="column2">
        <!-- additional column, hidden in styles -->
      </div>
      <div id="content">
        <div id="shadow-top"></div>
        <div id="shadow-corner"></div>
        <div id="banner0">$aBanners[0]</div>
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
