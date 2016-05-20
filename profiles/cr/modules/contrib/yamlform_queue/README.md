YAML Form Queue
===============

Provides a queue handler for YAML Form (https://www.drupal.org/project/yamlform).

Can be used in combination with e.g. RabbitMQ (https://www.drupal.org/project/rabbitmq) to send form submissions to a rabbit message queue.

## Configuration

- Set up a YAML form and and in the "Email / Handlers" section, add a new "Queue" handler.
- Add the machine name of the queue that should be used to store the serialized data.
- If the debugging mode is enabled, messages sent to the queue will also be printed to the screen
- If you wish to send messages to a Rabbit Message Queue, install the "rabbitmq" module and make sure to set up the queue as a rabbit queue in `settings.php` (see the README of rabbitmq for configuration instructions)
