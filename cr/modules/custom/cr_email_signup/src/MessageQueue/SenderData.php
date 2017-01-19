<?php

namespace Drupal\cr_email_signup\MessageQueue;

class SenderData extends Sender {
    /* @var string */
  protected $campaign = 'RND17';

    /**
     * @inheritdoc
     */
  protected function populate($message) {
    // Add dynamic keys.
    $message['timestamp'] = time();
    $current_path = \Drupal::service('path.current')->getPath();
    $current_alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);
    $message['transSourceURL'] = \Drupal::request()->getHost() . $current_alias;
    $message['transSource'] = "{$this->campaign}_ESU_[PageElementSource]";

    // RND-178: Device & Source Replacements.
    $source = (empty($message['source'])) ? "Unknown" : $message['source'];

    $message['transSource'] = str_replace(
      ['[PageElementSource]'],
      [$source],
      $message['transSource']
    );

    // Add passed arguments.
    $message['campaign'] = $this->campaign;
    return $message;
  }

}
