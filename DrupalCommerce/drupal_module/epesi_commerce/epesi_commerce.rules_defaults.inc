<?php
function epesi_commerce_default_rules_configuration() {
  $configs = array();
  $rule = '{ "commerce_multicurrency_set_currency_price_eur" : {
  "LABEL" : "Set the currency price to EUR",
  "PLUGIN" : "reaction rule",
  "WEIGHT" : "-9",
  "REQUIRES" : [ "rules", "commerce_line_item", "commerce_product_reference" ],
  "ON" : [ "commerce_product_calculate_sell_price" ],
  "IF" : [
  { "data_is" : { "data" : [ "site:commerce-currency" ], "value" : "EUR" } },
  { "entity_has_field" : { "entity" : [ "commerce-line-item" ], "field" : "commerce_product" } },
  { "entity_has_field" : {
  "entity" : [ "commerce-line-item:commerce-product" ],
  "field" : "commerce_price_eur"
  }
  },
  { "NOT data_is_empty" : { "data" : [ "commerce-line-item:commerce-product:commerce-price-eur" ] } }
  ],
  "DO" : [
  { "commerce_line_item_unit_price_amount" : {
  "commerce_line_item" : [ "commerce-line-item" ],
  "amount" : [ "commerce-line-item:commerce-product:commerce-price-eur:amount" ],
  "component_name" : "base_price",
  "round_mode" : "1"
  }
  },
  { "commerce_line_item_unit_price_currency_code" : {
  "commerce_line_item" : [ "commerce_line_item" ],
  "currency_code" : "EUR"
  }
  }
  ]
  }
  }';
  $configs['commerce_multicurrency_set_currency_price_eur'] = rules_import($rule);
  $currencies = array('usd','pln','jpy','gbp','chf','cad','aud','nzd','rub');
  foreach($currencies as $curr)
    $configs['commerce_multicurrency_set_currency_price_'.strtolower($curr)] = rules_import(str_replace(array('eur','EUR'),array(strtolower($curr),strtoupper($curr)),$rule));
  return $configs;
}
?>