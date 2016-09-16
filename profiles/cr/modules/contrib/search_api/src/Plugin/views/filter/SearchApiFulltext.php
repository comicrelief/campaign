<?php

namespace Drupal\search_api\Plugin\views\filter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\search_api\UncacheableDependencyTrait;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a filter for adding a fulltext search to the view.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("search_api_fulltext")
 */
class SearchApiFulltext extends FilterPluginBase {

  use UncacheableDependencyTrait;
  use SearchApiFilterTrait;

  /**
   * The parse mode manager.
   *
   * @var \Drupal\search_api\ParseMode\ParseModePluginManager|null
   */
  protected $parseModeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $plugin */
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $plugin->setParseModeManager($container->get('plugin.manager.search_api.parse_mode'));

    return $plugin;
  }

  /**
   * Retrieves the parse mode manager.
   *
   * @return \Drupal\search_api\ParseMode\ParseModePluginManager
   *   The parse mode manager.
   */
  public function getParseModeManager() {
    return $this->parseModeManager ?: \Drupal::service('plugin.manager.search_api.parse_mode');
  }

  /**
   * Sets the parse mode manager.
   *
   * @param \Drupal\search_api\ParseMode\ParseModePluginManager $parse_mode_manager
   *   The new parse mode manager.
   *
   * @return $this
   */
  public function setParseModeManager(ParseModePluginManager $parse_mode_manager) {
    $this->parseModeManager = $parse_mode_manager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function operatorForm(&$form, FormStateInterface $form_state) {
    parent::operatorForm($form, $form_state);

    if (!empty($form['operator'])) {
      $form['operator']['#description'] = $this->t('Based on the parse mode set, some of these options might not work as expected. Please either use "Multiple terms" as the parse mode or make sure that the filter behaves as expected for multiple words.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function operatorOptions($which = 'title') {
    $options = array();
    foreach ($this->operators() as $id => $info) {
      $options[$id] = $info[$which];
    }

    return $options;
  }

  /**
   * Returns information about the available operators for this filter.
   *
   * @return array[]
   *   An associative array mapping operator identifiers to their information.
   *   The operator information itself is an associative array with the
   *   following keys:
   *   - title: The translated title for the operator.
   *   - short: The translated short title for the operator.
   *   - values: The number of values the operator requires as input.
   */
  public function operators() {
    return array(
      'and' => array(
        'title' => $this->t('Contains all of these words'),
        'short' => $this->t('and'),
        'values' => 1,
      ),
      'or' => array(
        'title' => $this->t('Contains any of these words'),
        'short' => $this->t('or'),
        'values' => 1,
      ),
      'not' => array(
        'title' => $this->t('Contains none of these words'),
        'short' => $this->t('not'),
        'values' => 1,
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();

    $options['parse_mode'] = array('default' => 'terms');
    $options['operator']['default'] = 'and';

    $options['min_length']['default'] = '';
    $options['fields']['default'] = array();

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['parse_mode'] = array(
      '#type' => 'select',
      '#title' => $this->t('Parse mode'),
      '#description' => $this->t('Choose how the search keys will be parsed.'),
      '#options' => $this->getParseModeManager()->getInstancesOptions(),
      '#default_value' => $this->options['parse_mode'],
    );
    foreach ($this->getParseModeManager()->getInstances() as $key => $mode) {
      if ($mode->getDescription()) {
        $states['visible'][':input[name="options[parse_mode]"]']['value'] = $key;
        $form["parse_mode_{$key}_description"] = array(
          '#type' => 'item',
          '#title' => $mode->label(),
          '#description' => $mode->getDescription(),
          '#states' => $states,
        );
      }
    }

    $fields = $this->getFulltextFields();
    if (!empty($fields)) {
      $form['fields'] = array(
        '#type' => 'select',
        '#title' => $this->t('Searched fields'),
        '#description' => $this->t('Select the fields that will be searched. If no fields are selected, all available fulltext fields will be searched.'),
        '#options' => $fields,
        '#size' => min(4, count($fields)),
        '#multiple' => TRUE,
        '#default_value' => $this->options['fields'],
      );
    }
    else {
      $form['fields'] = array(
        '#type' => 'value',
        '#value' => array(),
      );
    }
    if (isset($form['expose'])) {
      $form['expose']['#weight'] = -5;
    }

    $form['min_length'] = array(
      '#title' => $this->t('Minimum keyword length'),
      '#description' => $this->t('Minimum length of each word in the search keys. Leave empty to allow all words.'),
      '#type' => 'number',
      '#min' => 1,
      '#default_value' => $this->options['min_length'],
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    $form['value'] = array(
      '#type' => 'textfield',
      '#title' => !$form_state->get('exposed') ? $this->t('Value') : '',
      '#size' => 30,
      '#default_value' => $this->value,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateExposed(&$form, FormStateInterface $form_state) {
    // Only validate exposed input.
    if (empty($this->options['exposed']) || empty($this->options['expose']['identifier'])) {
      return;
    }

    // We only need to validate if there is a minimum word length set.
    if ($this->options['min_length'] < 2) {
      return;
    }

    $identifier = $this->options['expose']['identifier'];
    $input = &$form_state->getValue($identifier, '');

    if ($this->options['is_grouped'] && isset($this->options['group_info']['group_items'][$input])) {
      $this->operator = $this->options['group_info']['group_items'][$input]['operator'];
      $input = &$this->options['group_info']['group_items'][$input]['value'];
    }

    // If there is no input, we're fine.
    if (!trim($input)) {
      return;
    }

    $words = preg_split('/\s+/', $input);
    foreach ($words as $i => $word) {
      if (Unicode::strlen($word) < $this->options['min_length']) {
        unset($words[$i]);
      }
    }
    if (!$words) {
      $vars['@count'] = $this->options['min_length'];
      $msg = $this->t('You must include at least one positive keyword with @count characters or more.', $vars);
      $form_state->setError($form[$identifier], $msg);
    }
    $input = implode(' ', $words);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    while (is_array($this->value)) {
      $this->value = $this->value ? reset($this->value) : '';
    }
    // Catch empty strings entered by the user, but not "0".
    if ($this->value === '') {
      return;
    }
    $fields = $this->options['fields'];
    $fields = $fields ? $fields : array_keys($this->getFulltextFields());
    $query = $this->getQuery();

    if ($this->options['parse_mode']) {
      $parse_mode = $this->getParseModeManager()
        ->createInstance($this->options['parse_mode']);
      $query->setParseMode($parse_mode);
    }

    // If something already specifically set different fields, we silently fall
    // back to mere filtering.
    $old = $query->getFulltextFields();
    $use_conditions = $old && (array_diff($old, $fields) || array_diff($fields, $old));

    if ($use_conditions) {
      $conditions = $query->createConditionGroup('OR');
      $op = $this->operator === 'not' ? '<>' : '=';
      foreach ($fields as $field) {
        $conditions->addCondition($field, $this->value, $op);
      }
      $query->addConditionGroup($conditions);
      return;
    }

    // If the operator was set to OR or NOT, set OR as the conjunction. It is
    // also set for NOT since otherwise it would be "not all of these words".
    if ($this->operator != 'and') {
      $query->getParseMode()->setConjunction('OR');
    }

    $query->setFulltextFields($fields);
    $old = $query->getKeys();
    $old_original = $query->getOriginalKeys();
    $query->keys($this->value);
    if ($this->operator == 'not') {
      $keys = &$query->getKeys();
      if (is_array($keys)) {
        $keys['#negation'] = TRUE;
      }
      else {
        // We can't know how negation is expressed in the server's syntax.
      }
    }

    // If there were fulltext keys set, we take care to combine them in a
    // meaningful way (especially with negated keys).
    if ($old) {
      $keys = &$query->getKeys();
      // Array-valued keys are combined.
      if (is_array($keys)) {
        // If the old keys weren't parsed into an array, we instead have to
        // combine the original keys.
        if (is_scalar($old)) {
          $keys = "($old) ({$this->value})";
        }
        else {
          // If the conjunction or negation settings aren't the same, we have to
          // nest both old and new keys array.
          if (!empty($keys['#negation']) != !empty($old['#negation']) || $keys['#conjunction'] != $old['#conjunction']) {
            $keys = array(
              '#conjunction' => 'AND',
              $old,
              $keys,
            );
          }
          // Otherwise, just add all individual words from the old keys to the
          // new ones.
          else {
            foreach (Element::children($old) as $i) {
              $keys[] = $old[$i];
            }
          }
        }
      }
      // If the parse mode was "direct" for both old and new keys, we
      // concatenate them and set them both via method and reference (to also
      // update the originalKeys property.
      elseif (is_scalar($old_original)) {
        $combined_keys = "($old_original) ($keys)";
        $query->keys($combined_keys);
        $keys = $combined_keys;
      }
    }
  }

  /**
   * Retrieves a list of all available fulltext fields.
   *
   * @return string[]
   *   An options list of fulltext field identifiers mapped to their prefixed
   *   labels.
   */
  protected function getFulltextFields() {
    $fields = array();
    /** @var \Drupal\search_api\IndexInterface $index */
    $index = Index::load(substr($this->table, 17));

    $fields_info = $index->getFields();
    foreach ($index->getFulltextFields() as $field_id) {
      $fields[$field_id] = $fields_info[$field_id]->getPrefixedLabel();
    }

    return $fields;
  }

}
