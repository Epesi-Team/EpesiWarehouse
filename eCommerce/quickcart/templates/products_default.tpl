<!-- BEGIN CONTAINER -->
<script type="text/javascript">
<!--
  var sTitle = "$aData[sName]";
  var fPrice = Math.abs( "$aData[fPrice]" );
//-->
</script>
<div id="product">
  $sTxtSize
  <h3>$aData[sName]</h3>
  <h4>$aData[sPages]</h4>
  $aImages[3]
  $aImages[1]
  <div id="box">
    $sPrice
    $sAvailable
    $sBasket
  </div>
  $aImages[2]
  <div class="content" id="productDescription">$aData[sDescriptionFull]</div>
  $sFilesList
  $aImages[4]
</div>
<!-- END CONTAINER -->
<!-- BEGIN AVAILABLE -->
<div id="available">$aData[sAvailable]</div>
<!-- END AVAILABLE -->

<!-- BEGIN TXT_SIZE -->
<div class="tS"><a href="javascript:txtSize( 0 )" class="tS0">A</a> <a href="javascript:txtSize( 1 )" class="tS1">A</a> <a href="javascript:txtSize( 2 )" class="tS2">A</a></div>
<!-- END TXT_SIZE -->

<!-- BEGIN BASKET -->
<form action="$sBasketPage" method="post" id="addBasket">
  <fieldset>
    <input type="hidden" name="iProductAdd" value="$aData[iProduct]" />
    <input type="hidden" name="iQuantity" value="1" />
    <input type="submit" value="$lang[Basket_add]" class="submit" />
  </fieldset>
</form>
<!-- END BASKET -->

<!-- BEGIN PRICE --><div id="price"><em>$lang['Price']:</em><strong id="priceValue">$aData[sPrice]</strong><span>$config[currency_symbol]</span></div><!-- END PRICE -->
<!-- BEGIN NO_PRICE --><div id="noPrice">$aData[sPrice]</div><!-- END NO_PRICE -->

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
