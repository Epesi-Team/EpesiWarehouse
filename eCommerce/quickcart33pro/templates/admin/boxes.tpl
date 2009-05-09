<!-- BEGIN FORM -->
<h1><img src="$config[dir_templates]admin/img/ico_boxes.gif" alt="$lang[Boxes_form]" />$lang[Boxes_form]</h1>

<form action="?p=$p" method="post" id="mainForm" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <input type="hidden" name="iBox" value="$aData[iBox]" />
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
        <tr class="l0">
          <th>$lang[Name]</th>
          <td><input type="text" name="sName" value="$aData[sName]" size="80" maxlength="50" class="input" alt="simple" /></td>
        </tr>
        <tr class="l1">
          <th>$lang[Content]</th>
          <td>$sContent</td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<!-- END FORM -->

<!-- BEGIN LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_boxes.gif" alt="$lang[Boxes]" />$lang[Boxes]</h1>
<!-- END LIST_TITLE -->
<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td>
    $aData[iBox]
  </td>
  <td>
    <a href="?p=$aActions[f]-form&amp;iBox=$aData[iBox]">$aData[sName]</a>
  </td>
  <td>
    &#36;aBoxes[$aData[iBox]]
  </td>
  <td class="options">
    <a href="?p=$aActions[f]-form&amp;iBox=$aData[iBox]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;iBox=$aData[iBox]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->
<!-- BEGIN HEAD --><table id="list" class="boxes" cellspacing="1">
  <thead>
    <tr>
      <td class="id">$lang['Id']</td>
      <td class="name">$lang['Name']</td>
      <td class="variable">$lang['Variable']</td>
      <td class="options">&nbsp;</td>
    </tr>
  </thead>
  <tbody><!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table><!-- END FOOT -->
