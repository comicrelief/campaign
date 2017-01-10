<?php

namespace Drupal\cr_email_signup\MessageQueue;

class SenderData extends Sender {

    /**
     * @inheritdoc
     */
  protected function populate($message) {
    $message['timestamp'] = time();
    $current_path = \Drupal::service('path.current')->getPath();
    $current_alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);
    $message['transSourceURL'] = \Drupal::request()->getHost() . $current_alias;
    $message['transSource'] = "{$message['campaign']}_ESU_[PageElementSource]";

    // RND-178: Device & Source Replacements.
    $source = (empty($message['source'])) ? "Unknown" : $message['source'];

    $message['transSource'] = str_replace(
      ['[PageElementSource]'],
      [$source],
      $message['transSource']
    );

    return $message;
  }

}
