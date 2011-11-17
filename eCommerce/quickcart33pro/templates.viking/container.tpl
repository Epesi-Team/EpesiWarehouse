<!-- BEGIN HEAD -->
<?xml version="1.0" encoding="$config[charset]"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="$config[language]">

<head>
  <title>$sTitle$config[title]</title>
  <meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
  <meta name="Description" content="$sDescription" />
  <meta name="Keywords" content="$sKeywords" />
  <meta name="Author" content="EpesiBIM eCommerce premium module" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  $sRssMeta

  <script type="text/javascript" src="$config[dir_core]common.js"></script>
  <script type="text/javascript" src="$config[dir_core]plugins.js"></script>
  <script type="text/javascript" src="$config[dir_core]prototype.lite.js"></script>
  <script type="text/javascript" src="$config[dir_core]moo.fx.js"></script>
  <script type="text/javascript" src="$config[dir_core]moo.ajax.js"></script>
  <script type="text/javascript" src="$config[dir_core]litebox-1.0.js"></script>
  <script type="text/javascript" src="$config[dir_templates]moo.fx.pack.js"></script>
  <script type="text/javascript" src="$config[dir_templates]default.js"></script>
  <script type="text/javascript">
    <!--
    var cfBorderColor     = "#af9e83";
    var cfLangNoWord      = "$lang[cf_no_word]";
    var cfLangMail        = "$lang[cf_mail]";
    var cfWrongValue      = "$lang[cf_wrong_value]";
    var cfToSmallValue    = "$lang[cf_to_small_value]";
    var cfTxtToShort      = "$lang[cf_txt_to_short]";
    AddOnload( initLightbox );
    AddOnload( targetBlank );
    AddOnload( emptyProdDesc );
    //-->
  </script>

  <style type="text/css">@import "$config[dir_templates]$config[template]";</style>
  $sBanner
  <script language="javascript" type="text/javascript">
    //<![CDATA[
    var cot_loc0=(window.location.protocol == "https:")? "https://secure.comodo.net/trustlogo/javascript/cot.js":'http://www.trustlogo.com/trustlogo/javascript/cot.js';
    document.write("<script language='javascript' type='text/javascript' src='"+cot_loc0+"'><\/sc" + "ript>");
    //]]>
  </script>
<!-- END HEAD -->
<!-- BEGIN MOBILE_HEAD -->
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
  <script type="text/javascript">
    <!--
    var cfBorderColor     = "#cabfbf";
    var cfLangNoWord      = "$lang[cf_no_word]";
    var cfLangMail        = "$lang[cf_mail]";
    var cfWrongValue      = "$lang[cf_wrong_value]";
    var cfToSmallValue    = "$lang[cf_to_small_value]";
    var cfTxtToShort      = "$lang[cf_txt_to_short]";
    //-->
  </script>

  <style type="text/css">@import "$config[dir_templates]$config[template]";</style>
  <script language="javascript" type="text/javascript">
    //<![CDATA[
    var cot_loc0=(window.location.protocol == "https:")? "https://secure.comodo.net/trustlogo/javascript/cot.js":"http://www.trustlogo.com/trustlogo/javascript/cot.js";
    document.write("<script language='javascript' type='text/javascript' src='"+cot_loc0+"'><\/sc" + "ript>");
    //]]>
  </script>
<!-- END MOBILE_HEAD -->

<!-- BEGIN BANNER_NORMAL --><div class="banner"><a href="$aData[sBannerLink]"><img src="$config[dir_files]$aData[sFile]" alt="$aData[sLink]" title="$aData[sLink]" style="width:$aData[iWidth]px;height:$aData[iHeight]px;" /></a></div><!-- END BANNER_NORMAL -->

<!-- BEGIN BANNER_NORMAL_NO_LINK --><div class="banner"><img src="$config[dir_files]$aData[sFile]" style="width:$aData[iWidth]px;height:$aData[iHeight]px;" alt="" /></div><!-- END BANNER_NORMAL_NO_LINK -->

