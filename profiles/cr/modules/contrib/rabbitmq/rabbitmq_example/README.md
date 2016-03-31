## Example RabbitMQ module

This module shows the integration of the rabbitmq module in a custom form.

You need to add this to your `settings.php`

    $settings['queue_service_queue1'] = 'queue.rabbitmq';

to make sure that `queue1` will use a rabbit message queue rather than the default database queue.

Now, make sure RabbitMQ is running in the background and go to

    /rabbitmq_example

to submit an email address to the queue.
