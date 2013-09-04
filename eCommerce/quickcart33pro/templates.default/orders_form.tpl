<!-- BEGIN ORDER_NEW_ACCOUNT -->
    <fieldset id="personalDataBlock">
      <legend>$lang[Create_account] <input type="checkbox" onChange="if(this.checked)Element.show('new_account_block'); else{Element.hide('new_account_block');$('oPassword').value='';$('oPassword2').value='';}" id="new_account_checkbox" /></legend>
      <span id="new_account_block" style="display:none">
      <fieldset id="personalData">
        <fieldset id="setBasic">
          <div id="password1">
            <label for="oPassword">$lang[Password_new_user]</label>
            <input type="password" name="sPassword" value="$aUser[sPassword]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oPassword" alt="txt;4;$lang[Password_too_short];if"/>
          </div>
        </fieldset>
        <fieldset id="setExtend">
          <div id="password2">
            <label for="oPassword2">$lang[Password_confirmation]</label>
            <input type="password" name="sPassword2" value="$aUser[sPassword2]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oPassword2" alt="txtValue;#oPassword;$lang[Password_mismatch]" />
          </div>
        </fieldset>
      </fieldset>
      </span>
    </fieldset>
    <script type="text/javascript">
	if("$aUser[sPassword]" != "") {
		Element.show('new_account_block');
		$('new_account_checkbox').checked=1;
	}
    </script>
<!-- END ORDER_NEW_ACCOUNT -->
<!-- BEGIN ORDER_FORM -->
    <h2>Step 1/3: Address Details</h2>
<script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
<div id="order">
  <a name="orderFormAnchor"></a>
  <form action="$aData[sLinkName]" method="post" onsubmit="return checkForm( this )" id="orderForm">
    <fieldset id="personalDataBlock">
      <legend style="width:100%"><span style="float:right;color:#ED1C24">*&nbsp;Required fields</span>$lang[Your_personal_information]</legend>
      <fieldset id="personalData">
        <fieldset id="setBasic">
          <div id="firstName">
            <label for="oFirstName">$lang[First_name]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sFirstName" value="$aUser[sFirstName]" maxlength="30" class="input" onblur="saveUserData( this.name, this.value )" id="oFirstName" alt="simple" />
          </div>
          <div id="lastName">
            <label for="oLastName">$lang[Last_name]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sLastName" value="$aUser[sLastName]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oLastName" alt="simple" />
          </div>
          <div id="company">
            <label for="oCompany">$lang[Company]</label>
            <input type="text" name="sCompanyName" value="$aUser[sCompanyName]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oCompany" />
          </div>
