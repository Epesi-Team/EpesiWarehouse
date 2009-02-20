<!-- BEGIN EDIT -->
  <script type="text/javascript" src="$config[dir_plugins]edit/global.js"></script>
  <script type="text/javascript" src="$config[dir_plugins]edit/editor.js"></script>
  <script type="text/javascript" src="$config[dir_plugins]edit/stdedit.js"></script>
  <script type="text/javascript">
  <!--
  var normalmode = false;
  var sEditName = '$aHtmlConfig[sName]';
  var vbphrase = {
  	"enter_text_to_be_formatted" :"$lang[Enter_text_to_be_formatted]",
  	"enter_list_type" :"$lang[Enter_list_type]",
  	"enter_list_item" :"$lang[Enter_list_item]",
  	"enter_link_url" : "$lang[Enter_link_url]",
  	"enter_email_link" :"$lang[Enter_email_link]"
  };
  //-->
  </script>

	<div class="controlholder">
		<table cellpadding="0" cellspacing="0" border="0" style="width:$aHtmlConfig[iW];border-top:1px solid #ccc;">
		<tr>
			<td><a href="#" onclick="return vbcode('b', '')" accesskey="b"><img src="$config[dir_plugins]edit/bold.gif" alt="$lang[Bold]" title="$lang[Bold]" width="21" height="20" border="0" /></a></td>
			<td><a href="#" onclick="return vbcode('i', '')" accesskey="i"><img src="$config[dir_plugins]edit/italic.gif" alt="$lang[Italic]" title="$lang[Italic]" width="21" height="20" border="0" /></a></td>
			<td><a href="#" onclick="return vbcode('u', '')" accesskey="u"><img src="$config[dir_plugins]edit/underline.gif" alt="$lang[Underline]" title="$lang[Underline]" width="21" height="20" border="0" /></a></td>
			<td><a href="#" onclick="return vbcode('LEFT', '')"><img src="$config[dir_plugins]edit/justifyleft.gif" alt="$lang[Align_left]" title="$lang[Align_left]" width="21" height="20" border="0" /></a></td>
			<td><a href="#" onclick="return vbcode('CENTER', '')"><img src="$config[dir_plugins]edit/justifycenter.gif" alt="$lang[Align_center]" title="$lang[Align_center]" width="21" height="20" border="0" /></a></td>
			<td><a href="#" onclick="return vbcode('RIGHT', '')"><img src="$config[dir_plugins]edit/justifyright.gif" alt="$lang[Align_right]" title="$lang[Align_right]" width="21" height="20" border="0" /></a></td>

			<td><a href="#" onclick="return dolist()"><img src="$config[dir_plugins]edit/insertunorderedlist.gif" alt="$lang[Add_li]" title="$lang[Add_li]" width="21" height="20" border="0" /></a></td>

			<td><a href="#" onclick="namedlink('URL')"><img src="$config[dir_plugins]edit/createlink.gif" alt="$lang[Add_hyperlink]" title="$lang[Add_hyperlink]" width="21" height="20" border="0" /></a></td>
			<td><a href="#" onclick="namedlink('mailto:')"><img src="$config[dir_plugins]edit/email.gif" alt="$lang[Add_email_link]" title="$lang[Add_email_link]" width="21" height="20" border="0" /></a></td>

			<td><a href="#" onclick="insertCustomHtml( '&lt;br />\n' )"><img src="$config[dir_plugins]edit/code.gif" alt="$lang[New_line]" title="$lang[New_line]" width="21" height="20" border="0" /></a></td>
        
      <td><a href="#" onclick="insertCustomHtml( '[break]\n' )" id="pageBreakButton"><img src="$config[dir_plugins]edit/break.gif" alt="$lang[Page_break]" title="$lang[Page_break]" width="21" height="20" border="0" /></a></td>

			<td style="width:100%">
				<span class="imagebutton"><input type="button" style="color:red;font:bold 11px verdana" value=" x " onclick="closeall(this.form)" /></span>
			</td>
		</tr>
		</table>
	</div>

	<textarea name="$aHtmlConfig[sName]" id="$aHtmlConfig[sName]" rows="20" cols="60" style="width:$aHtmlConfig[iW];height:$aHtmlConfig[iH];" tabindex="1">$aHtmlConfig[sContent]</textarea>	

	<div>
		<a href="#" onclick="return alter_box_height('$aHtmlConfig[sName]', 100)">$lang[Size_plus]</a>
    &nbsp;|&nbsp;
		<a href="#" onclick="return alter_box_height('$aHtmlConfig[sName]', -100)">$lang[Size_minus]</a>
	</div>
  <script type="text/javascript">
  <!--
    editInit( '$aHtmlConfig[sName]' );
    if( document.URL.match( /p\-form/ ) != 'p-form' )
      gEBI( "pageBreakButton" ).style.display = "none";
  //-->
  </script>
<!-- END EDIT -->

<!-- BEGIN TINY_HEAD -->
<script language="javascript" type="text/javascript" src="$config[dir_plugins]tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		theme : "advanced",
		mode : "exact",
    entity_encoding : "raw",
		elements : "sDescriptionShort,sDescriptionFull,sContent",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,undo,redo",
		theme_advanced_buttons2 : "link,unlink,anchor,cleanup,help,code,hr,removeformat,visualaid,|,charmap",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		debug : false,
    content_css : "plugins/tinymce/jscripts/tiny_mce/plugins/content.css"
	});
</script>
<!-- END TINY_HEAD -->
<!-- BEGIN TINY -->
<textarea name="$aHtmlConfig[sName]" id="$aHtmlConfig[sName]" rows="20" cols="60" style="width:$aHtmlConfig[iW];height:$aHtmlConfig[iH];" tabindex="1">$aHtmlConfig[sContent]</textarea>	
<!-- END TINY -->

<!-- BEGIN TEXTAREA -->
<textarea name="$aHtmlConfig[sName]" id="$aHtmlConfig[sName]" rows="20" cols="60" style="width:$aHtmlConfig[iW];height:$aHtmlConfig[iH];" tabindex="1">$aHtmlConfig[sContent]</textarea>	
<!-- END TEXTAREA -->
