<?php
/**
 * @file
 * epesi_commerce.features.user_permission.inc
 */

/**
 * Implements hook_user_default_permissions().
 */
function epesi_commerce_user_default_permissions() {
  $permissions = array();

  // Exported permission: view any commerce_product entity of bundle shoes.
  $permissions['view any commerce_product entity of bundle epesi_products'] = array(
    'name' => 'view any commerce_product entity of bundle epesi_products',
    'roles' => array(
      0 => 'administrator',
      1 => 'anonymous user',
      2 => 'authenticated user',
    ),
    'module' => 'commerce_product',
  );

  // Exported permission: view own commerce_product entities of bundle shoes.
  $permissions['view own commerce_product entities of bundle epesi_products'] = array(
    'name' => 'view own commerce_product entities of bundle epesi_products',
    'roles' => array(
      0 => 'administrator',
      1 => 'anonymous user',
      2 => 'authenticated user',
    ),
    'module' => 'commerce_product',
  );

  return $permissions;
}
