<!-- BEGIN CONTAINER -->
<div id="page">
  $sTxtSize
  $aImages[3]
  $aImages[1]
  $aImages[2]
  $sRssIco
  <h3>$aData[sName]</h3>
  $sContactPanel
  <div class="content" id="pageDescription">$aData[sDescriptionFull]</div>
  $sPages
  $sSiteMap
  $sFilesList
  $aImages[4]
  $sSubpagesList
  $sCommentsList
  $sCommentsForm
  $sProductsList
  $sBasketList
  $sOrder
</div>
<!-- END CONTAINER -->
<!-- BEGIN PAGES_TREE -->$aData[sPagesTree]<!-- END PAGES_TREE -->
<!-- BEGIN PAGES --><div class="pages">$lang['Pages']: $aData[sPages]</div><!-- END PAGES -->

<!-- BEGIN TXT_SIZE -->
<div class="tS"><a href="javascript:txtSize( 0 )" class="tS0">A</a> <a href="javascript:txtSize( 1 )" class="tS1">A</a> <a href="javascript:txtSize( 2 )" class="tS2">A</a></div>
<!-- END TXT_SIZE -->

<!-- BEGIN SUBPAGES_LIST_1 --><li class="l$aData[sStyle]"><h4><a href="$aData[sLinkName]">$aData[sName]</a></h4>$aData[sDescriptionShort]</li><!-- END SUBPAGES_LIST_1 -->
<!-- BEGIN SUBPAGES_DESCRIPTION_1 --><h5>$aData[sDescriptionShort]</h5><!-- END SUBPAGES_DESCRIPTION_1 -->
<!-- BEGIN SUBPAGES_HEAD_1 --><ul class="subpagesList" id="subList1"><!-- END SUBPAGES_HEAD_1 -->
<!-- BEGIN SUBPAGES_FOOT_1 --></ul><!-- END SUBPAGES_FOOT_1 -->

<!-- BEGIN SUBPAGES_LIST_2 --><li class="l$aData[sStyle]">$aData[sImage]<h4><a href="$aData[sLinkName]">$aData[sName]</a></h4>$aData[sDescriptionShort]</li><!-- END SUBPAGES_LIST_2 -->
<!-- BEGIN SUBPAGES_DESCRIPTION_2 --><h5>$aData[sDescriptionShort]</h5><!-- END SUBPAGES_DESCRIPTION_2 -->
<!-- BEGIN SUBPAGES_IMAGE_2 --><div class="photo"><a href="$aData[sLinkName]"><img src="$config[dir_files]$aDataImage[iSizeValue1]/$aDataImage[sFileName]" alt="$aDataImage[sFileDescription]" /></a></div><!-- END SUBPAGES_IMAGE_2 -->
<!-- BEGIN SUBPAGES_NO_IMAGE_2 --><!-- END SUBPAGES_NO_IMAGE_2 -->
<!-- BEGIN SUBPAGES_HEAD_2 --><ul class="subpagesList" id="subList2"><!-- END SUBPAGES_HEAD_2 -->
<!-- BEGIN SUBPAGES_FOOT_2 --></ul><!-- END SUBPAGES_FOOT_2 -->

<!-- BEGIN SUBPAGES_GALLERY_LIST --><td style="width:$aData[iWidth]%;">$aData[sImage]<h4><a href="$aData[sLinkName]">$aData[sName]</a></h4></td><!-- END SUBPAGES_GALLERY_LIST -->
<!-- BEGIN SUBPAGES_GALLERY_IMAGE --><div class="photo"><a href="$aData[sLinkName]"><img src="$config[dir_files]$aDataImage[iSizeValue1]/$aDataImage[sFileName]" alt="$aDataImage[sFileDescription]" /></a></div><!-- END SUBPAGES_GALLERY_IMAGE -->
<!-- BEGIN SUBPAGES_GALLERY_NO_IMAGE --><!-- END SUBPAGES_GALLERY_NO_IMAGE -->
<!-- BEGIN SUBPAGES_GALLERY_BREAK --></tr><tr><!-- END SUBPAGES_GALLERY_BREAK -->
<!-- BEGIN SUBPAGES_GALLERY_BLANK --><td>&nbsp;</td><!-- END SUBPAGES_GALLERY_BLANK -->
<!-- BEGIN SUBPAGES_GALLERY_HEAD --><table id="subpagesGallery" cellspacing="0"><tr><!-- END SUBPAGES_GALLERY_HEAD -->
<!-- BEGIN SUBPAGES_GALLERY_FOOT --></tr></table><!-- END SUBPAGES_GALLERY_FOOT -->

