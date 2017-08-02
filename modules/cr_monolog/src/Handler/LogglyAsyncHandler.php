<?php

namespace Drupal\cr_monolog\Handler;

use Monolog\Handler\LogglyHandler;

/**
 * Class LogglyAsyncHandler
 */
class LogglyAsyncHandler extends LogglyHandler {

  protected function send($data, $endpoint) {
    $url = sprintf('https://%s/%s/%s/', self::HOST, $endpoint, $this->token);

    $headers = ['Content-Type: application/json'];

    if (!empty($this->tag)) {
      $headers[] = 'X-LOGGLY-TAG: ' . implode(',', $this->tag);
    }

    // Use "exec" command to trigger system "curl"
    // This is fire-and-forget to prevent Loggly delaying us
    // Using "> /dev/null 2>&1 &" to immediately return from command
    $headersString = '';
    foreach ($headers as $header) {
      $headersString = sprintf("-H '%s' ", $header);
    }

    $curlCommand = sprintf(
      "curl -X POST %s -d %s %s > /dev/null 2>&1 &",
      $headersString,
      escapeshellarg($data),
      escapeshellarg($url)
    );

    exec($curlCommand, $output, $return);
  }
}
