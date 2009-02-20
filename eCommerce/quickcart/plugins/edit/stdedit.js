
var tags = new Array();

var aTagsOpen = Array();
var aTagsClose = Array();
aTagsOpen['b'] = '<b>';
aTagsOpen['i'] = '<i>';
aTagsOpen['u'] = '<u>';
aTagsOpen['LEFT'] = '<div class="left">';
aTagsOpen['CENTER'] = '<div class="center">';
aTagsOpen['RIGHT'] = '<div class="right">';

aTagsClose['b'] = '</b>';
aTagsClose['i'] = '</i>';
aTagsClose['u'] = '</u>';
aTagsClose['LEFT'] = '</div>';
aTagsClose['CENTER'] = '</div>';
aTagsClose['RIGHT'] = '</div>';

var text = "";


var AddTxt = "";


var theEditor = "";

function editInit( )
{
  var sName = sEditName;
  vbcode_init( sName );
}


function vbcode_init( sName )
{
  if (editor_loaded)
  {
  
  return;
  }
  
  if (!is_ie4 && !is_ns4)
  {
  
  if (fetch_object("controlbar"))
  {
  set_unselectable(fetch_object("controlbar"));
  }
  
  if (fetch_object("controlbar"))
  {
  var divs = fetch_object("controlbar").getElementsByTagName("div");
  for (var i  = 0; i < divs.length; i++)
  {
  var elm = divs[i];
  switch (elm.className)
  {
  case "imagebutton":
  {
  elm.onmouseover = elm.onmouseout = elm.onmouseup = elm.onmousedown = button_eventhandler;
  }
  break;
  }
  }
  }
  }
  
  theEditor = document.getElementById( sName );
}


function button_eventhandler(e, elm)
{
  if (is_v4)
  { 
  }
  
  e = do_an_e(e);
  
  switch (e.type)
  {
  case "mousedown":
  {
  format_control(elm ? elm : this, "button", "down");
  }
  break;
  
  case "mouseover":
  case "mouseup":
  {
  format_control(elm ? elm : this, "button", "hover");
  }
  break;
  
  default:
  {
  format_control(elm ? elm : this, "button", "normal");
  }
  }
}


function thearrayisgood(thearray, i)
{
  if (typeof(thearray[i]) == "undefined" || (thearray[i] == "") || (thearray[i] == null))
  {
  return false;
  }
  else
  {
  return true;
  }
}


function sizeof(thearray)
{
  for (var i = 0; i < thearray.length; i++)
  {
  if (!thearrayisgood(thearray, i))
  {
  return i;
  }
  }
  return thearray.length;
}

function array_push(thearray, value)
{
  var thearraysize = sizeof(thearray);
  thearray[thearraysize] = value;
  return thearray[thearraysize];
}


function array_pop(thearray)
{
  var thearraysize = sizeof(thearray);
  var retval = thearray[thearraysize - 1];
  delete thearray[thearraysize - 1];
  return retval;
}


function getActiveText()
{
  setfocus();
  if (!is_ie || (is_ie && !document.selection))
  {
  return false;
  }
  
  var sel = document.selection;
  var rng = sel.createRange();
  
  if (rng != null && (sel.type == "Text" || sel.type == "None"))
  {
  text = rng.text;
  }
  if (rng != null && theEditor.createTextRange)
  {
  theEditor.caretPos = rng.duplicate();
  }
  return true;
}


function AddText(NewCode)
{
  if (typeof(theEditor.createTextRange) != "undefined" && theEditor.caretPos)
  {
  var caretPos = theEditor.caretPos;
  caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? NewCode + ' ' : NewCode;
  caretPos.select();
  }
  else if (theEditor.selectionStart || theEditor.selectionStart == '0')
  {
  var start_selection = theEditor.selectionStart;
  var end_selection = theEditor.selectionEnd;
  
  var start = (theEditor.value).substring(0, start_selection);
  var middle = NewCode;
  var end = (theEditor.value).substring(end_selection, theEditor.textLength);
  
  theEditor.value = start + middle + end;
  setfocus();
  theEditor.selectionStart = end_selection + middle.length;
  theEditor.selectionEnd = start_selection + middle.length;
  getActiveText();
  AddTxt = "";
  return;
  }
  else
  {
  theEditor.value += NewCode;
  }
  setfocus();
  getActiveText();
  AddTxt = "";
}

function setfocus()
{
  theEditor.focus();
}

