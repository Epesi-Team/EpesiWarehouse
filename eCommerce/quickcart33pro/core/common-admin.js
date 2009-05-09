function redirectToUrl( sUrl ){
  window.location = sUrl;
} // end function selectRedirect

function displayTab( sBlock ){
  if( sBlock && gEBI( sBlock ) ){
    for( var i = 0; i < aTabsId.length; i++ ){
      gEBI( aTabsId[i] ).style.display = 'none';
    } // end for

    gEBI( sBlock ).style.display = 'block';
    createCookie( 'sSelectedTab', sBlock, 2 );

    var aLi = gEBI( 'tabsNames' ).getElementsByTagName( 'li' );
    for( var i = 0; i < aLi.length; i++ ){
      if( aLi[i].className == sBlock )
        aLi[i].getElementsByTagName( 'a' )[0].style.color = '#000';
      else
        aLi[i].getElementsByTagName( 'a' )[0].style.color = '#ca2222';
    } // end for
  }
} // end function displayTab

function checkSelectedTab( ){
  if( isset( 'bDone' ) && bDone === true ){
    var sSelectedName = throwCookie( 'sSelectedTab' );
    if( sSelectedName && sSelectedName != '' ){
      displayTab( sSelectedName, null );
    }
  }
  else
    delCookie( 'sSelectedTab' );
} // end function checkSelectedTab

var aTabsId = Array( );
function getTabsArray( ){
  var aTables = gEBI( 'tabs' ).getElementsByTagName( 'table' );
  for( var i = 0; i < aTables.length; i++ ){
    if( aTables[i].className == 'tab' )
      aTabsId[aTabsId.length] = aTables[i].getAttribute( 'id' );
  } // end for
} // end function getTabsArray

function displayTabs( bShow ){
  if( bShow == true ){
    gEBI( "tabs" ).style.display = "block";
    gEBI( "tabsHide" ).style.display = "inline";
    gEBI( "tabsShow" ).style.display = "none";
  }
  else{
    gEBI( "tabs" ).style.display = "none";
    gEBI( "tabsHide" ).style.display = "none";
    gEBI( "tabsShow" ).style.display = "inline";
  }
} // end function displayTabs

function displayBlock( sBlock, oObj ){
  var bCheck = false;
  if( !oObj ){
    var bCheck = true;
  }
  if( gEBI( sBlock ).style.display == 'block' && bCheck === true )
    gEBI( sBlock ).style.display = 'none';
  else
    gEBI( sBlock ).style.display = 'block';
} // end function displayBlock

function checkType( ){
  if( gEBI( 'oPageParent' ).value == "" ){
    gEBI( "type" ).style.display = "";
  }
  else
    gEBI( "type" ).style.display = "none";
} // end function checkType

function throwYesNoSelect( sName, iSelected ){
  var aSelected = Array( '', '' );
  if( iSelected && iSelected == 1 )
    aSelected[1] = ' selected="selected"';
  else
    aSelected[0] = ' selected="selected"';
  document.write( '<select name="'+sName+'"><option value="1"'+aSelected[1]+'>'+yes+'</option><option value="0"'+aSelected[0]+'>'+no+'</option></select>' );
} // end function throwYesNoSelect

function throwYesNoBox( sName, bSelected ){
  if( bSelected && bSelected == true )
    sSelected = ' checked="checked"';
  else
    sSelected = '';
  document.write( '<input type="checkbox" name="'+sName+'" value="1" '+sSelected+' />' );
} // end function 

function changeInputStatus( oObj, sId ){
  if( oObj.checked )
    gEBI( sId ).className = 'inputr';
  else
    gEBI( sId ).className = 'inputr inputrd';
} // end function changeInputstatus

function Browser() {

  var ua, s, i;

  this.isIE    = false;  // Internet Explorer
  this.isOP    = false;  // Opera
  this.isNS    = false;  // Netscape
  this.version = null;

  ua = navigator.userAgent;

  s = "Opera";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isOP = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Gecko";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }

  s = "MSIE";
  if ((i = ua.indexOf(s))) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }
}

var browser = new Browser();


var activeButton = null;

if (browser.isIE)
  document.onmousedown = pageMousedown;
else
  document.addEventListener("mousedown", pageMousedown, true);

function pageMousedown(event) {

  var el;

  if (activeButton == null)
    return;

  if (browser.isIE)
    el = window.event.srcElement;
  else
    el = (event.target.tagName ? event.target : event.target.parentNode);

  if (el == activeButton)
    return;

  if (getContainerWith(el, "DIV", "menu") == null) {
    resetButton(activeButton);
    activeButton = null;
  }
}

function buttonClick(event, menuId) {

  var button;

  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;

  button.blur();

  if (button.menu == null) {
    button.menu = document.getElementById(menuId);
    if (button.menu.isInitialized == null)
      menuInit(button.menu);
  }

  if (activeButton != null)
    resetButton(activeButton);

  if (button != activeButton) {
    depressButton(button);
    activeButton = button;
  }
  else
    activeButton = null;

  return false;
}

function buttonMouseover(event, menuId) {

  var button;

  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;

  if (activeButton != null && activeButton != button)
    buttonClick(event, menuId);
}

function depressButton(button) {

  var x, y;

  button.className += " menuButtonActive";

  x = getPageOffsetLeft(button);
  y = getPageOffsetTop(button) + button.offsetHeight;

  if (browser.isIE) {
    x += button.offsetParent.clientLeft;
    y += button.offsetParent.clientTop;
  }

  button.menu.style.left = x + "px";
  button.menu.style.top  = y + "px";
  button.menu.style.visibility = "visible";
}

