<!-- BEGIN PANEL -->
</head>
<body id="bodyLogin">
  <div id="panelLogin">
    <div id="top"></div>
    <div id="body">
      <div id="logo"><a href="?p="><img src="$config[dir_templates]admin/img/logo_os.jpg" alt="OpenSolution"/></a></div>
      $sLoginContent
    </div>
    <div id="bottom">
      <div id="home"><a href="">$lang['homepage']</a></div>
      <div id="version"><a href="http://opensolution.org/">Quick.Cart v$config[version]</a></div>
<!-- END PANEL -->
<!-- BEGIN FORM -->
<script language="JavaScript" type="text/javascript">
<!--
function cursor( ){
  if( document.form.sLogin.value == "" ){
    document.form.sLogin.focus( );
  }
  else{
    document.form.sPass.focus( );        
  }
}
window.onload = cursor;
//-->
</script>
<form method="post" action="$sLoginPage" name="form">
  <fieldset>
    <input type="hidden" name="sLoginPageNext" value="$_SERVER[REQUEST_URI]" />
    <div id="login"><label>$lang['Login']:</label><input type="text" name="sLogin" class="input" value="$_COOKIE[sLogin]" /></div>
    <div id="pass"><label>$lang['Password']:</label><input type="password" name="sPass" class="input" value="" /></div>
    <div id="submit"><input type="submit" value="$lang['log_in'] &raquo;" /></div>
  </fieldset>
</form>
<!-- END FORM -->
<!-- BEGIN INCORRECT -->
<div id="error">
  $lang['Wrong_login_or_pass']
  <div id="back"><a href="javascript:history.back()">&laquo; $lang['back']</a></div>
</div>
<!-- END INCORRECT -->