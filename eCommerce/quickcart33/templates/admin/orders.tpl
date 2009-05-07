<!-- BEGIN LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_orders.gif" alt="$lang['Orders']" />$lang['Orders']</h1>
<form action="" method="get" id="search">
  <fieldset>
    <input type="hidden" name="p" value="$p" />
    <input type="text" name="sPhrase" value="$sPhrase" class="input" size="50" />
    <select name="iStatus">
      <option value="">$lang[All_status]</option>
      $sStatusSelect
    </select>
    &nbsp;&nbsp;
    $lang[Orders_search_products]
    $sProductsBox
    <input type="submit" value="$lang['search'] &raquo;" />
  </fieldset>
</form>
<!-- END LIST_TITLE -->

<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td class="id">
    $aData[iOrder]
  </td><td class="name">
    <a href="?p=$aActions[f]-form&amp;iOrder=$aData[iOrder]">$aData[sFirstName] $aData[sLastName]</a>
  </td><td class="email">
    <a href="mailto:$aData[sEmail]">$aData[sEmail]</a>
  </td><td class="phone">
    $aData[sPhone]
  </td><td class="company">
    $aData[sCompanyName]&nbsp;
  </td><td class="date">
    $aData[sDate]
  </td><td class="status">
    <input type="checkbox" name="aStatus[$aData[iOrder]]" value="1" class="checkbox" />
    <a href="?p=$aActions[f]-form&amp;iOrder=$aData[iOrder]">$aData[sStatus]</a>
  </td><td class="options">
    <a href="?p=$aActions[f]-form&amp;iOrder=$aData[iOrder]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;iOrder=$aData[iOrder]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->

<!-- BEGIN HEAD -->
<form action="?p=$p" method="post">
  <fieldset>
  <table id="list" class="orders" cellspacing="1">
    <thead>
      <tr class="save">
        <td colspan="5">
          $lang[Pages]: $aData[sPages]
        </td>
        <th colspan="3">
          <select name="iStatus">
            <option>$lang[Change_status]</option>
            $sStatusSelect
          </select>
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
      <tr>
        <td class="id">$lang['Id']</td>
        <td class="name">$lang['First_and_last_name']</td>
        <td class="email">$lang[Email]</td>
        <td class="phone">$lang[Telephone]</td>
        <td class="company">$lang[Company]</td>
        <td class="date">$lang['Date']</td>
        <td class="status">$lang['Status']</td>
        <td class="options">&nbsp;</td>
      </tr>
    </thead>
    <tfoot>
      <tr class="save">
        <td colspan="5">
          $lang[Pages]: $aData[sPages]
        </td>
        <th colspan="3">
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
    </tfoot>
    <tbody>
<!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table></fieldset></form><!-- END FOOT -->

