<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\Field.
 */

namespace Drupal\ds\Plugin\DsField;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;

/**
 * The base plugin to create DS fields.
 */
abstract class Field extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    // Initialize output
    $output = '';

    // Basic string.
    $entity_render_key = $this->entityRenderKey();

    if (isset($config['link text'])) {
      $output = t($config['link text']);
    }
    elseif (!empty($entity_render_key) && isset($this->entity()->{$entity_render_key})) {
      if ($this->getEntityTypeId() == 'user' && $entity_render_key == 'name') {
        $output = $this->entity()->getUsername();
      }
      else {
        $output = $this->entity()->{$entity_render_key}->value;
      }
    }

    if (empty($output)) {
      return array();
    }

    // Link.
    if (!empty($config['link'])) {
      /** @var $entity EntityInterface */
      $entity = $this->entity();
      $url_info = $entity->urlInfo();
      if (!empty($config['link class'])) {
        $url_info->setOption('attributes', array('class' => explode(' ', $config['link class'])));
      }
      $output = \Drupal::l($output, $url_info);
    }
    else {
      $output = Html::escape($output);
    }

    // Wrapper and class.
    if (!empty($config['wrapper'])) {
      $wrapper = Html::escape($config['wrapper']);
      $class = (!empty($config['class'])) ? ' class="' . Html::escape($config['class']) . '"' : '';
      $output = '<' . $wrapper . $class . '>' . $output . '</' . $wrapper . '>';
    }

    return array(
      '#markup' => $output,
    );
  }

  /**
   * Returns the entity render key for this field.
   */
  protected function entityRenderKey() {
    return '';
  }

}
