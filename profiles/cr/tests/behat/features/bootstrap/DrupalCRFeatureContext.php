<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class DrupalCRFeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @Then I should see the correct sitemap elements
   * @And I should see the correct sitemap elements
   */
  public function iShouldSeeTheCorrectSitemapElements()
  {
    // Grab sitemap.xml page contents and parse it as XML using SimpleXML library
    $sitemap_contents = $this->getSession()->getDriver()->getContent();
    try {
      $xml = new SimpleXMLElement($sitemap_contents);
    } catch(Exception $e) {
      throw new Exception('Unable to read sitemap xml content - '.$e->getMessage());
    }

    // check if <url> nodes exist
    if (!($xml->count() > 0 && isset($xml->url))) {
      throw new InvalidArgumentException('No urlset found');
    }
  }

  /**
   * @Then I should see :url_path as a sitemap url
   * @And I should see :url_path as a sitemap url
   */
  public function iShouldSeeAsASitemapUrl($url_path)
  {
    // Grab sitemap.xml page contents and parse it as XML using SimpleXML library
    $sitemap_contents = $this->getSession()->getDriver()->getContent();
    try {
      $xml = new SimpleXMLElement($sitemap_contents);
    } catch(Exception $e) {
      throw new Exception('Unable to read sitemap xml content - '.$e->getMessage());
    }

    // Parse through each <url> node and check if url paths provided exist or not
    $path_found = false;
    foreach ($xml->children() as $xml_node) {
      if ( strpos($xml_node->loc, $url_path) ) {
        $path_found = true;
      }
    }

    // If no match found then throw exception
    if (!$path_found) {
      throw new InvalidArgumentException('Url not found');
    }
  }

  /**
   * @Given I click on :arg1 elememnt
   * @When I click on :arg1 button
   *
   */
  public function iClickOnElementButton($locator)
  {
    $this->getSession()->getPage()->find('xpath', $locator)->click();
  }

  /**
   * @When we wait for :arg1
   *
   */
  public function weWaitFor($time)
  {
    $this->getSession()->wait($time);
  }

  /**
   * @Given I click the :arg1 element
   * @And I click the :arg1 element
   *
   */
  public function iClickTheElement($selector)
  {
    $page = $this->getSession()->getPage();
    $element = $page->find('xpath', $selector);

    if (empty($element)) {
      throw new Exception("No html element found with this selector ('$selector')");
    }
    $element->click();
  }

}
