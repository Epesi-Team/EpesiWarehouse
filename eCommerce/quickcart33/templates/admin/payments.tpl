<!-- BEGIN LIST_TITLE -->
<h1>$lang['Payment_methods']</h1>
<!-- END LIST_TITLE -->

<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td class="id">
    $aData[iPayment]
  </td><td class="name">
    <a href="?p=$aActions[f]-form&amp;iPayment=$aData[iPayment]">$aData[sName]</a>
  </td><td class="options">
    <a href="?p=$aActions[f]-form&amp;iPayment=$aData[iPayment]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;iPayment=$aData[iPayment]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->

<!-- BEGIN HEAD -->
<form action="?p=$p" method="post">
  <fieldset>
  <table id="list" class="payments" cellspacing="1">
    <thead>
      <tr>
        <td class="id">$lang['Id']</td>
        <td class="name">$lang['Name']</td>
        <td class="options">&nbsp;</td>
      </tr>
    </thead>
    <tbody>
<!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table></fieldset></form><!-- END FOOT -->

<!-- BEGIN FORM -->
<h1>$lang['Edit_payment_method']</h1>
<form action="?p=$p" method="post" id="mainForm" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <input type="hidden" name="iPayment" value="$aData[iPayment]" />
    <table cellspacing="1" class="mainTable" id="payment">
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
      <tbody>
        <tr class="l0">
          <th>$lang[Name]</th>
          <td><input type="text" name="sName" value="$aData[sName]" class="input" size="50" maxlength="40" alt="simple" /></td>
          <td rowspan="2" class="tabs">$sFormTabs</td>
        </tr>
        <tr class="end">
          <td colspan="2">&nbsp;</td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<!-- END FORM -->

<!-- BEGIN FORM_TABS -->
<div id="tabs">
  <ul id="tabsNames">
    <!-- tabs start -->
    <li class="tabPayments"><a href="#more" onclick="displayTab( 'tabPayments' )">$lang['Carriers']</a></li>
    <!-- tabs end -->
  </ul>
  <div id="tabsForms">
    <!-- tabs list start -->
    <table class="tab" id="tabPayments">
      $sCarriersList
      <!-- tab carriers -->
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

<!-- BEGIN CARRIER_LIST -->
<tr>
  <td><input type="checkbox" name="aCarriers[$aData[iCarrier]]" $aData[sChecked] value="1" onclick="changeInputStatus( this, 'oCarrier$aData[iCarrier]' )" /></td>
  <td><b>$aData[sName]</b> ($aData[fPrice])</td>
  <td>
    <input type="text" name="aCarriersPrices[$aData[iCarrier]]" value="$aData[sPrice]" class="inputr$aData[sDisable]" size="10" id="oCarrier$aData[iCarrier]" />
    $lang[example] 10, -10, 10%
  </td>
</tr>
<!-- END CARRIER_LIST -->
<!-- BEGIN CARRIER_HEAD --><thead>
  <tr>
    <td></td>
    <td>$lang[Name]</td>
    <td>$lang[Price]</td>
  </tr>
</thead><tbody><!-- END CARRIER_HEAD -->
<!-- BEGIN CARRIER_FOOT --></tbody><!-- END CARRIER_FOOT -->