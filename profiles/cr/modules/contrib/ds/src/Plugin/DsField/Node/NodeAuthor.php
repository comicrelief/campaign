<?php

namespace Drupal\ds\Plugin\DsField\Node;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that renders the author of a node.
 *
 * @DsField(
 *   id = "node_author",
 *   title = @Translation("Author"),
 *   entity_type = "node",
 *   provider = "node"
 * )
 */
class NodeAuthor extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /* @var $node NodeInterface */
    $node = $this->entity();

    /* @var $user UserInterface */
    $user = $node->getOwner();

    // Users without a user name are anonymous users. These are never linked.
    if (empty($user->name)) {
      return array(
        '#plain_text' => \Drupal::config('user.settings')->get('anonymous'),
      );
    }

    $field = $this->getFieldConfiguration();
    if ($field['formatter'] == 'author') {
      return array(
        '#markup' => $user->getUsername(),
        '#cache' => array(
          'tags' => $user->getCacheTags(),
        ),
      );
    }

    if ($field['formatter'] == 'author_linked') {
      return array(
        '#theme' => 'username',
        '#account' => $user,
        '#cache' => array(
          'tags' => $user->getCacheTags(),
        ),
      );
    }

    // Otherwise return an empty array.
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function formatters() {

    $formatters = array(
      'author' => $this->t('Author'),
      'author_linked' => $this->t('Author linked to profile'),
    );

    return $formatters;
  }

}