<!-- BEGIN SUBPAGES_LIST_3 --><li>$aData[sImage]<h4><a href="$aData[sLinkName]">$aData[sName]</a></h4><h6>$aData[sDate]</h6>$aData[sDescriptionShort]</li><!-- END SUBPAGES_LIST_3 -->
<!-- BEGIN SUBPAGES_DESCRIPTION_3 --><h5>$aData[sDescriptionShort]</h5><!-- END SUBPAGES_DESCRIPTION_3 -->
<!-- BEGIN SUBPAGES_IMAGE_3 --><div class="photo"><a href="$aData[sLinkName]"><img src="$config[dir_files]$aDataImage[iSizeValue1]/$aDataImage[sFileName]" alt="$aDataImage[sFileDescription]" /></a></div><!-- END SUBPAGES_IMAGE_3 -->
<!-- BEGIN SUBPAGES_NO_IMAGE_3 --><!-- END SUBPAGES_NO_IMAGE_3 -->
<!-- BEGIN SUBPAGES_HEAD_3 --><ul class="subpagesList" id="subList3"><!-- END SUBPAGES_HEAD_3 -->
<!-- BEGIN SUBPAGES_FOOT_3 --></ul><div class="pages$aData[sHidePages]">$lang['Pages']: $aData[sPages]</div><!-- END SUBPAGES_FOOT_3 -->

<!-- BEGIN FILES_LIST --><li class="l$aData[sStyle]"><img src="$config[dir_files]ext/$aData[sIcon].gif" alt="ico" /><a href="$config[dir_files]$aData[sFileName]">$aData[sFileName]</a>$aData[sDescriptionContent]</li><!-- END FILES_LIST -->
<!-- BEGIN FILES_DESCRIPTION -->, <em>$aData[sDescription]</em><!-- END FILES_DESCRIPTION -->
<!-- BEGIN FILES_HEAD --><ul id="filesList"><!-- END FILES_HEAD -->
<!-- BEGIN FILES_FOOT --></ul><!-- END FILES_FOOT -->

<!-- BEGIN IMAGES_LIST_1 --><li><a href="$config[dir_files]$aData[sFileName]" rel="lightbox-page" title="$aData[sDescription]"><img src="$config[dir_files]$aData[iSizeValue2]/$aData[sFileName]" alt="$aData[sDescription]" /></a>$aData[sDescriptionContent]</li><!-- END IMAGES_LIST_1 -->
<!-- BEGIN IMAGES_DESCRIPTION_1 --><div>$aData[sDescription]</div><!-- END IMAGES_DESCRIPTION_1 -->
<!-- BEGIN IMAGES_HEAD_1 --><ul class="imagesList" id="imagesList1"><!-- END IMAGES_HEAD_1 -->
<!-- BEGIN IMAGES_FOOT_1 --></ul><!-- END IMAGES_FOOT_1 -->

<!-- BEGIN IMAGES_LIST_2 --><li><a href="$config[dir_files]$aData[sFileName]" rel="lightbox-page" title="$aData[sDescription]"><img src="$config[dir_files]$aData[iSizeValue2]/$aData[sFileName]" alt="$aData[sDescription]" /></a>$aData[sDescriptionContent]</li><!-- END IMAGES_LIST_2 -->
<!-- BEGIN IMAGES_DESCRIPTION_2 --><div>$aData[sDescription]</div><!-- END IMAGES_DESCRIPTION_2 -->
<!-- BEGIN IMAGES_HEAD_2 --><ul class="imagesList" id="imagesList2"><!-- END IMAGES_HEAD_2 -->
<!-- BEGIN IMAGES_FOOT_2 --></ul><!-- END IMAGES_FOOT_2 -->

<!-- BEGIN GALLERY_LIST --><td style="width:$aData[iWidth]%;">
  <p><a href="$config[dir_files]$aData[sFileName]" rel="lightbox-page" title="$aData[sDescription]"><img src="$config[dir_files]$aData[iSizeValue2]/$aData[sFileName]" alt="$aData[sDescription]" /></a></p>
  $aData[sDescriptionContent]
</td><!-- END GALLERY_LIST -->
<!-- BEGIN GALLERY_DESCRIPTION --><div>$aData[sDescription]</div><!-- END GALLERY_DESCRIPTION -->
<!-- BEGIN GALLERY_BREAK --></tr><tr><!-- END GALLERY_BREAK -->
<!-- BEGIN GALLERY_BLANK --><td>&nbsp;</td><!-- END GALLERY_BLANK -->
<!-- BEGIN GALLERY_HEAD --><table id="imagesGallery$aData[iType]" class="imagesGallery" cellspacing="0"><tr><!-- END GALLERY_HEAD -->
<!-- BEGIN GALLERY_FOOT --></tr></table><!-- END GALLERY_FOOT -->

