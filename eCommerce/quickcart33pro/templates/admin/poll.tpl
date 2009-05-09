<!-- BEGIN POLL -->
<script type="text/javascript" src="$config[dir_js]checkForm.js"> </script>
<h1><img src="$config[dir_templates]admin/img/ico_poll.gif" alt="$lang[Poll]" />$lang[Poll]</h1>

<form action="?p=$p" method="post" id="mainForm" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <input type="hidden" name="iPoll" value="$aData[iPoll]" />
    <table cellspacing="1" class="mainTable" id="translations">
      <thead>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="$lang['save_list'] &raquo;" name="sOptionList" />
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="$lang['save_list'] &raquo;" name="sOptionList" />
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </tfoot>
      <tbody>
        <tr class="l1">
          <th>
            $lang[Status]
          </th>
          <td colspan="2">
            $aData[sStatusBox]
          </td>
        </tr>
        <tr class="l0">
          <th>
            $lang[Question]
          </th>
          <td colspan="2">
            <input type="text" name="sQuestions" value="$aData[sQuestions]" size="50" maxlength="200" class="input" alt="simple" />
            <a href="?p=$p&amp;sOption=reset&amp;iPoll=$aData[iPoll]" onclick="return confirm('$lang[Are_you_sure]')"><strong>$lang[Reset_answers_selected] &raquo;</strong></a>
          </td>
        </tr>
        $aData[sAnswers]
      </tbody>
    </table>
  </fieldset>
</form>
<!-- END POLL -->

<!-- BEGIN ANSWERS_FORM_HEAD -->
<!-- END ANSWERS_FORM_HEAD -->

<!-- BEGIN ANSWERS_FORM_LIST -->
  <tr class="l1">
    <th>
      $lang[Answer]
    </th>
    <td>
      <input type="text" name="aAnswers[]" value="$aList[sAnswer]" size="50" maxlength="200" class="input" />
      <input type="hidden" name="aAnswersId[]" value="$aList[iAnswer]" />
      $lang[PollQuestionSelected] $aList[iCountAnswers] $lang[PollQuestionTimes] - $aList[iPercentage]%
    </td>
    <td>
      <div class="graph"><div style="width:$aList['iWidth']px;">&nbsp;</div></div>
    </td>
  </tr>
<!-- END ANSWERS_FORM_LIST -->

<!-- BEGIN ANSWERS_FORM_FOOT -->
<!-- END ANSWERS_FORM_FOOT -->

<!-- BEGIN ANSWERS_FORM_NEW -->
  <tr class="l1">
    <th>
      $lang[Answer]
    </th>
    <td colspan="2">
      <input type="text" name="aAnswers[]" value="" size="50" maxlength="200" class="input" />
    </td>
  </tr>
<!-- END ANSWERS_FORM_NEW -->

<!-- BEGIN LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_poll.gif" alt="$lang[Poll]" />$lang[Poll]</h1>
<!-- END LIST_TITLE -->
<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td>
    $aData[iPoll]
  </td>
  <td>
    <a href="?p=$aActions[f]-form&amp;iPoll=$aData[iPoll]">$aData[sQuestions]</a>
  </td>
  <td class="options">
    <a href="?p=$aActions[f]-form&amp;iPoll=$aData[iPoll]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;iPoll=$aData[iPoll]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->
<!-- BEGIN HEAD --><table id="list" class="boxes" cellspacing="1">
  <thead>
    <tr>
      <td class="id">$lang['Id']</td>
      <td class="name">$lang['Question']</td>
      <td class="options">&nbsp;</td>
    </tr>
  </thead>
  <tbody><!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table><!-- END FOOT -->

<!-- BEGIN NOT_EXIST -->
<div id="msg" class="error">
  $lang['Data_not_found']
</div>
<!-- END NOT_EXIST -->