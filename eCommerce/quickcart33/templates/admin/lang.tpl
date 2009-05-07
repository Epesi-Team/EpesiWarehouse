<!-- BEGIN LANG_LIST -->
<tr class="l$aData[iStyle]">
  <th>$aData[sKey]</th>
  <td><input type="text" name="$aData[sKey]" value="$aData[sValue]" class="input" size="80" /></td>
</tr>
<!-- END LANG_LIST -->
<!-- BEGIN LANG_HEAD --><form action="?p=$p&amp;sLanguage=$sLanguage" method="post" id="mainForm">
  <fieldset id="type2">
    <table cellspacing="1" class="mainTable" id="translations">
      <thead>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </tfoot>
      <tbody>
<!-- END LANG_HEAD -->
<!-- BEGIN LANG_FOOT --></tbody></table></fieldset></form><!-- END LANG_FOOT -->

<!-- BEGIN FORM -->
<h1><img src="$config[dir_templates]admin/img/ico_lang.gif" alt="$lang[New_language]" />$lang[New_language]</h1>
<form action="?p=$p" method="post" id="mainForm" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <table cellspacing="1" class="mainTable" id="language">
      <thead>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </tfoot>
      <tbody>
        <tr class="l0">
          <th>$lang[Language]</th>
          <td><input type="text" name="language" value="" class="input" size="3" maxlength="2" alt="simple" /></td>
        </tr>
        <tr class="l1">
          <th>$lang[Use_language]</th>
          <td><select name="language_from">$sLangSelect</select></td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<!-- END FORM -->

<!-- BEGIN LIST_TITLE --><h1><img src="$config[dir_templates]admin/img/ico_lang.gif" alt="$lang[Languages]" />$lang[Languages] $sLanguage</h1><!-- END LIST_TITLE -->
<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td>
    <a href="?p=$aActions[f]-translations&amp;sLanguage=$aData[sName]">$aData[sName]</a>
  </td>
  <td class="options">
    <a href="?p=$aActions[f]-translations&amp;sLanguage=$aData[sName]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;sLanguage=$aData[sName]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->
<!-- BEGIN HEAD --><table id="list" class="languages" cellspacing="1">
  <thead>
    <tr>
      <td class="name">$lang['Name']</td>
      <td class="options">&nbsp;</td>
    </tr>
  </thead>
  <tbody><!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table><!-- END FOOT -->