function resetButton(button) {

  removeClassName(button, "menuButtonActive");

  if (button.menu != null) {
    closeSubMenu(button.menu);
    button.menu.style.visibility = "hidden";
  }
}

function menuMouseover(event) {

  var menu;

  if (browser.isIE)
    menu = getContainerWith(window.event.srcElement, "DIV", "menu");
  else
    menu = event.currentTarget;

  if (menu.activeItem != null)
    closeSubMenu(menu);
}

function menuItemMouseover(event, menuId) {

  var item, menu, x, y;

  if (browser.isIE)
    item = getContainerWith(window.event.srcElement, "A", "menuItem");
  else
    item = event.currentTarget;
  menu = getContainerWith(item, "DIV", "menu");

  if (menu.activeItem != null)
    closeSubMenu(menu);
  menu.activeItem = item;

  item.className += " menuItemHighlight";

  if (item.subMenu == null) {
    item.subMenu = document.getElementById(menuId);
    if (item.subMenu.isInitialized == null)
      menuInit(item.subMenu);
  }

  x = getPageOffsetLeft(item) + item.offsetWidth;
  y = getPageOffsetTop(item);

  var maxX, maxY;

  if (browser.isIE) {
    maxX = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft) +
      (document.documentElement.clientWidth != 0 ? document.documentElement.clientWidth : document.body.clientWidth);
    maxY = Math.max(document.documentElement.scrollTop, document.body.scrollTop) +
      (document.documentElement.clientHeight != 0 ? document.documentElement.clientHeight : document.body.clientHeight);
  }
  if (browser.isOP) {
    maxX = document.documentElement.scrollLeft + window.innerWidth;
    maxY = document.documentElement.scrollTop  + window.innerHeight;
  }
  if (browser.isNS) {
    maxX = window.scrollX + window.innerWidth;
    maxY = window.scrollY + window.innerHeight;
  }
  maxX -= item.subMenu.offsetWidth;
  maxY -= item.subMenu.offsetHeight;

  if (x > maxX)
    x = Math.max(0, x - item.offsetWidth - item.subMenu.offsetWidth
      + (menu.offsetWidth - item.offsetWidth));
  y = Math.max(0, Math.min(y, maxY));

  item.subMenu.style.left = x + "px";
  item.subMenu.style.top  = y + "px";
  item.subMenu.style.visibility = "visible";

  if (browser.isIE)
    window.event.cancelBubble = true;
  else
    event.stopPropagation();
}

function closeSubMenu(menu) {

  if (menu == null || menu.activeItem == null)
    return;

  if (menu.activeItem.subMenu != null) {
    closeSubMenu(menu.activeItem.subMenu);
    menu.activeItem.subMenu.style.visibility = "hidden";
    menu.activeItem.subMenu = null;
  }
  removeClassName(menu.activeItem, "menuItemHighlight");
  menu.activeItem = null;
}

function menuInit(menu) {

  var itemList, spanList;
  var textEl, arrowEl;
  var itemWidth;
  var w, dw;
  var i, j;

  if (browser.isIE) {
    menu.style.lineHeight = "2.5ex";
    spanList = menu.getElementsByTagName("SPAN");
    for (i = 0; i < spanList.length; i++)
      if (hasClassName(spanList[i], "menuItemArrow")) {
        spanList[i].style.fontFamily = "Webdings";
        spanList[i].firstChild.nodeValue = "4";
      }
  }

  itemList = menu.getElementsByTagName("A");
  if (itemList.length > 0)
    itemWidth = itemList[0].offsetWidth;
  else
    return;

  for (i = 0; i < itemList.length; i++) {
    spanList = itemList[i].getElementsByTagName("SPAN");
    textEl  = null;
    arrowEl = null;
    for (j = 0; j < spanList.length; j++) {
      if (hasClassName(spanList[j], "menuItemText"))
        textEl = spanList[j];
      if (hasClassName(spanList[j], "menuItemArrow")) {
        arrowEl = spanList[j];
      }
    }
    if (textEl != null && arrowEl != null) {
      textEl.style.paddingRight = (itemWidth 
        - (textEl.offsetWidth + arrowEl.offsetWidth)) + "px";
      if (browser.isOP)
        arrowEl.style.marginRight = "0px";
    }
  }

  if (browser.isIE) {
    w = itemList[0].offsetWidth;
    itemList[0].style.width = w + "px";
    dw = itemList[0].offsetWidth - w;
    w -= dw;
    itemList[0].style.width = w + "px";
  }

  menu.isInitialized = true;
}

function getContainerWith(node, tagName, className) {

  while (node != null) {
    if (node.tagName != null && node.tagName == tagName &&
        hasClassName(node, className))
      return node;
    node = node.parentNode;
  }

  return node;
}

function hasClassName(el, name) {

  var i, list;

  list = el.className.split(" ");
  for (i = 0; i < list.length; i++)
    if (list[i] == name)
      return true;

  return false;
}

function removeClassName(el, name) {

  var i, curList, newList;

  if (el.className == null)
    return;

  newList = new Array();
  curList = el.className.split(" ");
  for (i = 0; i < curList.length; i++)
    if (curList[i] != name)
      newList.push(curList[i]);
  el.className = newList.join(" ");
}

function getPageOffsetLeft(el) {

  var x;

  x = el.offsetLeft;
  if (el.offsetParent != null)
    x += getPageOffsetLeft(el.offsetParent);

  return x;
}

function getPageOffsetTop(el) {

  var y;

  y = el.offsetTop;
  if (el.offsetParent != null)
    y += getPageOffsetTop(el.offsetParent);

  return y;
}

function del( ){
  if( confirm( delShure ) ) 
    return true;
  else 
    return false
} // end function del
