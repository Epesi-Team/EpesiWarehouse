<!-- BEGIN NOKAUT_LIST -->
  <offer>
    <id>$aData[iProduct]</id>
    <name><![CDATA[$aData[sName]]]></name>
    <description><![CDATA[$aData[sDescriptionFull]]]></description>
    <url><![CDATA[$aUrl[scheme]://$aUrl[host]$aUrl[path]$aData[sLinkName]]]></url>
    <image>$aData[sImage]</image>
    <price>$aData[fPrice]</price>
    <category>$aData[sCategoryNokaut]</category>
    <producer><![CDATA[$aData[sProducer]]]></producer>
    $aData[sFeatures]
  </offer><!-- END NOKAUT_LIST -->
<!-- BEGIN NOKAUT_LIST_IMAGE -->$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]<!-- END NOKAUT_LIST_IMAGE -->
<!-- BEGIN NOKAUT_FEATURES --><property name="$aData[sFeatureNameEscaped]"><![CDATA[$aData[sFeatureValue]]]></property><!-- END NOKAUT_FEATURES -->
<!-- BEGIN NOKAUT_HEAD --><?xml version="1.0" encoding="$config[charset]"?>
<!DOCTYPE nokaut SYSTEM "http://www.nokaut.pl/integracja/nokaut.dtd">
<nokaut>
  <offers><!-- END NOKAUT_HEAD -->
<!-- BEGIN NOKAUT_FOOT -->
</offers>
</nokaut><!-- END NOKAUT_FOOT -->

<!-- BEGIN CENEO_LIST -->
      <offer>
        <id>$aData[iProduct]</id>
        <name><![CDATA[$aData[sName]]]></name>
        <price>$aData[fPrice]</price>
        <url>$aUrl[scheme]://$aUrl[host]$aUrl[path]$aData[sLinkName]</url>
        <categoryId><![CDATA[$aData[sPages]]]></categoryId>
        <description><![CDATA[$aData[sDescriptionShort]]]></description>
        <image width="" height="" title="$aData[sNameEscaped]">$aData[sImage]</image>
	<attributes>
		<attribute>
			<name>Producent</name>
			<producer><![CDATA[$aData[sProducer]]]></producer>
		</attribute>
	</attributes>
	<availability>$aData[iAvailableDays]</availability>
      </offer>
<!-- END CENEO_LIST -->
<!-- BEGIN CENEO_FEATURES --><!-- END CENEO_FEATURES -->
<!-- BEGIN CENEO_LIST_IMAGE -->$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]<!-- END CENEO_LIST_IMAGE -->
<!-- BEGIN CENEO_HEAD --><?xml version="1.0" encoding="$config[charset]"?>
<!DOCTYPE pasaz:Envelope SYSTEM "loadOffers.dtd">
<pasaz:Envelope xmlns:pasaz="http://schemas.xmlsoap.org/soap/envelope/">
  <pasaz:Body>
    <loadOffers xmlns="urn:ExportB2B">
      <offers>
<!-- END CENEO_HEAD -->
<!-- BEGIN CENEO_FOOT -->
      </offers>
    </loadOffers>
  </pasaz:Body>
</pasaz:Envelope>
<!-- END CENEO_FOOT -->

<!-- BEGIN SKAPIEC_LIST -->
		<item>
			<compid>$aData[iProduct]</compid>
			<vendor><![CDATA[$aData[sProducer]]]></vendor>
			<desc><![CDATA[$aData[sName]]]></desc>
			<price>$aData[fPrice]</price>
			<dprice></dprice>
			<partnr>$aData[sPartNumber]</partnr>
			<catid>$aData[iPage]</catid>
      $aData[sImage]
      <desclong><![CDATA[$aData[sDescriptionFull]]]></desclong>
		</item>
