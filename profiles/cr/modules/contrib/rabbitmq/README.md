RabbitMQ Integration
====================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FGM/rabbitmq/badges/quality-score.png?b=8.x-1.x)](https://scrutinizer-ci.com/g/FGM/rabbitmq/?branch=8.x-1.x)

Requirements
------------

* RabbitMQ server needs to be installed and configured.
* Drupal 8.0.0-RC4 or more recent must be configured with `php-amqplib`  
    * go to the root directory of your site
    * edit `composer.json` (not `core/composer.json`)
    * insert `"videlalvaro/php-amqplib": "^2.6"` in the `require` section of 
      the file, then save it.
    * update your `vendor` directory by typing `composer update`.


Installation
------------

* Provide connection credentials as part of the `$settings` global variable in 
  `settings.php`.

        $settings['rabbitmq_credentials'] = [
          'host' => 'localhost',
          'port' => 5672,
          'username' => 'guest',
          'password' => 'guest',
        ];

* Configure RabbitMQ as the queuing system for the queues you want RabbitMQ to 
  maintain, either as the default queue service, default reliable queue service,
  or specifically for each queue:
    * If you want to set RabbitMQ as the default queue manager, then add the 
      following to your settings.

          $settings['queue_default'] = 'queue.rabbitmq';
    * Alternatively you can also set for each queue to use RabbitMQ using one 
      of these formats:

          $settings['queue_service_{queue_name}'] = 'queue.rabbitmq';
          $settings['queue_reliable_service_{queue_name}'] = 'queue.rabbitmq';

Customization
-------------

Modules may override queue defaults built in the module by implementing
`hook_rabbitmq_queue_info()`. See `rabbitmq.api.php` for details.