<!--          <div id="nip">
            <label for="oNip">$lang[Nip]</label>
            <input type="text" name="sNip" value="$aUser[sNip]" maxlength="20" class="input" onblur="saveUserData( this.name, this.value )" id="oNip" />
          </div>
          <div id="invoice">
            <input type="checkbox" name="iInvoice" value="1" id="oInvoice" />
            <label for="oInvoice">$lang[Invoice_info]</label>
          </div>-->
	  <input type="hidden" name="iInvoice" value="1" id="oInvoice" />
          <div id="street">
            <label for="oStreet">$lang[Street]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sStreet" value="$aUser[sStreet]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oStreet" alt="simple" />
          </div>
        </fieldset>
        <fieldset id="setBasic">
          <div id="zipCode">
            <label for="oZipCode">$lang[Zip_code]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sZipCode" value="$aUser[sZipCode]" maxlength="20" class="input" onblur="saveUserData( this.name, this.value )" id="oZipCode" alt="simple" />
          </div>
          <div id="city">
            <label for="oCity">$lang[City]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sCity" value="$aUser[sCity]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oCity" alt="simple" />
          </div>
          <div id="country">
            <label for="oCountry">$lang[Country]&nbsp;<span style="color:#ED1C24">*</span></label>
            <select name="sCountry" class="input" onblur="saveUserData( this.name, this.value )" id="oCountry" alt="simple" onChange="loadStates(this.value,'')" />
    	    $countriesList
    	    </select>
          </div>
          <div id="state">
            <label for="oState">$lang[State]</label>
            <select name="sState" class="input" onblur="saveUserData( this.name, this.value )" id="oState" alt="simple" />
    	    </select>
          </div>
        </fieldset>
        <fieldset id="setExtend">
          <div id="phone">
            <label for="oPhone">$lang[Telephone]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sPhone" value="$aUser[sPhone]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oPhone" alt="simple" />
          </div>
          <div id="email">
            <label for="oEmail">$lang[Email]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sEmail" value="$aUser[sEmail]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oEmail" alt="email" />
          </div>
          <div id="promotionCode">
            <label for="oPromotionCode">$lang[Promotion_code]</label>
            <input type="text" name="sPromotionCode" value="$aUser[sPromotionCode]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oPromotionCode" />
          </div>
        </fieldset>
          <div id="comment">
            <label for="oComment">$lang[Order_comment]</label>
            <textarea name="sComment" cols="50" rows="9" id="oComment"></textarea>
          </div>
      </fieldset>
    </fieldset>
    $sNewAccount
    <fieldset id="shippingDataBlock">
      <legend>$lang[Different_shipping_address] <input type="checkbox" onChange="if(this.checked)Element.show('shipping_address_block'); else Element.hide('shipping_address_block');" name="bShippingAddress" id="shipping_address_checkbox" /></legend>
      <span id="shipping_address_block" style="display:none">
      <fieldset id="shippingData">
        <fieldset id="setBasic">
          <div id="firstName">
            <label for="oShippingFirstName">$lang[First_name]</label>
            <input type="text" name="sShippingFirstName" value="$aUser[sShippingFirstName]" maxlength="30" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingFirstName" />
          </div>
          <div id="lastName">
            <label for="oShippingLastName">$lang[Last_name]</label>
            <input type="text" name="sShippingLastName" value="$aUser[sShippingLastName]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingLastName" />
          </div>
          <div id="company">
            <label for="oShippingCompany">$lang[Company]</label>
            <input type="text" name="sShippingCompanyName" value="$aUser[sShippingCompanyName]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingCompany" />
          </div>
        </fieldset>
        <fieldset id="setBasic">
          <div id="street">
            <label for="oShippingStreet">$lang[Street]</label>
            <input type="text" name="sShippingStreet" value="$aUser[sShippingStreet]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingStreet" />
          </div>
          <div id="zipCode">
            <label for="oShippingZipCode">$lang[Zip_code]</label>
            <input type="text" name="sShippingZipCode" value="$aUser[sShippingZipCode]" maxlength="20" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingZipCode" />
          </div>
          <div id="city">
            <label for="oShippingCity">$lang[City]</label>
            <input type="text" name="sShippingCity" value="$aUser[sShippingCity]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingCity" />
          </div>
        </fieldset>
        <fieldset id="setExtend">
          <div id="country">
            <label for="oShippingCountry">$lang[Country]</label>
            <select name="sShippingCountry" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingCountry" onChange="loadStates(this.value,'Shipping')" />
    	    $shippingCountriesList
    	    </select>
          </div>
          <div id="state">
            <label for="oShippingState">$lang[State]</label>
            <select name="sShippingState" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingState" alt="simple" />
    	    </select>
          </div>
          <div id="phone">
            <label for="oShippingPhone">$lang[Telephone]</label>
            <input type="text" name="sShippingPhone" value="$aUser[sShippingPhone]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingPhone" />
          </div>
        </fieldset>
      </fieldset>
      </span>
    </fieldset>
    <div style="border-bottom:2px solid #C0D4EB;text-align:right;padding-bottom:5px;padding-right:10px;">
        <input type="hidden" value="1" name="sOrderSend" />
	<input style="margin-top:10px" type="submit" value="$lang[Page_next]" class="submit" />
    </div>
  </form>
</div>
<script type="text/javascript">
<!--

function initOrderForm() {
    if("$aUser[bShippingAddress]"!="") {
	document.getElementById('shipping_address_checkbox').checked=1;
        Element.show('shipping_address_block');
    }
    states = $statesJS;
    loadStates(document.getElementById('oCountry').value,'');
    loadStates(document.getElementById('oShippingCountry').value,'Shipping');
    var old = aUserDataNames;
    aUserDataNames = new Array('sState','sShippingState');
    checkSavedUserData();
    aUserDataNames = old;
}

