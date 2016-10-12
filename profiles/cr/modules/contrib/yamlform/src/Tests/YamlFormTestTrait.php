<?php

namespace Drupal\yamlform\Tests;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\UrlHelper;
use Drupal\filter\Entity\FilterFormat;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;
use Drupal\yamlform\YamlFormInterface;

/**
 * Defines form test trait.
 */
trait YamlFormTestTrait {

  /**
   * A normal user to submit forms.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $normalUser;

  /**
   * An form administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminFormUser;

  /**
   * An form submission administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminSubmissionUser;

  /**
   * An form own access.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $ownFormUser;

  /**
   * An form any access.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $anyFormUser;

  /**
   * Basic HTML filter format.
   *
   * @var \Drupal\filter\FilterFormatInterface
   */
  protected $basicHtmlFilter;

  /**
   * Full HTML filter format.
   *
   * @var \Drupal\filter\FilterFormatInterface
   */
  protected $fullHtmlFilter;

  /**
   * Create form test users.
   */
  protected function createUsers() {
    $this->normalUser = $this->drupalCreateUser([
      'access user profiles',
      'access content',
    ]);
    $this->adminFormUser = $this->drupalCreateUser([
      'access user profiles',
      'access content',
      'administer yamlform',
      'administer blocks',
      'administer nodes',
      'administer users',
      'create yamlform',
    ]);
    $this->ownFormUser = $this->drupalCreateUser([
      'access content',
      'access yamlform overview',
      'create yamlform',
      'edit own yamlform',
      'delete own yamlform',
    ]);
    $this->anyFormUser = $this->drupalCreateUser([
      'access content',
      'access yamlform overview',
      'create yamlform',
      'edit any yamlform',
      'delete any yamlform',
    ]);
    $this->adminSubmissionUser = $this->drupalCreateUser([
      'access user profiles',
      'access content',
      'administer yamlform submission',
    ]);
  }

  /**
   * Place breadcrumb page, tasks, and actions.
   */
  protected function placeBlocks() {
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->drupalPlaceBlock('page_title_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Create basic HTML filter format.
   */
  protected function createFilters() {
    $this->basicHtmlFilter = FilterFormat::create([
      'format' => 'basic_html',
      'name' => 'Basic HTML',
      'filters' => [
        'filter_html' => [
          'status' => 1,
          'settings' => [
            'allowed_html' => '<p> <br> <strong> <a> <em>',
          ],
        ],
      ],
    ]);
    $this->basicHtmlFilter->save();

    $this->fullHtmlFilter = FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
    ]);
    $this->fullHtmlFilter->save();
  }

  /**
   * Purge all submission before the yamlform.module is uninstalled.
   */
  protected function purgeSubmissions() {
    db_query('DELETE FROM {yamlform_submission}');
  }

  /**
   * Post a new submission to a form.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   * @param array $edit
   *   Submission values.
   * @param string $submit
   *   Value of the submit button whose click is to be emulated.
   *
   * @return int
   *   The created submission's sid.
   */
  protected function postSubmission(YamlFormInterface $yamlform, array $edit = [], $submit = NULL) {
    $submit = $submit ?: t('Submit');
    $this->drupalPostForm('yamlform/' . $yamlform->id(), $edit, $submit);
    return $this->getLastSubmissionId($yamlform);
  }

  /**
   * Post a new test submission to a form.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   * @param array $edit
   *   Submission values.
   * @param string $submit
   *   Value of the submit button whose click is to be emulated.
   *
   * @return int
   *   The created test submission's sid.
   */
  protected function postSubmissionTest(YamlFormInterface $yamlform, array $edit = [], $submit = NULL) {
    $submit = $submit ?: t('Submit');
    $this->drupalPostForm('yamlform/' . $yamlform->id() . '/test', $edit, $submit);
    return $this->getLastSubmissionId($yamlform);
  }

