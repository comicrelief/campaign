<?php

namespace Drupal\ds\Plugin\DsField;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a generic bundle field.
 *
 * @DsField(
 *   id = "bundle_field",
 *   deriver = "Drupal\ds\Plugin\Derivative\BundleField"
 * )
 */
class BundleField extends DsFieldBase {

  /**
   * The EntityDisplayRepository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $entity = $this->entity();
    $bundles_info = $this->entityTypeManager->getBundleInfo($config['field']['entity_type']);
    $output = $bundles_info[$entity->bundle()]['label'];

    return array(
      '#markup' => $output,
    );
  }

}
