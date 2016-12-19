<?php

namespace Drupal\search_api\Tests;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Entity\Server;
use Drupal\search_api\Item\Field;
use Drupal\search_api_test\PluginTestTrait;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;

/**
 * Tests the admin UI for processors.
 *
 * @todo Move this whole class into a single IntegrationTest check*() method?
 * @todo Add tests for the "Aggregated fields" and "Role filter" processors.
 *
 * @group search_api
 */
class ProcessorIntegrationTest extends WebTestBase {

  use EntityReferenceTestTrait;
  use PluginTestTrait;
  use TaxonomyTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'filter',
    'taxonomy',
  );

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);

    $this->indexId = 'test_index';
    $index = Index::create(array(
      'name' => 'Test index',
      'id' => $this->indexId,
      'status' => 1,
      'datasource_settings' => array(
        'entity:node' => array(
          'plugin_id' => 'entity:node',
          'settings' => array(),
        ),
      ),
    ));

    // Add a node and a taxonomy term reference, both of which could be used to
    // create a hierarchy.
    $this->createEntityReferenceField(
      'node',
      'page',
      'term_field',
      NULL,
      'taxonomy_term',
      'default',
      array(),
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    );
    $this->createEntityReferenceField(
      'node',
      'page',
      'parent_reference',
      NULL,
      'node',
      'default',
      array('target_bundles' => array('page' => 'page'))
    );

    // Index the taxonomy and entity reference fields.
    $term_field = new Field($index, 'term_field');
    $term_field->setType('integer');
    $term_field->setPropertyPath('term_field');
    $term_field->setDatasourceId('entity:node');
    $term_field->setLabel('Terms');
    $index->addField($term_field);

    $parent_reference = new Field($index, 'parent_reference');
    $parent_reference->setType('integer');
    $parent_reference->setPropertyPath('parent_reference');
    $parent_reference->setDatasourceId('entity:node');
    $parent_reference->setLabel('Terms');
    $index->addField($parent_reference);
    $index->save();
  }

  /**
   * Tests the admin UI for processors.
   *
   * Calls the other test methods in this class, named check*Integration(), to
   * avoid the overhead of having one test per processor.
   */
  public function testProcessorIntegration() {
    // Some processors are always enabled.
    $enabled = array('add_url', 'aggregated_field', 'rendered_item');
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    // Ensure the hidden processors aren't displayed in the UI.
    $this->loadProcessorsTab();
    $hidden = $enabled;
    foreach ($hidden as $processor_id) {
      $this->assertNoRaw(Html::escape($processor_id), "The \"$processor_id\" processor is not displayed in the UI.");
    }

    $this->checkAggregatedFieldsIntegration();

    $this->checkContentAccessIntegration();
    $enabled[] = 'content_access';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkHighlightIntegration();
    $enabled[] = 'highlight';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkHtmlFilterIntegration();
    $enabled[] = 'html_filter';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkIgnoreCaseIntegration();
    $enabled[] = 'ignorecase';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkIgnoreCharactersIntegration();
    $enabled[] = 'ignore_character';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkNodeStatusIntegration();
    $enabled[] = 'node_status';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkRenderedItemIntegration();

    $this->checkStopWordsIntegration();
    $enabled[] = 'stopwords';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkTokenizerIntegration();
    $enabled[] = 'tokenizer';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkTransliterationIntegration();
    $enabled[] = 'transliteration';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    $this->checkAddHierarchyIntegration();
    $enabled[] = 'hierarchy';
    sort($enabled);
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);

    // The 'add_url' processor is not available to be removed because it's
    // locked.
    $this->checkUrlFieldIntegration();

    // Check whether disabling processors also works correctly.
    $this->loadProcessorsTab();
    $edit = array(
      'status[stopwords]' => FALSE,
    );
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $enabled = array_values(array_diff($enabled, array('stopwords')));
    $actual_processors = array_keys($this->loadIndex()->getProcessors());
    sort($actual_processors);
    $this->assertEqual($enabled, $actual_processors);
  }

  /**
   * Test that the "Alter processors test backend" actually alters processors.
   *
   * @see https://www.drupal.org/node/2228739
   */
  public function testLimitProcessors() {
    $this->loadProcessorsTab();
    $this->assertResponse(200);
    $this->assertText($this->t('Highlight'));
    $this->assertText($this->t('Ignore character'));
    $this->assertText($this->t('Tokenizer'));
    $this->assertText($this->t('Stopwords'));

    // Create a new server with the "search_api_test" backend.
    $server = Server::create(array(
      'id' => 'webtest_server',
      'name' => 'WebTest server',
      'description' => 'WebTest server',
      'backend' => 'search_api_test',
      'backend_config' => array(),
    ));
    $server->save();
    $processors = array(
      'highlight',
      'ignore_character',
      'tokenizer',
      'stopwords',
    );
    $this->setReturnValue('backend', 'getDiscouragedProcessors', $processors);

    // Use the newly created server.
    $settings_path = 'admin/config/search/search-api/index/' . $this->indexId . '/edit';
    $this->drupalGet($settings_path);
    $this->drupalPostForm(NULL, array('server' => 'webtest_server'), $this->t('Save'));

    // Load the processors again and check that they are not shown anymore.
    $this->loadProcessorsTab();
    $this->assertResponse(200);
    $this->assertNoText($this->t('Highlight'));
    $this->assertNoText($this->t('Ignore character'));
    $this->assertNoText($this->t('Tokenizer'));
    $this->assertNoText($this->t('Stopwords'));
  }

  /**
   * Tests the integration of the "Aggregated fields" processor.
   */
  public function checkAggregatedFieldsIntegration() {
    $index = $this->loadIndex();
    $index->removeProcessor('aggregated_field');
    $index->save();

    $this->assertTrue($this->loadIndex()->isValidProcessor('aggregated_field'), 'The "Aggregated fields" processor cannot be disabled.');

    $options['query']['datasource'] = '';
    $this->drupalGet($this->getIndexPath('fields/add'), $options);

    // See \Drupal\search_api\Tests\IntegrationTest::addField().
    $this->assertRaw('name="aggregated_field"');
    $post = '&' . $this->serializePostValues(array('aggregated_field' => $this->t('Add')));
    $this->drupalPostForm(NULL, array(), NULL, array(), array(), NULL, $post);
    $args['%label'] = $this->t('Aggregated field');
    $this->assertRaw($this->t('Field %label was added to the index.', $args));
    $this->assertUrl($this->getIndexPath('fields/aggregated_field/edit'));
    $edit = array(
      'type' => 'first',
      'fields[entity:node/title]' => 'title',
      'fields[entity:node/type]' => 'type',
      'fields[entity:node/uid]' => 'uid',
    );
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    $this->assertUrl($this->getIndexPath('fields'));
    $this->assertRaw($this->t('The field configuration was successfully saved.'));
  }

  /**
   * Tests the UI for the "Content access" processor.
   */
  public function checkContentAccessIntegration() {
    $this->enableProcessor('content_access');
  }

  /**
   * Tests the UI for the "Highlight" processor.
   */
  public function checkHighlightIntegration() {
    $configuration = array(
      'highlight' => 'never',
      'excerpt' => FALSE,
      'excerpt_length' => 128,
      'prefix' => '<em>',
      'suffix' => '</em>',
    );
    $this->editSettingsForm($configuration, 'highlight');
  }

  /**
   * Tests the UI for the "HTML filter" processor.
   */
  public function checkHtmlFilterIntegration() {
    $configuration = array(
      'tags' => <<<TAGS
h1: 4
foo bar
TAGS
    );
    $this->checkValidationError($configuration, 'html_filter', $this->t('Tags is not a valid YAML map.'));
    $configuration = array(
      'tags' => <<<TAGS
h1:
  - 1
  - 2
h2: foo
h3: -1
TAGS
    );
    $errors = array(
      $this->t("Boost value for tag @tag can't be an array.", array('@tag' => '<h1>')),
      $this->t('Boost value for tag @tag must be numeric.', array('@tag' => '<h2>')),
      $this->t('Boost value for tag @tag must be non-negative.', array('@tag' => '<h3>')),
    );
    $this->checkValidationError($configuration, 'html_filter', $errors);

    $configuration = $form_values = array(
      'title' => FALSE,
      'alt' => FALSE,
      'tags' => array(
        'h1' => 10,
      ),
    );
    $form_values['tags'] = 'h1: 10';
    $this->editSettingsForm($configuration, 'html_filter', $form_values);
  }

  /**
   * Tests the UI for the "Ignore case" processor.
   */
  public function checkIgnoreCaseIntegration() {
    $this->editSettingsForm(array(), 'ignorecase');
  }

  /**
   * Tests the UI for the "Ignore characters" processor.
   */
  public function checkIgnoreCharactersIntegration() {
    $configuration = array(
      'ignorable' => ':)',
    );
    $this->checkValidationError($configuration, 'ignore_character', $this->t('The entered text is no valid regular expression.'));

    $configuration = $form_values = array(
      'ignorable' => '[¿¡!?,.]',
      'strip' => array(
        'character_sets' => array(
          'Pc' => 'Pc',
          'Pd' => 'Pd',
          'Pe' => 'Pe',
          'Pf' => 'Pf',
          'Pi' => 'Pi',
          'Po' => 'Po',
          'Ps' => 'Ps',
          'Cc' => 'Cc',
          'Cf' => FALSE,
          'Co' => FALSE,
          'Mc' => FALSE,
          'Me' => FALSE,
          'Mn' => FALSE,
          'Sc' => FALSE,
          'Sk' => FALSE,
          'Sm' => FALSE,
          'So' => FALSE,
          'Zl' => FALSE,
          'Zp' => FALSE,
          'Zs' => FALSE,
        ),
      ),
    );
    $this->editSettingsForm($configuration, 'ignore_character', $form_values);
  }

  /**
   * Tests the UI for the "Node status" processor.
   */
  public function checkNodeStatusIntegration() {
    $this->enableProcessor('node_status');
  }

  /**
   * Tests the integration of the "Rendered item" processor.
   */
  public function checkRenderedItemIntegration() {
    $index = $this->loadIndex();
    $index->removeProcessor('rendered_item');
    $index->save();

    $this->assertTrue($this->loadIndex()->isValidProcessor('rendered_item'), 'The "Rendered item" processor cannot be disabled.');

    $options['query']['datasource'] = '';
    $this->drupalGet($this->getIndexPath('fields/add'), $options);

    // See \Drupal\search_api\Tests\IntegrationTest::addField().
    $this->assertRaw('name="rendered_item"');
    $post = '&' . $this->serializePostValues(array('rendered_item' => $this->t('Add')));
    $this->drupalPostForm(NULL, array(), NULL, array(), array(), NULL, $post);
    $args['%label'] = $this->t('Rendered HTML output');
    $this->assertRaw($this->t('Field %label was added to the index.', $args));
    $this->assertUrl($this->getIndexPath('fields/rendered_item/edit'));
    $edit = array(
      'roles[]' => array('authenticated'),
      'view_mode[entity:node][article]' => 'default',
      'view_mode[entity:node][page]' => 'teaser',
    );
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    $this->assertUrl($this->getIndexPath('fields'));
    $this->assertRaw($this->t('The field configuration was successfully saved.'));
  }

  /**
   * Tests the UI for the "Stopwords" processor.
   */
  public function checkStopWordsIntegration() {
    $configuration = array(
      'stopwords' => array('the'),
    );
    $form_values = array(
      'stopwords' => 'the',
    );
    $this->editSettingsForm($configuration, 'stopwords', $form_values);
  }

  /**
   * Tests the UI for the "Tokenizer" processor.
   */
  public function checkTokenizerIntegration() {
    $configuration = array(
      'spaces' => ':)',
    );
    $this->checkValidationError($configuration, 'tokenizer', $this->t('The entered text is no valid regular expression.'));

    $configuration = array(
      'spaces' => '',
      'overlap_cjk' => FALSE,
      'minimum_word_size' => 2,
    );
    $this->editSettingsForm($configuration, 'tokenizer');
  }

  /**
   * Tests the UI for the "Transliteration" processor.
   */
  public function checkTransliterationIntegration() {
    $this->editSettingsForm(array(), 'transliteration');
  }

  /**
   * Tests the hierarchy processor.
   */
  protected function checkAddHierarchyIntegration() {
    $configuration = array(
      'fields' => array(
        'term_field' => 'taxonomy_term-parent',
        'parent_reference' => 'node-parent_reference',
      ),
    );
    $edit = array(
      'fields' => array(
        'term_field' => array('status' => 1),
        'parent_reference' => array('status' => 1),
      ),
    );
    $this->editSettingsForm($configuration, 'hierarchy', $edit, TRUE, FALSE);
  }

  /**
   * Tests the integration of the "URL field" processor.
   */
  public function checkUrlFieldIntegration() {
    $index = $this->loadIndex();
    $index->removeProcessor('add_url');
    $index->save();

    $this->assertTrue($this->loadIndex()->isValidProcessor('add_url'), 'The "Add URL" processor cannot be disabled.');
  }

  /**
   * Tests that a processor can be enabled.
   *
   * @param string $processor_id
   *   The ID of the processor to enable.
   */
  protected function enableProcessor($processor_id) {
    $this->loadProcessorsTab();

    $edit = array(
      "status[$processor_id]" => 1,
    );
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $this->assertTrue($this->loadIndex()->isValidProcessor($processor_id), "Successfully enabled the '$processor_id' processor.'");
  }

  /**
   * Enables a processor with a given configuration.
   *
   * @param array $configuration
   *   The configuration to set for the processor.
   * @param string $processor_id
   *   The ID of the processor to edit.
   * @param array|null $form_values
   *   (optional) The processor configuration to set, as it appears in the form.
   *   Only relevant if the processor does some processing on the form values
   *   before storing them, like parsing YAML or cleaning up checkboxes values.
   *   Defaults to using $configuration as-is.
   * @param bool $enable
   *   (optional) If TRUE, explicitly enable the processor. If FALSE, it should
   *   already be enabled.
   * @param bool $unset_fields
   *   (optional) If TRUE, the "fields" property will be removed from the
   *   actual configuration prior to comparing with the given configuration.
   */
  protected function editSettingsForm(array $configuration, $processor_id, array $form_values = NULL, $enable = TRUE, $unset_fields = TRUE) {
    if (!isset($form_values)) {
      $form_values = $configuration;
    }

    $this->loadProcessorsTab();

    $edit = $this->getFormValues($form_values, "processors[$processor_id][settings]");
    if ($enable) {
      $edit["status[$processor_id]"] = 1;
    }
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    $processor = $this->loadIndex()->getProcessor($processor_id);
    $this->assertTrue($processor, "Successfully enabled the '$processor_id' processor.'");
    if ($processor) {
      $actual_configuration = $processor->getConfiguration();
      unset($actual_configuration['weights']);
      if ($unset_fields) {
        unset($actual_configuration['fields']);
      }
      $configuration += $processor->defaultConfiguration();
      $this->assertEqual($configuration, $actual_configuration, "Processor configuration for processor '$processor_id' was set correctly.");
    }
  }

  /**
   * Makes sure that the given form values will fail when submitted.
   *
   * @param array $form_values
   *   The form values, relative to the processor form.
   * @param string $processor_id
   *   The processor's ID.
   * @param string[]|string $messages
   *   Either the expected error message or an array of expected error messages.
   */
  protected function checkValidationError(array $form_values, $processor_id, $messages) {
    $this->loadProcessorsTab();

    $edit = $this->getFormValues($form_values, "processors[$processor_id][settings]");
    $edit["status[$processor_id]"] = 1;
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    if (!is_array($messages)) {
      $messages = array($messages);
    }
    foreach ($messages as $message) {
      $this->assertText($message);
    }
    $this->assertNoText($this->t('The indexing workflow was successfully edited.'));
    $this->assertNoText($this->t('No values were changed.'));

    $this->loadProcessorsTab(TRUE);
  }

  /**
   * Converts a configuration array into an array of form values.
   *
   * @param array $configuration
   *   The configuration to convert.
   * @param string $prefix
   *   The common prefix for all form values.
   *
   * @return string[]
   *   An array of form values ready for submission.
   */
  protected function getFormValues(array $configuration, $prefix) {
    $edit = array();

    foreach ($configuration as $key => $value) {
      $key = $prefix . "[$key]";
      if (is_array($value)) {
        // Handling of numerically indexed and associative arrays needs to be
        // different.
        if ($value == array_values($value)) {
          $key .= '[]';
          $edit[$key] = $value;
        }
        else {
          $edit += $this->getFormValues($value, $key);
        }
      }
      else {
        $edit[$key] = $value;
      }
    }

    return $edit;
  }

  /**
   * Loads the test index's "Processors" tab in the test browser, if necessary.
   *
   * @param bool $force
   *   (optional) If TRUE, even load the tab if we are already on it.
   */
  protected function loadProcessorsTab($force = FALSE) {
    $settings_path = 'admin/config/search/search-api/index/' . $this->indexId . '/processors';
    if ($force || $this->getAbsoluteUrl($settings_path) != $this->getUrl()) {
      $this->drupalGet($settings_path);
    }
  }

  /**
   * Loads the search index used by this test.
   *
   * @return \Drupal\search_api\IndexInterface
   *   The search index used by this test.
   */
  protected function loadIndex() {
    $index_storage = \Drupal::entityTypeManager()->getStorage('search_api_index');
    $index_storage->resetCache([$this->indexId]);

    return $index_storage->load($this->indexId);
  }

}
