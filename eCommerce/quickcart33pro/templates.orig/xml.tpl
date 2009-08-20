<!-- BEGIN SITEMAP2XML_HEAD -->
<?xml version="1.0" encoding="$config[charset]"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
<!-- END SITEMAP2XML_HEAD -->
<!-- BEGIN SITEMAP2XML_LIST -->
<url>
  <loc>$aData[sSiteUrl]$aData[sLinkName]</loc>
  <priority>0.5</priority>
  <lastmod>$sDateTime</lastmod>
  <changefreq>weekly</changefreq>
</url>
<!-- END SITEMAP2XML_LIST -->
<!-- BEGIN SITEMAP2XML_FOOT -->
</urlset>
<!-- END SITEMAP2XML_FOOT -->

<!-- BEGIN RSS_HEAD -->
<?xml version="1.0" encoding="$config[charset]"?>
<rss version="2.0">
<channel>
  <title><![CDATA[$sTitle$config[title]]]></title>
  <link>$aData[sSiteUrl]</link>
  <description><![CDATA[$config[description]]]></description>
  <language>$config[language]</language>
  <generator>Quick.Cms - RSS generator</generator>
<!-- END RSS_HEAD -->
<!-- BEGIN RSS_LIST -->
  <item>
    <title><![CDATA[$aData[sName]]]></title>
    <link>$aData[sSiteUrl]$aData[sLinkName]</link>
    <description><![CDATA[$aData[sDescriptionShort]]]></description>
    <pubDate>$aData[sDate]</pubDate>
  </item>
<!-- END RSS_LIST -->
<!-- BEGIN RSS_FOOT -->
</channel>
</rss>
<!-- END RSS_FOOT -->