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
   * @Then the response should contain the tag :tag_name
   * @And the response should contain the tag :tag_name
   */
  public function theResponseShouldContainTheTag($tag_name)
  {
    // Grab webpage contents and parse using preg_match to find tag
    $html = $this->getSession()->getDriver()->getContent();
    $pattern = '/<'.$tag_name.'>(.*)<\/'.$tag_name.'>/siU';
    $tag_found = preg_match($pattern, $html, $matches);

    // If no match found then throw exception
    if (!$tag_found) {
     throw new InvalidArgumentException('Tag \''.$tag_name.'\' not found');
    }
  }

  /**
   * @Then response should contain the tag :tag_name with the attribute :attr_name with the value :attr_value
   * @And response should contain the tag :tag_name with the attribute :attr_name with the value :attr_value
   */
  public function responseShouldContainTheTagWithTheAttributeWithTheValue($tag_name, $attr_name, $attr_value)
  {
    // Grab webpage contents and parse it as a DOMDocument
    // - at some point might be worth revisiting code and look to use a HTML5 DOM parser;
    $html = $this->getSession()->getDriver()->getContent();
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_use_internal_errors(false);

    // Search for tag, comparing by matching the attribute value
    $tags = $dom->getElementsByTagName($tag_name);
    $tag_found = false;
    foreach ($tags as $tag) {
      if (strtolower($tag->getAttribute($attr_name)) == strtolower($attr_value)) {
        $tag_found = true;
      }
    }

    // If no match found then throw exception
    if (!$tag_found) {
     throw new InvalidArgumentException('Tag \''.$tag_name.'\' with attribute \''.$attr_name.'\' containing the value \''.$attr_value.'\' not found');
    }
  }

}