function vbcode(vbcode, prompttext, displayoption)
{
  
  var optioncompiled = "";
  
  getActiveText();
  
  if (text)
  { // its IE to the rescue
    if ( text.substring(0, aTagsOpen[vbcode].length ) == aTagsOpen[vbcode] && text.substring(text.length - aTagsOpen[vbcode].length - 1, text.length) == aTagsClose[vbcode] )
    {
      AddTxt = text.substring(aTagsOpen[vbcode].length, text.length - aTagsOpen[vbcode].length - 1);
    }
    else
    {
      AddTxt = aTagsOpen[vbcode] + optioncompiled + text + aTagsClose[vbcode];
    }
    AddText(AddTxt);
  }
  else if ((theEditor.selectionStart || theEditor.selectionStart == '0') && theEditor.selectionStart != theEditor.selectionEnd)
  { // its mozilla and we'll need to re-write entire text
    var start_selection = theEditor.selectionStart;
    var end_selection = theEditor.selectionEnd;
    
    var start = (theEditor.value).substring(0, start_selection);
    var middle = (theEditor.value).substring(start_selection, end_selection);
    var end = (theEditor.value).substring(end_selection, theEditor.textLength);
    
    if ( middle.substring(0, aTagsOpen[vbcode].length ) == vbcode && middle.substring(middle.length - aTagsOpen[vbcode].length - 1, middle.length) == aTagsClose[vbcode] )
    {
      middle = middle.substring(aTagsOpen[vbcode].length, middle.length - aTagsOpen[vbcode].length - 1);
    }
    else
    {
      middle = aTagsOpen[vbcode] + optioncompiled + middle + aTagsClose[vbcode];
    }
    
    theEditor.value = start + middle + end;
    setfocus();
    theEditor.selectionStart = end_selection + middle.length;
    theEditor.selectionEnd = start_selection + middle.length;
    return false;
  }
  else
  {
    
    if (!normalmode)
    {
      var donotinsert = false;
      var thetag = 0;
      for (var i = 0; i < tags.length; i++)
      {
        if (typeof(tags[i]) != "undefined" && tags[i] == vbcode)
        {
          donotinsert = true;
          thetag = i;
        }
      }
      
      if (!donotinsert)
      {
        array_push(tags, vbcode);
        AddTxt = aTagsOpen[vbcode];
        AddText(AddTxt);
      }
      else
      { // its already open
        var closedtag = "";
        while (typeof(tags[thetag]) != "undefined")
        {
          closedtag = array_pop(tags);
          AddTxt = aTagsClose[closedtag];
          AddText(AddTxt);
        }
      }
    }
    else
    {
      var inserttext = prompt(vbphrase["enter_text_to_be_formatted"] + "\n" + aTagsOpen[vbcode] + "xxx" + aTagsClose[vbcode], "");
      if ((inserttext != null) && (inserttext != ""))
      {
        AddTxt = aTagsOpen[vbcode] + inserttext + aTagsClose[vbcode];
      }
      AddText(AddTxt);
    }
  }
  setfocus();
  return false;
}

function closetag()
{
  getActiveText();
  if (!normalmode)
  {
  if (typeof(tags[0]) != "undefined")
  {
  var Tag = array_pop(tags)
  AddTxt = aTagsClose[Tag];
  AddText(AddTxt);
  }
  }
  setfocus();
}

function closeall()
{
  getActiveText();
  if (!normalmode)
  {
  var g = sizeof(tags);
  if (thearrayisgood(tags, g-1))
  {
  var Addtxt = "";
  var newtag = "";
  for (var h = 0; h < g; h++)
  {
  newtag = array_pop(tags);
  Addtxt += aTagsClose[newtag];
  }
  AddText(Addtxt);
  }
  }
  setfocus();
}


function namedlink(thetype)
{
  var extraspace = "";
  
  getActiveText();
  var dtext = "";
  if (text)
  {
  dtext = text;
  }
  else
  {
  extraspace = " ";
  }

  var prompttext, prompt_contents, mailto;
  if (thetype == "URL")
  {
  prompt_text = vbphrase["enter_link_url"];
  prompt_contents = "http://";
  mailto = '';
  }
  else
  {
  prompt_text = vbphrase["enter_email_link"];
  prompt_contents = "";
  mailto = 'mailto:';
  }
  var linkurl = prompt(prompt_text, prompt_contents);
  if ((linkurl != null) && (linkurl != ""))
  {
    AddTxt = '<a href="' + mailto + linkurl + '">' + linkurl + "</a>" + extraspace;
    AddText(AddTxt);
  }
}

function dolist()
{
  var listtype = prompt(vbphrase["enter_list_type"], "");
  
  if ((listtype == "a") || (listtype == "1") || (listtype == "i"))
  {
  sListElement = 'ol';
  thelist = "<"+ sListElement +" type=\"" + listtype + "\">\n";
  }
  else
  {
  sListElement = 'ul';
  thelist = "<"+ sListElement +">\n";
  }
  var listentry = "initial";
  while ((listentry != "") && (listentry != null))
  {
  listentry = prompt(vbphrase["enter_list_item"], "");
  if ((listentry != "") && (listentry != null))
  {
  thelist = thelist + "<li>" + listentry + "</li>\n";
  }
  }
  AddTxt = thelist + "</"+ sListElement +">";
  if (!text)
  {
  AddTxt = AddTxt + " ";
  }
  AddText(AddTxt);
}

function insertCustomHtml( sCustomHtml ){
  getActiveText();
  if ( ( sCustomHtml != null ) && ( sCustomHtml != "" ) ){
    AddText( sCustomHtml );
  }
}
