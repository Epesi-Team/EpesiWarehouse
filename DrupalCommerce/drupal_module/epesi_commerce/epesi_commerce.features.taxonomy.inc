<?php
/**
 * @file
 * epesi_commerce.features.taxonomy.inc
 */

/**
 * Implements hook_taxonomy_default_vocabularies().
 */
function epesi_commerce_taxonomy_default_vocabularies() {
  return array(
    'epesi_manufacturer' => array(
      'name' => 'Manufacturer',
      'machine_name' => 'epesi_manufacturer',
      'description' => '',
      'hierarchy' => '0',
      'module' => 'taxonomy',
      'weight' => '0',
    ),
    'epesi_category' => array(
      'name' => 'Epesi Category',
      'machine_name' => 'epesi_category',
      'description' => '',
      'hierarchy' => '0',
      'module' => 'taxonomy',
      'weight' => '0',
    ),
  );
}
