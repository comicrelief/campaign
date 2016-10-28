<?php

/**
 * @file
 * Contains \Drupal\monolog\Logger\Processor\MessagePlaceholderProcessor.
 */

namespace Drupal\monolog\Logger\Processor;

use Drupal\Core\Logger\LogMessageParserInterface;

/**
 * Parse and replace message placeholders.
 */
class MessagePlaceholderProcessor {

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   */
  public function __construct(LogMessageParserInterface $parser) {
    $this->parser = $parser;
  }

  /**
   * @param array $record
   *
   * @return array
   */
  public function __invoke(array $record) {
    // Populate the message placeholders and then replace them in the message.
    $message_placeholders = $this->parser->parseMessagePlaceholders($record['message'], $record['context']);
    $record['message'] = empty($message_placeholders) ? $record['message'] : strtr($record['message'], $message_placeholders);

    return $record;
  }

}
