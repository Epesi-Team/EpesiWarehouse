<!-- BEGIN LIST_TITLE -->
<h1>$lang['Carriers']</h1>
<!-- END LIST_TITLE -->

<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td class="id">
    $aData[iCarrier]
  </td><td class="name">
    <a href="?p=$aActions[f]-form&amp;iCarrier=$aData[iCarrier]">$aData[sName]</a>
  </td><td class="price">
    $aData[sPrice]
  </td><td class="options">
    <a href="?p=$aActions[f]-form&amp;iCarrier=$aData[iCarrier]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;iCarrier=$aData[iCarrier]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->

<!-- BEGIN HEAD -->
<form action="?p=$p" method="post">
  <fieldset>
  <table id="list" class="carriers" cellspacing="1">
    <thead>
      <tr>
        <td class="id">$lang['Id']</td>
        <td class="name">$lang['Name']</td>
        <td class="price">$lang['Price'] [$config[currency_symbol]]</td>
        <td class="options">&nbsp;</td>
      </tr>
    </thead>
    <tbody>
<!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table></fieldset></form><!-- END FOOT -->

<!-- BEGIN FORM_MAIN -->
<h1>$lang[Edit_carrier]</h1>
<form action="?p=$p&amp;iCarrier=$aData[iCarrier]" method="post" id="mainForm" name="form" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <input type="hidden" name="iCarrier" value="$aData[iCarrier]" />
    <table cellspacing="1" class="mainTable" id="carrier">
      <thead>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="$lang['save_list'] &raquo;" name="sOptionList" />
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="$lang['save_list'] &raquo;" name="sOptionList" />
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </tfoot>
      <!-- name start -->
      <tr class="l0">
        <th>
          $lang['Name']
        </th>
        <td>
          <input type="text" name="sName" value="$aData[sName]" size="30" maxlength="30" class="input" alt="simple" />
        </td>
        <td rowspan="3" class="tabs">$sFormTabs</td>
      </tr>
      <!-- name end -->
      <!-- price start -->
      <tr class="l1">
        <th>
          $lang[Price]
        </th>
        <td>
          <input type="text" name="fPrice" value="$aData[fPrice]" class="inputr" size="10" alt="float" /> $config[currency_symbol]
        </td>
      </tr>
      <!-- price end -->
      <tr class="end">
        <td colspan="2">&nbsp;</td>
      </tr>
    </table>
  </fieldset>
</form>
<!-- END FORM_MAIN -->

<!-- BEGIN FORM_TABS -->
<div id="tabs">
  <ul id="tabsNames">
    <!-- tabs start -->
    <li class="tabPayments"><a href="#more" onclick="displayTab( 'tabPayments' )">$lang['Payment_methods']</a></li>
    <!-- tabs end -->
  </ul>
  <div id="tabsForms">
    <!-- tabs list start -->
    <table class="tab" id="tabPayments">
      $sPaymentList
      <!-- tab payments -->
    </table>

    <!-- tabs list end -->
  </div>
</div>

<script type="text/javascript">
<!--
AddOnload( getTabsArray );
AddOnload( checkSelectedTab );
//-->
</script>
<!-- END FORM_TABS -->

<!-- BEGIN PAYMENT_LIST -->
<tr>
  <td><input type="checkbox" name="aPayments[$aData[iPayment]]" $aData[sChecked] value="1" onclick="changeInputStatus( this, 'oPayment$aData[iPayment]' )" /></td>
  <td><b>$aData[sName]</b></td>
  <td>
    <input type="text" name="aPaymentsPrices[$aData[iPayment]]" value="$aData[sPrice]" class="inputr$aData[sDisable]" size="10" id="oPayment$aData[iPayment]" />
    $lang[example] 10, -10, 10%
  </td>
</tr>
<!-- END PAYMENT_LIST -->
<!-- BEGIN PAYMENT_HEAD --><thead>
  <tr>
    <td></td>
    <td>$lang[Name]</td>
    <td>$lang[Price]</td>
  </tr>
</thead><tbody><!-- END PAYMENT_HEAD -->
<!-- BEGIN PAYMENT_FOOT --></tbody><!-- END PAYMENT_FOOT -->