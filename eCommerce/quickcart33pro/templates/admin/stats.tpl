<!-- BEGIN PRODUCTS_LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_stats.gif" alt="$lang[Products_stats]" />$lang[Products_stats] $sProductName</h1>
<!-- END PRODUCTS_LIST_TITLE -->
<!-- BEGIN PAGES_LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_stats.gif" alt="$lang[Pages_stats]" />$lang[Pages_stats] $sPageName</h1>
<!-- END PAGES_LIST_TITLE -->
<!-- BEGIN ORDERS_PRODUCTS_LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_stats.gif" alt="$lang[Orders_products]" />$lang[Orders_products]</h1>
<!-- END ORDERS_PRODUCTS_LIST_TITLE -->
<!-- BEGIN CUSTOMERS_LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_stats.gif" alt="$lang[Orders_customers]" />$lang[Orders_customers]</h1>
<!-- END CUSTOMERS_LIST_TITLE -->

<!-- BEGIN ORDERS_PRODUCTS_LIST -->
<tr class="l$aData[iStyle]">
  <td>
    <a href="?p=products-form&amp;iProduct=$aData[iProduct]">$aData[sName]</a>
  </td>
  <td>
    $aData[iQuantity]
  </td>
  <td>
    $aData[sSummary]
  </td>
</tr>
<!-- END ORDERS_PRODUCTS_LIST -->
<!-- BEGIN ORDERS_PRODUCTS_HEAD --><table id="list" class="stats" cellspacing="1">
  <thead>
    <tr class="save">
      <th colspan="3">
        <form action="" method="get">
          <fieldset>
            <input type="hidden" name="p" value="$p" />
            $lang['Date_from'] <input type="text" name="sDateFrom" value="$sDateFrom" class="input" maxlength="10" />
            $lang['Date_to'] <input type="text" name="sDateTo" value="$sDateTo" class="input" maxlength="10" />
            &nbsp;&nbsp;
            $lang[Status_from] <select name="iStatus1">$sStatusSelect1</select>
            $lang[Status_to] <select name="iStatus2">$sStatusSelect2</select>
            <input type="submit" value="$lang['search'] &raquo;" class="submit" />
          </fieldset>
        </form>
      </th>
    </tr>
    <tr>
      <td class="name">$lang['Name']</td>
      <td class="quantity">$lang['Quantity']</td>
      <td class="summary">$lang['Summary']</td>
    </tr>
  </thead>
  <tbody><!-- END ORDERS_PRODUCTS_HEAD -->
<!-- BEGIN ORDERS_PRODUCTS_FOOT --></tbody></table><!-- END ORDERS_PRODUCTS_FOOT -->

<!-- BEGIN CUSTOMERS_LIST -->
<tr class="l$aData[iStyle]">
  <td>
    $aData[sName]
  </td>
  <td>
    $aData[iQuantity]
  </td>
  <td>
    $aData[sSummary]
  </td>
</tr>
<!-- END CUSTOMERS_LIST -->
<!-- BEGIN CUSTOMERS_HEAD --><table id="list" class="stats" cellspacing="1">
  <thead>
    <tr class="save">
      <th colspan="3">
        <form action="" method="get">
          <fieldset>
            <input type="hidden" name="p" value="$p" />
            $lang['Date_from'] <input type="text" name="sDateFrom" value="$sDateFrom" class="input" maxlength="10" />
            $lang['Date_to'] <input type="text" name="sDateTo" value="$sDateTo" class="input" maxlength="10" />
            &nbsp;&nbsp;
            $lang[Status_from] <select name="iStatus1">$sStatusSelect1</select>
            $lang[Status_to] <select name="iStatus2">$sStatusSelect2</select>
            <input type="submit" value="$lang['search'] &raquo;" class="submit" />
          </fieldset>
        </form>
      </th>
    </tr>
    <tr>
      <td class="name">$lang['Name']</td>
      <td class="quantity">$lang['Quantity']</td>
      <td class="summary">$lang['Summary']</td>
    </tr>
  </thead>
  <tbody><!-- END CUSTOMERS_HEAD -->
<!-- BEGIN CUSTOMERS_FOOT --></tbody></table><!-- END CUSTOMERS_FOOT -->

<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td>
    <a href="?p=$p&amp;$aData[sLink]=$aData[iPage]$aData[iProduct]">$aData[sName]</a>
  </td>
  <td>
    $aData[iVisits]
  </td>
</tr>
<!-- END LIST -->
<!-- BEGIN HEAD --><table id="list" class="stats" cellspacing="1">
  <thead>
    <tr class="save">
      <td>
        <a href="?p=$p&amp;sOption=delete" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']" style="vertical-align:middle;padding:0 5px 0;" /> $lang['clear_stats']</a>
      </td>
      <th>
        <form action="" method="get">
          <fieldset>
            <input type="hidden" name="p" value="$p" />
            $lang['Date_from'] <input type="text" name="sDateFrom" value="$sDateFrom" class="input" maxlength="10" />
            $lang['Date_to'] <input type="text" name="sDateTo" value="$sDateTo" class="input" maxlength="10" />
            <input type="submit" value="$lang['search'] &raquo;" class="submit" />
          </fieldset>
        </form>
      </th>
    </tr>
    <tr>
      <td>$lang['Name']</td>
      <td class="name">$lang['Visits']</td>
    </tr>
  </thead>
  <tbody><!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table><!-- END FOOT -->

<!-- BEGIN DETAILS_LIST -->
<tr class="l$aData[iStyle]">
  <td>
    $aData[sDate]
  </td>
  <td>
    $aData[iVisits]
  </td>
</tr>
<!-- END DETAILS_LIST -->
<!-- BEGIN DETAILS_HEAD --><table id="list" cellspacing="1">
  <thead>
    <tr>
      <td class="id">$lang['Date']</td>
      <td class="name">$lang['Visits']</td>
    </tr>
  </thead>
  <tbody><!-- END DETAILS_HEAD -->
<!-- BEGIN DETAILS_FOOT --></tbody></table><!-- END DETAILS_FOOT -->

<!-- BEGIN PHRASES_LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_stats.gif" alt="$lang[Products_phrases]" />$lang[Products_phrases]</h1>
<!-- END PHRASES_LIST_TITLE -->

<!-- BEGIN PHRASES_LIST -->
<tr class="l$aData[iStyle]">
  <td>
    $aData[sPhrase]
  </td>
  <td>
    $aData[iSearched]
  </td>
</tr>
<!-- END PHRASES_LIST -->
<!-- BEGIN PHRASES_HEAD --><table id="list" class="stats" cellspacing="1">
  <thead>
    <tr class="save">
      <td>
        <a href="?p=$p&amp;sOption=delete" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']" style="vertical-align:middle;padding:0 5px 0;" /> $lang['clear_stats']</a>
      </td>
      <th>
        <form action="" method="get">
          <fieldset>
            <input type="hidden" name="p" value="$p" />
            $lang['Date_from'] <input type="text" name="sDateFrom" value="$sDateFrom" class="input" maxlength="10" />
            $lang['Date_to'] <input type="text" name="sDateTo" value="$sDateTo" class="input" maxlength="10" />
            <input type="submit" value="$lang['search'] &raquo;" class="submit" />
          </fieldset>
        </form>
      </th>
    </tr>
    <tr>
      <td>$lang['Key_words']</td>
      <td class="name">$lang['Count']</td>
    </tr>
  </thead>
  <tbody><!-- END PHRASES_HEAD -->
<!-- BEGIN PHRASES_FOOT --></tbody></table><!-- END PHRASES_FOOT -->

