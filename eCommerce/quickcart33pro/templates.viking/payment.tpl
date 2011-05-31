<!-- BEGIN PAYMENT_FORM_1 -->
<form action="https://ssl.dotpay.eu/" method="post" id="formPayment">
  <fieldset>
    <input type="hidden" name="id" value="$config[allpay_id]" />
    <input type="hidden" name="lang" value="$config[language]" />
    <input type="hidden" name="potw" value="0" />
    <input type="hidden" name="email_potw" value="" />
    <input type="hidden" name="as" value="yes" />
    <input type="hidden" name="kwota" value="$aData[fOrderSummary]" />
    <input type="hidden" name="waluta" value="$config[currency_symbol]" />
    <input type="hidden" name="opis" value="ID $iOrder" />
    <input type="hidden" name="kanal" value="$aOrder[mPaymentChannel]" />
    <input type="hidden" name="URL" value="$aUrl[scheme]://$aUrl[host]$aUrl[path]?,return,payment" />
    <input type="hidden" name="type" value="3" />

    <input type="hidden" name="forename" value="$aOrder[sFirstName]" />
    <input type="hidden" name="surname" value="$aOrder[sLastName]" />
    <input type="hidden" name="street" value="$aStreet[sStreetName]" />
    <input type="hidden" name="street_n1" value="$aStreet[sStreetNumber1]" />
    <input type="hidden" name="street_n2" value="$aStreet[sStreetNumber2]" />
    <input type="hidden" name="city" value="$aOrder[sCity]" />
    <input type="hidden" name="postcode" value="$aOrder[sZipCode]" />
    <input type="hidden" name="country" value="$aOrder[sCountry]" />
    <input type="hidden" name="email" value="$aOrder[sEmail]" />
    <input type="hidden" name="phone" value="$aOrder[sPhone]" />
  </fieldset>
</form>
<!-- END PAYMENT_FORM_1 -->

<!-- BEGIN PAYMENT_FORM_2 -->
<form action="https://secure.przelewy24.pl/index.php" method="post" id="formPayment">
  <fieldset>
    <input type="hidden" name="p24_session_id" value="$iOrder" />
    <input type="hidden" name="p24_id_sprzedawcy" value="$config[przelewy24_id]" />
    <input type="hidden" name="p24_language" value="$config[language]" />
    <input type="hidden" name="p24_kwota" value="$iAmount" />
    <input type="hidden" name="p24_opis" value="ID $iOrder" />
    <input type="hidden" name="p24_return_url_ok" value="$aUrl[scheme]://$aUrl[host]$aUrl[path]?,return,payment&amp;status=OK" />
    <input type="hidden" name="p24_return_url_error" value="$aUrl[scheme]://$aUrl[host]$aUrl[path]?,return,payment&amp;status=FAIL" />

    <input type="hidden" name="p24_klient" value="$aOrder[sFirstName] $aOrder[sLastName]" />
    <input type="hidden" name="p24_adres" value="$aOrder[sStreet]" />
    <input type="hidden" name="p24_miasto" value="$aOrder[sCity]" />
    <input type="hidden" name="p24_kod" value="$aOrder[sZipCode]" />
    <input type="hidden" name="p24_kraj" value="PL" />
    <input type="hidden" name="p24_email" value="$aOrder[sEmail]" />
  </fieldset>
</form>
<!-- END PAYMENT_FORM_2 -->

<!-- BEGIN PAYMENT_FORM_3 -->
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id='formPayment'>
  <fieldset>
    <input type="hidden" name="cmd" value="_cart" />
    <input type="hidden" name="upload" value="1" />
    <input type="hidden" name="tx" value="$iOrder" />
    <input type="hidden" name="business" value="$config[paypal_email]" />
    <input type="hidden" name="at" value="" />
    <input type="hidden" name="item_name_1" value="ID $iOrder" />
    <input type="hidden" name="amount_1" value="$aData[sOrderSummary]" />
    <input type="hidden" name="currency_code" value="$config[currency_symbol]" />
    <input type="hidden" name="return" value="$aUrl[scheme]://$aUrl[host]$aUrl[path]?,paypal,payment" />
    <input type="hidden" name="rm" value="2" />
  </fieldset>
</form>
<!-- END PAYMENT_FORM_3 -->

