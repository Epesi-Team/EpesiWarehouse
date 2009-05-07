<!-- BEGIN CONTAINER -->
<div id="page">
  $sTxtSize
  $aImages[3]
  $aImages[1]
  $aImages[2]
  <h3>$aData[sName]</h3>
  <div class="content" id="pageDescription">$aData[sDescriptionFull]</div>
  $sPages
  $sFilesList
  $aImages[4]
  $sSubpagesList
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

<!-- BEGIN BANNER --><style type="text/css">
<!--
#head2{background-image:url('$config[dir_files]$aData[sBanner]');}
@media print{
  #head2{background:inherit;color:#000;}
}
-->
</style><!-- END BANNER -->

<!-- BEGIN PRODUCTS_LIST -->
<li class="l$aData[sStyle]"><h3><a href="$aData[sLinkName]">$aData[sName]</a></h3><h4>$aData[sPages]</h4>$aData[sImage]$aData[sDescriptionShort]$aData[sBasket]$aData[sPrice]</li>
<!-- END PRODUCTS_LIST -->
<!-- BEGIN PRODUCTS_PRICE --><div class="price"><em>$lang[Price]:</em><strong>$aData[sPrice]</strong><span>$config[currency_symbol]</span></div><!-- END PRODUCTS_PRICE -->
<!-- BEGIN PRODUCTS_NO_PRICE --><div class="noPrice"><strong>$aData[sPrice]</strong></div><!-- END PRODUCTS_NO_PRICE -->
<!-- BEGIN PRODUCTS_DESCRIPTION -->
<h5>$aData[sDescriptionShort]</h5>
<!-- END PRODUCTS_DESCRIPTION -->
<!-- BEGIN PRODUCTS_IMAGE -->
<div class="photo"><a href="$aData[sLinkName]"><img src="$config[dir_files]$aDataImage[iSizeValue1]/$aDataImage[sFileName]" alt="$aDataImage[sFileDescription]" /></a></div>
<!-- END PRODUCTS_IMAGE -->
<!-- BEGIN PRODUCTS_NO_IMAGE --><!-- END PRODUCTS_NO_IMAGE -->
<!-- BEGIN PRODUCTS_BASKET -->
<div class="basket"><a href="$aData[sBasketPage]&amp;iProductAdd=$aData[iProduct]&amp;iQuantity=1" rel="nofollow">$lang[Basket_add]</a></div>
<!-- END PRODUCTS_BASKET -->
<!-- BEGIN PRODUCTS_HEAD -->
<ul id="products">
<li class="pages$aData[sHidePages]" id="pagesBefore"><a href="$_SERVER[REQUEST_URI]&amp;bViewAll=true">$lang['View_all']</a> | $lang[Pages]: $aData[sPages]</li>
<!-- END PRODUCTS_HEAD -->
<!-- BEGIN PRODUCTS_FOOT -->
<li class="pages$aData[sHidePages]" id="pagesAfter"><a href="$_SERVER[REQUEST_URI]&amp;bViewAll=true">$lang['View_all']</a> | $lang[Pages]: $aData[sPages]</li>
</ul>
<!-- END PRODUCTS_FOOT -->