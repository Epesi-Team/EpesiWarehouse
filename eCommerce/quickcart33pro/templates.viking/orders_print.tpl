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
            <td height="80" width="100%"><font size="4" face="Arial,sans-serif" color="#4c4c4c"><center><strong>$config['title']</strong></center></font></td>
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
            <td width="300" valign="top">
                <table border="0" cellpadding="0" cellspacing="0" width="300" height="20">
                    <tbody>
                        <tr>
                            <td height="30" width="20"  bgcolor="#993333"></td>
                            <td height="30" width="250" bgcolor="#993333" valign="middle"><font size="2" face="Arial,sans-serif" color="white"><strong>$lang['Contact_us']</strong></font></td>
                            <td height="30" width="20"  bgcolor="#993333"></td>
                            <td height="30" width="10"><img src="templates/img/shadow-corner-3.gif"></td>
                        </tr>
                    </tbody>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" width="300">
                    <tbody>
                        <tr>
                            <td width="20"  bgcolor="#f2f2f2"></td>
                            <td width="250" bgcolor="#f2f2f2"><font size="2" face="Arial,sans-serif" color="black"><br>$aData[contactus]<br><br><br><br><br><br><br><br></font></td>
                            <td width="20"  bgcolor="#f2f2f2"></td>
                            <td width="10" background="templates/img/shadow-left.gif"></td>
                        </tr>
                    </tbody>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" width="300">
                    <tbody>
                        <tr>
                            <td width="340" height="10" background="templates/img/shadow-top.gif"></td>
                            <td width="10" height="10"><img src="templates/img/shadow-corner-2.gif"></td>
                        </tr>
                    </tbody>
                </table>
            </td>
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

                                    <h4>$lang['Payment_description']</h4>
                                    $aData['sPaymentDescription']
                                    <br>

                                    <h4>$lang[Your_personal_information]</h4>
                                    $lang[First_and_last_name]: <i>$aData[sFirstName] $aData[sLastName]</i><br>
                                    $aData[sCompanyInfo]
				    $lang[Invoice]: <i>$aData[sInvoice]</i><br>
                                    $lang[Street]: <i>$aData[sStreet]</i><br>
                                    $lang[Zip_code]: <i>$aData[sZipCode]</i><br>
                                    $lang[City]: <i>$aData[sCity]</i><br>
                                    $lang[Country]: <i>$aData[sCountry]</i><br>
                                    $lang[Telephone]: <i>$aData[sPhone]</i><br>
                                    $lang[Email]: <i>$aData[sEmail]</i><br>
                                    <br>

                                    <h4>$lang[Order_summary]</h4>
                                    <ul>
                                    $aData[sProducts]
                                    <li>$aData[sCarrierName] ($aData[sPaymentName]) = $aData[sPaymentCarrierPrice] $config[currency_symbol]</li>
                                    $aData[sPaymentChannelInfo]
                                    $aData[sShipmentDiscountInfo]
				    
                                    </ul>
                                    $lang[Summary_cost]: $aData[sOrderSummary] $config[currency_symbol]
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
<!-- BEGIN ORDER_EMAIL_COMPANY -->$lang[Company]: <i>$aData[sCompanyName]</i><br>$lang[Nip]: <i>$aData[sNip]</i><br><!-- END ORDER_EMAIL_COMPANY -->
<!-- BEGIN ORDER_EMAIL_PAYMENT_CHANNEL --><li>$lang[Payment_channel]: $aData[sPaymentChannel]</li><!-- END ORDER_EMAIL_PAYMENT_CHANNEL -->
<!-- BEGIN_ORDER_SHIPMENT_DISCOUNT --><li>$lang[Shipment_discount]: $aOrder[sShipmentDiscount]</li><!-- END ORDER_EMAIL_SHIPMENT_DISCOUNT -->
<!-- BEGIN ORDER_EMAIL_LIST --><li>$aData[sName] - $aData[sPrice] $config[currency_symbol] * $aData[iQuantity] = $aData[sSummary] $config[currency_symbol]</li><!-- END ORDER_EMAIL_LIST -->
<!-- BEGIN ORDER_EMAIL_HEAD --><!-- END ORDER_EMAIL_HEAD -->
<!-- BEGIN ORDER_EMAIL_FOOT --><!-- END ORDER_EMAIL_FOOT -->