<!-- BEGIN ORDER_HEADER -->
$lang[Click_on_order_to_see_details]
<!-- END ORDER_HEADER -->
<!-- BEGIN ORDER_FOOTER -->
  <script type="text/javascript">
  <!--
  var myDivs = document.getElementsByClassName('order_details');
  var myLinks = document.getElementsByClassName('order');
  var myAccordion = new fx.Accordion(myLinks, myDivs, {opacity:true, height:true, width:false});
  //-->
  </script>
<!-- END ORDER_FOOTER -->

<!-- BEGIN ORDER_PRINT -->
<div id="orderPrint">
  <dl class="order" style="clear:both">
    <dt class="orderId">ID:</dt><dd class="orderId">$aOrder[iOrder]</dd>
    <dt class="orderDate">$lang[Date]:</dt><dd class="orderDate">$aOrder[sDate]</dd>
    <dt class="orderDate">$lang[Status]:</dt><dd class="orderDate">$aOrder[sStatus]</dd>
    <dt class="orderDate">$lang[Shipment_no]:</dt><dd class="orderDate">$aOrder[sShipmentNo]</dd>
    <dt class="orderDate">$lang[Tracking_info]:</dt><dd class="orderDate">$aOrder[sTrackingInfo]</dd>
  </dl>
  <div class="order_details" style="clear:both">
  <dl>
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
    <dt class="orderIP">IP:</dt><dd class="orderIP">$aOrder[sIp]</dd>
    <dt class="paymentChannel">$lang[Payment_channel]:</dt><dd class="paymentChannel">$aOrder[sPaymentChannel]</dd>
    <dt class="orderComment">$lang[Order_comment]:</dt><dd class="orderComment">$aOrder[sComment]</dd>
  </dl>
  <h4>$lang[Ordered_products]</h4>
  $sOrderProducts
  <br />
  <br />
  <br />
  <br />
  </div>
</div>
<!-- END ORDER_PRINT -->

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
        <th colspan="3">$lang[Delivery_and_payment]: $aOrder[sCarrierName], $aOrder[sPaymentName]</th>
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

