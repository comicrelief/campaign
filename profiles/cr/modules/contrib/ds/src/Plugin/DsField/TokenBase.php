<?php

namespace Drupal\ds\Plugin\DsField;

use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The base plugin to create DS code fields.
 */
abstract class TokenBase extends DsFieldBase {

  /**
   * The Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, Token $token_service) {
    $this->token = $token_service;

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
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = $this->content();
    $format = $this->format();
    $value = $this->token->replace($content, array($this->getEntityTypeId() => $this->entity()), array('clear' => TRUE));

    return array(
      '#type' => 'processed_text',
      '#text' => $value,
      '#format' => $format,
      '#filter_types_to_skip' => array(),
      '#langcode' => '',
    );
  }

  /**
   * Returns the format of the code field.
   */
  protected function format() {
    return 'plain_text';
  }

  /**
   * Returns the value of the code field.
   */
  protected function content() {
    return '';
  }

}
