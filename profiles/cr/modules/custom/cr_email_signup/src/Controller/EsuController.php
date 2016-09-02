<?php

namespace Drupal\cr_email_signup\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Contains EsuController.php.
 */
class EsuController extends ControllerBase {

  public function simple() {
    return [
      '#markup' => '<p>' . $this->t('Simple page: The quick brown fox jumps over the lazy dog.') . '</p>',
    ];
  }
}
