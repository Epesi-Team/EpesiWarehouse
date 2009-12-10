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
<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; font-family: Tahoma; font-size: 12px;">
    <tbody>
        <tr>
            <td colspan="2" style="height: 80px; background: url('http://www.prosperixgroup.com/templates/img/logo.gif') no-repeat; vertical-align: bottom; text-align: right;">
                <div style="height: 80px; width: 300px; float: right; color: #4c4c4c; font-size: 11px; font-weight: bold; background: url('http://www.prosperixgroup.com/templates/img/gradient.gif') right repeat-y;">
                    <div style="padding: 10px;">$config['title']</div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="height: 20px; background: url('http://www.prosperixgroup.com/templates/img/top-menu.gif') repeat-x"></td>
        </tr>
        <tr>
            <td colspan="2" style="height: 20px; background: url('http://www.prosperixgroup.com/templates/img/path.gif') repeat-x"></td>
        </tr>
        <tr>
            <td style="width: 300px; vertical-align: top; font-size: 11px;">
                <div style="width: 10px; height: 10px; float: right; background: url('http://www.prosperixgroup.com/templates/img/shadow-corner.gif') no-repeat;"></div>
                <div style="width: 300px; background: #993333 url('http://www.prosperixgroup.com/templates/img/shadow-left.gif') right repeat-y; color: white; font-weight: bold; padding: 5px 0px 5px 20px;">$lang['Contact_us']</div>
                <div style="width: 280px; background: #f2f2f2 url('http://www.prosperixgroup.com/templates/img/shadow-left.gif') right repeat-y; padding: 20px;">
$aData[contactus]
                </div>
                <div style="width: 320px; height: 10px; background: url('http://www.prosperixgroup.com/templates/img/shadow-top.gif') right repeat-x;">
                    <div style="width: 10px; height: 10px; float: right; background: url('http://www.prosperixgroup.com/templates/img/shadow-corner-2.gif') no-repeat;"></div>
                </div>
            </td>
            <td style="vertical-align: top;">
                <div style="padding: 20px; background: url('http://www.prosperixgroup.com/templates/img/shadow-top.gif') repeat-x;">
                    <div style="width: 100%; text-align: left;">
<h3>$lang['Order_info_title'] $aData[iOrder]</h3>
$aData[sCustomHello]
$aData[sPaymentDescription]
<br>
<h4>$lang[Your_personal_information]</h4>
$lang[First_and_last_name]: <i>$aData[sFirstName] $aData[sLastName]</i><br>
$lang[Company]: <i>$aData[sCompanyName]</i><br>
$lang[Nip]: <i>$aData[sNip]</i><br>
$lang[Street]: <i>$aData[sStreet]</i><br>
$lang[Zip_code]: <i>$aData[sZipCode]</i><br>
$lang[City]: <i>$aData[sCity]</i><br>
$lang[Country]: <i>$aData[sCountry]</i><br>
$lang[Telephone]: <i>$aData[sPhone]</i><br>
$lang[Email]: <i>$aData[sEmail]</i><br>
<h4>$lang[Order_summary]</h4>
<ul>
$aData[sProducts]
<li>$aData[sCarrierName] ($aData[sPaymentName]) = $aData[sPaymentCarrierPrice] $config[currency_symbol]</li>
<li>$lang[Payment_channel]: $aData[sPaymentChannel]</li>
</ul>
$lang[Summary_cost]: $aData[sOrderSummary] $config[currency_symbol]
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<!-- END ORDER_EMAIL_BODY -->

<!-- BEGIN ORDER_EMAIL_LIST --><li>$aData[sName] - $aData[sPrice] $config[currency_symbol] * $aData[iQuantity] = $aData[sSummary] $config[currency_symbol]</li><!-- END ORDER_EMAIL_LIST -->
<!-- BEGIN ORDER_EMAIL_HEAD --><!-- END ORDER_EMAIL_HEAD -->
<!-- BEGIN ORDER_EMAIL_FOOT --><!-- END ORDER_EMAIL_FOOT -->
