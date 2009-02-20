<!-- BEGIN ORDER_FORM -->
<script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
<div id="order">
  <form action="$aData[sLinkName]" method="post" onsubmit="return checkForm( this )" id="orderForm">
    <fieldset id="personalDataBlock">
      <legend>$lang[Your_personal_information]</legend>
      <fieldset id="personalData">
        <fieldset id="setBasic">
          <div id="firstName">
            <label for="oFirstName">$lang[First_name]</label>
            <input type="text" name="sFirstName" value="$aUser[sFirstName]" maxlength="30" class="input" onblur="saveUserData( this.name, this.value )" id="oFirstName" alt="simple" />
          </div>
          <div id="lastName">
            <label for="oLastName">$lang[Last_name]</label>
            <input type="text" name="sLastName" value="$aUser[sLastName]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oLastName" alt="simple" />
          </div>
          <div id="company">
            <label for="oCompany">$lang[Company]</label>
            <input type="text" name="sCompanyName" value="$aUser[sCompanyName]" maxlength="100" class="input" onblur="saveUserData( this.name, this.value )" id="oCompany" />
          </div>
          <div id="street">
            <label for="oStreet">$lang[Street]</label>
            <input type="text" name="sStreet" value="$aUser[sStreet]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oStreet" alt="simple" />
          </div>
          <div id="zipCode">
            <label for="oZipCode">$lang[Zip_code]</label>
            <input type="text" name="sZipCode" value="$aUser[sZipCode]" maxlength="20" class="input" onblur="saveUserData( this.name, this.value )" id="oZipCode" alt="simple" />
          </div>
          <div id="city">
            <label for="oCity">$lang[City]</label>
            <input type="text" name="sCity" value="$aUser[sCity]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oCity" alt="simple" />
          </div>
        </fieldset>
        <fieldset id="setExtend">
          <div id="phone">
            <label for="oPhone">$lang[Telephone]</label>
            <input type="text" name="sPhone" value="$aUser[sPhone]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oPhone" alt="simple" />
          </div>
          <div id="email">
            <label for="oEmail">$lang[Email]</label>
            <input type="text" name="sEmail" value="$aUser[sEmail]" maxlength="40" class="input" onblur="saveUserData( this.name, this.value )" id="oEmail" alt="email" />
          </div>
          <div id="comment">
            <label for="oComment">$lang[Order_comment]</label>
            <textarea name="sComment" cols="50" rows="9" id="oComment"></textarea>
          </div>
        </fieldset>
      </fieldset>
    </fieldset>
    <fieldset id="deliveryAndPayment">
      <legend>$lang[Delivery_and_payment]</legend>
      $sPaymentCarriers
    </fieldset>
    <fieldset id="orderedProducts">
      <legend>$lang[Ordered_products]</legend>
      $sOrderProducts
    </fieldset>
  </form>
</div>
<!-- END ORDER_FORM -->
<!-- BEGIN RULES_ACCEPT -->
<input type="hidden" name="iRules" value="1" />
<em><input type="checkbox" name="iRulesAccept" value="1" alt="box;$lang[Require_rules_accept]" /></em>
<span>$lang[Rules_accept] (<a href="$aRules[sLinkName]" class="new-window">$lang[rules_read] &raquo;</a>).</span>
<!-- END RULES_ACCEPT -->

<!-- BEGIN ORDER_PRODUCTS_LIST -->
<tr class="l$aData[sStyle]">
  <th>
    <a href="$aData[sLinkName]">$aData[sName]</a>
  </th>
  <td class="price">
    $aData[sPrice]
  </td>
  <td class="quantity">
    $aData[iQuantity]
  </td>
  <td class="summary">
    $aData[sSummary]
  </td>
</tr>
<!-- END ORDER_PRODUCTS_LIST -->
<!-- BEGIN ORDER_PRODUCTS_HEAD -->
<script type="text/javascript">
<!--
var fOrderSummary = "$aData[fProductsSummary]";
AddOnload( checkSavedUserData );
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
          <em>$lang[Price]</em><span>[$config[currency_symbol]]</span>
        </td>
        <td class="quantity">
          $lang[Quantity]
        </td>
        <td class="summary">
          <em>$lang[Summary]</em><span>[$config[currency_symbol]]</span>
        </td>
      </tr>
    </thead>
    <tfoot>
      <tr class="summaryProducts">
        <th colspan="3">
          $lang[Order_summary]
        </th>
        <td>
          $aData[sProductsSummary]
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
      <tr id="rulesAccept">
        <th colspan="4">
          $sRules
        </th>
      </tr>
      <tr id="nextStep">
        <th colspan="4" class="nextStep">
          <input type="submit" value="$lang[order_send] &raquo;" name="sOrderSend" class="submit" />
        </th>
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
  <em>$aData[sName]</em><span>[$config[currency_symbol]]</span>
</th>
<!-- END ORDER_PAYMENTS -->
<!-- BEGIN ORDER_CARRIERS -->
<tr>
  <th>$aData[sName]</th>
  $aData[sPayments]
</tr>
<!-- END ORDER_CARRIERS -->
<!-- BEGIN ORDER_PAYMENT_CARRIERS_LIST -->
<td><input type="radio" name="sPaymentCarrier" value="$aData[iCarrier];$aData[iPayment];$aData[fPaymentCarrierPrice]" onclick="countCarrierPrice( this )" alt="radio;$lang['Select_delivery_and_payment']" />$aData[sPaymentCarrierPrice]</td>
<!-- END ORDER_PAYMENT_CARRIERS_LIST -->
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
<!-- END ORDER_PAYMENT_CARRIERS_FOOT -->