AddOnload( checkSavedUserData );
AddOnload( initOrderForm );

//-->
</script>
<!-- END ORDER_FORM -->
<!-- BEGIN ORDER_FORM_LOGGED -->
<h2>Step 1/3: Address Details</h2>
<script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
<div id="order">
  <form action="$aData[sLinkName]" method="post" onsubmit="return checkForm( this )" id="orderForm">
    <fieldset id="personalDataBlock">
      <legend style="width:100%"><span style="float:right;color:#ED1C24">*&nbsp;Required fields</span>$lang[Your_personal_information]</legend>
      <fieldset id="personalData">
        <div id="addressesList">
        $addressesList
        </div>
        <fieldset id="setBasic">
          <div id="firstName">
            <label for="oFirstName">$lang[First_name]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sFirstName" value="$aUser[sFirstName]" maxlength="30" class="input" onblur="saveUserData( this.name, this.value )" id="oFirstName" alt="simple" />
          </div>
          <div id="lastName">
            <label for="oLastName">$lang[Last_name]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sLastName" value="$aUser[sLastName]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oLastName" alt="simple" />
          </div>
          <div id="company">
            <label for="oCompany">$lang[Company]</label>
            <input type="text" name="sCompanyName" value="$aUser[sCompanyName]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oCompany" />
          </div>
<!--          <div id="nip">
            <label for="oNip">$lang[Nip]</label>
            <input type="text" name="sNip" value="$aUser[sNip]" maxlength="20" class="input" onblur="saveUserData( this.name, this.value )" id="oNip" />
          </div>
          <div id="invoice">
            <input type="checkbox" name="iInvoice" value="1" id="oInvoice" />
            <label for="oInvoice">$lang[Invoice_info]</label>
          </div>-->
	  <input type="hidden" name="iInvoice" value="1" id="oInvoice" />
          <div id="street">
            <label for="oStreet">$lang[Street]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sStreet" value="$aUser[sStreet]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oStreet" alt="simple" />
          </div>
        </fieldset>
        <fieldset id="setBasic">
          <div id="zipCode">
            <label for="oZipCode">$lang[Zip_code]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sZipCode" value="$aUser[sZipCode]" maxlength="20" class="input" onblur="saveUserData( this.name, this.value )" id="oZipCode" alt="simple" />
          </div>
          <div id="city">
            <label for="oCity">$lang[City]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sCity" value="$aUser[sCity]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oCity" alt="simple" />
          </div>
          <div id="country">
            <label for="oCountry">$lang[Country]&nbsp;<span style="color:#ED1C24">*</span></label>
            <select name="sCountry" class="input" onblur="saveUserData( this.name, this.value )" id="oCountry" alt="simple" onChange="loadStates(this.value,'')" />
    	    $countriesList
    	    </select>
          </div>
          <div id="state">
            <label for="oState">$lang[State]</label>
            <select name="sState" class="input" onblur="saveUserData( this.name, this.value )" id="oState" alt="simple" />
    	    </select>
          </div>
        </fieldset>
        <fieldset id="setExtend">
          <div id="phone">
            <label for="oPhone">$lang[Telephone]&nbsp;<span style="color:#ED1C24">*</span></label>
            <input type="text" name="sPhone" value="$aUser[sPhone]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oPhone" alt="simple" />
          </div>
          <input type="hidden" name="sEmail" value="$aUser[sEmail]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oEmail" alt="email" />
          <div id="promotionCode">
            <label for="oPromotionCode">$lang[Promotion_code]</label>
            <input type="text" name="sPromotionCode" value="$aUser[sPromotionCode]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oPromotionCode" />
          </div>
        </fieldset>
          <div id="comment">
            <label for="oComment">$lang[Order_comment]</label>
            <textarea name="sComment" cols="50" rows="9" id="oComment" style="height:155px"></textarea>
          </div>
      </fieldset>
    </fieldset>
    $sNewAccount
    <fieldset id="shippingDataBlock">
      <legend>$lang[Different_shipping_address] <input type="checkbox" onChange="if(this.checked)Element.show('shipping_address_block'); else Element.hide('shipping_address_block');" name="bShippingAddress" id="shipping_address_checkbox" /></legend>
      <span id="shipping_address_block" style="display:none">
      <fieldset id="shippingData">
        <fieldset id="setBasic">
          <div id="firstName">
            <label for="oShippingFirstName">$lang[First_name]</label>
            <input type="text" name="sShippingFirstName" value="$aUser[sShippingFirstName]" maxlength="30" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingFirstName" />
          </div>
          <div id="lastName">
            <label for="oShippingLastName">$lang[Last_name]</label>
            <input type="text" name="sShippingLastName" value="$aUser[sShippingLastName]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingLastName" />
          </div>
          <div id="company">
            <label for="oShippingCompany">$lang[Company]</label>
            <input type="text" name="sShippingCompanyName" value="$aUser[sShippingCompanyName]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingCompany" />
          </div>
        </fieldset>
        <fieldset id="setBasic">
          <div id="street">
            <label for="oShippingStreet">$lang[Street]</label>
            <input type="text" name="sShippingStreet" value="$aUser[sShippingStreet]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingStreet" />
          </div>
          <div id="zipCode">
            <label for="oShippingZipCode">$lang[Zip_code]</label>
            <input type="text" name="sShippingZipCode" value="$aUser[sShippingZipCode]" maxlength="20" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingZipCode" />
          </div>
          <div id="city">
            <label for="oShippingCity">$lang[City]</label>
            <input type="text" name="sShippingCity" value="$aUser[sShippingCity]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingCity" />
          </div>
        </fieldset>
        <fieldset id="setExtend">
          <div id="country">
            <label for="oShippingCountry">$lang[Country]</label>
            <select name="sShippingCountry" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingCountry" onChange="loadStates(this.value,'Shipping')" />
    	    $shippingCountriesList
    	    </select>
          </div>
          <div id="state">
            <label for="oShippingState">$lang[State]</label>
            <select name="sShippingState" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingState" alt="simple" />
    	    </select>
          </div>
          <div id="phone">
            <label for="oShippingPhone">$lang[Telephone]</label>
            <input type="text" name="sShippingPhone" value="$aUser[sShippingPhone]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oShippingPhone" />
          </div>
        </fieldset>
      </fieldset>
      </span>
    </fieldset>
    <div style="border-bottom:2px solid #C0D4EB;text-align:right;padding-bottom:5px;padding-right:10px;">
	<input type="hidden" value="1" name="sOrderSend" />
        <input style="margin-top:10px" type="submit" value="$lang[Page_next]" class="submit" />
    </div>
  </form>
