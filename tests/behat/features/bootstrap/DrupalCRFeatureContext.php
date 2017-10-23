<?php

namespace BehatTests;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines application features from the specific context.
 */
class DrupalCRFeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @Then I should see the correct sitemap elements
   * @And I should see the correct sitemap elements
   */
  public function iShouldSeeTheCorrectSitemapElements() {
    // Grab sitemap.xml page contents and parse it as XML using SimpleXML library
    $sitemap_contents = $this->getSession()->getDriver()->getContent();
    try {
      $xml = new \SimpleXMLElement($sitemap_contents);
    } catch (\Exception $e) {
      throw new \Exception('Unable to read sitemap xml content - ' . $e->getMessage());
    }

    // check if <url> nodes exist
    if (!($xml->count() > 0 && isset($xml->url))) {
      throw new \InvalidArgumentException('No urlset found');
    }
  }

  /**
   * @Then I should see :url_path as a sitemap url
   * @And I should see :url_path as a sitemap url
   */
  public function iShouldSeeAsASitemapUrl($url_path) {
    // Grab sitemap.xml page contents and parse it as XML using SimpleXML library
    $sitemap_contents = $this->getSession()->getDriver()->getContent();
    try {
      $xml = new \SimpleXMLElement($sitemap_contents);
    } catch (\Exception $e) {
      throw new \Exception('Unable to read sitemap xml content - ' . $e->getMessage());
    }

    // Parse through each <url> node and check if url paths provided exist or not
    $path_found = FALSE;
    foreach ($xml->children() as $xml_node) {
      if (strpos($xml_node->loc, $url_path)) {
        $path_found = TRUE;
      }
    }

    // If no match found then throw exception
    if (!$path_found) {
      throw new \InvalidArgumentException('Url not found');
    }
  }

  /**
   * @Given /^(?:|I )wait for AJAX loading to finish$/
   *
   * Wait for the jQuery AJAX loading to finish. ONLY USE FOR DEBUGGING!
   */
  public function iWaitForAJAX() {
    $this->getSession()->wait(10000, 'jQuery.active === 0');
  }

  /**
   * @Then /^(?:|I )enter today date for "(?P<element>[^"]*)"$/
   *
   * @throws \Exception
   *   If element cannot be found
   */
  public function iEnterTodaysDateFor($field) {
    $date = date('Y-m-d');
    $this->getSession()->getPage()->fillField($field, $date);
  }

  /**
   * @Then /^(?:|I )enter the time for "(?P<element>[^"]*)"$/
   *
   * Inputs current time for 30 seconds in the future
   *
   * @throws \Exception
   *   If element cannot be found
   */
  public function iEnterTheTimeFor($field) {
    $time = date("H:i:s", time() + 30);
    $this->getSession()->getPage()->fillField($field, $time);
  }

  /**
   * @Given I wait for :arg1 seconds
   *
   * Wait for the given number of seconds. ONLY USE FOR DEBUGGING! Or any task
   *   using scheduled updates
   */
  public function iWaitForSeconds($arg1) {
    sleep($arg1);
  }

  /**
   * @Given I scroll :elementId into view
   *
   * Scroll to the id of an element, selenium will not do this for you
   */
  public function scrollIntoView($elementId) {
    $elem = $this->getSession()->getPage()->find('css', $elementId);
    $elem->focus();
  }

  /**
   * @Then /^the metatag attribute "(?P<attribute>[^"]*)" should have the value "(?P<value>[^"]*)"$/
   *
   * @throws \Exception
   *   If region or link within it cannot be found.
   */
  public function assertMetaRegion($metatag, $value) {
    $this->assertMetaRegionGeneric('name', $metatag, $value, 'equals');
  }

  /**
   * @Then /^the metatag property "(?P<attribute>[^"]*)" should have the value "(?P<value>[^"]*)"$/
   *
   * @throws \Exception
   *   If region or link within it cannot be found.
   */
  public function assertMetaRegionProperty($metatag, $value) {
    $this->assertMetaRegionGeneric('property', $metatag, $value, 'equals');
  }

  /**
   * @Then /^the metatag attribute "(?P<attribute>[^"]*)" should contain the value "(?P<value>[^"]*)"$/
   *
   * @throws \Exception
   *   If region or link within it cannot be found.
   */
  public function assertMetaRegionContains($metatag, $value) {
    $this->assertMetaRegionGeneric('name', $metatag, $value, 'contains');
  }

  /**
   * @Then /^the metatag property "(?P<attribute>[^"]*)" should contain the value "(?P<value>[^"]*)"$/
   *
   * @throws \Exception
   *   If region or link within it cannot be found.
   */
  public function assertMetaRegionPropertyContains($metatag, $value) {
    $this->assertMetaRegionGeneric('property', $metatag, $value, 'contains');
  }

  /**
   * Helper function to generalize metatag behat tests
   */
  private function assertMetaRegionGeneric($type, $metatag, $value, $comparison) {
    $element = $this->getSession()
      ->getPage()
      ->find('xpath', '/head/meta[@' . $type . '="' . $metatag . '"]');
    if ($element) {
      $contentValue = $element->getAttribute('content');
      if ($comparison == 'equals' && $value == $contentValue) {
        $result = $value;
      }
      else {
        if ($comparison == 'contains' && strpos($contentValue, $value) !== FALSE) {
          $result = $value;
        }
      }
    }
    if (empty($result)) {
      throw new \Exception(sprintf('Metatag "%s" expected to be "%s", but found "%s" on the page %s', $metatag, $value, $element->getText(), $this->getSession()
        ->getCurrentUrl()));
    }
  }

  /**
   * Creates a node that has paragraphs provided in a table.
   *
   * @Given I am viewing a/an :type( content) with :title( title) and :img( image) and :body( body)
   */
  public function assertParagraphs($type, $title, $image, $body) {
    // First, create a landing page node.
    $node = (object) [
      'title' => $title,
      'type' => $type,
      'uid' => 1,
    ];
    $node = $this->nodeCreate($node);

    // Add all the data to the node
    $node_loaded = \Drupal\node\Entity\Node::load($node->nid);
    $node_loaded->field_landing_image = $this->expandImage($image);
    $node_loaded->body = [
      'value' => $body,
      'format' => 'full_html',
    ];
    $node_loaded->save();

    // Set internal page on the new landing page.
    $this->getSession()->visit($this->locatePath('/node/' . $node->nid));
  }

  /**
   * Helper function to create our different paragraph types.
   *
   * @param  [type] $paragraph [description]
   *
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
        $data['field_background'] = $this->expandImage($paragraph['image']);
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
        $data['field_single_msg_row_lr_image'] = $this->expandImage($paragraph['image']);
        break;
      case 'single_msg':
        $data['field_single_msg_title'] = [
          'value' => $paragraph['title'],
        ];
        $data['field_single_msg_body'] = [
          'value' => $paragraph['body'],
          'format' => 'basic_html',
        ];
        $data['field_single_msg_img'] = $this->expandImage($paragraph['image']);
        $data['field_single_msg_bg'] = [
          'value' => $paragraph['bg_color'],
        ];
        break;
    }

    $paragraph_item = \Drupal\paragraphs\Entity\Paragraph::create($data);
    $paragraph_item->save();
    return $paragraph_item;
  }

  /**
   * Process image field values so we can use images.
   *
   * Shamelessly ripped off from \Drupal\Driver\Fields\Drupal8\ImageHandler
   *
   * We need to provide our own field handlers since we can't use the ones
   * provided by AbstractCore::expandEntityFields as they are protected.
   *
   * @param  [type] $values [description]
   *
   * @return [type]         [description]
   */
  private function expandImage($value) {
    // Skip empty values
    if (!$value) {
      return [];
    }

    $data = file_get_contents($value);
    if (FALSE === $data) {
      throw new \Exception("Error reading file");
    }

    /* @var \Drupal\file\FileInterface $file */
    $file = file_save_data(
      $data,
      'public://' . uniqid() . '.jpg');
    if (FALSE === $file) {
      throw new \Exception("Error saving file");
    }

    $file->save();
    $return = [
      'target_id' => $file->id(),
      'alt' => 'Behat test image',
      'title' => 'Behat test image',
    ];
    return $return;
  }

  /**
   * @Then I should see the image :Uri
   *
   * @param String $uri
   *
   * @throws \Exception
   */
  public function FindImage($uri) {
    $img = $this->getSession()->getPage()
      ->find('css', 'img[src*="' . $uri . '"]');
    if (!$img) {
      $img = $this->getSession()->getPage()
        ->find('css', 'img[data-src*="' . $uri . '"]');
    }
    if (!$img) {
      throw new \Exception("Image not found : $uri");
    }
    return $img;
  }

  /**
   * @Then I should not see the image :Uri
   *
   */
  public function NotFindImage($uri) {
    return !$this->getSession()->getPage()
      ->find('css', 'img[src="' . $uri . '"]');
  }

  /**
   * Selects option in select field with specified id|name|label|value in a
   * region Example: When I select "Bats" from "user_fears" in the "some"
   * region Example: And I select "Bats" from "user_fears" in the "some" region
   *
   * @Then I select :option from :select in the :region region
   */
  public function selectOptionRegion($select, $option, $region) {
    $regionObj = $this->getSession()->getPage()->find('region', $region);
    $regionObj->selectFieldOption($select, $option);
  }


  /**
   * Asserts that the last queue element contains given data.
   *
   * @Then I should have received the following data in the :queue( queue):
   */
  public function assertQueueElement($queue_name, TableNode $data) {
    /* @var \Drupal\Core\Queue\QueueFactory $queue_factory */
    $queue = \Drupal::service('queue')->get($queue_name);

    if (!$queue) {
      throw new \Exception('Unable to access queue "' . $queue_name . '"');
    }

    $item = $queue->claimItem();

    if (!$item || !is_object($item) || !is_array($item->data)) {
      throw new \Exception('Unable to claim item from queue "' . $queue_name . '"');
    }

    // Remove the item from the queue
    $queue->deleteItem($item);

    // Take off the data from the queue item
    $item = $item->data;

    // Get the expected data
    $expected = $data->getHash()[0];

    foreach ($expected as $name => $expected_value) {
      if (!isset($item[$name])) {
        throw new \Exception('Expected queue property "' . $name . '" was not found in last item from queue "' . $queue_name . '"');
      }

      // Check if the value from the queue is the same one as the expected value.
      // If we pass "*" as expected value, all values are correct.
      if ($expected_value != '*' && $item[$name] != $expected_value) {
        throw new \Exception('Expected queue property "' . $name . '" contains value "' . $item[$name] . '" but "' . $expected_value . '" expected, for last item from queue "' . $queue_name . '"');
      }
    }
  }

  /**
   * Creates unpublished content of the given type.
   *
   * @Given a/an unpublished :type (content )with the title :title
   */
  public function createUnpublishedNode($type, $title) {
    // @todo make this easily extensible.
    $node = (object) [
      'title' => $title,
      'type' => $type,
      'body' => $this->getRandom()->name(255),
      'status' => 0,
    ];
    $saved = $this->nodeCreate($node);
    // Set internal page on the new node.
    $this->getSession()->visit($this->locatePath('/node/' . $saved->nid));
  }

  /**
   * Check for unpublished partner titles.
   *
   * @Then I should see the hidden partner title :title
   */
  public function iShouldSeeTheHiddenPartnerTitle($title) {

    // Attempt to grab all the hidden partner titles
    $elements = $this->getSession()
      ->getPage()
      ->findAll('css', '.node--type-partner .field--name-title');
    if (empty($elements)) {
      throw new \Exception('No hidden partner titles in the markup to check');
    }
    $found = FALSE;
    // Loop through all elements to find our search title
    foreach ($elements as $element) {
      if ($element->getText() == $title) {
        $found = TRUE;
        break;
      }
    }

    if (!$found) {
      throw new \Exception('The hidden partner title ' . $title . ' was not found in the markup');
    }
  }

  /**
   * Check for video background.
   *
   * @Then I should see a :ext with the following filename :filename
   * @And I should see a :ext with the following filename :filename
   */
  public function iShouldSeeTheVideoSource($filename, $ext) {
    // Attempt to grab all the video elements
    $videos = $this->getSession()->getPage()->findAll('css', 'video');

    if (empty($videos)) {
      throw new \Exception('No video container in markup to check');
    }
    $found = FALSE;
    $pattern = '".+' . $filename . '.{0,}\.' . $ext . '"';

    // Loop through all video elements to find video source
    foreach ($videos as $video) {
      $sourceTag = $video->find('css', 'source');
      $source = $sourceTag->getAttribute('src');

      if (preg_match($pattern, $source) === 1) {
        $found = TRUE;
        break;
      }
    }

    if (!$found) {
      throw new \Exception('The video with filename ' . $filename . ' was not found in the markup');
    }
  }

  /**
   * Click on the element with given CSS
   *
   * @When I click on :arg element
   *
   * @param string $field
   */
  public function iClickOnElement(string $field): void {
    $this->getSession()->getPage()->find('css', $field)->click();
  }

  /**
   * Helper function to create landing page
   *
   * @param string $type
   * @param string $title
   *
   */
  public function createLandingPage($type, $title) {

    // Create a landing page node.
    $lnode = (object) [
      'title' => $title,
      'type' => $type,
      'uid' => 1,
    ];

    return $this->nodeCreate($lnode);

  }


  /**
   * Creates paragraphs with specified fields in landing page
   *
   * @Then I add :paragraph( paragraph ) with following fields in a test landing page:
   */
  public function createLandingPageWithParagraph($paragraph, TableNode $fields) {

    // Create paragraphs
    $paragraph_items = [];
    $data = [
      'type' => $paragraph,
    ];

    foreach ($fields->getRowsHash() as $field => $value) {

      if (strpos(strtolower($field), 'copy') !== FALSE) {
        $data[$field] = [
          'value' => $value,
          'format' => 'basic_html',
        ];
      }
      elseif ((strpos(strtolower($field), 'image') !== FALSE) || (strpos(strtolower($field), 'background') !== FALSE)) {
        $data[$field] = $this->expandImage($value);
      }
      elseif ((strpos(strtolower($field), 'img') !== FALSE)) {
        $data[$field] = $this->expandImage($value);
      }
      else {
        $data[$field] = [
          'value' => $value,
        ];
      }

    }

    $paragraph_item = \Drupal\paragraphs\Entity\Paragraph::create($data);
    $paragraph_item->save();

    $paragraph_items[] = [
      'target_id' => $paragraph_item->id(),
      'target_revision_id' => $paragraph_item->getRevisionId(),
    ];

    // Create test landing page
    $landingPage = $this->createLandingPage('landing', 'Test landing page');

    // Add paragraph to the landing page and save
    $node_loaded = \Drupal\node\Entity\Node::load($landingPage->nid);
    $node_loaded->field_paragraphs = $paragraph_items;
    $node_loaded->save();

  }

  /**
   * Mouse hover with specified CSS locator
   * 
   * @When /^I hover over the element "([^"]*)"$/
   *
   * @param string $locator
   *
   * @throws \Exception
   */
  public function iHoverOverTheElement($locator) {
    $session = $this->getSession(); // get the mink session
    $element = $session->getPage()
      ->find('css', $locator); // runs the actual query and returns the element

    if ($element === NULL) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $locator));
    }
    $element->mouseOver();
  }

}
