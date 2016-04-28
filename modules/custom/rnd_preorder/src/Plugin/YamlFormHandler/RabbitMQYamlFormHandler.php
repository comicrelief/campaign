<?php

/**
 * @file
 * Contains \Drupal\rnd_preorder\Plugin\YamlFormHandler\RabbitMQYamlFormHandler.
 */

namespace Drupal\rnd_preorder\Plugin\YamlFormHandler;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Utility\Token;
use Drupal\file\Entity\File;
use Drupal\yamlform\YamlFormHandlerBase;
use Drupal\yamlform\YamlFormHandlerMessageInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Emails a YAML form submission.
 *
 * @YamlFormHandler(
 *   id = "rabbitmq",
 *   label = @Translation("RabbitMQ"),
 *   description = @Translation("Submits submission to a RabbitMQ"),
 *   cardinality = \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\yamlform\YamlFormHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class RabbitMQYamlFormHandler extends YamlFormHandlerBase implements YamlFormHandlerMessageInterface {

}
