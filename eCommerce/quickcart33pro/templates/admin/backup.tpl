<!-- BEGIN LIST_TITLE -->
<h1><img src="templates/admin/img/ico_tools.gif" alt="$lang['Backup_list']" />$lang['Backup_list']</h1>
<!-- END LIST_TITLE -->

<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td>
    $aData[sFile]
  </td>
  <td>
    $aData[sDate]
  </td>
  <td class="options">
    <a href="?p=$aActions[f]-list&amp;sOption=restore&amp;sFile=$aData[sFile]"><img src="templates/admin/img/ico_add.gif" alt="$lang['Backup_restore']" title="$lang['Backup_restore']" /></a>
    <a href="?p=$aActions[f]-delete&amp;sFile=$aData[sFile]" onclick="return del( );"><img src="templates/admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->
<!-- BEGIN HEAD -->
<table id="list" class="backup" cellspacing="1">
  <thead>
    <tr>
      <td class="name">$lang[Name]</td>
      <td class="date">$lang[Date]</td>
      <td class="options">&nbsp;</td>
    </tr>
  </thead>
  <tbody>
<!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table><!-- END FOOT -->