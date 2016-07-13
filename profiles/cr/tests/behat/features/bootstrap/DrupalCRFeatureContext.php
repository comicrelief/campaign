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
   * Use a 'spin' function to continuously check if a statement is true
   */
  public function spin ($lambda, $wait = 70)
  {
    $endTime = time() + 60;
    for ($i = 0; $i < $wait; $i++)
    {
      try {
        if ($lambda($endTime)) {
          return true;
        }
      } catch (Exception $e) {

      }

      sleep(1);
    }
    throw new Exception("Article is not ready to be released yet", 1);
  }


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
   * @Then /^(?:|I )enter todays date for "(?P<element>[^"]*)"$/
   *
   * @throws \Exception
   *   If element cannot be found
   */
  public function iEnterTodaysDateFor($field) {
    $date = date("j/m/Y");
    $this->getSession()->getPage()->fillField($field, $date);
  }

  /**
   * @Then /^(?:|I )enter the time for "(?P<element>[^"]*)"$/
   *
   * @throws \Exception
   *   If element cannot be found
   */
  public function iEnterTheTimeFor($field) {
    $time = date("H:i:s", time() + 60);
    $this->getSession()->getPage()->fillField($field, $time);
  }

  /**
   * @Given /^(?:|I )wait for update time$/
   *
   * Wait for scheduled update time, then run cron
   */
  public function iWaitForUpdateTime() {
    $this->spin(function($endTime) {
      if (time() >= $endTime) {
        return true;
      }
    });
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
    $element = $this->getSession()->getPage()->find('xpath', '/head/meta[@' . $type . '="' . $metatag . '"]/@content');
    if ($element) {
      if ($comparison == 'equals' && $value == $element->getText()) {
        $result = $value;
      }
      else if ($comparison == 'contains' && strpos($element->getText(), $value) !== false) {
        $result = $value;
      }
    }
    if (empty($result)) {
      throw new Exception(sprintf('Metatag "%s" expected to be "%s", but found "%s" on the page %s', $metatag, $value, $element->getText(), $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Creates a node that has paragraphs provided in a table.
   *
   * @Given I am viewing a/an :type( content) with :title( title) and :img( image) and :body( body) and with the following paragraphs:
   */
  public function assertParagraphs($type, $title, $image, $body, TableNode $paragraphs) {
    // First, create a landing page node.
    $node = (object) array(
      'title' => $title,
      'type' => $type,
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

    // Add all the data to the node
    $node_loaded = \Drupal\node\Entity\Node::load($node->nid);
    $node_loaded->field_landing_image = $this->expandImage($image);
    $node_loaded->body = [
      'value' => $body,
      'format' => 'full_html',
    ];
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
        $data['field_single_msg_feat'] = [
          'value' => $paragraph['featured'],
        ];
        $data['field_single_msg_img_r'] = [
          'value' => $paragraph['image_right'],
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
   * We need to provide our own field handlers since we can't use the ones provided by AbstractCore::expandEntityFields as they are protected.
   *
   * @param  [type] $values [description]
   * @return [type]         [description]
   */
  private function expandImage($value) {
    // Skip empty values
    if (!$value) {
      return array();
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

    $return = array(
      'target_id' => $file->id(),
      'alt' => 'Behat test image',
      'title' => 'Behat test image',
    );
    return $return;
  }

}
