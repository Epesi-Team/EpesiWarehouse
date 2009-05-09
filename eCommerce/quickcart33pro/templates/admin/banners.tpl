<!-- BEGIN FORM -->
<script type="text/javascript">
  <!--
  var cfWrongExt = "$lang[cf_wrong_value]";
  //-->
</script>

<h1><img src="$config[dir_templates]admin/img/ico_banners.gif" alt="$lang['Banner_form']" />$lang[Banner_form]</h1>

<form action="?p=$p" method="post" id="mainForm" enctype="multipart/form-data" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <input type="hidden" name="iBanner" value="$aData[iBanner]" />
    <table cellspacing="1" class="mainTable" id="box">
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
      <tbody>
        $sFile
        <tr class="l1">
          <th>$lang[Address]</th>
          <td><input class="input" type="text" name="sLink" value="$aData[sLink]" maxlength="100" size="40" /></td>
        </tr>
        <tr class="l0">
          <th>$lang[Width]</th>
          <td><input class="input" type="text" name="iWidth" value="$aData[iWidth]" maxlength="4" size="10" alt="int;0" /> px</td>
        </tr>
        <tr class="l1">
          <th>$lang[Height]</th>
          <td><input class="input" type="text" name="iHeight" value="$aData[iHeight]" maxlength="4" size="10" alt="int;0" /> px</td>
        </tr>
        <tr class="l0">
          <th>$lang[Color]</th>
          <td><input class="input" type="text" name="sColor" value="$aData[sColor]" maxlength="7" size="10" alt="simple" /></td>
        </tr>
        <tr class="l1">
          <th>$lang[Views_max]</th>
          <td><input class="input" type="text" name="iMax" value="$aData[iMax]" maxlength="7" size="10" alt="int;-1" /> 0 - $lang[no_limits]</td>
        </tr>
        <tr class="l0">
          <th>$lang[Views]</th>
          <td><input class="input" type="text" name="iViews" value="$aData[iViews]" maxlength="7" size="10" alt="int;-1" /></td>
        </tr>
        <tr class="l1">
          <th>$lang[Clicks]</th>
          <td><input class="input" type="text" name="iClicks" value="$aData[iClicks]" maxlength="7" size="10" alt="int;-1" /></td>
        </tr>
        <tr class="l0">
          <th>$lang[Type]</th>
          <td>
            <select name="iType">
              $sBannersTypes
            </select>          
          </td>
        </tr>
        <tr class="l1">
          <th>$lang[Active]</th>
          <td>$sBannersStatus</td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<!-- END FORM -->

<!-- BEGIN FORM_FILE -->
<tr class="l0">
  <th>$lang[File]</th>
  <td><input type="file" name="sFile" size="30" class="input" alt="extension;jpg|gif|jpeg|png|swf" /></td>
</tr>
<!-- END FORM_FILE -->

<!-- BEGIN FILE -->
<tr class="l0">
  <th>$lang[File]</th>
  <td><a href="$config[dir_files]$aData[sFile]" target="_blank">$aData[sFile]</a><input type="hidden" name="sFile" value="$aData[sFile]" /></td>
</tr>
<!-- END FILE -->


<!-- BEGIN LIST_TITLE --><h1><img src="$config[dir_templates]admin/img/ico_banners.gif" alt="$lang['Banners']" />$lang[Banners]</h1><!-- END LIST_TITLE -->

<!-- BEGIN LIST_LIST -->
  <tr class="l$aData[iStyle]">
    <td class="id">
      $aData[iBanner]
    </td>
    <td class="details">
      $aData[sBanner]
      <br />
      $lang[Active]: $aData[sStatus]
      &nbsp;&nbsp;
      $lang[Clicks]: $aData[iClicks]
      &nbsp;&nbsp;
      $lang[Views]: $aData[iViews]
      &nbsp;&nbsp;
      $lang[Views_limit]: $aData[iMax]
      &nbsp;&nbsp;
      $lang[Type]: $aData[sType]
    </td>
    <td class="options">
      <a href="?p=$aActions[f]-form&amp;iBanner=$aData[iBanner]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
      <a href="?p=$aActions[f]-delete&amp;iBanner=$aData[iBanner]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>
    </td>
  </tr>
<!-- END LIST_LIST -->
<!-- BEGIN NORMAL --><img src="$config[dir_files]$aData[sFile]" alt="$aData[sLink]" title="$aData[sLink]" style="border:0;width:$aData[iWidth]px;height:$aData[iHeight]px;" /><!-- END NORMAL -->
<!-- BEGIN FLASH --><object type="application/x-shockwave-flash" data="$config[dir_files]$aData[sFile]" width="$aData[iWidth]" height="$aData[iHeight]"><param name="bgcolor" value="$aData[sColor]" /><param name="movie" value="$config[dir_files]$aData[sFile]" /></object><!-- END FLASH -->

<!-- BEGIN LIST_HEAD -->
<table id="list" class="banners" cellspacing="1">
  <thead>
    <tr>
      <td class="id">
        ID
      </td>
      <td class="details">
        $lang[Name]
      </td>
      <td class="options">
      </td>
    </tr>
  </thead>
  <tbody>
<!-- END LIST_HEAD -->
<!-- BEGIN LIST_FOOT --></tbody></table><!-- END LIST_FOOT -->