<!-- BEGIN BANNER_FLASH --><div class="bannerFlash"><object type="application/x-shockwave-flash" data="$config[dir_files]$aData[sFile]" width="$aData[iWidth]" height="$aData[iHeight]"><param name="bgcolor" value="$aData[sColor]" /><param name="movie" value="$config[dir_files]$aData[sFile]" /><param name="wmode" value="opaque"></object></div><!-- END BANNER_FLASH -->
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
  <div id="head1">
    <div class="container">
      <div class="main">
        <a class="logo" href="?">&nbsp;</a>
        <div class="banner">AS SEEN ON THE <a href="http://www.marthastewart.com/portal/site/mslo/menuitem.3a0656639de62ad593598e10d373a0a0/?vgnextoid=d498e608ce2b1110VgnVCM1000003d370a0aRCRD&autonomy_kw=viking%20importing&rsc=ns2006_m4">MARTHA STEWART</a> SHOW</div>
        <img alt="" title="" src="$config[dir_templates]img/credit_card_logos_11.gif" width="235" height="35" border="0" style="padding-top:35px;float:right" />
      </div>
    </div>
  </div>
  <div id="head3">
    <div class="container">
      <div class="main">
        $sMenu2
        $sMenu1
      </div>
    </div>
  </div>
  <div id="orderbody">
    <div class="container">
      <div class="main">
        <div id="column2">
          <!-- additional column, hidden in styles -->
        </div>
        <div id="content">
<!-- END ORDER_BODY -->

<!-- BEGIN BODY -->
</head>
<body>
<div class="skiplink"><a href="#content" accesskey="2">$lang['Skip_navigation']</a></div>
<div id="container">
  <div id="head1">
    <div class="container">
      <div class="main">
        <a class="logo" href="?">&nbsp;</a>
        <div class="banner">AS SEEN ON THE <a href="http://www.marthastewart.com/portal/site/mslo/menuitem.3a0656639de62ad593598e10d373a0a0/?vgnextoid=d498e608ce2b1110VgnVCM1000003d370a0aRCRD&autonomy_kw=viking%20importing&rsc=ns2006_m4">MARTHA STEWART</a> SHOW</div>

        $sBasket
      </div>
    </div>
  </div>
  <div id="head3">
    <div class="container">
      <div class="main">
        $sMenu2
        $sMenu1
      </div>
    </div>
  </div>
<!--  <div id="head4">
    <div class="container">
      <div class="main">
      </div>
    </div>
  </div>-->

  <div id="body">
    <div class="container">
      <div class="main">
        <div id="column2">
          <!-- additional column, hidden in styles -->
        </div>

        <div id="column">
          $sSearchForm
          $sMenu3
        <img alt="" title="" src="$config[dir_templates]img/credit_card_logos_11.gif" width="235" height="35" border="0" style="padding-left:5px" />
        </div>
        <div id="content">
<!-- END BODY -->

<!-- BEGIN SEARCH_FORM -->
<form method="post" action="$sLinkSearch" id="searchForm">
  <fieldset>
    <span><label for="$lang[search]">$lang[search]</label><input type="text" size="20" name="sPhrase" id="$lang[search]" value="$sPhrase" class="input" maxlength="100" /></span>
    <em><input type="submit" value="$lang[search] &raquo;" class="submit" /></em>
  </fieldset>
</form>
<!-- END SEARCH_FORM -->

<!-- BEGIN FOOT -->
          <div id="options"><div class="print"><a href="javascript:window.print();">$lang['print']</a></div><div class="back"><a href="javascript:history.back();">&laquo; $lang['back']</a></div></div>
        </div>
      </div>
    </div>
  </div>
  <div id="foot">
    <div class="container">
      <div class="main">
        <div id="copy">$config[foot_info]</div>
        <!--
          LICENSE REQUIREMENTS - DONT DELETE/HIDE LINK "Shopping cart by Quick.Cart" TO www.OpenSolution.org
        -->
        <div class="foot" id="powered"><a href="http://opensolution.org/">$sLangFooter</a></div>
      </div>
    </div>
  </div>
  <div class="clear">&nbsp;</div>
</div>

<a href="http://www.instantssl.com" id="comodoTL">Trusted SSL Certificate</a>
<script language="JavaScript" type="text/javascript">
COT("http://www.vikingimporting.com/templates/img/secure_site.gif", "SC2", "none");
</script>
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