<!-- BEGIN PAYMENT_FORM_4 -->
<form action="https://www.platnosci.pl/paygw/ISO/NewPayment" method="post" id="formPayment">
  <fieldset>
    <input type="hidden" name="pos_id" value="$config[platnosci_id]" />
    <input type="hidden" name="pos_auth_key" value="$config[platnosci_pos_auth_key]" />
    <input type="hidden" name="pay_type" value="$aOrder[mPaymentChannel]" />
    <input type="hidden" name="session_id" value="$iOrder" />
    <input type="hidden" name="amount" value="$iAmount" />
    <input type="hidden" name="desc" value="ID $iOrder" />
    <input type="hidden" name="order_id" value="$iOrder" />

    <input type="hidden" name="first_name" value="$aOrder[sFirstName]" />
    <input type="hidden" name="last_name" value="$aOrder[sLastName]" />
    <input type="hidden" name="street" value="$aStreet[sStreetName]" />
    <input type="hidden" name="street_nh" value="$aStreet[sStreetNumber1]" />
    <input type="hidden" name="street_an" value="$aStreet[sStreetNumber2]" />
    <input type="hidden" name="city" value="$aOrder[sCity]" />
    <input type="hidden" name="post_code" value="$aOrder[sZipCode]" />
    <input type="hidden" name="country" value="$aOrder[sCountry]" />
    <input type="hidden" name="email" value="$aOrder[sEmail]" />
    <input type="hidden" name="phone" value="$aOrder[sPhone]" />
    <input type="hidden" name="language" value="PL" />
    <input type="hidden" name="client_ip" value="$_SERVER[REMOTE_ADDR]" />
    <input type="hidden" name="js" id="oJsEnabled" value="0" />
  </fieldset>
</form>
<script type="text/javascript">
<!--
  gEBI( 'oJsEnabled' ).value = 1;
//-->
</script>
<!-- END PAYMENT_FORM_4 -->

<!-- BEGIN PAYMENT_FORM_5 -->
<form action="http://www.zagiel.com.pl/kalkulator/index_smart.php" method="post" id="formPayment">
  <fieldset>
    $sProductsZagielList
    <input type="hidden" name="action" value="getklientdet_si" />
    <input type="hidden" name="IDZamowienieSklep" value="$iOrder" />
    <input type="hidden" name="ImieSklep" value="$aOrder[sFirstName]" />
    <input type="hidden" name="NazwiskoSklep" value="$aOrder[sLastName]" />
    <input type="hidden" name="EmailSklep" value="$aOrder[sEmail]" />
    <input type="hidden" name="TelKontaktSklep" value="$aOrder[sPhone]" />
    <input type="hidden" name="UlicaSklep" value="$aOrder[sStreetName]" />
    <input type="hidden" name="NrDomuSklep" value="$aStreet[sStreetNumber1]" />
    <input type="hidden" name="NrMieszkaniaSklep" value="$aStreet[sStreetNumber2]" />
    <input type="hidden" name="MiejscowoscSklep" value="$aOrder[sCity]" />
    <input type="hidden" name="KodPocztowySklep" value="$aOrder[sZipCode]" />
    <input type="hidden" name="wniosekZapisany" value="$aUrl[scheme]://$aUrl[host]$aUrl[path]?,return,payment&amp;status=OK" />
    <input type="hidden" name="wniosekAnulowany" value="$aUrl[scheme]://$aUrl[host]$aUrl[path]?,return,payment&amp;status=FAIL" />
    <input type="hidden" name="shopNo" value="$config[zagiel_id]" />
    <input type="hidden" name="shopName" value="$aUrl[host]$aUrl[path]" />
    <input type="hidden" name="shopHttp" value="$aUrl[scheme]://$aUrl[host]$aUrl[path]" />
    <input type="hidden" name="shopMailAdress" value="$config['email']" />
    <input type="hidden" name="shopPhone" value="" />
  
  </fieldset>
</form>
<!-- END PAYMENT_FORM_5 -->

