<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;

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
   * @Given /^(?:|I )wait for AJAX loading to finish$/
   *
   * Wait for the jQuery AJAX loading to finish. ONLY USE FOR DEBUGGING!
   */
  public function iWaitForAJAX() {
    $this->getSession()->wait(5000, 'jQuery.active === 0');
  }

  /**
   * @Given /^I am viewing a landing page with the following paragraphs:$/
   */
  public function assertParagraphs(TableNode $paragraphs) {
    // First, create a landing page node.
    $node = (object) array(
      'title' => 'Landing page',
      'type' => 'landing',
      'uid' => 1,
    );
    $node = $this->nodeCreate($node);

    $paragraph_items = array();

    // Create paragraphs
    foreach ($paragraphs->getHash() as $paragraph) {
      $paragraph_item = $this->createParagraphItem($paragraph);
      $paragraph_items[] = [
        'target_id' => $paragraph_item->id(),
        'target_revision_id' => $paragraph_item->getRevisionId(),
      ];
    }

    $node_loaded = \Drupal\node\Entity\Node::load($node->nid);
    $node_loaded->field_paragraphs = $paragraph_items;
    $node_loaded->save();

    // Set internal page on the new landing page.
    $this->getSession()->visit($this->locatePath('/node/' . $node->nid));
  }

  /**
   * Helper function to create our different paragraph types.
   *
   * @param  [type] $paragraph [description]
   * @return [type]            [description]
   */
  private function createParagraphItem($paragraph) {
    // Default data for all paragraph types
    $data = [
      'type' => $paragraph['type'],
    ];

    // Every paragraph type might add specific data
    switch ($paragraph['type']) {
      case 'cr_rich_text_paragraph':
        $data['field_body'] = [
          'value' => $paragraph['body'],
          'format' => 'basic_html',
        ];
        break;
      case 'cr_single_message_row':
        $data['field_single_msg_row_lr_title'] = [
          'value' => $paragraph['title'],
        ];
        $data['field_single_msg_row_lr_variant'] = [
          'value' => $paragraph['variant'],
        ];
        $data['field_single_msg_row_lr_body'] = [
          'value' => $paragraph['body'],
          'format' => 'basic_html',
        ];
        break;
    }

    $paragraph_item = \Drupal\paragraphs\Entity\Paragraph::create($data);
    $paragraph_item->save();
    return $paragraph_item;
  }

}
