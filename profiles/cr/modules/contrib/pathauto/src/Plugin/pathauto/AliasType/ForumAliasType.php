<?php

/**
 * @file
 * Contains \Drupal\pathauto\Plugin\AliasType\ForumAliasType.
 */

namespace Drupal\pathauto\Plugin\pathauto\AliasType;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * A pathauto alias type plugin for forum terms.
 *
 * @AliasType(
 *   id = "forum",
 *   label = @Translation("Forum"),
 *   types = {"term"},
 *   provider = "forum",
 * )
 */
class ForumAliasType extends EntityAliasTypeBase implements ContainerFactoryPluginInterface {


  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId() {
    return 'taxonomy_term';
  }

  /**
   * {@inheritdoc}
   */
  public function getSourcePrefix() {
    return '/forum/';
  }

  /**
   * {@inheritdoc}
   */
  public function applies($object) {
    if (parent::applies($object)) {
      /** @var \Drupal\taxonomy\TermInterface $object */
      $config_forum = $this->configFactory->get('forum.settings');
      return $object->getVocabularyId() == $config_forum->get('vocabulary');
    }
    return FALSE;
  }

}
