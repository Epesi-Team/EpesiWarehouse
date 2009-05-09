<!-- BEGIN CONFIG_MAIN -->
<h1>$lang['Configuration']</h1>
<form action="?p=$p" method="post" id="mainForm" name="form" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <table cellspacing="1" class="mainTable" id="config">
      <thead>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </tfoot>
      <!-- title start -->
      <tr class="l0">
        <th>
          $lang['Page_title']
        </th>
        <td>
          <input type="text" name="title" value="$config[title]" size="70" maxlength="200" class="input" />
        </td>
        <td rowspan="10" class="tabs">$sFormTabs</td>
      </tr>
      <!-- title end -->
      <!-- description start -->
      <tr class="l1">
        <th>
          $lang['Description']
        </th>
        <td>
          <input type="text" name="description" value="$config[description]" size="70" maxlength="200" class="input" />
        </td>
      </tr>
      <!-- description end -->
      <!-- keywords start -->
      <tr class="l0">
        <th>
          $lang['Key_words']
        </th>
        <td>
          <input type="text" name="keywords" value="$config[keywords]" size="70" maxlength="255" class="input"/>
        </td>
      </tr>
      <!-- keywords end -->
      <!-- slogan start -->
      <tr class="l1">
        <th>
          $lang['Slogan']
        </th>
        <td>
          <input type="text" name="slogan" value="$config[slogan]" size="70" maxlength="200" class="input" />
        </td>
      </tr>
      <!-- slogan end -->
      <!-- foot info start -->
      <tr class="l0">
        <th>
          $lang['Foot_info']
        </th>
        <td>
          <input type="text" name="foot_info" value="$config[foot_info]" size="70" maxlength="200" class="input" />
        </td>
      </tr>
      <!-- foot info end -->
      <!-- login start -->
      <tr class="l1" id="login">
        <th>
          $lang['Login']
        </th>
        <td>
          <input type="text" name="login" value="$config[login]" size="40" class="input" alt="simple" />
        </td>
      </tr>
      <!-- login end -->
      <!-- pass start -->
      <tr class="l0" id="pass">
        <th>
          $lang['Password']
        </th>
        <td>
          <input type="text" name="pass" value="$config[pass]" size="40" class="input" alt="simple" />
        </td>
      </tr>
      <!-- pass end -->
      <!-- orders_email start -->
      <tr class="l1" id="orders_email">
        <th>
          $lang['Mail_informing']
        </th>
        <td>
          <input type="text" name="orders_email" value="$config[orders_email]" size="40" class="input" />
        </td>
      </tr>
      <!-- orders_email end -->
      <!-- email start -->
      <tr class="l0">
        <th>
          $lang[Contact_mail]
        </th>
        <td>
          <input type="text" name="email" value="$config[email]" class="input" size="40" alt="email" />
        </td>
      </tr>
      <!-- email end -->
      <tr class="end">
        <td colspan="2">&nbsp;</td>
      </tr>
    </table>
  </fieldset>
</form>
<!-- END CONFIG_MAIN -->