</div>
<script type="text/javascript">
<!--

function initOrderForm() {
    if("$aUser[bShippingAddress]"!="") {
	document.getElementById('shipping_address_checkbox').checked=1;
        Element.show('shipping_address_block');
    }
    states = $statesJS;
    if("$aUser[sCountry]"!="")
	document.getElementById('oCountry').value = "$aUser[sCountry]";
    loadStates(document.getElementById('oCountry').value,'');
    if("$aUser[sState]"!="")
	document.getElementById('oState').value = "$aUser[sState]";
    loadStates(document.getElementById('oShippingCountry').value,'Shipping');
}

AddOnload( initOrderForm );
//-->
</script>
<!-- END ORDER_FORM_LOGGED -->
<!-- BEGIN ORDER_FORM_SHIPPING -->
<h2>Step 2/3: Shipment Type</h2>
<script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
<div id="order">
  <form action="$aData[sLinkName]" method="post" onsubmit="return checkForm( this )" id="orderForm">
    <fieldset id="orderedProducts">
      <legend>$lang[Ordered_products]</legend>
      $sOrderProducts
    </fieldset>
    <fieldset id="deliveryAndPayment">
      <legend>$lang[Delivery_and_payment]</legend>
      $sPaymentCarriers
    </fieldset>
    <fieldset id="pickupShop" style="display:none">
      <legend>$lang[Pickup_shop]</legend>
      $sPickupShops
    </fieldset>
    <fieldset id="orderedProducts">
    <table cellspacing="0" style="border-top:0px">
    <tfoot>
      <tr id="rulesAccept">
        <th colspan="4">
          $sRules
        </th>
      </tr>
      <tr id="nextStep">
        <th colspan="4" class="nextStep">
          <input type="hidden" value="2" name="sOrderSend" />
          <input type="submit" value="$lang[order_send] &raquo;" class="submit" />
        </th>
      </tr>
    </tfoot>
    </table>
    </fieldset>
  </form>
