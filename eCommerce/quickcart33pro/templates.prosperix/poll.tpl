<!-- BEGIN POLL -->
        <div class="poll">
          <div class="name">$aData[sQuestions]</div>
          <div>
            $aData[sAnswers]
          </div>
        </div>
<!-- END POLL -->

<!-- BEGIN ANSWERS_FORM_HEAD -->
  <form action="" method="post">
    <fieldset>
<!-- END ANSWERS_FORM_HEAD -->

<!-- BEGIN ANSWERS_FORM_LIST -->
      <div class="answer">
        <input type="radio" name="iPollSelected" value="$aList[iAnswer]" id="answer$aList[iAnswer]" />
        <label for="answer$aList[iAnswer]">$aList[sAnswer]</label>
      </div>
<!-- END ANSWERS_FORM_LIST -->

<!-- BEGIN ANSWERS_FORM_FOOT -->
      <span>
        <input type="hidden" name="iPoll" value="$aList[iPoll]" />
        <input type="submit" value="$lang[PollVote]" class="submit" />
      </span>
    </fieldset>
  </form>
<!-- END ANSWERS_FORM_FOOT -->

<!-- BEGIN ANSWERS_RESULT_HEAD -->
<!-- END ANSWERS_RESULT_HEAD -->

<!-- BEGIN ANSWERS_RESULT_LIST -->
    <div class="result">
      <div>$aList[sAnswer] - <strong>$aList[iPercentage]%</strong></div>
      <div class="graph"><div style="width:$aList['iWidth']px;">&nbsp;</div></div>
    </div>
<!-- END ANSWERS_RESULT_LIST -->

<!-- BEGIN ANSWERS_RESULT_FOOT -->
<!-- END ANSWERS_RESULT_FOOT -->

<!-- BEGIN NOT_EXIST --><!-- END NOT_EXIST -->
