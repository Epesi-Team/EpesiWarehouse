<!-- BEGIN COMMENTS_TITLE --><h1>$lang[Comments]</h1><!-- END COMMENTS_TITLE -->

<!-- BEGIN COMMENTS_LIST -->
<tr class="l$aData[iStyle]">
  <th>
    <div>$aData[sName]</div>
    <div><a href="http://www.ripe.net/perl/whois?form_type=simple&amp;full_query_string=&amp;searchtext=$aData[sIp]&amp;do_search=Search" target="_blank">$aData[sIp]</a></div>
    <p>$aData[sDate]</p>
    <p><a href="?p=$aData[sPage]-form&amp;$aData[sLink]=$aData[iLink]"><u>$aData[sLinkName]</u></a></p>
  </th>
  <td class="content">
    $aData[sContent]
  </td>
  <td class="status">
    <input type="hidden" name="aId[$aData[iComment]]" value="" />
    <script type="text/javascript">
    <!--
      throwYesNoBox( 'aStatus[$aData[iComment]]', '$aData[iStatus]' );
    //-->
    </script>
  </td>
  <td>
    <a href="?p=$aActions[f]-delete&amp;iComment=$aData[iComment]&amp;$aData[sLink]=$aData[iLink]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang[delete]" title="$lang[delete]" /></a>
  </td>
</tr>
<!-- END COMMENTS_LIST -->
<!-- BEGIN COMMENTS_HEAD --><form action="?p=$p&amp;sFileDb=$sFileDb" method="post" id="comments"><table id="list" class="comments" cellspacing="1">    <thead>
      <tr class="save">
        <th colspan="4">
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
      <tr>
        <td class="name">$lang[Name_and_surname]</td>
        <td class="content">$lang[Comment_content]</td>
        <td class="position">$lang['Status']</td>
        <td class="options">&nbsp;</td>
      </tr>
   </thead>
   <tfoot>
      <tr class="save">
        <th colspan="4">
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
   </tfoot>
  <tbody><!-- END COMMENTS_HEAD -->
<!-- BEGIN COMMENTS_FOOT --></tbody></table></form><!-- END COMMENTS_FOOT -->