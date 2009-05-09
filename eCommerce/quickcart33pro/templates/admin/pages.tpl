<!-- BEGIN LIST_TITLE -->
  <h1><img src="$config[dir_templates]admin/img/ico_pages.gif" alt="$lang['Pages']" />$lang['Pages']</h1>
<!-- END LIST_TITLE -->

<!-- BEGIN LIST -->
<tr class="l$aData[iDepth]">
  <td class="id">
    $aData[iPage]
  </td><th class="name">
    <a href="?p=$aActions[f]-form&amp;iPage=$aData[iPage]">$aData[sName]</a>
  </th><td class="position">
    <input type="text" name="aPositions[$aData[iPage]]" value="$aData[iPosition]" class="inputr" size="2" maxlength="3" />
  </td><td class="status">
    <input type="checkbox" name="aStatus[$aData[iPage]]" $aData[sStatusBox] value="1" />
  </td><td class="options">
    <a href="?p=comments-list&amp;iPage=$aData[iPage]"><img src="$config[dir_templates]admin/img/ico_comments.gif" alt="$lang[Pages_comment]" title="$lang[Pages_comment]" /></a>
    <a href="?p=$aActions[f]-form&amp;iPage=$aData[iPage]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;iPage=$aData[iPage]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>
  </td>
</tr>
<!-- END LIST -->

<!-- BEGIN TYPE -->
<tr class="type">
  <td colspan="5">
    $aData[sType]
  </td>
</tr><!-- END TYPE -->
<!-- BEGIN HEAD -->
<form action="?p=$p" method="post">
  <fieldset>
  <table id="list" class="pages" cellspacing="1">
    <thead>
      <tr class="save">
        <th colspan="5">
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
      <tr>
        <td class="id">$lang['Id']</td>
        <td class="name">$lang['Name']</td>
        <td class="position">$lang['Position']</td>
        <td class="status">$lang['Status']</td>
        <td class="options">&nbsp;</td>
      </tr>
    </thead>
    <tfoot>
      <tr class="save">
        <th colspan="5">
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
<h1><img src="$config[dir_templates]admin/img/ico_pages.gif" alt="$lang['Pages_form']" />$lang['Pages_form']</h1>
<script type="text/javascript">
<!--
function checkParentForm( aData ){
  if( aData['iPageParent'].value != '' && aData['iPageParent'].value == aData['iPage'].value ){
    alert( "$lang['Parent_page'] - $lang[cf_wrong_value]" );
    aData['iPageParent'].focus( );
    return false;
  }
  else{
    return checkForm( aData );
  }
} // end function checkParentForm
//-->
</script>
<form action="?p=$p&amp;iPage=$aData[iPage]" name="form" enctype="multipart/form-data" method="post" id="mainForm" onsubmit="return checkParentForm( this );">
  <fieldset id="type1">
    <input type="hidden" name="iPage" value="$aData[iPage]" />
    <table cellspacing="0" class="mainTable" id="page">
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
        <th rowspan="7" class="tabs">$sFormTabs</th>
      </tr>
      <tr class="l1">
        <td>
          <input type="text" name="sName" value="$aData[sName]" class="input" style="width:100%;" alt="simple" />
        </td>
      </tr>
      <!-- name end -->
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
      <!-- description_short end -->
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
    <li class="tabView"><a href="#more" onclick="displayTab( 'tabView' )">$lang['View']</a></li>
    <li class="tabFiles"><a href="#more" onclick="displayTab( 'tabFiles' )">$lang['Files']</a></li>
    <li class="tabSeo"><a href="#more" onclick="displayTab( 'tabSeo' )">$lang['SEO']</a></li>
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
        <td>$lang['Products']</td>
        <td>$sProductsBox</td>
      </tr>
      <tr>
        <td>$lang[Rss]</td>
        <td>
          $sRssBox
        </td>
      </tr>
      <tr>
        <td>$lang[Pages_comment]</td>
        <td>
          $sCommentsBox
        </td>
      </tr>
      <tr>
        <td>$lang['Position']</td>
        <td><input type="text" name="iPosition" value="$aData[iPosition]" class="inputr" size="3" maxlength="3" /></td>
      </tr>
      <tr>
        <td>$lang['Parent_page']</td>
        <td><select name="iPageParent" onchange="checkType( );" id="oPageParent"><option value="">$lang['none']</option>$sPagesSelect</select></td>
      </tr>
      <tr id="type">
        <td>$lang['Menu']</td>
        <td><select name="iType">$sTypesSelect</select></td>
      </tr>
      <tr>
        <td>$lang['Address']</td>
        <td><input type="text" name="sUrl" value="$aData[sUrl]" size="40" class="input" /></td>
      </tr>
      <tr>
        <td>$lang[Date]</td>
        <td><input type="text" name="sDate" value="$aData[sDate]" size="20" maxlength="16" class="input" /></td>
      </tr>
      $sNokautForm
      <!-- tab options -->
    </table>

    <table class="tab" id="tabView">
      <tr>
        <td>$lang['Subpages']</td>
        <td><select name="iSubpagesShow">$sSubpagesShowSelect</select></td>
      </tr>
      <tr>
        <td>$lang['Template']</td>
        <td><select name="sTemplate">$sTemplatesSelect</select></td>
      </tr>
      <tr>
        <td>$lang['Topic']</td>
        <td><select name="sTheme">$sThemesSelect</select></td>
      </tr>
      <tr>
        <td>$lang['Banner']</td>
        <td>
          <input type="file" name="sBannerFile" class="input" size="30" />
          $sBannerForm
        </td>
      </tr>
      <!-- tab view -->
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

    <table class="tab" id="tabSeo">
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
      <!-- tab seo -->
    </table>
    <!-- tabs list end -->
  </div>
</div>

<script type="text/javascript">
<!--
AddOnload( getTabsArray );
AddOnload( checkType );
AddOnload( checkSelectedTab );
//-->
</script>
<!-- END FORM_TABS -->

<!-- BEGIN FORM_BANNER -->
<div class="banner">
  <input type="hidden" name="sBanner" value="$aData[sBanner]" />
  <a href="$config[dir_files]$aData[sBanner]" target="_blank">$aData[sBanner]</a>&nbsp;&nbsp;<input type="checkbox" name="iBannerDel" value="1" /> - $lang['delete']
</div>
<!-- END FORM_BANNER -->

<!-- BEGIN NOKAUT_CATEGORY -->
      <tr>
        <td>$lang[Nokaut_category]</td>
        <td>
          <select name="iCategoryNokaut" style="width:300px;">
            <option value="">$lang[none]</option>
            $sCategoriesNokaut
          </select>
        </td>
      </tr>
<!-- END NOKAUT_CATEGORY -->