<!-- BEGIN BANNER --><style type="text/css">
<!--
#head2{background-image:url('$config[dir_files]$aData[sBanner]');}
@media print{
  #head2{background:inherit;color:#000;}
}
-->
</style><!-- END BANNER -->

<!-- BEGIN SITEMAP_LIST --><li class="l$aData[sStyle]"><a href="$aData[sLinkName]">$aData[sName]</a>$aData[sSubContent]$aData[sProducts]</li><!-- END SITEMAP_LIST -->
<!-- BEGIN SITEMAP_PRODUCTS --><li class="l$aData[sStyle]"><a href="$aData[sLinkName]">$aData[sName]</a>$aData[sPrice]</li><!-- END SITEMAP_PRODUCTS -->
<!-- BEGIN SITEMAP_PRODUCTS_PRICE --><strong>$aData[sPrice]</strong><span>$config[currency_symbol]</span><!-- END SITEMAP_PRODUCTS_PRICE -->
<!-- BEGIN SITEMAP_PRODUCTS_NO_PRICE --><strong>$aData[sPrice]</strong><!-- END SITEMAP_PRODUCTS_NO_PRICE -->
<!-- BEGIN SITEMAP_HEAD --><ul id="siteMap"><!-- END SITEMAP_HEAD -->
<!-- BEGIN SITEMAP_FOOT --></ul><!-- END SITEMAP_FOOT -->
<!-- BEGIN SITEMAP_HEAD_SUB --><ul class="sub$aData[iDepth]"><!-- END SITEMAP_HEAD_SUB -->
<!-- BEGIN SITEMAP_FOOT_SUB --></ul><!-- END SITEMAP_FOOT_SUB -->
<!-- BEGIN SITEMAP_HEAD_PRODUCTS --><ul class="products"><!-- END SITEMAP_HEAD_PRODUCTS -->
<!-- BEGIN SITEMAP_FOOT_PRODUCTS --></ul><!-- END SITEMAP_FOOT_PRODUCTS -->

<!-- BEGIN COMMENTS_FORM -->
<script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
<form action="$aData[sLinkName]" method="post" id="commentForm" onsubmit="return checkForm( this );">
  <fieldset>
  <input type="hidden" name="sOption" value="saveComment" />
  <table cellspacing="0">
    <tr>
      <th>
        <label for="comment_name">$lang[Name_and_surname]</label>
      </th>
      <td>
        <input type="text" name="sName" class="input" value="$_SESSION[sUserName]" id="comment_name" alt="simple" maxlength="40" size="30" />
      </td>
    </tr>
    <tr>
      <th>
        <label for="comment_text">$lang[Comment_content]</label>
      </th>
      <td>
        <textarea cols="45" rows="7" name="sContent" id="comment_text" title="simple"></textarea>
      </td>
    </tr>
    <tr class="save">
      <th></th>
      <td>
        <input type="submit" value="$lang[Add_comment]" class="submit" />
      </td>
    </tr>
  </table>
  </fieldset>
</form>
<!-- END COMMENTS_FORM -->

<!-- BEGIN COMMENTS_TITLE -->
<h3 id="commentTitle">$lang[Comments]</h3>
<!-- END COMMENTS_TITLE -->
<!-- BEGIN COMMENTS_LIST --><tr class="l$aData[iStyle]">
  <th>
    <h6>$aData[sName]</h6>
    <p>$aData[sDate]</p>
  </th>
  <td>
    $aData[sContent]
  </td>
</tr><!-- END COMMENTS_LIST -->
<!-- BEGIN COMMENTS_HEAD --><table cellspacing="0" id="comments"><!-- END COMMENTS_HEAD -->
<!-- BEGIN COMMENTS_FOOT --></table><!-- END COMMENTS_FOOT -->
<!-- BEGIN CONTACT_FORM -->
<script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
<form action="$aData[sLinkName]" method="post" onsubmit="return checkForm( this );" id="contactPanel">
  <fieldset>
    <input type="hidden" name="sSend" value="" />
    <dl>
      <dt><label for="contactName">$lang[Name_and_surname]:</label></dt>
      <dd><input type="text" name="sName" class="input" alt="simple" id="contactName" /></dd>
      <dt><label for="contactEmail">$lang[Your_email]:</label></dt>
      <dd><input type="text" name="sSender" class="input" alt="email" id="contactEmail" /></dd>
      <dt><label for="contactPhone">$lang[Phone]:</label></dt>
      <dd><input type="text" name="sPhone" class="input" id="contactPhone" /></dd>
      <dt><label for="contactTopic">$lang[Topic]:</label></dt>
      <dd><input type="text" name="sTopic" class="input" alt="simple" id="contactTopic" /></dd>
      <dt><label for="contactContent">$lang[Content_mail]:</label></dt>
      <dt><textarea cols="25" rows="8" name="sMailContent" title="simple" id="contactContent"></textarea></dt>
    </dl>
    <h6><input type="submit" value="$lang[send]" class="submit" /></h6>
  </fieldset>
