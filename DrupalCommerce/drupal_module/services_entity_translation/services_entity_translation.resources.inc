<?php

function _services_entity_translation_resource_translate($entity_type, $entity_id,$translation,$values) {
  $entity = entity_load_single($entity_type, $entity_id);
  $handler = entity_translation_get_handler($entity_type, $entity);
  $handler->setTranslation($translation, $values);
  entity_save($entity_type,$entity);
}


