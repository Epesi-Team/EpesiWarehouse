<?php

/**
 * Implementation of hook_services_resources().
 */
function services_entity_translation_services_resources() {
  $resources = array();

      $resources["entity_translation"]['actions']['translate'] = array(
        'file' => array('type' => 'inc', 'module' => 'services_entity_translation', 'name' => 'services_entity_translation.resources'),
        'callback' => '_services_entity_translation_resource_translate',
        'description' => "Entity translation",
        'args' => array(
          array(
            'name' => 'entity_type',
            'optional' => FALSE,
            'source' => array('data' => 'entity_type'),
            'type' => 'string',
            'description' => 'The type of the entity to create',
          ),
          array(
            'name' => 'entity_id',
            'optional' => FALSE,
            'source' => array('data' => 'entity_id'),
            'description' => 'entity',
            'type' => 'integer',
          ),
          array(
            'name' => 'translation',
            'optional' => FALSE,
            'source' => array('data' => 'translation'),
            'description' => 'translation info',
            'type' => 'struct',
          ),
          array(
            'name' => 'values',
            'optional' => FALSE,
            'source' => array('data' => 'values'),
            'description' => 'translation values',
            'type' => 'struct',
          ),
        ),
        'access arguments' => array('access content'),
      );

  return $resources;
}