<!-- BEGIN FORM_MAIN -->
<h1><img src="$config[dir_templates]admin/img/ico_orders.gif" alt="$lang['Orders_form']" />$lang[Orders_form] - $aData[iOrder]</h1>
<form action="?p=$p&amp;iOrder=$aData[iOrder]" method="post" id="mainForm" name="form" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <input type="hidden" name="iOrder" value="$aData[iOrder]" />
    <table cellspacing="1" class="mainTable" id="order">
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
          $lang['First_and_last_name']
        </th>
        <td>
          <input type="text" name="sFirstName" value="$aData[sFirstName]" size="24" maxlength="30" class="input" alt="simple" />
          <input type="text" name="sLastName" value="$aData[sLastName]" size="24" maxlength="40" class="input" alt="simple" />
        </td>
        <td rowspan="13" class="tabs">$sFormTabs</td>
      </tr>
      <!-- name end -->
      <!-- company start -->
      <tr class="l1">
        <th>
          $lang[Company]
        </th>
        <td>
          <input type="text" name="sCompanyName" value="$aData[sCompanyName]" size="55" maxlength="100" class="input" />
        </td>
      </tr>
      <!-- company end -->
      <!-- street start -->
      <tr class="l0">
        <th>
          $lang[Street]
        </th>
        <td>
          <input type="text" name="sStreet" value="$aData[sStreet]" size="50" maxlength="40" class="input" alt="simple" />
        </td>
      </tr>
      <!-- street end -->
      <!-- zip_code start -->
      <tr class="l1">
        <th>
          $lang[Zip_code]
        </th>
        <td>
          <input type="text" name="sZipCode" value="$aData[sZipCode]" size="50" maxlength="20" class="input" alt="simple" />
        </td>
      </tr>
      <!-- zip_code end -->
      <!-- city start -->
      <tr class="l0">
        <th>
          $lang[City]
        </th>
        <td>
          <input type="text" name="sCity" value="$aData[sCity]" size="50" maxlength="40" class="input" alt="simple" />
        </td>
      </tr>
      <!-- city end -->
      <!-- telephone start -->
      <tr class="l1">
        <th>
          $lang[Telephone]
        </th>
        <td>
          <input type="text" name="sPhone" value="$aData[sPhone]" size="50" maxlength="40" class="input" alt="simple" />
        </td>
      </tr>
      <!-- telephone end -->
      <!-- email start -->
      <tr class="l0">
        <th>
          $lang[Email]
        </th>
        <td>
          <input type="text" name="sEmail" value="$aData[sEmail]" size="50" maxlength="40" class="input" alt="email" />
        </td>
      </tr>
      <!-- email end -->
      <!-- date start -->
      <tr class="l1">
        <th>
          $lang[Date]
        </th>
        <td>
          $aData[sDate]
        </td>
      </tr>
      <!-- date end -->
      <!-- lang start -->
      <tr class="l0">
        <th>
          $lang[Language]
        </th>
        <td>
          $aData[sLanguage]
        </td>
      </tr>
      <!-- lang end -->
      <!-- ip start -->
      <tr class="l1">
        <th>
          IP
        </th>
        <td>
          <a href="http://www.ripe.net/perl/whois?form_type=simple&amp;full_query_string=&amp;searchtext=$aData[sIp]&amp;do_search=Search" target="_blank">$aData[sIp]</a>
        </td>
      </tr>
      <!-- ip end -->
      <!-- status start -->
      <tr class="l0">
        <th>
          $lang[Status]
        </th>
        <td>
          <select name="iStatus">
            $sStatusSelect
          </select>
          $sStatusList
        </td>
      </tr>
      <!-- status end -->
      <!-- comment start -->
      <tr class="l1">
        <th>
          $lang[Order_comment]
        </th>
        <td>
          <textarea name="sComment" cols="50" rows="7">$aData[sComment]</textarea>
        </td>
      </tr>
      <!-- comment end -->
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
    <li class="tabProductsPayment"><a href="#more" onclick="displayTab( 'tabProductsPayment' )">$lang['Products_and_payment']</a></li>
    <!-- tabs end -->
  </ul>
  <div id="tabsForms">
    <script type="text/javascript">
    <!--
    function showAddProductForm( ){
      gEBI( "addProductForm" ).style.display = "";
      gEBI( "addProductLink" ).style.display = "none";
      gEBI( "newProductId" ).focus( );
    } // end function showAddProductForm
    //-->
    </script>
    <!-- tabs list start -->
    <table class="tab" id="tabProductsPayment">
      <thead>
        <tr>
          <td class="id">$lang['Id']</td>
          <td class="name">$lang['Name']</td>
          <td class="price">$lang[Price] [$config[currency_symbol]]</td>
          <td class="quantity">$lang[Quantity]</td>
          <td class="summary">$lang[Summary] [$config[currency_symbol]]</td>
          <td class="options">$lang[Delete]</td>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="4" class="info">
            $lang[Summary]
          </td>
          <td colspan="2" class="summary">
            $sOrderSummary
          </td>
        </tr>
      </tfoot>
      <tbody>
        $sProductsList
        <tr id="addProductLink">
          <td>&nbsp;</td>
          <td colspan="5"><a href="#" onclick="showAddProductForm( )"><img src="$config[dir_templates]admin/img/ico_add_small.gif" alt="" /></a> <a href="#" onclick="showAddProductForm( )">$lang[order_add_product]</a></td>
        </tr>
        <tr id="addProductForm" style="display:none;">
          <td class="id"><input type="text" name="aNewProduct[iProduct]" value="" size="2" class="input" id="newProductId" /></td>
          <td class="name"><input type="text" name="aNewProduct[sName]" value="" class="input" size="40" /></td>
          <td class="price"><input type="text" name="aNewProduct[fPrice]" value="" class="inputr" size="7" maxlength="15" /></td>
          <td class="quantity"><input type="text" name="aNewProduct[iQuantity]" value="" class="input" size="2" maxlength="3" /></td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td class="id"><input type="text" name="iCarrier" value="$aData[iCarrier]" size="2" class="input" alt="int;0" /></td>
          <td class="name"><input type="text" name="sCarrierName" value="$aData[sCarrierName]" class="input" size="40" alt="simple" /></td>
          <td class="price"><input type="text" name="fCarrierPrice" value="$aData[fCarrierPrice]" class="inputr" size="7" maxlength="15" alt="float" /></td>
          <td colspan="3"></td>
        </tr>
        <tr>
          <td class="id"><input type="text" name="iPayment" value="$aData[iPayment]" size="2" class="input" alt="int;0" /></td>
          <td class="name"><input type="text" name="sPaymentName" value="$aData[sPaymentName]" class="input" size="40" alt="simple" /></td>
          <td class="price"><input type="text" name="sPaymentPrice" value="$aData[sPaymentPrice]" class="inputr" size="7" maxlength="15" /></td>
          <td colspan="3"></td>
        </tr>
      </tbody>
      <!-- tab products_payment -->
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

<!-- BEGIN STATUS_LIST -->
<li><span>$aData[sDate]</span> - <strong>$aData[sStatus]</strong></li>
<!-- END STATUS_LIST -->
<!-- BEGIN STATUS_HEAD -->
<ul id="status">
<!-- END STATUS_HEAD -->
<!-- BEGIN STATUS_FOOT -->
</ul>
<!-- END STATUS_FOOT -->

<!-- BEGIN PRODUCTS_LIST -->
<tr>
  <td class="id"><a href="?p=products-form&amp;iProduct=$aData[iProduct]" target="_blank">$aData[iProduct]</a></td>
  <td class="name"><input type="text" name="aProducts[$aData[iElement]][sName]" value="$aData[sName]" class="input" size="40" alt="simple" /></td>
  <td class="price"><input type="text" name="aProducts[$aData[iElement]][fPrice]" value="$aData[fPrice]" class="inputr" size="7" maxlength="15" alt="float" /></td>
  <td class="quantity"><input type="text" name="aProducts[$aData[iElement]][iQuantity]" value="$aData[iQuantity]" class="input" size="2" maxlength="3" alt="int" /></td>
  <td class="summary">$aData[sSummary]</td>
  <td class="options"><input type="checkbox" name="aProductsDelete[$aData[iElement]]" value="1" /></td>
</tr>
<!-- END PRODUCTS_LIST -->
<!-- BEGIN PRODUCTS_HEAD -->
<!-- END PRODUCTS_HEAD -->
<!-- BEGIN PRODUCTS_FOOT -->
<!-- END PRODUCTS_FOOT -->