</div>
<!-- END ORDER_FORM_SHIPPING -->
<!-- BEGIN RULES_ACCEPT -->
<input type="hidden" name="iRules" value="1" />
<em><input type="checkbox" name="iRulesAccept" value="1" alt="box;$lang[Require_rules_accept]" onChange="saveUserData( this.name, this.checked )" /></em>
<span>$lang[Rules_accept] (<a href="$aRules[sLinkName]" class="new-window">$lang[rules_read] &raquo;</a>).</span>
<!-- END RULES_ACCEPT -->

<!-- BEGIN ORDER_PRODUCTS_LIST -->
<tr class="l$aData[sStyle]">
  <th>
    <a href="$aData[sLinkName]">$aData[sName]</a>
  </th>
  <td class="price">
    $config[currency_symbol]&nbsp;$aData[sPrice]
  </td>
  <td class="quantity">
    $aData[iQuantity]
  </td>
  <td class="summary">
    $config[currency_symbol]&nbsp;$aData[sSummary]
  </td>
</tr>
<!-- END ORDER_PRODUCTS_LIST -->
<!-- BEGIN ORDER_PRODUCTS_HEAD -->
<script type="text/javascript">
<!--
var fOrderSummary = "$aData[fProductsSummary]";
//-->
</script>
<div>
  <table cellspacing="0">
    <thead>
      <tr>
        <td class="name">
          $lang[Name]
        </td>
        <td class="price">
          <em>$lang[Price]</em>
        </td>
        <td class="quantity">
          $lang[Quantity]
        </td>
        <td class="summary">
          <em>$lang[Summary]</em>
        </td>
      </tr>
    </thead>
    <tfoot>
      <tr class="summaryProducts">
        <th colspan="3">
          $lang[Order_summary]
        </th>
        <td>
          $config[currency_symbol]&nbsp;$aData[sProductsSummary]
        </td>
      </tr>
      <tr class="summaryDelivery">
        <th colspan="3">
          $lang[Delivery_and_payment]
        </th>
        <td id="carrierCost">
          0.00
        </td>
      </tr>
      <tr class="summaryOrder">
        <th colspan="3">
          $lang[Summary_cost]
        </th>
        <td id="orderSummary">
          $aData[sProductsSummary]
        </td>
      </tr>
    </tfoot>
    <tbody>
<!-- END ORDER_PRODUCTS_HEAD -->
<!-- BEGIN ORDER_PRODUCTS_FOOT -->
    </tbody>
  </table>
</div>
<!-- END ORDER_PRODUCTS_FOOT -->

<!-- BEGIN ORDER_PAYMENTS -->
<th>
  <em>$aData[sName]</em>
  $aData[sPaymentChannel]
</th>
<!-- END ORDER_PAYMENTS -->
<!-- BEGIN ORDER_CARRIERS -->
<tr>
  <th>$aData[sName]&nbsp;$aData[sPickupShop]</th>
  $aData[sPayments]
</tr>
<!-- END ORDER_CARRIERS -->
<!-- BEGIN ORDER_PICKUP_SHOP_HEAD -->
<script type="text/javascript">
function pickupShopCheck(e) {
	var k = e.value.split(';');
	var list = e.form['iPickupShop'];
	if(typeof list.length == 'undefined') list = new Array(list);
	if(k[0]==0) {
		document.getElementById('pickupShop').style.display = 'block';
		for(var j=0; j<list.length; j++)
			list[j].alt = "radio;$lang['Select_pickup_shop']";
	} else {
		document.getElementById('pickupShop').style.display = 'none';
		for(var j=0; j<list.length; j++)
			list[j].alt = '';
	}
}
</script>
<table cellspacing="0">
  <tbody>
