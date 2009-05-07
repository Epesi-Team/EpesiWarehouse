<!-- BEGIN LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_products.gif" alt="$lang['Products']" />$lang['Products']</h1>
<form action="" method="get" id="search">
  <fieldset>
    <input type="hidden" name="p" value="$p" />
    <input type="text" name="sPhrase" value="$sPhrase" class="input" size="50" />
    <input type="submit" value="$lang['search'] &raquo;" />
  </fieldset>
</form>
<!-- END LIST_TITLE -->

<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td class="id">
    $aData[iProduct]
  </td><td class="name">
    <a href="?p=$aActions[f]-form&amp;iProduct=$aData[iProduct]">$aData[sName]</a>
  </td><td class="pages">
    $aData[sPages]
  </td><td class="price">
    <input type="text" name="aPrices[$aData[iProduct]]" value="$aData[fPrice]" class="inputr" size="8" />
  </td><td class="position">
    <input type="text" name="aPositions[$aData[iProduct]]" value="$aData[iPosition]" class="inputr" size="2" maxlength="3" />
  </td><td class="status">
    <input type="checkbox" name="aStatus[$aData[iProduct]]" $aData[sStatusBox] value="1" />
  </td><td class="options">
    <a href="?p=$aActions[f]-form&amp;iProduct=$aData[iProduct]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;iProduct=$aData[iProduct]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->

<!-- BEGIN HEAD -->
<form action="?p=$p" method="post">
  <fieldset>
  <table id="list" class="products" cellspacing="1">
    <thead>
      <tr class="save">
        <td colspan="6">
          $lang[Pages]: $aData[sPages]
        </td>
        <th colspan="1">
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
      <tr>
        <td class="id">$lang['Id']</td>
        <td class="name">$lang['Name']</td>
        <td class="pages">$lang[Pages]</td>
        <td class="price">$lang[Price] [$config[currency_symbol]]</td>
        <td class="position">$lang['Position']</td>
        <td class="status">$lang['Status']</td>
        <td class="options">&nbsp;</td>
      </tr>
    </thead>
    <tfoot>
      <tr class="save">
        <td colspan="6">
          $lang[Pages]: $aData[sPages]
        </td>
        <th colspan="1">
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
    </tfoot>
    <tbody>
<!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table></fieldset></form><!-- END FOOT -->

<!-- BEGIN FORM_MAIN -->
<div id="tabsDisplayLinks">
  <a href="#more" onclick="displayTabs( );" id="tabsHide">$lang['Hide_tabs']</a>
  <a href="#more" onclick="displayTabs( true );" id="tabsShow">$lang['Display_tabs']</a>
</div>
<h1><img src="$config[dir_templates]admin/img/ico_products.gif" alt="$lang['Products_form']" />$lang['Products_form']</h1>
<form action="?p=$p&amp;iProduct=$aData[iProduct]" name="form" enctype="multipart/form-data" method="post" id="mainForm" onsubmit="return checkForm( this );">
  <fieldset id="type1">
    <input type="hidden" name="iProduct" value="$aData[iProduct]" />
    <table cellspacing="0" class="mainTable" id="product">
      <thead>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="$lang['save_list'] &raquo;" name="sOptionList" />
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="$lang['save_list'] &raquo;" name="sOptionList" />
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </tfoot>
      <!-- name start -->
      <tr class="l0">
        <td>
          $lang['Name']
        </td>
        <th rowspan="9" class="tabs">$sFormTabs</th>
      </tr>
      <tr class="l1">
        <td>
          <input type="text" name="sName" value="$aData[sName]" class="input" style="width:100%;" alt="simple" />
        </td>
      </tr>
      <!-- name end -->
      <!-- price start -->
      <tr class="l0">
        <td>
          $lang['Price']
        </td>
      </tr>
      <tr class="l1">
        <td>
          <input type="text" name="fPrice" value="$aData[fPrice]" class="inputr" size="10" /> $config[currency_symbol]
        </td>
      </tr>
      <!-- price end -->
      <!-- description_short start -->
      <tr class="l0">
        <td>
          $lang['Short_description']
        </td>
      </tr>
      <tr class="l1">
        <td>
          $sDescriptionShort
        </td>
      </tr>
      <!-- description_short end -->
      <!-- description_full start -->
      <tr class="l0">
        <td>
          $lang['Full_description']
        </td>
      </tr>
      <tr class="l1">
        <td>
          $sDescriptionFull
        </td>
      </tr>
      <!-- description_full end -->
      <tr class="end">
        <td>&nbsp;</td>
      </tr>
    </table>
  </fieldset>
</form>
<!-- END FORM_MAIN -->

<!-- BEGIN FORM_TABS -->
<div id="tabs">
  <ul id="tabsNames">
    <!-- tabs start -->
    <li class="tabOptions"><a href="#more" onclick="displayTab( 'tabOptions' )">$lang['Options']</a></li>
    <li class="tabViewSeo"><a href="#more" onclick="displayTab( 'tabViewSeo' )">$lang['View_and_seo']</a></li>
    <li class="tabFiles"><a href="#more" onclick="displayTab( 'tabFiles' )">$lang['Files']</a></li>
    <!-- tabs end -->
  </ul>
  <div id="tabsForms">
    <!-- tabs list start -->
    <table class="tab" id="tabOptions">
      <tr>
        <td>$lang['Status']</td>
        <td>$sStatusBox</td>
      </tr>
      <tr>
        <td>$lang['Position']</td>
        <td><input type="text" name="iPosition" value="$aData[iPosition]" class="inputr" size="3" maxlength="3" /></td>
      </tr>
      <tr>
        <td>$lang['Product_available']</td>
        <td><input type="text" name="sAvailable" value="$aData[sAvailable]" class="input" size="40" /></td>
      </tr>
      <tr>
        <td>$lang['Pages']</td>
        <td><select name="aPages[]" size="15" multiple="multiple" style="width:180px;" title="simple">$sPagesSelect</select></td>
      </tr>
      <!-- tab options -->
    </table>

    <table class="tab" id="tabViewSeo">
      <tr>
        <td>$lang['Template']</td>
        <td><select name="sTemplate">$sTemplatesSelect</select></td>
      </tr>
      <tr>
        <td>$lang['Topic']</td>
        <td><select name="sTheme">$sThemesSelect</select></td>
      </tr>
      <tr>
        <td>$lang['Page_title']</td>
        <td><input type="text" name="sNameTitle" value="$aData[sNameTitle]" class="input" size="50" /></td>
      </tr>
      <tr>
        <td>$lang['Meta_description']</td>
        <td><input type="text" name="sMetaDescription" value="$aData[sMetaDescription]" class="input" size="50" maxlength="255" /></td>
      </tr>
      <tr>
        <td>$lang['Key_words']</td>
        <td><input type="text" name="sMetaKeywords" value="$aData[sMetaKeywords]" class="input" size="50" maxlength="255" /></td>
      </tr>
      <!-- tab view_seo -->
    </table>

    <table class="tab" id="tabFiles">
      <tr>
        <td>
          <!-- tab files start -->
          $sFilesList
          $sFilesForm
          $sFilesDir
          <!-- tab files end -->
        </td>
      </tr>
    </table>
    <!-- tabs list end -->
  </div>
</div>

<script type="text/javascript">
<!--
AddOnload( getTabsArray );
AddOnload( checkSelectedTab );
//-->
</script>
<!-- END FORM_TABS -->