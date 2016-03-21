<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
//use Behat\Behat\Context\Exception\ContextNotFoundException\ContextNotFoundException;
use Behat\Behat\Tester\Exception\PendingException;
//use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

/**
 * Defines application features from the specific context.
 */
class DrupalCRFeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @Then I should see the correct sitemap elements
   */
  public function iShouldSeeTheCorrectSitemapElements()
  {
    try {
      $str_xml = $this->getSession()->getDriver()->getContent();
      $domdoc_xml = new DOMDocument();
      $domdoc_xml->loadXML($str_xml);
      $xpath = new DOMXpath($domdoc_xml);
      $elements = $xpath->query("/urlset/url");

      if (is_null($elements)) {
        throw new InvalidArgumentException('No urlset found');
      }
    } catch(Exception $e) {
      throw new PendingException();
    }
  }

}
