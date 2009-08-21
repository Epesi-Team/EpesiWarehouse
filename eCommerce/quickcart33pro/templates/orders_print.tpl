<!-- BEGIN ORDER_PRINT -->
<div id="orderPrint">
  <div class="message" id="ok">
    <h3>$lang[Order_sent]</h3>
  </div>
  $sPaymentDescription
	
  <h4>$lang[Your_personal_information]</h4>
  <dl>
    <dt class="orderId">ID:</dt><dd class="orderId">$aOrder[iOrder]</dd>
    <dt class="firstAndLastName">$lang[First_and_last_name]:</dt><dd class="firstAndLastName">$aOrder[sFirstName] $aOrder[sLastName]</dd>
    <dt class="company">$lang[Company]:</dt><dd class="company">$aOrder[sCompanyName]</dd>
    <dt class="nip">$lang[Nip]:</dt><dd class="nip">$aOrder[sNip]</dd>
    <dt class="invoice">$lang[Invoice]:</dt><dd class="invoice">$aOrder[sInvoice]</dd>
    <dt class="street">$lang[Street]:</dt><dd class="street">$aOrder[sStreet]</dd>
    <dt class="zipCode">$lang[Zip_code]:</dt><dd class="zipCode">$aOrder[sZipCode]</dd>
    <dt class="city">$lang[City]:</dt><dd class="city">$aOrder[sCity]</dd>
    <dt class="country">$lang[Country]:</dt><dd class="country">$aOrder[sCountry]</dd>
    <dt class="phone">$lang[Telephone]:</dt><dd class="phone">$aOrder[sPhone]</dd>
    <dt class="email">$lang[Email]:</dt><dd class="email">$aOrder[sEmail]</dd>
    <dt class="orderDate">$lang[Date]:</dt><dd class="orderDate">$aOrder[sDate]</dd>
    <dt class="orderIP">IP:</dt><dd class="orderIP">$aOrder[sIp]</dd>
    <dt class="paymentChannel">$lang[Payment_channel]:</dt><dd class="paymentChannel">$aOrder[sPaymentChannel]</dd>
    <dt class="orderComment">$lang[Order_comment]:</dt><dd class="orderComment">$aOrder[sComment]</dd>
  </dl>
  <h4>$lang[Ordered_products]</h4>
  $sOrderProducts
  <script type="text/javascript">
  <!--
  AddOnload( delSavedUserData );
  //-->
  </script>
</div>
<!-- END ORDER_PRINT -->

<!-- BEGIN ORDER_PRINT_PAYMENT -->
<div id="paymentDescription">
  $aPayment[sDescription]
  $sPaymentOuter
</div>
<!-- END ORDER_PRINT_PAYMENT -->

<!-- BEGIN ORDER_PRINT_LIST -->
<tr class="l$aData[sStyle]">
  <th>$aData[sName]</th>
  <td class="price">$aData[sPrice]</td>
  <td class="quantity">$aData[iQuantity]</td>
  <td class="summary">$aData[sSummary]</td>
</tr>
<!-- END ORDER_PRINT_LIST -->
<!-- BEGIN ORDER_PRINT_HEAD -->
<div id="orderedProducts">
  <table cellspacing="0">
    <thead>
      <tr>
        <td class="name">$lang[Name]</td>
        <td class="price"><em>$lang[Price]</em><span>[$config[currency_symbol]]</span></td>
        <td class="quantity">$lang[Quantity]</td>
        <td class="summary"><em>$lang[Summary]</em><span>[$config[currency_symbol]]</span></td>
      </tr>
    </thead>
    <tfoot>
      <tr class="summaryProducts">
        <th colspan="3">$lang[Order_summary]</th>
        <td>$aData[sProductsSummary]</td>
      </tr>
      <tr class="summaryDelivery">
        <th colspan="3">$lang[Delivery_and_payment]: $aOrder[sCarrierName], $aOrder[sPaymentName]</th>
        <td id="carrierCost">$aOrder[sPaymentCarrierPrice]</td>
      </tr>
      <tr class="summaryOrder">
        <th colspan="3">$lang[Summary_cost]</th>
        <td id="orderSummary">$aData[sOrderSummary]</td>
      </tr>
    </tfoot>
    <tbody>
<!-- END ORDER_PRINT_HEAD -->
<!-- BEGIN ORDER_PRINT_FOOT -->
    </tbody>
  </table>
</div>
<!-- END ORDER_PRINT_FOOT -->


<!-- BEGIN ORDER_EMAIL_TITLE -->$lang[Order_info_title] $aData[iOrder]<!-- END ORDER_EMAIL_TITLE -->
<!-- BEGIN ORDER_EMAIL_BODY -->
$aData[sFirstName] $aData[sLastName]
$aData[sCompanyName]
$aData[sNip]
$aData[sStreet]
$aData[sZipCode] $aData[sCity]
$aData[sCountry]
$aData[sPhone]
$aData[sEmail]
------------$aData[sProducts]
- $aData[sCarrierName] ($aData[sPaymentName]) = $aData[sPaymentCarrierPrice] $config[currency_symbol]
- $lang[Payment_channel]: $aData[sPaymentChannel]
---
$lang[Summary_cost]: $aData[sOrderSummary] $config[currency_symbol]
---
$aData[sPaymentDescription]
<!-- END ORDER_EMAIL_BODY -->

<!-- BEGIN ORDER_EMAIL_LIST -->|n|- $aData[sName] - $aData[sPrice] $config[currency_symbol] * $aData[iQuantity] = $aData[sSummary] $config[currency_symbol]<!-- END ORDER_EMAIL_LIST -->
<!-- BEGIN ORDER_EMAIL_HEAD --><!-- END ORDER_EMAIL_HEAD -->
<!-- BEGIN ORDER_EMAIL_FOOT --><!-- END ORDER_EMAIL_FOOT -->