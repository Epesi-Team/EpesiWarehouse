<?php

/**
 * Implementation of hook_services_resources().
 */
function epesi_commerce_services_resources() {
  $resources = array();

      $resources["epesi_commerce"]['actions']['get_product_fields'] = array(
        'file' => array('type' => 'inc', 'module' => 'epesi_commerce', 'name' => 'epesi_commerce.resources'),
        'callback' => '_epesi_commerce_resource_get_product_fields',
        'description' => "Get available epesi product fields",
        'args' => array(),
        'access arguments' => array('access content'),
      );

      $resources["epesi_commerce"]['actions']['get_node_fields'] = array(
        'file' => array('type' => 'inc', 'module' => 'epesi_commerce', 'name' => 'epesi_commerce.resources'),
        'callback' => '_epesi_commerce_resource_get_node_fields',
        'description' => "Get available epesi product node fields",
        'args' => array(),
        'access arguments' => array('access content'),
      );

  return $resources;
}
