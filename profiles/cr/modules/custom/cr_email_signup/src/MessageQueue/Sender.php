<?php

namespace Drupal\cr_email_signup\MessageQueue;

class Sender {
  protected $campaign = 'RND17';

  /**
   * @param string $name
   * @param array $data
   */
  public function sendTo($name, $data){
    $this->sendQmessage($name, $this->fillmessage($data));
  }

  /**
   * Fill a message for the queue service.
   */
  private function fillmessage($message) {
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

  /**
   * Send a message to the queue service.
   */
  private function sendQmessage($name, $queue_message) {
    try {
      $queue_factory = \Drupal::service('queue');
      /* @var \Drupal\rabbitmq\Queue\Queue $queue */
      $queue = $queue_factory->get($name);

      if (FALSE === $queue->createItem($queue_message)) {
        throw new \Exception("createItem Failed. Check Queue.");
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('cr_email_signup')->error(
        "Unable to queue message. Error was: " . $e->getMessage()
      );
    }
  }

}
