<!-- BEGIN NEWSLETTER_TITLE --><h1><img src="$config[dir_templates]admin/img/ico_newsletter.gif" alt="$lang[Newsletter_list]" />$lang[Newsletter_list]</h1><!-- END NEWSLETTER_TITLE -->
<!-- BEGIN NEWSLETTER_FORM -->
<h1><img src="$config[dir_templates]admin/img/ico_newsletter.gif" alt="$lang[Newsletter_list]" />$lang[Newsletter_list]</h1>
<form action="" method="post" id="mainForm">
  <fieldset id="type1">
    <table cellspacing="1" class="mainTable" id="newsletterForm">
      <tbody>
        <tr class="l0">
          <td>
            $lang[Newsletter_copy_list]
          </td>
        </tr>
        <tr class="l1">
          <td>
            <textarea cols="160" rows="12">$sNewsletterList</textarea>
          </td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<!-- END NEWSLETTER_FORM -->

<!-- BEGIN LIST -->
  <tr class="l$aData[iStyle]">
    <td>
      $aData[sEmail]
    </td>
    <td class="options">
      <a href="?p=$aActions[f]-delete&amp;sEmail=$aData[sEmail]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>
    </td>
  </tr>
<!-- END LIST -->
<!-- BEGIN HEAD --><table id="list" class="newsletter" cellspacing="1">
  <tbody><!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table><!-- END FOOT -->