<!-- END SKAPIEC_LIST -->
<!-- BEGIN SKAPIEC_FEATURES --><!-- END SKAPIEC_FEATURES -->
<!-- BEGIN SKAPIEC_LIST_IMAGE --><foto>$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]</foto><!-- END SKAPIEC_LIST_IMAGE -->
<!-- BEGIN SKAPIEC_HEAD --><?xml version="1.0" encoding="$config[charset]"?>
<xmldata>
	<version>12.0</version>
	<header>
		<name><![CDATA[$config[title]]]></name>
		<shopid>$config[skapiec_shop_id]</shopid>
		<www>$aUrl[scheme]://$aUrl[host]$aUrl[path]</www>
		<time>$aData[sSkapiecDate]</time>
		<dprice>$aData[fSkapiecMinCourier]</dprice>
	</header>
  $aData[sSkapiecCategories]
	<data>
<!-- END SKAPIEC_HEAD -->
<!-- BEGIN SKAPIEC_FOOT -->
	</data>
</xmldata>
<!-- END SKAPIEC_FOOT -->

<!-- BEGIN PAGES_LIST -->
<catitem> 
  <catid>$aData[iPage]</catid>
  <catname><![CDATA[$aData[sName]]]></catname>
</catitem>
<!-- END PAGES_LIST -->
<!-- BEGIN PAGES_HEAD --><category><!-- END PAGES_HEAD -->
<!-- BEGIN PAGES_FOOT --></category><!-- END PAGES_FOOT -->

<!-- BEGIN FROOGLE_LIST -->
      <item>
        <title><![CDATA[$aData[sName]]]></title>
        <g:price>$aData[fPrice]</g:price>
        <url>$aUrl[scheme]://$aUrl[host]$aUrl[path]$aData[sLinkName]</url>
        <description><![CDATA[$aData[sDescriptionShort]]]></description>
        <g:image_link>$aData[sImage]</g:image_link>
      </item>
<!-- END FROOGLE_LIST -->
<!-- BEGIN FROOGLE_FEATURES --><!-- END FROOGLE_FEATURES -->
<!-- BEGIN FROOGLE_LIST_IMAGE -->$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]<!-- END FROOGLE_LIST_IMAGE -->
<!-- BEGIN FROOGLE_HEAD --><?xml version="1.0" encoding="$config[charset]" ?>
<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
	<title><![CDATA[$config[title]]]></title>
	<description><![CDATA[$config[description]]]></description>
	<link>$aUrl[scheme]://$aUrl[host]$aUrl[path]</link><!-- END FROOGLE_HEAD -->
<!-- BEGIN FROOGLE_FOOT -->
</channel>
</rss>
<!-- END FROOGLE_FOOT -->
]]></description>

<!-- BEGIN HANDELO_LIST -->
<product>
  <name><![CDATA[$aData[sName]]]></name>
  <id><![CDATA[$aData[iProduct]]]></id>
  <description><![CDATA[$aData[sDescriptionShort]]]></description>
  <price><![CDATA[$aData[fPrice]]]></price>
  <categories><![CDATA[$aData[sPages]]]></categories>
  <categories_id><![CDATA[$aData[iPage]]]></categories_id>
  <image><![CDATA[$aData[sImage]]]></image>
</product>
<!-- END HANDELO_LIST -->
<!-- BEGIN HANDELO_FEATURES --><!-- END HANDELO_FEATURES -->
<!-- BEGIN HANDELO_LIST_IMAGE -->$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]<!-- END HANDELO_LIST_IMAGE -->
<!-- BEGIN HANDELO_HEAD -->
<?xml version="1.0" encoding="$config[charset]"?>
<handelo>
  <products>
<!-- END HANDELO_HEAD -->
<!-- BEGIN HANDELO_FOOT -->
  </products>
</handelo>
<!-- END HANDELO_FOOT -->

