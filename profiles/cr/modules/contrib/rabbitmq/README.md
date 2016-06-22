RabbitMQ Integration
====================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/comicrelief/rabbitmq/badges/quality-score.png?b=8.x-1.x)](https://scrutinizer-ci.com/g/comicrelief/rabbitmq/?branch=8.x-1.x)

Requirements
------------

* RabbitMQ server needs to be installed and configured.
* Drupal 8.0.0-RC4 or more recent must be configured with `php-amqplib`  
    * go to the root directory of your site
    * edit `composer.json` (not `core/composer.json`)
    * insert `"videlalvaro/php-amqplib": "^2.6"` in the `require` section of 
      the file, then save it.
    * update your `vendor` directory by typing `composer update`.

Example module
--------------

To test RabbitMQ from your Drupal site, enable the `rabbitmq_example` module and following the instructions from the README.

Installation
------------

* Provide connection credentials as part of the `$settings` global variable in 
  `settings.php`.

        $settings['rabbitmq.credentials'] = [
          'host' => 'localhost',
          'port' => 5672,
          'username' => 'guest',
          'password' => 'guest',
          'vhost' => '/'
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

Modules may override queue or exchange defaults built in a custom module by implementing
`config/install//rabbitmq.config.yml`. See `src/Queue/QueueBase.php` for details.
