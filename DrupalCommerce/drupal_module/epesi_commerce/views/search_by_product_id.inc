<?php

$view = new view();
$view->name = 'epesi_products_search_by_product_id';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'node';
$view->human_name = 'Epesi Products - search by product_id';
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
/* Field: Content: Nid */
$handler->display->display_options['fields']['nid']['id'] = 'nid';
$handler->display->display_options['fields']['nid']['table'] = 'node';
$handler->display->display_options['fields']['nid']['field'] = 'nid';
$handler->display->display_options['fields']['nid']['label'] = '';
$handler->display->display_options['fields']['nid']['element_label_colon'] = FALSE;
/* Sort criterion: Content: Post date */
$handler->display->display_options['sorts']['created']['id'] = 'created';
$handler->display->display_options['sorts']['created']['table'] = 'node';
$handler->display->display_options['sorts']['created']['field'] = 'created';
$handler->display->display_options['sorts']['created']['order'] = 'DESC';
/* Contextual filter: Content: Product variations (field_product) */
$handler->display->display_options['arguments']['field_product_product_id']['id'] = 'field_product_product_id';
$handler->display->display_options['arguments']['field_product_product_id']['table'] = 'field_data_field_product';
$handler->display->display_options['arguments']['field_product_product_id']['field'] = 'field_product_product_id';
$handler->display->display_options['arguments']['field_product_product_id']['default_action'] = 'empty';
$handler->display->display_options['arguments']['field_product_product_id']['default_argument_type'] = 'fixed';
$handler->display->display_options['arguments']['field_product_product_id']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['field_product_product_id']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['field_product_product_id']['summary_options']['items_per_page'] = '25';
/* Filter criterion: Content: Published */
$handler->display->display_options['filters']['status']['id'] = 'status';
$handler->display->display_options['filters']['status']['table'] = 'node';
$handler->display->display_options['filters']['status']['field'] = 'status';
$handler->display->display_options['filters']['status']['value'] = 1;
$handler->display->display_options['filters']['status']['group'] = 1;
$handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;
/* Filter criterion: Content: Type */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'node';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['value'] = array(
'epesi_products' => 'epesi_products',
);

/* Display: Services */
$handler = $view->new_display('services', 'Services', 'services_1');
$handler->display->display_options['path'] = 'epesi_products_search_by_product_id';
$translatables['epesi_products_search_by_product_id'] = array(
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