<!-- BEGIN PAYMENT_FORM_6 -->
<div id="order">
  <form action="$aData[sLinkName]" method="post" onsubmit="return checkForm( this )" id="orderForm">
    <input type="hidden" name="order_id" value="$iOrder" />
    <fieldset id="personalDataBlock">
      <legend>$lang[Your_personal_information]</legend>
      <fieldset id="personalData">
        <fieldset id="setBasic">
          <div id="firstName">
            <label for="oFirstName">$lang[First_name]</label>
            <input type="text" name="sFirstName" value="$aOrder[sFirstName]" maxlength="30" class="input" id="oFirstName" alt="simple" />
          </div>
          <div id="lastName">
            <label for="oLastName">$lang[Last_name]</label>
            <input type="text" name="sLastName" value="$aOrder[sLastName]" maxlength="40" class="input" id="oLastName" alt="simple" />
          </div>
          <div id="company">
            <label for="oCompany">$lang[Company]</label>
            <input type="text" name="sCompanyName" value="$aOrder[sCompanyName]" maxlength="100" class="input" id="oCompany" />
          </div>
          <div id="street">
            <label for="oStreet">$lang[Street]</label>
            <input type="text" name="sStreet" value="$aOrder[sStreet]" maxlength="40" class="input" id="oStreet" alt="simple" />
          </div>
          <div id="zipCode">
            <label for="oZipCode">$lang[Zip_code]</label>
            <input type="text" name="sZipCode" value="$aOrder[sZipCode]" maxlength="20" class="input" id="oZipCode" alt="simple" />
          </div>
          <div id="city">
            <label for="oCity">$lang[City]</label>
            <input type="text" name="sCity" value="$aOrder[sCity]" maxlength="40" class="input" id="oCity" alt="simple" />
          </div>
        </fieldset>
        <fieldset id="setExtend">
          <div id="country">
            <label for="oCountry">$lang[Country]</label>
            <select name="sCountry" value="$aOrder[sCountry]" class="input" id="oCountry" alt="simple" />
    	    $countriesList
    	    </select>
          </div>
          <div id="phone">
            <label for="oPhone">$lang[Telephone]</label>
            <input type="text" name="sPhone" value="$aOrder[sPhone]" maxlength="40" class="input" id="oPhone" alt="simple" />
          </div>
          <div id="email">
            <label for="oEmail">$lang[Email]</label>
            <input type="text" name="sEmail" value="$aOrder[sEmail]" maxlength="40" class="input" id="oEmail" alt="email" />
          </div>
        </fieldset>
      </fieldset>
    </fieldset>
  </form>
</div>
<!-- END PAYMENT_FORM_6 -->

<!-- BEGIN PAYMENT_OUTER -->
<div id="paymentOuter">
  $sPaymentOuterForm

  <script type="text/javascript">
  <!--
  if( document.getElementById( 'formPayment' ) )
    setTimeout( "document.getElementById( 'formPayment' ).submit()", 15000 );
  //-->
  </script>
  <div id="authWindow"><a href="#" onclick="document.getElementById( 'formPayment' ).submit();">&raquo; $lang[open_authorization_window] &laquo;</a></div>
</div>
<!-- END PAYMENT_OUTER -->

<!-- BEGIN ACCEPT -->
<div class="message" id="ok">
  <h3>$lang[There_are_money_to_pay]</h3>
</div>
<!-- END ACCEPT -->

<!-- BEGIN DENIED -->
<div class="message" id="error">
  <h3>$lang[There_are_no_money_to_pay]</h3>
</div>
<!-- END DENIED -->

<!-- BEGIN ERROR -->
<div class="message" id="error">
  <h3>$lang[Authorization_error]</h3>
</div>
<!-- END ERROR -->

<!-- BEGIN RETURN_INFO -->
<div class="message" id="error">
  <h3>$lang[Payment_return_info]</h3>
</div>
<!-- END RETURN_INFO -->

<!-- BEGIN ZAGIEL_LIST -->
	<input type="hidden" name="goodsId$aData[iItem]" readonly="readonly" value="$aData[iProduct]" />
	<input type="hidden" name="goodsName$aData[iItem]" readonly="readonly" value="$aData[sName]" />
	<input type="hidden" name="goodsValue$aData[iItem]" readonly="readonly" value="$aData[fSummary]" />
<!-- END ZAGIEL_LIST -->
<!-- BEGIN ZAGIEL_HEAD -->
<!-- END ZAGIEL_HEAD -->
<!-- BEGIN ZAGIEL_FOOT -->
  <input type="hidden" name="goodsNo" value="$aData[iItem]" />
<!-- END ZAGIEL_FOOT -->