  /**
   * Get the last submission id.
   *
   * @return int
   *   The last submission id.
   */
  protected function getLastSubmissionId($yamlform) {
    // Get submission sid.
    $url = UrlHelper::parse($this->getUrl());
    if (isset($url['query']['sid'])) {
      return $url['query']['sid'];
    }
    else {
      $entity_ids = \Drupal::entityQuery('yamlform_submission')
        ->sort('sid', 'DESC')
        ->condition('yamlform_id', $yamlform->id())
        ->execute();
      return reset($entity_ids);
    }
  }

  /**
   * Get nodes keyed by nid.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Associative array of nodes keyed by nid.
   */
  protected function getNodes() {
    if (empty($this->nodes)) {
      $this->drupalCreateContentType(['type' => 'page']);
      for ($i = 0; $i < 3; $i++) {
        $this->nodes[$i] = $this->drupalCreateNode(['type' => 'page', 'title' => 'Node ' . $i, 'status' => NODE_PUBLISHED]);
        $this->drupalGet('node/' . $this->nodes[$i]->id());
      }
    }
    return $this->nodes;
  }

  /**
   * Create a form with submissions.
   *
   * @param array|null $elements
   *   (optional) Array of elements.
   * @param array $settings
   *   (optional) Form settings.
   *
   * @return \Drupal\yamlform\YamlFormInterface
   *   A form.
   */
  protected function createYamlForm($elements = NULL, array $settings = []) {
    if ($elements === NULL) {
      $elements = [
        'first_name' => [
          '#type' => 'textfield',
          '#title' => 'First name',
        ],
        'last_name' => [
          '#type' => 'textfield',
          '#title' => 'Last name',
        ],
        'sex' => [
          '#type' => 'select',
          '#title' => 'Sex',
          '#options' => 'gender',
        ],
        'dob' => [
          '#type' => 'date',
          '#title' => 'Date of birth',
          '#format' => 'l, F j, Y',
        ],
        'node' => [
          '#type' => 'entity_autocomplete',
          '#title' => 'Favorite node',
          '#target_type' => 'node',
        ],
        'colors' => [
          '#type' => 'checkboxes',
          '#title' => 'Flag colors',
          '#options' => [
            'red' => 'Red',
            'white' => 'White',
            'blue' => 'Blue',
          ],
        ],
        'likert' => [
          '#type' => 'likert',
          '#title' => 'Likert',
          '#questions' => [
            'q1' => 'Question 1',
            'q2' => 'Question 2',
            'q3' => 'Question 3',
          ],
          '#answers' => [
            '1' => 'Answer 1',
            '2' => 'Answer 2',
            '3' => 'Answer 3',
          ],
        ],
        'address' => [
          '#type' => 'yamlform_address',
          '#title' => 'Address',
        ],
      ];
    }

    // Create new form.
    $id = $this->randomMachineName(8);
    $yamlform = YamlForm::create([
      'langcode' => 'en',
      'status' => TRUE,
      'id' => $id,
      'title' => $id,
      'elements' => Yaml::encode($elements),
      'settings' => $settings + YamlForm::getDefaultSettings(),
    ]);
    $yamlform->save();
    return $yamlform;
  }