<!-- BEGIN CONFIG_TABS -->
<div id="tabs">
  <ul id="tabsNames">
    <!-- tabs start -->
    <li class="tabOptions"><a href="#more" onclick="displayTab( 'tabOptions' )">$lang['Options']</a></li>
    <li class="tabPages"><a href="#more" onclick="displayTab( 'tabPages' )">$lang['Pages']</a></li>
    <li class="tabPayments"><a href="#more" onclick="displayTab( 'tabPayments' )">$lang['Payments_config']</a></li>
  <!-- tabs end -->
  </ul>
  <div id="tabsForms">
    <!-- tabs list start -->
    <table class="tab" id="tabOptions">
      <tr>
        <td>$lang['Default_language']</td>
        <td>
          <select name="default_lang">
            $sLangSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Styles_template']</td>
        <td>
          <select name="template">
            $sCssSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang[Products_on_page]</td>
        <td>
          <input type="text" name="products_list" value="$config[products_list]" size="3" maxlength="3" alt="int;0" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang[Admin_items_on_page]</td>
        <td>
          <input type="text" name="admin_list" value="$config[admin_list]" size="3" maxlength="3" alt="int;0" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang[News_on_page]</td>
        <td>
          <input type="text" name="news_list" value="$config[news_list]" size="3" maxlength="3" alt="int;0" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang[Currency_symbol]</td>
        <td>
          <input type="text" name="currency_symbol" value="$config[currency_symbol]" size="5" maxlength="5" alt="simple" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang[Delivery_free]</td>
        <td>
          <input type="text" name="delivery_free" value="$config[delivery_free]" size="10" maxlength="10" alt="float" class="inputr" /> $config[currency_symbol]
        </td>
      </tr>
      <tr>
        <td>$lang['Admin_see_hidden_pages']</td>
        <td>
          <select name="hidden_shows">
            $sHiddenShowsSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['WYSWIG_editor']</td>
        <td>
          <select name="wysiwyg">
            $sWysiwygSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Display_all_files']</td>
        <td>
          <select name="display_all_files">
            $sDisplayAllFilesSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Change_files_names']</td>
        <td>
          <select name="change_files_names">
            $sChangeFilesNamesSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Display_text_size_option']</td>
        <td>
          <select name="text_size">
            $sTextSizeSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Products_search_description']</td>
        <td>
          <select name="search_products_description">
            $sProductsDescSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Display_expanded_menu']</td>
        <td>
          <select name="display_expanded_menu">
            $sExpandedMenuSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Inherit_from_parents']</td>
        <td>
          <select name="inherit_from_parents">
            $sInheritFromParentsSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Language_in_url']</td>
        <td>
          <select name="language_in_url">
            $sLanguageInUrl
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Display_subcategory_products']</td>
        <td>
          <select name="display_subcategory_products">
            $sSubcategoryProductsSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang[Newsletter_form]</td>
        <td>
          <select name="newsletter">
            $sNewsletterSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang[Cross_sell]</td>
        <td>
          <select name="cross_sell">
            $sCrossSellSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang[Site_map_page_products]</td>
        <td>
          <select name="site_map_products">
            $sSiteMapProductsSelect
          </select>
        </td>
      </tr>
      $sSkapiecId
      <tr>
        <td>
          $lang[Friendly_links]
        </td>
        <td>
          <select name="friendly_links">
            $sFriendlyLinksSelect
          </select>
        </td>
      </tr>
    <!-- tab options -->
    </table>

    <table class="tab" id="tabPages">
      <tr>
        <td>$lang['Start_page']</td>
        <td>
          <select name="start_page">
            $sStartPageSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Basket_page']</td>
        <td>
          <select name="basket_page">
            <option value="">$lang['none']</option>
            $sBasketPageSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Order_page']</td>
        <td>
          <select name="order_page">
            <option value="">$lang['none']</option>
            $sOrderPageSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang['Rules_page']</td>
        <td>
          <select name="rules_page">
            <option value="">$lang['none']</option>
            $sRulesPageSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang[Page_search]</td>
        <td>
          <select name="page_search">
            <option value="">$lang['none']</option>
            $sPageSearchSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang[Contact_page]</td>
        <td>
          <select name="contact_page">
            <option value="">$lang['none']</option>
            $sContactPageSelect
          </select>
        </td>
      </tr>
      <tr>
        <td>$lang[Site_map_page]</td>
        <td>
          <select name="site_map">
            <option value="">$lang['none']</option>
            $sSiteMapPageSelect
          </select>
        </td>
      </tr>
    <!-- tab pages -->
    </table>

    <table class="tab" id="tabPayments">
      <tr>
        <td>$lang['AllPay_shop_id']</td>
        <td>
          <input type="text" name="allpay_id" value="$config[allpay_id]" size="10" maxlength="10" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang['Przelewy24_shop_id']</td>
        <td>
          <input type="text" name="przelewy24_id" value="$config[przelewy24_id]" size="10" maxlength="10" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang['Platnosci_shop_id']</td>
        <td>
          <input type="text" name="platnosci_id" value="$config[platnosci_id]" size="10" maxlength="10" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang['Platnosci_pos_auth_key']</td>
        <td>
          <input type="text" name="platnosci_pos_auth_key" value="$config[platnosci_pos_auth_key]" size="10" maxlength="10" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang['Platnosci_key1']</td>
        <td>
          <input type="text" name="platnosci_key1" value="$config[platnosci_key1]" size="40" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang['Platnosci_key2']</td>
        <td>
          <input type="text" name="platnosci_key2" value="$config[platnosci_key2]" size="40" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang['Zagiel_shop_id']</td>
        <td>
          <input type="text" name="zagiel_id" value="$config[zagiel_id]" size="10" maxlength="10" class="input" />
        </td>
      </tr>
      <tr>
        <td>$lang['Paypal_email']</td>
        <td>
          <input type="text" name="paypal_email" value="$config[paypal_email]" size="40" class="input" />
        </td>
      </tr>
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
<!-- END CONFIG_TABS -->

<!-- BEGIN SKAPIEC_ID -->
      <tr>
        <td>$lang[Skapiec_shop_id]</td>
        <td>
          <input type="text" name="skapiec_shop_id" value="$config[skapiec_shop_id]" size="5" class="input" />
        </td>
      </tr>
<!-- END SKAPIEC_ID -->