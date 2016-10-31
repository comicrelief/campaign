<?php

namespace Drupal\ds\Plugin\DsField\Node;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\Date;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin that renders the submitted by field.
 *
 * @DsField(
 *   id = "node_submitted_by",
 *   title = @Translation("Submitted by"),
 *   entity_type = "node",
 *   provider = "node"
 * )
 */
class NodeSubmittedBy extends Date {

  /**
   * Drupal core Render service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Renderer $renderer, DateFormatterInterface $date_service) {
    $this->renderer = $renderer;

    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $date_service);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $field = $this->getFieldConfiguration();

    /* @var $node \Drupal\node\NodeInterface */
    $node = $this->entity();

    /* @var $account \Drupal\user\UserInterface */
    $account = $node->getOwner();

    $date_format = str_replace('ds_post_date_', '', $field['formatter']);
    $user_name = array(
      '#theme' => 'username',
      '#account' => $account,
    );
    return array(
      '#markup' => $this->t('Submitted by <a href=":user_link">@user</a> on @date.',
        array(
          '@user' => $this->renderer->render($user_name),
          '@date' => $this->dateFormatter->format($this->entity()->created->value, $date_format),
          ':user_link' => Url::fromUri('entity:user/' . $account->id())->toString(),
        )
      ),
      '#cache' => array(
        'tags' => $account->getCacheTags(),
      ),
    );
  }

}
