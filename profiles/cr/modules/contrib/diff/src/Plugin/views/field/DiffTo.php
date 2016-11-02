<?php

namespace Drupal\diff\Plugin\views\field;

/**
 * @ViewsField("diff__to")
 */
class DiffTo extends DiffPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['label']['default'] = t('To');
    return $options;
  }

}
