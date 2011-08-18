<!-- BEGIN ORDER_PRINT -->
<h2>Step 3/3: Order Sent</h2>
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
  <td class="price">&#36;&nbsp;$aData[sPrice]</td>
  <td class="quantity">$aData[iQuantity]</td>
  <td class="summary">&#36;&nbsp;$aData[sSummary]</td>
</tr>
<!-- END ORDER_PRINT_LIST -->
<!-- BEGIN ORDER_PRINT_HEAD -->
<div id="orderedProducts">
  <table cellspacing="0">
    <thead>
      <tr>
        <td class="name">$lang[Name]</td>
        <td class="price"><em>$lang[Price]</em></td>
        <td class="quantity">$lang[Quantity]</td>
        <td class="summary"><em>$lang[Summary]</em></td>
      </tr>
    </thead>
    <tfoot>
      <tr class="summaryProducts">
        <th colspan="3">$lang[Order_summary]</th>
        <td>$aData[sProductsSummary]</td>
      </tr>
      <tr class="summaryDelivery">
        <th colspan="3">$lang[Delivery_and_payment]: <strong>$aOrder[sCarrierName], $aOrder[sPaymentName]</strong></th>
        <td id="carrierCost">$aOrder[sPaymentCarrierPrice]</td>
      </tr>
      <tr class="summaryDelivery">
        <th colspan="3">$lang[Shipment_discount]</th>
        <td>$aOrder[sShipmentDiscount]</td>
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
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
        <tr>
            <td colspan="2" height="80" width="430"><img src="templates/img/logo.gif"></td>
            <td height="80" width="100%"><font size="1">
            1260 E. Woodland Avenue-Unit 17A<br />
            Springfield,PA 19064<br />
            <br />
            Phone: <b>610-690-2900</b><br />
            Fax: 610-690-0888
            </font></td>
            <td height="80" width="270"><img src="templates/img/gradient.gif"></td>
        </tr>
    </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
        <tr>
            <td height="20" width="100%" align="right" background="templates/img/top-menu.gif">
            	&nbsp;
            </td>
        </tr>
    </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
        <tr>
            <td height="20" background="templates/img/path.gif"></td>
        </tr>
    </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
        <tr>
            <td width="100%" valign="top">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td width="100%" height="10" background="templates/img/shadow-top.gif"></td>
                        </tr>
                    </tbody>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td width="20"></td>
                            <td>
                                <font size="2" face="Arial,sans-serif" color="black">
                                    <font color="#993333"><h3>$lang['Order_info_title'] $aData[iOrder]</h3></font>
                                    $aData[sCustomHello]

<!--                                    <h4>$lang['Payment_description']</h4>
                                    $aData['sPaymentDescription']
                                    <br>-->
				    
				    <table border="0" width="100%"><tr><td width="50%">
				    $aData[sBillingAddress]
				    </td><td>
				    $aData[sShippingAddress]
				    </td></tr></table>

                                    <h4>$lang[Order_summary]</h4>
                                    $aData[sProducts]
                                    <ul>
                                    <li>$aData[sCarrierName] ($aData[sPaymentName]) = &#36;&nbsp;$aData[sPaymentCarrierPrice]</li>
                                    $aData[sPaymentChannelInfo]
                                    $aData[sShipmentDiscountInfo]
                                    </ul>
                                    
                                    <b>$lang[Summary_cost]: &#36;&nbsp;$aData[sOrderSummary]</b>
                                </font>
                            </td>
                            <td width="20"></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<!-- END ORDER_EMAIL_BODY -->
<!-- BEGIN ORDER_EMAIL_COMPANY -->$lang[Company]: <i>$aData[sCompanyName]</i><br><!-- END ORDER_EMAIL_COMPANY -->
<!-- BEGIN ORDER_EMAIL_BILLING -->
                                    <h4>$lang[Your_personal_information]</h4>
                                    $lang[First_and_last_name]: <i>$aData[sFirstName] $aData[sLastName]</i><br>
                                    $aData[sCompanyInfo]
                                    $lang[Street]: <i>$aData[sStreet]</i><br>
                                    $lang[Zip_code]: <i>$aData[sZipCode]</i><br>
                                    $lang[City]: <i>$aData[sCity]</i><br>
                                    $lang[Country]: <i>$aData[sCountry]</i><br>
                                    $lang[Telephone]: <i>$aData[sPhone]</i><br>
                                    $lang[Email]: <i>$aData[sEmail]</i><br>
                                    <br>
<!-- END ORDER_EMAIL_BILLING -->
<!-- BEGIN ORDER_EMAIL_SHIPPING_COMPANY -->$lang[Company]: <i>$aData[sShippingCompanyName]</i><br><!-- END ORDER_EMAIL_SHIPPING_COMPANY -->
<!-- BEGIN ORDER_EMAIL_SHIPPING -->
                                    <h4>$lang[Different_shipping_address]</h4>
                                    $lang[First_and_last_name]: <i>$aData[sShippingFirstName] $aData[sShippingLastName]</i><br>
                                    $aData[sShippingCompanyInfo]
                                    $lang[Street]: <i>$aData[sShippingStreet]</i><br>
                                    $lang[Zip_code]: <i>$aData[sShippingZipCode]</i><br>
                                    $lang[City]: <i>$aData[sShippingCity]</i><br>
                                    $lang[Country]: <i>$aData[sShippingCountry]</i><br>
                                    $lang[Telephone]: <i>$aData[sShippingPhone]</i><br>
                                    <br>
<!-- END ORDER_EMAIL_SHIPPING -->
<!-- BEGIN ORDER_EMAIL_PAYMENT_CHANNEL --><li>$lang[Payment_channel]: $aData[sPaymentChannel]</li><!-- END ORDER_EMAIL_PAYMENT_CHANNEL -->
<!-- BEGIN_ORDER_SHIPMENT_DISCOUNT --><li>$lang[Shipment_discount]: $aOrder[sShipmentDiscount]</li><!-- END ORDER_EMAIL_SHIPMENT_DISCOUNT -->
<!-- BEGIN ORDER_EMAIL_LIST --><tr><td>$aData[sName]</td><td>&#36;&nbsp;$aData[sPrice]</td><td>$aData[iQuantity]</td><td>&#36;&nbsp;$aData[sSummary]</li></td></tr><!-- END ORDER_EMAIL_LIST -->
<!-- BEGIN ORDER_EMAIL_HEAD --><table border="1" cellspacing="0" style="text-align:center" width="100%"><tr><th>Name</th><th>Unit Price</th><th>Qty</th><th>Price</th></tr><!-- END ORDER_EMAIL_HEAD -->
<!-- BEGIN ORDER_EMAIL_FOOT --></table><!-- END ORDER_EMAIL_FOOT -->