</form>
<!-- END CONTACT_FORM -->

<!-- BEGIN RSS --><div id="rss"><a href="$sRssUrl"><img src="$config[dir_templates]img/ico_rss.gif" alt="$lang[Rss]" /></a></div><!-- END RSS -->
<!-- BEGIN RSS_META --><link rel="alternate" type="application/rss+xml" title="$lang[Rss]" href="$sRssUrl" /><!-- END RSS_META -->

<!-- BEGIN PRODUCTS_LIST -->
<li class="l$aData[sStyle]"><h3><a href="$aData[sLinkName]">$aData[sName]</a></h3>$aData[sRecommended]<h4>$aData[sPages]</h4>$aData[sImage]$aData[sDescriptionShort]$aData[sBasket]$aData[sPrice]</li>
<!-- END PRODUCTS_LIST -->
<!-- BEGIN PRODUCTS_PRICE --><div class="price"><em>$lang[Price]:</em><strong>$aData[sPrice]</strong><span>$config[currency_symbol]</span></div><!-- END PRODUCTS_PRICE -->
<!-- BEGIN PRODUCTS_NO_PRICE --><div class="noPrice"><strong>$aData[sPrice]</strong></div><!-- END PRODUCTS_NO_PRICE -->
<!-- BEGIN PRODUCTS_DESCRIPTION -->
<h5>$aData[sDescriptionShort]<small><br><br><i>SKU:</i> $aData[sSku]</small></h5>
<!-- END PRODUCTS_DESCRIPTION -->
<!-- BEGIN PRODUCTS_IMAGE -->
<div class="photo"><a href="$aData[sLinkName]"><img src="$config[dir_files]$aDataImage[iSizeValue1]/$aDataImage[sFileName]" alt="$aDataImage[sFileDescription]" /></a></div>
<!-- END PRODUCTS_IMAGE -->
<!-- BEGIN PRODUCTS_NO_IMAGE --><!-- END PRODUCTS_NO_IMAGE -->
<!-- BEGIN PRODUCTS_BASKET -->
<div class="basket"><a href="$aData[sBasketPage]iProductAdd=$aData[iProduct]&amp;iQuantity=1" rel="nofollow">$lang[Basket_add]</a></div>
<!-- END PRODUCTS_BASKET -->

<!-- BEGIN PRODUCTS_RECOMMENDED --><div class="recommended"><img src="$config[dir_templates]img/recommended.png" alt="$lang[Recommended]" title="$lang[Recommended]" /></div><!-- END PRODUCTS_RECOMMENDED -->
<!-- BEGIN PRODUCTS_HEAD -->
<ul id="products">
<li class="pages$aData[sHidePages]" id="pagesBefore"><a href="$_SERVER[REQUEST_URI]$config[search_amp]bViewAll=true">$lang['View_all']</a> | $lang[Pages]: $aData[sPages]</li>
<!-- END PRODUCTS_HEAD -->
<!-- BEGIN PRODUCTS_FOOT -->
<li class="pages$aData[sHidePages]" id="pagesAfter"><a href="$_SERVER[REQUEST_URI]$config[search_amp]bViewAll=true">$lang['View_all']</a> | $lang[Pages]: $aData[sPages]</li>
</ul>
<!-- END PRODUCTS_FOOT -->

<!-- BEGIN PRODUCTS_GALLERY_LIST -->
<!-- Old version --><!-- <td style="width:$aData[iWidth]%;">$aData[sImage]<h4><a href="$aData[sLinkName]">$aData[sName]</a></h4>$aData[sBasket]$aData[sPrice]</td> -->
<td class="gallery">$aData[sImage]<h4><a href="$aData[sLinkName]">$aData[sName]</a></h4>$aData[sBasket]$aData[sPrice]<small><i>SKU:</i> $aData[sSku]</small></td>
<!-- END PRODUCTS_GALLERY_LIST -->
<!-- BEGIN PRODUCTS_GALLERY_BREAK --></tr><tr><!-- END PRODUCTS_GALLERY_BREAK -->
<!-- BEGIN PRODUCTS_GALLERY_BLANK --><td>&nbsp;</td><!-- END PRODUCTS_GALLERY_BLANK -->
<!-- BEGIN PRODUCTS_GALLERY_HEAD --><table id="subpagesGallery" cellspacing="0"><tr><!-- END PRODUCTS_GALLERY_HEAD -->
<!-- BEGIN PRODUCTS_GALLERY_FOOT --></tr></table><!-- END PRODUCTS_GALLERY_FOOT -->