<!-- END ORDER_PICKUP_SHOP_HEAD -->
<!-- BEGIN ORDER_PICKUP_SHOP_FOOT -->
  </tbody>
</table>
<!-- END ORDER_PICKUP_SHOP_FOOT -->
<!-- BEGIN ORDER_PICKUP_SHOP_LIST -->
<tr><td><input type="radio" name="iPickupShop" value="$aData[iShop]" onChange="saveUserData( this.name, this.value )" />$aData[sName]</td></tr>
<!-- END ORDER_PICKUP_SHOP_LIST -->
<!-- BEGIN ORDER_PAYMENT_CARRIERS_LIST -->
<td><input type="radio" name="sPaymentCarrier" value="$aData[iCarrier];$aData[iPayment];$aData[fPaymentCarrierPrice]" alt="radio;$lang['Select_delivery_and_payment']" onChange="countCarrierPrice( this );saveUserData( this.name, this.value );if(typeof pickupShopCheck != 'undefined')pickupShopCheck(this)" />$config[currency_symbol]&nbsp;$aData[sPaymentCarrierPrice]</td>
<!-- END ORDER_PAYMENT_CARRIERS_LIST -->
<!-- BEGIN ORDER_PAYMENT_CARRIERS_MULTI_BEGIN -->
<td>
<table class="multi_payment_carrier">
<!-- END ORDER_PAYMENT_CARRIERS_MULTI_BEGIN -->
<!-- BEGIN ORDER_PAYMENT_CARRIERS_MULTI_LIST -->
<tr><td>$aData[sPaymentCarrierDescription]</td><td><input type="radio" name="sPaymentCarrier" value="$aData[iCarrier];$aData[iPayment];$aData[fPaymentCarrierPrice]" alt="radio;$lang['Select_delivery_and_payment']" onChange="countCarrierPrice( this );saveUserData( this.name, this.value );if(typeof pickupShopCheck != 'undefined')pickupShopCheck(this)" />$config[currency_symbol]&nbsp;$aData[sPaymentCarrierPrice]</td></tr>
<!-- END ORDER_PAYMENT_CARRIERS_MULTI_LIST -->
<!-- BEGIN ORDER_PAYMENT_CARRIERS_MULTI_END -->
</table>
</td>
<!-- END ORDER_PAYMENT_CARRIERS_MULTI_END -->
<!-- BEGIN ORDER_PAYMENT_CARRIERS_EMPTY -->
<td>&nbsp;</td>
<!-- END ORDER_PAYMENT_CARRIERS_EMPTY -->
<!-- BEGIN ORDER_PAYMENT_CARRIERS_HEAD -->
<table cellspacing="0">
  <thead>
    <tr>
      <td>&nbsp;</td>
      $aData[sPaymentList]
    </tr>
  </thead>
  <tbody>
<!-- END ORDER_PAYMENT_CARRIERS_HEAD -->
<!-- BEGIN ORDER_PAYMENT_CARRIERS_FOOT -->
  </tbody>
</table>
<script type="text/javascript">
  function setCarrier() {
    var l = $('orderForm').elements['sPaymentCarrier'];
    if(typeof l.length != "undefined" && l.length>0)
	l = l[0];
    if(typeof l != "undefined") {
	l.checked = 1;
	countCarrierPrice( l );
	if(typeof pickupShopCheck != 'undefined')pickupShopCheck(l);
    }
  }
  AddOnload(setCarrier);
</script>
<!-- END ORDER_PAYMENT_CARRIERS_FOOT -->
<!-- BEGIN PAYMENT_CHANNEL -->
<div id="paymentChannel">
  <select name="aPaymentChannel[$aData[iPayment]]">
  $aData[sPaymentChannelSelect]
  </select>
</div>
<!-- END PAYMENT_CHANNEL -->
<!-- BEGIN ZAGIEL_INFO -->
<div id="paymentChannel" class="zagielInfo">
  <a href="javascript:void(0);" onclick="windowNew( 'http://www.zagiel.com.pl/kalkulator/jak_kupic.html', 600, 500, 'Zagiel' )">$lang[Know_about_zagiel_payment]</a>
</div>
<!-- END ZAGIEL_INFO -->