<!-- BEGIN SZOKER_LIST -->
  <offer>
    <id>$aData[iProduct]</id>
    <name><![CDATA[$aData[sName]]]></name>
    <description><![CDATA[$aData[sDescriptionFull]]]></description>
    <url><![CDATA[$aUrl[scheme]://$aUrl[host]$aUrl[path]$aData[sLinkName]]]></url>
    <image>$aData[sImage]</image>
    <price>$aData[fPrice]</price>
    <category><![CDATA[$aData[sPages]]]></category>
    <producer></producer>
  </offer><!-- END SZOKER_LIST -->
<!-- BEGIN SZOKER_FEATURES --><!-- END SZOKER_FEATURES -->
<!-- BEGIN SZOKER_LIST_IMAGE -->$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]<!-- END SZOKER_LIST_IMAGE -->
<!-- BEGIN SZOKER_HEAD --><?xml version="1.0" encoding="$config[charset]"?>
<offers><!-- END SZOKER_HEAD -->
<!-- BEGIN SZOKER_FOOT -->
</offers>
<!-- END SZOKER_FOOT -->

<!-- BEGIN CENUS_LIST -->
  <product>
    <name><![CDATA[$aData[sName]]]></name>
    <description><![CDATA[$aData[sDescriptionFull]]]></description>
    <price>$aData[fPrice]</price>
    <url><![CDATA[$aUrl[scheme]://$aUrl[host]$aUrl[path]$aData[sLinkName]]]></url>
    <image>$aData[sImage]</image>
    <category><![CDATA[$aData[sPages]]]></category>
  </product><!-- END CENUS_LIST -->
<!-- BEGIN CENUS_FEATURES --><!-- END CENUS_FEATURES -->
<!-- BEGIN CENUS_LIST_IMAGE -->$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]<!-- END CENUS_LIST_IMAGE -->
<!-- BEGIN CENUS_HEAD --><?xml version="1.0" encoding="$config[charset]"?>
<!DOCTYPE cenus SYSTEM "http://www.cenus.pl/xml/cenus.dtd">
<cenus><!-- END CENUS_HEAD -->
<!-- BEGIN CENUS_FOOT -->
</cenus>
<!-- END CENUS_FOOT -->

<!-- BEGIN SHOPPING_LIST -->
  <product>
    <name><![CDATA[$aData[sName]]]></name>
    <description><![CDATA[$aData[sDescriptionFull]]]></description>
    <price>$aData[fPrice]</price>
    <product_url><![CDATA[$aUrl[scheme]://$aUrl[host]$aUrl[path]$aData[sLinkName]]]></product_url>
    <image_url>$aData[sImage]</image_url>
    <category><![CDATA[$aData[sPages]]]></category>
    <manufacturer></manufacturer>
    <upc></upc>
    <mpn></mpn>
  </product><!-- END SHOPPING_LIST -->
<!-- BEGIN SHOPPING_FEATURES --><!-- END SHOPPING_FEATURES -->
<!-- BEGIN SHOPPING_LIST_IMAGE -->$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]<!-- END SHOPPING_LIST_IMAGE -->
<!-- BEGIN SHOPPING_HEAD --><?xml version="1.0" encoding="$config[charset]"?>
<shopping>
<!-- END SHOPPING_HEAD -->
<!-- BEGIN SHOPPING_FOOT -->
</shopping><!-- END SHOPPING_FOOT -->

<!-- BEGIN ONET_LIST -->
  <oferta>
    <identyfikator>$aData[iProduct]</identyfikator>
    <nazwa><![CDATA[$aData[sName]]]></nazwa>
    <url><![CDATA[$aUrl[scheme]://$aUrl[host]$aUrl[path]$aData[sLinkName]]]></url>
    <cena>$aData[fPrice]</cena>
    <sciezka_kategorii><![CDATA[$aData[sPagesOnet]]]></sciezka_kategorii>
    <id_kategorii_sklepu>$aData[iPage]</id_kategorii_sklepu>
    <marka_producent><![CDATA[$aData[sProducer]]]></marka_producent>
    <opis><![CDATA[$aData[sDescriptionFull]]]></opis>
    $aData[sImage]
  </oferta>
<!-- END ONET_LIST -->
<!-- BEGIN ONET_FEATURES --><!-- END ONET_FEATURES -->
<!-- BEGIN ONET_LIST_IMAGE --><zdjecie>$aUrl[scheme]://$aUrl[host]$aUrl[path]$config[dir_files]$aDataImage[sFileName]</zdjecie><!-- END ONET_LIST_IMAGE -->
<!-- BEGIN ONET_HEAD --><?xml version="1.0" encoding="$config[charset]"?>
<oferty aktualizacja="N" xmlns="http://www.zakupy.onet.pl/walidacja/oferty-partnerzy.xsd">
<!-- END ONET_HEAD -->
<!-- BEGIN ONET_FOOT -->
</oferty>
<!-- END ONET_FOOT -->