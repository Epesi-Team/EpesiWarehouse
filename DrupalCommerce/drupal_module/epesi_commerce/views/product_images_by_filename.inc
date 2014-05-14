<?php

$view = new view();
$view->name = 'epesi_product_images_search_by_filename';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'file_managed';
$view->human_name = 'Epesi Product Images - search by filename';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['row_plugin'] = 'fields';
/* Field: File: File ID */
$handler->display->display_options['fields']['fid']['id'] = 'fid';
$handler->display->display_options['fields']['fid']['table'] = 'file_managed';
$handler->display->display_options['fields']['fid']['field'] = 'fid';
$handler->display->display_options['fields']['fid']['label'] = '';
$handler->display->display_options['fields']['fid']['element_label_colon'] = FALSE;
/* Contextual filter: File: Name */
$handler->display->display_options['arguments']['filename']['id'] = 'filename';
$handler->display->display_options['arguments']['filename']['table'] = 'file_managed';
$handler->display->display_options['arguments']['filename']['field'] = 'filename';
$handler->display->display_options['arguments']['filename']['default_action'] = 'empty';
$handler->display->display_options['arguments']['filename']['default_argument_type'] = 'fixed';
$handler->display->display_options['arguments']['filename']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['filename']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['filename']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['arguments']['filename']['limit'] = '0';

/* Display: Services */
$handler = $view->new_display('services', 'Services', 'services_1');
$handler->display->display_options['path'] = 'epesi_product_images_search_by_filename';
$translatables['epesi_product_images_search_by_filename'] = array(
  t('Master'),
  t('more'),
  t('Apply'),
  t('Reset'),
  t('Sort by'),
  t('Asc'),
  t('Desc'),
  t('Items per page'),
  t('- All -'),
  t('Offset'),
  t('« first'),
  t('‹ previous'),
  t('next ›'),
  t('last »'),
  t('All'),
  t('Services'),
);
