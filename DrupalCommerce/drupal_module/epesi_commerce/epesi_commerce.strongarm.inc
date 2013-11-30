<?php
/**
 * @file
 * epesi_commerce.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function epesi_commerce_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_epesi_products';
  $strongarm->value = '1';
  $export['comment_epesi_products'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'field_bundle_settings_commerce_product__epesi_products';
  $strongarm->value = array(
    'view_modes' => array(
      'node_product_list' => array(
        'custom_settings' => TRUE,
      ),
      'line_item' => array(
        'custom_settings' => TRUE,
      ),
      'node_full' => array(
        'custom_settings' => TRUE,
      ),
      'node_teaser' => array(
        'custom_settings' => TRUE,
      ),
      'full' => array(
        'custom_settings' => FALSE,
      ),
      'commerce_line_item_display' => array(
        'custom_settings' => FALSE,
      ),
      'node_rss' => array(
        'custom_settings' => FALSE,
      ),
      'node_search_index' => array(
        'custom_settings' => FALSE,
      ),
      'node_search_result' => array(
        'custom_settings' => FALSE,
      ),
    ),
    'extra_fields' => array(
      'form' => array(),
      'display' => array(
        'sku' => array(
          'default' => array(
            'weight' => '-10',
            'visible' => TRUE,
          ),
          'node_product_list' => array(
            'weight' => '2',
            'visible' => FALSE,
          ),
          'node_full' => array(
            'weight' => '0',
            'visible' => TRUE,
          ),
          'product_in_cart' => array(
            'weight' => '2',
            'visible' => TRUE,
          ),
          'add_to_cart_confirmation_view' => array(
            'weight' => '1',
            'visible' => TRUE,
          ),
        ),
        'title' => array(
          'default' => array(
            'weight' => '-5',
            'visible' => TRUE,
          ),
          'node_product_list' => array(
            'weight' => '4',
            'visible' => FALSE,
          ),
          'node_full' => array(
            'weight' => '3',
            'visible' => FALSE,
          ),
          'product_in_cart' => array(
            'weight' => '1',
            'visible' => TRUE,
          ),
        ),
        'status' => array(
          'product_in_cart' => array(
            'weight' => '6',
            'visible' => FALSE,
          ),
          'add_to_cart_confirmation_view' => array(
            'weight' => '4',
            'visible' => FALSE,
          ),
        ),
      ),
    ),
  );
  $export['field_bundle_settings_commerce_product__epesi_products'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'field_bundle_settings_node__epesi_products';
  $strongarm->value = array(
    'view_modes' => array(
      'full' => array(
        'custom_settings' => TRUE,
      ),
      'teaser' => array(
        'custom_settings' => TRUE,
      ),
      'product_list' => array(
        'custom_settings' => TRUE,
      ),
      'rss' => array(
        'custom_settings' => FALSE,
      ),
      'search_index' => array(
        'custom_settings' => FALSE,
      ),
      'search_result' => array(
        'custom_settings' => FALSE,
      ),
    ),
    'extra_fields' => array(
      'form' => array(),
      'display' => array(
        'product:sku' => array(
          'default' => array(
            'weight' => '-10',
            'visible' => FALSE,
          ),
          'full' => array(
            'weight' => '1',
            'visible' => TRUE,
          ),
          'product_list' => array(
            'weight' => '4',
            'visible' => FALSE,
          ),
        ),
        'product:title' => array(
          'default' => array(
            'weight' => '-5',
            'visible' => FALSE,
          ),
          'full' => array(
            'weight' => '9',
            'visible' => FALSE,
          ),
          'product_list' => array(
            'weight' => '9',
            'visible' => FALSE,
          ),
        ),
        'product:commerce_price' => array(
          'default' => array(
            'weight' => '0',
            'visible' => TRUE,
          ),
          'full' => array(
            'weight' => '3',
            'visible' => TRUE,
          ),
          'product_list' => array(
            'weight' => '2',
            'visible' => TRUE,
          ),
        ),
        'product:field_images' => array(
          'default' => array(
            'weight' => '37',
            'visible' => TRUE,
          ),
          'full' => array(
            'weight' => '6',
            'visible' => TRUE,
          ),
          'product_list' => array(
            'weight' => '0',
            'visible' => TRUE,
          ),
        ),
        'product:status' => array(
          'full' => array(
            'weight' => '8',
            'visible' => FALSE,
          ),
          'product_list' => array(
            'weight' => '6',
            'visible' => FALSE,
          ),
        ),
        'product:title_field' => array(
          'full' => array(
            'weight' => '7',
            'visible' => FALSE,
          ),
          'product_list' => array(
            'weight' => '3',
            'visible' => FALSE,
          ),
        ),
      ),
    ),
  );
  $export['field_bundle_settings_node__epesi_products'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'menu_options_epesi_products';
  $strongarm->value = array();
  $export['menu_options_epesi_products'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'node_options_epesi_products';
  $strongarm->value = array(
    0 => 'status',
  );
  $export['node_options_epesi_products'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'node_submitted_epesi_products';
  $strongarm->value = 0;
  $export['node_submitted_epesi_products'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_node_epesi_products_pattern';
  $strongarm->value = 'epesi-products/[node:title]';
  $export['pathauto_node_epesi_products_pattern'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_punctuation_hyphen';
  $strongarm->value = 1;
  $export['pathauto_punctuation_hyphen'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_taxonomy_term_manufacturer_pattern';
  $strongarm->value = 'manufacturer/[term:name]';
  $export['pathauto_taxonomy_term_manufacturer_pattern'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_taxonomy_term_epesi_category_pattern';
  $strongarm->value = 'epesi-category/[term:name]';
  $export['pathauto_taxonomy_term_category_epesi_pattern'] = $strongarm;

  return $export;
}
