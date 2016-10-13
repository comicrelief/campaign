<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handles form requests.
 */
class YamlFormRequest implements YamlFormRequestInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a YamlFormSubmissionExporter object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(EntityManagerInterface $entity_manager, RequestStack $request_stack, RouteMatchInterface $route_match) {
    $this->entityManager = $entity_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentSourceEntity($ignored_types = NULL) {
    // See if source entity is being set via query string parameters.
    if ($source_entity = $this->getCurrentSourceEntityFromQuery()) {
      return $source_entity;
    }

    $entity_types = $this->entityManager->getEntityTypeLabels();
    if ($ignored_types) {
      if (is_array($ignored_types)) {
        $entity_types = array_diff_key($entity_types, array_flip($ignored_types));
      }
      else {
        unset($entity_types[$ignored_types]);
      }
    }
    foreach ($entity_types as $entity_type => $entity_label) {
      $entity = $this->routeMatch->getParameter($entity_type);
      if ($entity instanceof EntityInterface) {
        return $entity;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentYamlForm() {
    $source_entity = self::getCurrentSourceEntity('yamlform');
    if ($source_entity && method_exists($source_entity, 'hasField') && $source_entity->hasField('yamlform')) {
      return $source_entity->yamlform->entity;
    }
    else {
      return $this->routeMatch->getParameter('yamlform');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormEntities() {
    $yamlform = $this->getCurrentYamlForm();
    $source_entity = $this->getCurrentSourceEntity('yamlform');
    return [$yamlform, $source_entity];
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormSubmissionEntities() {
    $yamlform_submission = $this->routeMatch->getParameter('yamlform_submission');
    $source_entity = $this->getCurrentSourceEntity('yamlform_submission');
    return [$yamlform_submission, $source_entity];
  }

  /****************************************************************************/
  // Routing helpers
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getRouteName(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL, $route_name) {
    return $this->getBaseRouteName($yamlform_entity, $source_entity) . '.' . $route_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL) {
    if (self::isValidSourceEntity($yamlform_entity, $source_entity)) {
      if ($yamlform_entity instanceof YamlFormSubmissionInterface) {
        return [
          'yamlform_submission' => $yamlform_entity->id(),
          $source_entity->getEntityTypeId() => $source_entity->id(),
        ];
      }
      else {
        return [$source_entity->getEntityTypeId() => $source_entity->id()];
      }
    }
    elseif ($yamlform_entity instanceof YamlFormSubmissionInterface) {
      return [
        'yamlform_submission' => $yamlform_entity->id(),
        'yamlform' => $yamlform_entity->getYamlForm()->id(),
      ];
    }
    else {
      return [$yamlform_entity->getEntityTypeId() => $yamlform_entity->id()];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseRouteName(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL) {
    if ($yamlform_entity instanceof YamlFormSubmissionInterface) {
      $yamlform = $yamlform_entity->getYamlForm();
    }
    elseif ($yamlform_entity instanceof YamlFormInterface) {
      $yamlform = $yamlform_entity;
    }
    else {
      throw new \InvalidArgumentException('Form entity');
    }

    if (self::isValidSourceEntity($yamlform, $source_entity)) {
      return 'entity.' . $source_entity->getEntityTypeId();
    }
    else {
      return 'entity';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isValidSourceEntity(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL) {
    if ($yamlform_entity instanceof YamlFormSubmissionInterface) {
      $yamlform = $yamlform_entity->getYamlForm();
    }
    elseif ($yamlform_entity instanceof YamlFormInterface) {
      $yamlform = $yamlform_entity;
    }
    else {
      throw new \InvalidArgumentException('Form entity');
    }

    if ($source_entity
      && method_exists($source_entity, 'hasField')
      && $source_entity->hasField('yamlform')
      && $source_entity->yamlform->target_id == $yamlform->id()
    ) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get form submission source entity from query string.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   A source entity.
   */
  protected function getCurrentSourceEntityFromQuery() {
    // Get and check for form.
    $yamlform = $this->routeMatch->getParameter('yamlform');
    if (!$yamlform) {
      return NULL;
    }

    // Get and check source entity type.
    $source_entity_type = $this->request->query->get('source_entity_type');
    if (!$source_entity_type) {
      return NULL;
    }

    // Get and check source entity id.
    $source_entity_id = $this->request->query->get('source_entity_id');
    if (!$source_entity_id) {
      return NULL;
    }

    // Get and check source entity.
    $source_entity = \Drupal::entityTypeManager()->getStorage($source_entity_type)->load($source_entity_id);
    if (!$source_entity) {
      return NULL;
    }

    // Check source entity access.
    if (!$source_entity->access('view')) {
      return NULL;
    }

    // Check that the form is referenced by the source entity.
    if (!$yamlform->getSetting('form_prepopulate_source_entity')) {
      // Get source entity's yamlform field.
      $yamlform_field_name = $this->getSourceEntityYamlFormFieldName($source_entity);
      if (!$yamlform_field_name) {
        return NULL;
      }

      // Check that source entity's reference form is the current YAML
      // form.
      if ($source_entity->$yamlform_field_name->target_id != $yamlform->id()) {
        return NULL;
      }
    }

    return $source_entity;
  }

  /**
   * Get the source entity's yamlform field name.
   *
   * @param EntityInterface $source_entity
   *   A form submission's source entity.
   *
   * @return string
   *   The name of the yamlform field, or an empty string.
   */
  protected function getSourceEntityYamlFormFieldName(EntityInterface $source_entity) {
    if ($source_entity instanceof ContentEntityInterface) {
      $fields = $source_entity->getFieldDefinitions();
      foreach ($fields as $field_name => $field_definition) {
        if ($field_definition->getType() == 'yamlform') {
          return $field_name;
        }
      }
    }
    return '';
  }

}
