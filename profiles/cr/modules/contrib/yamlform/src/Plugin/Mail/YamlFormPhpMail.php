<?php

namespace Drupal\yamlform\Plugin\Mail;

use Drupal\Core\Mail\Plugin\Mail\PhpMail;

/**
 * Extend's the default Drupal mail backend to support HTML email.
 *
 * @Mail(
 *   id = "yamlform_php_mail",
 *   label = @Translation("Form PHP mailer"),
 *   description = @Translation("Sends the message as plain text or HTML, using PHP's native mail() function.")
 * )
 */
class YamlFormPhpMail extends PhpMail {

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    if (!empty($message['params']['html'])) {
      $message['body'] = implode("\n\n", $message['body']);
      return $message;
    }
    else {
      return parent::format($message);
    }
  }

}
