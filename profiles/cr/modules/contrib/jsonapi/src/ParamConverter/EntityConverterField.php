<?php

namespace Drupal\jsonapi\ParamConverter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\ParamConverter\EntityConverter;
use Drupal\Core\TypedData\TranslatableInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route;

/**
 * Class EntityConverterUuid.
 *
 * Make it possible to load an entity by uuid.
 *
 * @package Drupal\jsonapi\ParamConverter
 */
class EntityConverterField extends EntityConverter {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $route = $defaults[RouteObjectInterface::ROUTE_OBJECT];
    $entity_type_id = $this->getEntityTypeFromDefaults($definition, $name, $defaults);
    if ($storage = $this->entityManager->getStorage($entity_type_id)) {
      if (!$entities = $storage->loadByProperties([$route->getOption('_entity_load_key') => $value])) {
        return NULL;
      }
      // Since this field is used as an alternate ID, it's assumed to be unique.
      $entity = reset($entities);
      // If the entity type is translatable, ensure we return the proper
      // translation object for the current context.
      if ($entity instanceof EntityInterface && $entity instanceof TranslatableInterface) {
        $entity = $this->entityManager->getTranslationFromContext($entity, NULL, array('operation' => 'entity_upcast'));
      }
      return $entity;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    // Only apply to JSON API routes.
    if (!$route->getOption('_is_jsonapi')) {
      return FALSE;
    }
    $config = \Drupal::config('jsonapi.resource_info');
    if (($key = $config->get('id_field')) && $key != 'id' && parent::applies($definition, $name, $route)) {
      $route->addOptions(['_entity_load_key' => $config->get('id_field')]);
      return TRUE;
    }
    return FALSE;
  }


}
