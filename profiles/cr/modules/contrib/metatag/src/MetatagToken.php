<?php

/**
 * @file
 * Contains the \Drupal\metatag\MetatagToken class.
 */

namespace Drupal\metatag;
use Drupal\Core\Utility\Token;

/**
 * Token handling service. Uses core token service or contributed Token.
 */
class MetatagToken {

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a new MetatagToken object.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   Token service.
   */
  public function __construct(Token $token) {
    $this->token = $token;
  }

  /**
   * Wrapper for the Token module's string parsing.
   *
   * @param $string
   * @param $data
   * @param array $options
   *
   * @return mixed|string $string
   */
  public function replace($string, $data, $options = array()) {
    $options['clear'] = TRUE;

    $replaced = $this->token->replace($string, $data, $options);

    // Ensure that there are no double-slash sequences due to empty token
    // values.
    $replaced = preg_replace('/(?<!:)\/+\//', '/', $replaced);

    return $replaced;
  }

  /**
   * Gatekeeper function to direct to either the core or contributed Token.
   *
   * @param mixed $token_types
   *   The token types to return. Defaults to all.
   *
   * @return array
   *   If token module is installed, a popup browser plus a help text. If not
   *   only the help text.
   */
  public function tokenBrowser($token_types = NULL) {
    $form = array();

    $form['intro_text'] = array(
      '#markup' => '<p>' . t('Configure the meta tags below. Use tokens to avoid redundant meta data and search engine penalization. For example, a \'keyword\' value of "example" will be shown on all content using this configuration, whereas using the [node:field_keywords] automatically inserts the "keywords" values from the current entity (node, term, etc).') . '</p>',
    );

    // Normalize taxonomy tokens.
    if (!empty($token_types)) {
      $token_types = array_map(function($value) {
        return stripos($value, 'taxonomy_') === 0 ? substr($value, strlen('taoxnomy_')) : $value;
      }, (array) $token_types);
    }

    $form['tokens'] = array(
      '#theme' => 'token_tree_link',
      '#token_types' => !empty($token_types) ? $token_types : 'all',
      '#global_types' => TRUE,
      '#click_insert' => TRUE,
      '#show_restricted' => FALSE,
      '#recursion_limit' => 3,
      '#text' => t('Browse available tokens'),
    );

    return $form;
  }

}
