<!-- BEGIN ERROR -->
<div class="message" id="error">
  <h3>$lang['Data_not_found']</h3>
</div>
<!-- END ERROR -->

<!-- BEGIN NEWSLETTER_ADDED -->
<div class="message">
  <h3>
    $lang[Email_added]
  </h3>
</div>
<!-- END NEWSLETTER_ADDED -->
<!-- BEGIN REQUIRED_FIELDS -->
<div class="message" id="error">
  <h3>
    $lang['cf_no_word']
  </h3>
</div>
<!-- END REQUIRED_FIELDS -->
<!-- BEGIN COMMENT_ADDED -->
<div class="message" id="ok">
  <h3>$lang['Comment_added']</h3>
</div>
<!-- END COMMENT_ADDED -->

<!-- BEGIN MAIL_SEND_CORRECT -->
<div class="message">
  <h3>
    $lang[Mail_send_correct]
  </h3>
</div>
<!-- END MAIL_SEND_CORRECT -->
<!-- BEGIN MAIL_SEND_ERROR -->
<div class="message" id="error">
  <h3>
    $lang[Mail_send_error]
  </h3>
</div>
<!-- END MAIL_SEND_ERROR -->
<!-- BEGIN REQUIRED_FIELDS -->
<div class="message" id="error">
  <h3>
    $lang['cf_no_word']<br />
    <a href="javascript:history.back();">&laquo; $lang['back']</a>
  </h3>
</div>
<!-- END REQUIRED_FIELDS -->
<!-- BEGIN COMMENT_ADDED -->
<div class="message" id="ok">
  <h3>$lang['Comment_added']</h3>
</div>
<!-- END COMMENT_ADDED -->
<!-- BEGIN REQUIRED_FIELDS -->
<div class="message" id="error">
  <h3>
    $lang[cf_no_word]<br />
    <a href="javascript:history.back();">&laquo; $lang[back]</a>
  </h3>
</div>
<!-- END REQUIRED_FIELDS -->
<!-- BEGIN COMMENT_ADDED -->
<div class="message" id="ok">
  <h3>$lang['Comment_added']</h3>
</div>
<!-- END COMMENT_ADDED -->
<!-- BEGIN PROMOTION_INVALID -->
<div class="message" id="error">
  <h3>
    $lang['Promotion_invalid']
  </h3>
</div>
<!-- END PROMOTION_INVALID -->
<!-- BEGIN PROMOTION_EXPIRED -->
<div class="message" id="error">
  <h3>
    $lang['Promotion_expired']
  </h3>
</div>
<!-- END PROMOTION_EXPIRED -->
<!-- BEGIN PASSWORD_MISMATCH -->
<div class="message" id="error">
  <h3>
    $lang['Password_mismatch']
  </h3>
</div>
<!-- END PASSWORD_MISMATCH -->
<!-- BEGIN PASSWORD_INVALID -->
<div class="message" id="error">
  <h3>
    $lang['Password_invalid']
  </h3>
</div>
<!-- END PASSWORD_INVALID -->
<!-- BEGIN PASSWORD_INVALID_ORDER -->
<div class="message" id="error">
  <h3>
    $lang['Password_invalid']<br />
    An account with this e-mail address already exists
  </h3>
  <ul>
    <li><form style="display:none" action="?forgot-password,59" method="post" id="reminder"><input type="hidden" name="sEmail" value="$_POST[sEmail]" /></form><a href="javascript:$('reminder').submit()">$lang['Forgot_password']</a></li>
    <li><a href="javascript:void(0);" onClick="$('oPassword2').value='';$('oPassword').value='';$('new_account_checkbox').checked=0;var f=$('orderForm');if(checkForm(f))f.submit();">Order without loggin in</a></li>
    <li><a href="#orderFormAnchor">Change e-mail/password below</a></li>
  </ul>
</div>
<!-- END PASSWORD_INVALID_ORDER -->
<!-- BEGIN EMAIL_INVALID -->
<div class="message" id="error">
  <h3>
    $lang['Email_invalid']
  </h3>
</div>
<!-- END EMAIL_INVALID -->
<!-- BEGIN NEW_PASSWORD_SENT -->
<div class="message" id="error">
  <h3>
    $lang['New_password_sent']
  </h3>
</div>
<!-- END NEW_PASSWORD_SENT -->
