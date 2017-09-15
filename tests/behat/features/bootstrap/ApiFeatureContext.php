<?php

namespace BehatTests;

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use GuzzleHttp\Client;

/**
 * Defines api features from the specific context.
 */
class ApiFeatureContext extends MinkContext implements Context {

  /**
   * @var Client
   */
  private $client;

  /**
   * @var string
   */
  private $actualPage;

  /**
   * ApiFeatureContext constructor.
   */
  public function __construct() {
    $this->client = new Client();
  }

  /**
   * @When /^I do a ([^"]*) request to "([^"]*)"$/
   */
  public function doRequest($method, $url) {
    $uri = $this->getMinkParameter('base_url') . $url;
    $request = $this->client
      ->request($method, $uri, ['query' => ['_format' => 'json']]);
    $this->actualPage = $request->getBody();
  }

  /**
   * @Then /^I should find in the position ([^"]*) of the menu the "([^"]*)" with the value "([^"]*)"$/
   * @throws \Exception
   */
  public function findMenuElement($pos, $key, $val) {
    $json = json_decode($this->actualPage, TRUE);
    if ($json[$pos]['link'][$key] !== $val) {
      throw new \Exception('Expected "' . $val . '" but got: ' . $json[$pos]['link'][$key]);
    }
  }

  /**
   * @inheritdoc
   */
  public function assertPageContainsText($text) {
    if (strpos($this->actualPage, $text) === FALSE) {
      throw new \Exception('Not found the text: "' . $text);
    }
  }
}