  /**
   * Create a form with submissions.
   *
   * @return array
   *   Array containing the form and submissions.
   */
  protected function createYamlFormWithSubmissions() {
    $yamlform = $this->createYamlForm();

    $nodes = $this->getNodes();

    // Create some submissions.
    $names = [
      [
        'George',
        'Washington',
        'Male',
        '1732-02-22',
        $nodes[0],
        ['white'],
        ['q1' => 1, 'q2' => 1, 'q3' => 1],
        ['address' => '{Address}', 'city' => '{City}', 'state_province' => 'New York', 'country' => 'United States of America', 'postal_code' => '11111-1111'],
      ],
      [
        'Abraham',
        'Lincoln',
        'Male',
        '1809-02-12',
        $nodes[1],
        ['red', 'white', 'blue'],
        ['q1' => 2, 'q2' => 2, 'q3' => 2],
        ['address' => '{Address}', 'city' => '{City}', 'state_province' => 'New York', 'country' => 'United States of America', 'postal_code' => '11111-1111'],
      ],
      [
        'Hillary',
        'Clinton',
        'Female',
        '1947-10-26',
        $nodes[2],
        ['red'],
        ['q1' => 2, 'q2' => 2, 'q3' => 2],
        ['address' => '{Address}', 'city' => '{City}', 'state_province' => 'New York', 'country' => 'United States of America', 'postal_code' => '11111-1111'],
      ],
    ];
    $sids = [];
    foreach ($names as $name) {
      $edit = [
        'first_name' => $name[0],
        'last_name' => $name[1],
        'sex' => $name[2],
        'dob' => $name[3],
        'node' => $name[4]->label() . ' (' . $name[4]->id() . ')',
      ];
      foreach ($name[5] as $color) {
        $edit["colors[$color]"] = $color;
      }
      foreach ($name[6] as $question => $answer) {
        $edit["likert[$question]"] = $answer;
      }
      foreach ($name[7] as $composite_key => $composite_value) {
        $edit["address[$composite_key]"] = $composite_value;
      }
      $sids[] = $this->postSubmission($yamlform, $edit);
    }

    // Change array keys to index instead of using entity ids.
    $submissions = array_values(YamlFormSubmission::loadMultiple($sids));

    $this->assert($yamlform instanceof YamlForm, 'YamlForm was created');
    $this->assertEqual(count($submissions), 3, 'YamlFormSubmissions were created.');

    return [$yamlform, $submissions];
  }

  /**
   * Gets that last email sent during the currently running test case.
   *
   * @return array
   *   An array containing the last email message captured during the
   *   current test.
   */
  protected function getLastEmail() {
    $sent_emails = $this->drupalGetMails();
    $sent_email = end($sent_emails);
    $this->debug($sent_email);
    return $sent_email;
  }

  /****************************************************************************/
  // Debug and custom assert methods
  /****************************************************************************/

  /**
   * Passes if the substring is contained within text, fails otherwise.
   */
  protected function assertContains($haystack, $needle, $message = '', $group = 'Other') {
    if (!$message) {
      $t_args = [
        '@haystack' => Unicode::truncate($haystack, 150, TRUE, TRUE),
        '@needle' => $needle,
      ];
      $message = new FormattableMarkup('"@needle" found', $t_args);
    }
    $result = (strpos($haystack, $needle) !== FALSE);
    if (!$result) {
      $this->verbose($haystack);
    }
    return $this->assert($result, $message, $group);
  }

  /**
   * Passes if the substring is not contained within text, fails otherwise.
   */
  protected function assertNotContains($haystack, $needle, $message = '', $group = 'Other') {
    if (!$message) {
      $t_args = [
        '@haystack' => Unicode::truncate($haystack, 150, TRUE, TRUE),
        '@needle' => $needle,
      ];

      $message = new FormattableMarkup('"@needle" not found', $t_args);
    }
    $result = (strpos($haystack, $needle) === FALSE);
    if (!$result) {
      $this->verbose($haystack);
    }
    return $this->assert($result, $message, $group);
  }

  /**
   * Passes if the CSS selector IS found on the loaded page, fail otherwise.
   */
  protected function assertCssSelect($selector, $message = '') {
    $element = $this->cssSelect($selector);
    if (!$message) {
      $message = new FormattableMarkup('Found @selector', ['@selector' => $selector]);
    }
    $this->assertTrue(!empty($element), $message);
  }

  /**
   * Passes if the CSS selector IS NOT found on the loaded page, fail otherwise.
   */
  protected function assertNoCssSelect($selector, $message = '') {
    $element = $this->cssSelect($selector);
    $this->assertTrue(empty($element), $message);
  }

  /**
   * Logs verbose (debug) message in a text file.
   *
   * @param mixed $data
   *   Data to be output.
   */
  protected function debug($data) {
    $string = var_export($data, TRUE);
    $string = preg_replace('/=>\s*array\s*\(/', '=> array(', $string);
    $this->verbose('<pre>' . htmlentities($string) . '</pre>');
  }

}
