<?php

namespace Drupal\cr_email_signup\MessageQueue;

class Sender {

    /**
     * Deliver a populate message to the queue.
     *
     * @param string $name
     * @param array $data
     */
    public function deliver($name, $data) {
        $this->send($name, $this->populate($data));
    }

    /**
     * Fill a message for the queue service.
     *
     * @param string $message
     * @return array
     */
    protected function populate($message) {
        return [
            'emailAddress' => $message['email'],
            'templateName' => $message['templateName'],
            'messageParams' => []
        ];
    }

    /**
     * Send a message to the queue service.
     *
     * @param string $name
     * @param array $queue_message
     */
    protected function send($name, $queue_message) {
        try {
            $queue_factory = \Drupal::service('queue');
            /* @var \Drupal\rabbitmq\Queue\Queue $queue */
            $queue = $queue_factory->get($name);

            if (FALSE === $queue->createItem($queue_message)) {
                throw new \Exception("createItem Failed. Check Queue.");
            }
        } catch (\Exception $e) {
            \Drupal::logger('cr_email_signup')->error(
                "Unable to queue message. Error was: " . $e->getMessage()
            );
        }
    }

}
