<?php

namespace Drupal\search_api\Entity;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\ProcessorInterface;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultSetInterface;
use Drupal\search_api\SearchApiException;
use Drupal\search_api\ServerInterface;
use Drupal\search_api\Tracker\TrackerInterface;
use Drupal\search_api\Utility\Utility;
use Drupal\user\TempStoreException;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Defines the search index configuration entity.
 *
 * @ConfigEntityType(
 *   id = "search_api_index",
 *   label = @Translation("Search index"),
 *   label_singular = @Translation("search index"),
 *   label_plural = @Translation("search indexes"),
 *   label_count = @PluralTranslation(
 *     singular = "@count search index",
 *     plural = "@count search indexes",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *     "list_builder" = "Drupal\search_api\IndexListBuilder",
 *     "form" = {
 *       "default" = "Drupal\search_api\Form\IndexForm",
 *       "edit" = "Drupal\search_api\Form\IndexForm",
 *       "fields" = "Drupal\search_api\Form\IndexFieldsForm",
 *       "add_fields" = "Drupal\search_api\Form\IndexAddFieldsForm",
 *       "field_config" = "Drupal\search_api\Form\FieldConfigurationForm",
 *       "break_lock" = "Drupal\search_api\Form\IndexBreakLockForm",
 *       "processors" = "Drupal\search_api\Form\IndexProcessorsForm",
 *       "delete" = "Drupal\search_api\Form\IndexDeleteConfirmForm",
 *       "disable" = "Drupal\search_api\Form\IndexDisableConfirmForm",
 *       "reindex" = "Drupal\search_api\Form\IndexReindexConfirmForm",
 *       "clear" = "Drupal\search_api\Form\IndexClearConfirmForm",
 *     },
 *   },
 *   admin_permission = "administer search_api",
 *   config_prefix = "index",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   config_export = {
 *     "id",
 *     "name",
 *     "description",
 *     "read_only",
 *     "field_settings",
 *     "processor_settings",
 *     "options",
 *     "datasource_settings",
 *     "tracker_settings",
 *     "server",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/search/search-api/index/{search_api_index}",
 *     "add-form" = "/admin/config/search/search-api/add-index",
 *     "edit-form" = "/admin/config/search/search-api/index/{search_api_index}/edit",
 *     "fields" = "/admin/config/search/search-api/index/{search_api_index}/fields",
 *     "add-fields" = "/admin/config/search/search-api/index/{search_api_index}/fields/add",
 *     "break-lock-form" = "/admin/config/search/search-api/index/{search_api_index}/fields/break-lock",
 *     "processors" = "/admin/config/search/search-api/index/{search_api_index}/processors",
 *     "delete-form" = "/admin/config/search/search-api/index/{search_api_index}/delete",
 *     "disable" = "/admin/config/search/search-api/index/{search_api_index}/disable",
 *     "enable" = "/admin/config/search/search-api/index/{search_api_index}/enable",
 *   }
 * )
 */
class Index extends ConfigEntityBase implements IndexInterface {

  /**
   * The ID of the index.
   *
   * @var string
   */
  protected $id;

  /**
   * A name to be displayed for the index.
   *
   * @var string
   */
  protected $name;

  /**
   * A string describing the index.
   *
   * @var string
   */
  protected $description;

  /**
   * A flag indicating whether to write to this index.
   *
   * @var bool
   */
  protected $read_only = FALSE;

  /**
   * An array of field settings.
   *
   * @var array
   */
  protected $field_settings = array();

  /**
   * An array of field instances.
   *
   * In the ::preSave method we're saving the contents of these back into the
   * $field_settings array. When adding, removing or changing configuration we
   * should always use these.
   *
   * @var \Drupal\search_api\Item\FieldInterface[]|null
   */
  protected $fieldInstances;

  /**
   * An array of options configuring this index.
   *
   * @var array
   *
   * @see getOptions()
   */
  protected $options = array();

  /**
   * The settings of the datasources selected for this index.
   *
   * The array has the following structure:
   *
   * @code
   * array(
   *   'DATASOURCE_ID' => array(
   *     'plugin_id' => 'DATASOURCE_ID',
   *     'settings' => array(),
   *   ),
   *   …
   * )
   * @endcode
   *
   * @var array
   */
  protected $datasource_settings = array();

  /**
   * The instantiated datasource plugins.
   *
   * In the ::preSave method we're saving the contents of these back into the
   * $datasource_settings array. When adding, removing or changing configuration
   * we should therefore always manipulate this property instead of the stored
   * one.
   *
   * @var \Drupal\search_api\Datasource\DatasourceInterface[]|null
   *
   * @see getDatasources()
   */
  protected $datasourceInstances;

  /**
   * The tracker settings.
   *
   * The array has the following structure:
   *
   * @code
   * array(
   *   'TRACKER_ID' => array(
   *     'plugin_id' => 'TRACKER_ID',
   *     'settings' => array(),
   *   ),
   * )
   * @endcode
   *
   * There is always just a single entry in the array.
   *
   * @var array
   */
  protected $tracker_settings = NULL;

  /**
   * The tracker plugin instance.
   *
   * In the ::preSave method we're saving the contents of these back into the
   * $tracker_settings array. When adding, removing or changing configuration
   * we should therefore always manipulate this property instead of the stored
   * one.
   *
   * @var \Drupal\search_api\Tracker\TrackerInterface|null
   *
   * @see getTrackerInstance()
   */
  protected $trackerInstance;

  /**
   * The ID of the server on which data should be indexed.
   *
   * @var string|null
   */
  protected $server;

  /**
   * The server entity belonging to this index.
   *
   * @var \Drupal\search_api\ServerInterface
   *
   * @see getServerInstance()
   */
  protected $serverInstance;

  /**
   * The array of processor settings.
   *
   * The array has the following structure:
   *
   * @code
   * array(
   *   'PROCESSOR_ID' => array(
   *     'plugin_id' => 'PROCESSOR_ID',
   *     'settings' => array(
   *       'weights' => array(),
   *       …
   *     ),
   *   ),
   *   …
   * )
   * @endcode
   *
   * @var array
   */
  protected $processor_settings = array();

  /**
   * Instances of the processor plugins.
   *
   * In the ::preSave method we're saving the contents of these back into the
   * $tracker_settings array. When adding, removing or changing configuration
   * we should therefore always manipulate this property instead of the stored
   * one.
   *
   * @var \Drupal\search_api\Processor\ProcessorInterface[]|null
   */
  protected $processorInstances;

  /**
   * Whether reindexing has been triggered for this index in this page request.
   *
   * @var bool
   */
  protected $hasReindexed = FALSE;

  /**
   * The number of currently active "batch tracking" modes.
   *
   * @var int
   */
  protected $batchTracking = 0;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function isReadOnly() {
    return $this->read_only;
  }

  /**
   * {@inheritdoc}
   */
  public function getOption($name, $default = NULL) {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function setOption($name, $option) {
    $this->options[$name] = $option;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    $this->options = $options;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function createPlugin($type, $plugin_id, $configuration = array()) {
    try {
      /** @var \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager */
      $plugin_manager = \Drupal::getContainer()
        ->get("plugin.manager.search_api.$type");
      $configuration['#index'] = $this;
      return $plugin_manager->createInstance($plugin_id, $configuration);
    }
    catch (ServiceNotFoundException $e) {
      throw new SearchApiException("Unknown plugin type '$type'");
    }
    catch (PluginException $e) {
      throw new SearchApiException("Unknown $type plugin with ID '$plugin_id'");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createPlugins($type, array $plugin_ids = NULL, $configurations = array()) {
    if ($plugin_ids === NULL) {
      try {
        /** @var \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager */
        $plugin_manager = \Drupal::getContainer()
          ->get("plugin.manager.search_api.$type");
        $plugin_ids = array_keys($plugin_manager->getDefinitions());
      }
      catch (ServiceNotFoundException $e) {
        throw new SearchApiException("Unknown plugin type '$type'");
      }
    }

    $plugins = array();
    foreach ($plugin_ids as $plugin_id) {
      $configuration = array();
      if (isset($configurations[$plugin_id])) {
        $configuration = $configurations[$plugin_id];
      }
      elseif (isset($this->{"{$type}_settings"}[$plugin_id])) {
        $configuration = $this->{"{$type}_settings"}[$plugin_id]['settings'];
      }
      $plugins[$plugin_id] = $this->createPlugin($type, $plugin_id, $configuration);
    }

    return $plugins;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasources() {
    if ($this->datasourceInstances === NULL) {
      $this->datasourceInstances = $this->createPlugins('datasource', array_keys($this->datasource_settings));
    }

    return $this->datasourceInstances;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasourceIds() {
    return array_keys($this->getDatasources());
  }

  /**
   * {@inheritdoc}
   */
  public function isValidDatasource($datasource_id) {
    $datasources = $this->getDatasources();
    return !empty($datasources[$datasource_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasource($datasource_id) {
    $datasources = $this->getDatasources();

    if (empty($datasources[$datasource_id])) {
      $index_label = $this->label();
      throw new SearchApiException("The datasource with ID '$datasource_id' could not be retrieved for index '$index_label'.");
    }

    return $datasources[$datasource_id];
  }

  /**
   * {@inheritdoc}
   */
  public function addDatasource(DatasourceInterface $datasource) {
    // Make sure the datasourceInstances are loaded before trying to add a plugin
    // to them.
    if ($this->datasourceInstances === NULL) {
      $this->getDatasources();
    }
    $this->datasourceInstances[$datasource->getPluginId()] = $datasource;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeDatasource($datasource_id) {
    // Make sure the datasourceInstances are loaded before trying to remove a
    // plugin from them.
    if ($this->datasourceInstances === NULL) {
      $this->getDatasources();
    }
    unset($this->datasourceInstances[$datasource_id]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setDatasources(array $datasources = NULL) {
    $this->datasourceInstances = $datasources;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasValidTracker() {
    return (bool) \Drupal::getContainer()
      ->get('plugin.manager.search_api.tracker')
      ->getDefinition($this->getTrackerId(), FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getTrackerId() {
    if ($this->trackerInstance) {
      return $this->trackerInstance->getPluginId();
    }
    if (empty($this->tracker_settings)) {
      return \Drupal::config('search_api.settings')->get('default_tracker');
    }
    reset($this->tracker_settings);
    return key($this->tracker_settings);
  }

  /**
   * {@inheritdoc}
   */
  public function getTrackerInstance() {
    if (!$this->trackerInstance) {
      $tracker_id = $this->getTrackerId();

      $configuration = array();
      if (!empty($this->tracker_settings[$tracker_id]['settings'])) {
        $configuration = $this->tracker_settings[$tracker_id]['settings'];
      }

      $this->trackerInstance = $this->createPlugin('tracker', $tracker_id, $configuration);
    }

    return $this->trackerInstance;
  }

  /**
   * {@inheritdoc}
   */
  public function setTracker(TrackerInterface $tracker) {
    $this->trackerInstance = $tracker;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasValidServer() {
    return $this->serverInstance
        || ($this->server !== NULL && Server::load($this->server));
  }

  /**
   * {@inheritdoc}
   */
  public function isServerEnabled() {
    return $this->hasValidServer() && $this->getServerInstance()->status();
  }

  /**
   * {@inheritdoc}
   */
  public function getServerId() {
    return $this->server;
  }

  /**
   * {@inheritdoc}
   */
  public function getServerInstance() {
    if (!$this->serverInstance && $this->server) {
      $this->serverInstance = Server::load($this->server);
      if (!$this->serverInstance) {
        $index_label = $this->label();
        throw new SearchApiException("The server with ID '$this->server' could not be retrieved for index '$index_label'.");
      }
    }

    return $this->serverInstance;
  }

  /**
   * {@inheritdoc}
   */
  public function setServer(ServerInterface $server = NULL) {
    $this->serverInstance = $server;
    $this->server = $server ? $server->id() : NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessors() {
    if ($this->processorInstances !== NULL) {
      return $this->processorInstances;
    }

    // Filter the processors to only include those that are enabled (or locked).
    // We should only reach this point in the code once, at the first call after
    // the index is loaded.
    $this->processorInstances = array();
    foreach ($this->createPlugins('processor') as $processor_id => $processor) {
      if (isset($this->processor_settings[$processor_id]) || $processor->isLocked()) {
        $this->processorInstances[$processor_id] = $processor;
      }
    }

    return $this->processorInstances;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessorsByStage($stage) {
    // Get a list of all processors meeting the criteria (stage and, optionally,
    // enabled) along with their effective weights (user-set or default).
    $processors = $this->getProcessors();
    $processor_weights = array();
    foreach ($processors as $name => $processor) {
      if ($processor->supportsStage($stage)) {
        $processor_weights[$name] = $processor->getWeight($stage);
      }
    }

    // Sort requested processors by weight.
    asort($processor_weights);

    $return_processors = array();
    foreach ($processor_weights as $name => $weight) {
      $return_processors[$name] = $processors[$name];
    }

    return $return_processors;
  }

  /**
   * {@inheritdoc}
   */
  public function isValidProcessor($processor_id) {
    $processors = $this->getProcessors();
    return !empty($processors[$processor_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessor($processor_id) {
    $processors = $this->getProcessors();

    if (empty($processors[$processor_id])) {
      $index_label = $this->label();
      throw new SearchApiException("The processor with ID '$processor_id' could not be retrieved for index '$index_label'.");
    }

    return $processors[$processor_id];
  }

  /**
   * {@inheritdoc}
   */
  public function addProcessor(ProcessorInterface $processor) {
    // Make sure the processorInstances are loaded before trying to add a plugin
    // to them.
    if ($this->processorInstances === NULL) {
      $this->getProcessors();
    }
    $this->processorInstances[$processor->getPluginId()] = $processor;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeProcessor($processor_id) {
    // Make sure the processorInstances are loaded before trying to remove a
    // plugin from them.
    if ($this->processorInstances === NULL) {
      $this->getProcessors();
    }
    unset($this->processorInstances[$processor_id]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setProcessors(array $processors) {
    $this->processorInstances = $processors;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {
    foreach ($this->getProcessorsByStage(ProcessorInterface::STAGE_ALTER_ITEMS) as $processor) {
      $processor->alterIndexedItems($items);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessIndexItems(array $items) {
    foreach ($this->getProcessorsByStage(ProcessorInterface::STAGE_PREPROCESS_INDEX) as $processor) {
      $processor->preprocessIndexItems($items);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessSearchQuery(QueryInterface $query) {
    foreach ($this->getProcessorsByStage(ProcessorInterface::STAGE_PREPROCESS_QUERY) as $processor) {
      $processor->preprocessSearchQuery($query);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postprocessSearchResults(ResultSetInterface $results) {
    foreach ($this->getProcessorsByStage(ProcessorInterface::STAGE_POSTPROCESS_QUERY) as $processor) {
      $processor->postprocessSearchResults($results);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addField(FieldInterface $field) {
    $field_id = $field->getFieldIdentifier();
    if (Utility::isFieldIdReserved($field_id)) {
      throw new SearchApiException("'$field_id' is a reserved value and cannot be used as the machine name of a normal field.");
    }

    $old_field = $this->getField($field_id);
    if ($old_field && $old_field != $field) {
      throw new SearchApiException("Cannot add field with machine name '$field_id': machine name is already taken.");
    }

    $this->fieldInstances[$field_id] = $field;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function renameField($old_field_id, $new_field_id) {
    if (!isset($this->getFields()[$old_field_id])) {
      throw new SearchApiException("Could not rename field with machine name '$old_field_id': no such field.");
    }
    if (Utility::isFieldIdReserved($new_field_id)) {
      throw new SearchApiException("'$new_field_id' is a reserved value and cannot be used as the machine name of a normal field.");
    }
    if (isset($this->getFields()[$new_field_id])) {
      throw new SearchApiException("'$new_field_id' already exists and can't be used as a new field id.");
    }

    $this->fieldInstances[$new_field_id] = $this->fieldInstances[$old_field_id];
    unset($this->fieldInstances[$old_field_id]);
    $this->fieldInstances[$new_field_id]->setFieldIdentifier($new_field_id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeField($field_id) {
    $field = $this->getField($field_id);
    if (!$field) {
      return $this;
    }
    if ($field->isIndexedLocked()) {
      throw new SearchApiException("Cannot remove field with machine name '$field_id': field is locked.");
    }

    unset($this->fieldInstances[$field_id]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setFields(array $fields) {
    $this->fieldInstances = $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getFields($include_server_defined = FALSE) {
    if (!isset($this->fieldInstances)) {
      $this->fieldInstances = array();
      foreach ($this->field_settings as $key => $field_info) {
        $this->fieldInstances[$key] = Utility::createField($this, $key, $field_info);
      }
    }

    $fields = $this->fieldInstances;
    if ($include_server_defined && $this->hasValidServer()) {
      $fields += $this->getServerInstance()->getBackendDefinedFields($this);
    }
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getField($field_id) {
    $fields = $this->getFields();
    return isset($fields[$field_id]) ? $fields[$field_id] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldsByDatasource($datasource_id) {
    $datasource_fields = array_fill_keys(array_keys($this->getDatasources()), array());
    $datasource_fields[NULL] = array();
    foreach ($this->getFields() as $field_id => $field) {
      $datasource_fields[$field->getDatasourceId()][$field_id] = $field;
    }

    return $datasource_fields[$datasource_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getFulltextFields() {
    $fulltext_fields = array();
    foreach ($this->getFields() as $key => $field) {
      if (Utility::isTextType($field->getType())) {
        $fulltext_fields[] = $key;
      }
    }
    return $fulltext_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldRenames() {
    $renames = array();
    foreach ($this->getFields() as $field_id => $field) {
      if ($field->getOriginalFieldIdentifier() != $field_id) {
        $renames[$field->getOriginalFieldIdentifier()] = $field_id;
      }
    }
    return $renames;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions($datasource_id) {
    if (isset($datasource_id)) {
      $datasource = $this->getDatasource($datasource_id);
      $properties = $datasource->getPropertyDefinitions();
    }
    else {
      $datasource = NULL;
      $properties = array();
    }

    foreach ($this->getProcessorsByStage(ProcessorInterface::STAGE_ADD_PROPERTIES) as $processor) {
      $properties += $processor->getPropertyDefinitions($datasource);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function loadItem($item_id) {
    $items = $this->loadItemsMultiple(array($item_id));
    return $items ? reset($items) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadItemsMultiple(array $item_ids) {
    // Group the requested items by datasource. This will also later be used to
    // determine whether all items were loaded successfully.
    $items_by_datasource = array();
    foreach ($item_ids as $item_id) {
      list($datasource_id, $raw_id) = Utility::splitCombinedId($item_id);
      $items_by_datasource[$datasource_id][$raw_id] = $item_id;
    }

    // Load the items from the datasources and keep track of which were
    // successfully retrieved.
    $items = array();
    foreach ($items_by_datasource as $datasource_id => $raw_ids) {
      try {
        $datasource = $this->getDatasource($datasource_id);
        $datasource_items = $datasource->loadMultiple(array_keys($raw_ids));
        foreach ($datasource_items as $raw_id => $item) {
          $id = $raw_ids[$raw_id];
          $items[$id] = $item;
          // Remember that we successfully loaded this item.
          unset($items_by_datasource[$datasource_id][$raw_id]);
        }
      }
      catch (SearchApiException $e) {
        watchdog_exception('search_api', $e);
        // If the complete datasource could not be loaded, don't report all its
        // individual requested items as missing.
        unset($items_by_datasource[$datasource_id]);
      }
    }

    // Check whether there are requested items that couldn't be loaded.
    $items_by_datasource = array_filter($items_by_datasource);
    if ($items_by_datasource) {
      // Extract the second-level values of the two-dimensional array (that is,
      // the combined item IDs) and log a warning reporting their absence.
      $missing_ids = array_reduce(array_map('array_values', $items_by_datasource), 'array_merge', array());
      $args['%index'] = $this->label();
      $args['@items'] = '"' . implode('", "', $missing_ids) . '"';
      \Drupal::service('logger.channel.search_api')
        ->warning('Could not load the following items on index %index: @items.', $args);
      // Also remove those items from tracking so we don't keep trying to load
      // them.
      foreach ($items_by_datasource as $datasource_id => $raw_ids) {
        $this->trackItemsDeleted($datasource_id, array_keys($raw_ids));
      }
    }

    // Return the loaded items.
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function indexItems($limit = '-1', $datasource_id = NULL) {
    if ($this->hasValidTracker() && !$this->isReadOnly()) {
      $tracker = $this->getTrackerInstance();
      $next_set = $tracker->getRemainingItems($limit, $datasource_id);
      $items = $this->loadItemsMultiple($next_set);
      if ($items) {
        try {
          return count($this->indexSpecificItems($items));
        }
        catch (SearchApiException $e) {
          $variables['%index'] = $this->label();
          watchdog_exception('search_api', $e, '%type while trying to index items on index %index: @message in %function (line %line of %file)', $variables);
        }
      }
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function indexSpecificItems(array $search_objects) {
    if (!$search_objects || $this->read_only) {
      return array();
    }
    if (!$this->status) {
      $index_label = $this->label();
      throw new SearchApiException("Couldn't index values on index '$index_label' (index is disabled)");
    }

    /** @var \Drupal\search_api\Item\ItemInterface[] $items */
    $items = array();
    foreach ($search_objects as $item_id => $object) {
      $items[$item_id] = Utility::createItemFromObject($this, $object, $item_id);
    }

    // Remember the items that were initially passed, to be able to determine
    // the items rejected by alter hooks and processors afterwards.
    $rejected_ids = array_keys($items);
    $rejected_ids = array_combine($rejected_ids, $rejected_ids);

    // Preprocess the indexed items.
    $this->alterIndexedItems($items);
    \Drupal::moduleHandler()->alter('search_api_index_items', $this, $items);
    foreach ($items as $item) {
      // This will cache the extracted fields so processors, etc., can retrieve
      // them directly.
      $item->getFields();
    }
    $this->preprocessIndexItems($items);

    // Remove all items still in $items from $rejected_ids. Thus, only the
    // rejected items' IDs are still contained in $ret, to later be returned
    // along with the successfully indexed ones.
    foreach ($items as $item_id => $item) {
      unset($rejected_ids[$item_id]);
    }

    // Items that are rejected should also be deleted from the server.
    if ($rejected_ids) {
      $this->getServerInstance()->deleteItems($this, $rejected_ids);
    }

    $indexed_ids = array();
    if ($items) {
      $indexed_ids = $this->getServerInstance()->indexItems($this, $items);
    }

    // Return the IDs of all items that were either successfully indexed or
    // rejected before being handed to the server.
    $processed_ids = array_merge(array_values($rejected_ids), array_values($indexed_ids));

    if ($processed_ids) {
      if ($this->hasValidTracker()) {
        $this->getTrackerInstance()->trackItemsIndexed($processed_ids);
      }
      // Since we've indexed items now, triggering reindexing would have some
      // effect again. Therefore, we reset the flag.
      $this->hasReindexed = FALSE;
      \Drupal::moduleHandler()->invokeAll('search_api_items_indexed', array($this, $processed_ids));
    }

    return $processed_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function isBatchTracking() {
    return (bool) $this->batchTracking;
  }

  /**
   * {@inheritdoc}
   */
  public function startBatchTracking() {
    $this->batchTracking++;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function stopBatchTracking() {
    if (!$this->batchTracking) {
      throw new SearchApiException('Trying to leave "batch tracking" mode on index "' . $this->label() . '" which was not entered first.');
    }
    $this->batchTracking--;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function trackItemsInserted($datasource_id, array $ids) {
    $this->trackItemsInsertedOrUpdated($datasource_id, $ids, __FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function trackItemsUpdated($datasource_id, array $ids) {
    $this->trackItemsInsertedOrUpdated($datasource_id, $ids, __FUNCTION__);
  }

  /**
   * Tracks insertion or updating of items.
   *
   * Used as a helper method in trackItemsInserted() and trackItemsUpdated() to
   * avoid code duplication.
   *
   * @param string $datasource_id
   *   The ID of the datasource to which the items belong.
   * @param array $ids
   *   An array of datasource-specific item IDs.
   * @param string $tracker_method
   *   The method to call on the tracker. Must be either "trackItemsInserted" or
   *   "trackItemsUpdated".
   */
  protected function trackItemsInsertedOrUpdated($datasource_id, array $ids, $tracker_method) {
    if ($this->hasValidTracker() && $this->status()) {
      $item_ids = array();
      foreach ($ids as $id) {
        $item_ids[] = Utility::createCombinedId($datasource_id, $id);
      }
      $this->getTrackerInstance()->$tracker_method($item_ids);
      if (!$this->isReadOnly() && $this->getOption('index_directly') && !$this->batchTracking) {
        try {
          $items = $this->loadItemsMultiple($item_ids);
          if ($items) {
            $this->indexSpecificItems($items);
          }
        }
        catch (SearchApiException $e) {
          watchdog_exception('search_api', $e);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function trackItemsDeleted($datasource_id, array $ids) {
    if (!$this->status()) {
      return;
    }

    $item_ids = array();
    foreach ($ids as $id) {
      $item_ids[] = Utility::createCombinedId($datasource_id, $id);
    }
    if ($this->hasValidTracker()) {
      $this->getTrackerInstance()->trackItemsDeleted($item_ids);
    }
    if (!$this->isReadOnly() && $this->hasValidServer()) {
      $this->getServerInstance()->deleteItems($this, $item_ids);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function reindex() {
    if ($this->status() && !$this->hasReindexed) {
      $this->hasReindexed = TRUE;
      $this->getTrackerInstance()->trackAllItemsUpdated();
      \Drupal::moduleHandler()->invokeAll('search_api_index_reindex', array($this, FALSE));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    if ($this->status()) {
      // Only invoke the hook if we actually did something.
      $invoke_hook = FALSE;
      if (!$this->hasReindexed) {
        $invoke_hook = TRUE;
        $this->hasReindexed = TRUE;
        $this->getTrackerInstance()->trackAllItemsUpdated();
      }
      if (!$this->isReadOnly()) {
        $invoke_hook = TRUE;
        $this->getServerInstance()->deleteAllIndexItems($this);
      }
      if ($invoke_hook) {
        \Drupal::moduleHandler()->invokeAll('search_api_index_reindex', array($this, !$this->isReadOnly()));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isReindexing() {
    return $this->hasReindexed;
  }

  /**
   * {@inheritdoc}
   */
  public function query(array $options = array()) {
    if (!$this->status()) {
      throw new SearchApiException('Cannot search on a disabled index.');
    }
    return Utility::createQuery($this, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function postCreate(EntityStorageInterface $storage) {
    parent::postCreate($storage);

    // Merge in default options.
    $config = \Drupal::config('search_api.settings');
    $this->options += array(
      'cron_limit' => $config->get('default_cron_limit'),
      'index_directly' => TRUE,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    // Prevent enabling of indexes when the server is disabled.
    if ($this->status() && !$this->isServerEnabled()) {
      $this->disable();
    }

    // Merge in default options.
    $config = \Drupal::config('search_api.settings');
    $this->options += array(
      'cron_limit' => $config->get('default_cron_limit'),
      'index_directly' => TRUE,
    );

    foreach ($this->getFields() as $field_id => $field) {
      // Remove all "locked" and "hidden" flags from all fields of the index. If
      // they are still valid, they should be re-added by the processors.
      $field->setIndexedLocked(FALSE);
      $field->setTypeLocked(FALSE);
      $field->setHidden(FALSE);

      // Also check whether the underlying property actually (still) exists.
      $datasource_id = $field->getDatasourceId();
      if (!isset($properties[$datasource_id])) {
        if ($datasource_id === NULL || $this->isValidDatasource($datasource_id)) {
          $properties[$datasource_id] = $this->getPropertyDefinitions($datasource_id);
        }
        else {
          $properties[$datasource_id] = array();
        }
      }
      if (!Utility::retrieveNestedProperty($properties[$datasource_id], $field->getPropertyPath())) {
        $this->removeField($field_id);
      }
    }

    // Call the preIndexSave() method of all applicable processors.
    foreach ($this->getProcessorsByStage(ProcessorInterface::STAGE_PRE_INDEX_SAVE) as $processor) {
      $processor->preIndexSave();
    }

    // Calculate field dependencies and save field settings containing them.
    $fields = $this->getFields();
    $field_dependencies = $this->getFieldDependencies();
    $field_dependencies += array_fill_keys(array_keys($fields), array());
    $this->field_settings = array();
    foreach ($this->getFields() as $field_id => $field) {
      $field->setDependencies($field_dependencies[$field_id]);
      $this->field_settings[$field_id] = $field->getSettings();
    }

    // Write the enabled processors to the settings property.
    $processors = $this->getProcessors();
    $this->processor_settings = array();
    foreach ($processors as $processor_id => $processor) {
      $this->processor_settings[$processor_id] = array(
        'plugin_id' => $processor_id,
        'settings' => $processor->getConfiguration(),
      );
    }

    // Write the tracker configuration to the settings property.
    $tracker = $this->getTrackerInstance();
    $tracker_id = $tracker->getPluginId();
    $this->tracker_settings = array(
      $tracker_id => array(
        'plugin_id' => $tracker_id,
        'settings' => $tracker->getConfiguration(),
      ),
    );

    // Write the enabled datasources to the settings array.
    $this->datasource_settings = array();
    foreach ($this->getDatasources() as $plugin_id => $datasource) {
      $this->datasource_settings[$plugin_id] = array(
        'plugin_id' => $plugin_id,
        'settings' => $datasource->getConfiguration(),
      );
    }

    // Since we change dependency-relevant data in this method, we can only call
    // the parent method at the end (or we'd need to re-calculate the
    // dependencies).
    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    try {
      // Fake an original for inserts to make code cleaner.
      /** @var \Drupal\search_api\IndexInterface $original */
      $original = $update ? $this->original : static::create(array('status' => FALSE));
      $index_task_manager = \Drupal::getContainer()
        ->get('search_api.index_task_manager');

      if ($this->status() && $original->status()) {
        // React on possible changes that would require re-indexing, etc.
        $this->reactToServerSwitch($original);
        $this->reactToDatasourceSwitch($original);
        $this->reactToTrackerSwitch($original);
        $this->reactToProcessorChanges($original);
      }
      elseif (!$this->status() && $original->status()) {
        if ($this->hasValidTracker()) {
          $index_task_manager->stopTracking($original);
        }
        if ($original->isServerEnabled()) {
          $original->getServerInstance()->removeIndex($original);
        }
      }
      elseif ($this->status() && !$original->status()) {
        $this->getServerInstance()->addIndex($this);
        if ($this->hasValidTracker()) {
          $index_task_manager->startTracking($this);
        }
      }

      if (!$index_task_manager->isTrackingComplete($this)) {
        // Give tests and site admins the possibility to disable the use of a
        // batch for tracking items. Also, do not use a batch if running in the
        // CLI.
        $use_batch = \Drupal::state()->get('search_api_use_tracking_batch', TRUE);
        if (!$use_batch || php_sapi_name() == 'cli') {
          $index_task_manager->addItemsAll($this);
        }
        else {
          $index_task_manager->addItemsBatch($this);
        }
      }

      if (\Drupal::moduleHandler()->moduleExists('views')) {
        Views::viewsData()->clear();
        // Remove this line when https://www.drupal.org/node/2370365 gets fixed.
        Cache::invalidateTags(array('extension:views'));
        \Drupal::cache('discovery')->delete('views:wizard');
      }

      Cache::invalidateTags($this->getCacheTags());
    }
    catch (SearchApiException $e) {
      watchdog_exception('search_api', $e);
    }

    // Reset the field instances so saved renames won't be reported anymore.
    $this->fieldInstances = NULL;
  }

  /**
   * Checks whether the index switched server and reacts accordingly.
   *
   * Used as a helper method in postSave(). Should only be called when the index
   * was enabled before the change and remained so.
   *
   * @param \Drupal\search_api\IndexInterface $original
   *   The previous version of the index.
   */
  protected function reactToServerSwitch(IndexInterface $original) {
    // Asserts that the index was enabled before saving and will still be
    // enabled afterwards. Otherwise, this method should not be called.
    assert('$this->status() && $original->status()', '::reactToServerSwitch should only be called when the index is enabled');

    if ($this->getServerId() != $original->getServerId()) {
      if ($original->hasValidServer()) {
        $original->getServerInstance()->removeIndex($this);
      }
      if ($this->hasValidServer()) {
        $this->getServerInstance()->addIndex($this);
      }
      // When the server changes we also need to trigger a reindex.
      $this->reindex();
    }
    elseif ($this->hasValidServer()) {
      // Tell the server the index configuration got updated.
      $this->getServerInstance()->updateIndex($this);
    }
  }

  /**
   * Checks whether the index's datasources changed and reacts accordingly.
   *
   * Used as a helper method in postSave(). Should only be called when the index
   * was enabled before the change and remained so.
   *
   * @param \Drupal\search_api\IndexInterface $original
   *   The previous version of the index.
   */
  protected function reactToDatasourceSwitch(IndexInterface $original) {
    // Asserts that the index was enabled before saving and will still be
    // enabled afterwards. Otherwise, this method should not be called.
    assert('$this->status() && $original->status()', '::reactToDatasourceSwitch should only be called when the index is enabled');

    $new_datasource_ids = $this->getDatasourceIds();
    $original_datasource_ids = $original->getDatasourceIds();
    if ($new_datasource_ids != $original_datasource_ids) {
      $added = array_diff($new_datasource_ids, $original_datasource_ids);
      $removed = array_diff($original_datasource_ids, $new_datasource_ids);
      $index_task_manager = \Drupal::getContainer()->get('search_api.index_task_manager');
      $index_task_manager->stopTracking($this, $removed);
      if ($this->hasValidServer()) {
        /** @var \Drupal\search_api\ServerInterface $server */
        $server = $this->getServerInstance();
        foreach ($removed as $datasource_id) {
          $server->deleteAllIndexItems($this, $datasource_id);
        }
      }
      $index_task_manager->startTracking($this, $added);
    }
  }

  /**
   * Checks whether the index switched tracker plugin and reacts accordingly.
   *
   * Used as a helper method in postSave(). Should only be called when the index
   * was enabled before the change and remained so.
   *
   * @param \Drupal\search_api\IndexInterface $original
   *   The previous version of the index.
   */
  protected function reactToTrackerSwitch(IndexInterface $original) {
    // Asserts that the index was enabled before saving and will still be
    // enabled afterwards. Otherwise, this method should not be called.
    assert('$this->status() && $original->status()', '::reactToTrackerSwitch should only be called when the index is enabled');

    if ($this->getTrackerId() != $original->getTrackerId()) {
      $index_task_manager = \Drupal::getContainer()
        ->get('search_api.index_task_manager');
      if ($original->hasValidTracker()) {
        $index_task_manager->stopTracking($original);
      }
      if ($this->hasValidTracker()) {
        $index_task_manager->startTracking($this);
      }
    }
  }

  /**
   * Reacts to changes in processor configuration.
   *
   * @param \Drupal\search_api\IndexInterface $original
   *   The previous version of the index.
   */
  protected function reactToProcessorChanges(IndexInterface $original) {
    $old_processors = $original->getProcessors();
    $new_processors = $this->getProcessors();

    $requires_reindex = FALSE;

    // Loop over all new settings and check if the processors were already set
    // in the original entity.
    foreach ($new_processors as $key => $processor) {
      // The processor is new, because it wasn't configured in the original
      // entity.
      if (!isset($old_processors[$key])) {
        if ($processor->requiresReindexing(NULL, $processor->getConfiguration())) {
          $requires_reindex = TRUE;
          break;
        }
      }
    }

    if (!$requires_reindex) {
      // Loop over all original settings and check if one of them has been
      // removed or changed.
      foreach ($old_processors as $key => $old_processor) {
        $new_processor = isset($new_processors[$key]) ? $new_processors[$key] : NULL;
        $old_config = $old_processor->getConfiguration();
        $new_config = $new_processor ? $new_processor->getConfiguration() : NULL;
        if (!$new_processor || $old_config != $new_config) {
          if ($old_processor->requiresReindexing($old_config, $new_config)) {
            $requires_reindex = TRUE;
            break;
          }
        }
      }
    }

    if ($requires_reindex) {
      $this->reindex();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    $index_task_manager = \Drupal::getContainer()
      ->get('search_api.index_task_manager');
    /** @var \Drupal\search_api\IndexInterface[] $entities */
    foreach ($entities as $index) {
      if ($index->status()) {
        $index_task_manager->stopTracking($index);
        if ($index->hasValidServer()) {
          $index->getServerInstance()->removeIndex($index);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    if (\Drupal::moduleHandler()->moduleExists('views')) {
      Views::viewsData()->clear();
      // Remove this line when https://www.drupal.org/node/2370365 gets fixed.
      Cache::invalidateTags(array('extension:views'));
      \Drupal::cache('discovery')->delete('views:wizard');
    }

    /** @var \Drupal\user\SharedTempStore $temp_store */
    $temp_store = \Drupal::service('user.shared_tempstore')->get('search_api_index');
    foreach ($entities as $entity) {
      try {
        $temp_store->delete($entity->id());
      }
      catch (TempStoreException $e) {
        // Can't really be helped, I guess. But is also very unlikely to happen.
        // Ignore it.
      }
    }
  }

  // @todo Override static load() etc. methods? Measure performance difference.

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = $this->getDependencyData();
    $this->dependencies = array_map('array_keys', $dependencies);
    return $this;
  }

  /**
   * Retrieves data about this index's dependencies.
   *
   * The return value is structured as follows:
   *
   * @code
   * array(
   *   'config' => array(
   *     'CONFIG_DEPENDENCY_KEY' => array(
   *       'always' => array(
   *         'processors' => array(
   *           'PROCESSOR_ID' => $processor,
   *         ),
   *         'datasources' => array(
   *           'DATASOURCE_ID_1' => $datasource_1,
   *           'DATASOURCE_ID_2' => $datasource_2,
   *         ),
   *       ),
   *       'optional' => array(
   *         'index' => array(
   *           'INDEX_ID' => $index,
   *         ),
   *         'tracker' => array(
   *           'TRACKER_ID' => $tracker,
   *         ),
   *       ),
   *     ),
   *   )
   * )
   * @endcode
   *
   * @return object[][][][][]
   *   An associative array containing the index's dependencies. The array is
   *   first keyed by the config dependency type ("module", "config", etc.) and
   *   then by the names of the config dependencies of that type which the index
   *   has. The values are associative arrays with up to two keys, "always" and
   *   "optional", specifying whether the dependency is a hard one by the plugin
   *   (or index) in question or potentially depending on the configuration. The
   *   values on this level are arrays with keys "index", "tracker",
   *   "datasources" and/or "processors" and values arrays of IDs mapped to
   *   their entities/plugins.
   */
  protected function getDependencyData() {
    $dependency_data = array();

    // Since calculateDependencies() will work directly on the $dependencies
    // property, we first save its original state and then restore it
    // afterwards.
    $original_dependencies = $this->dependencies;
    parent::calculateDependencies();
    foreach ($this->dependencies as $dependency_type => $list) {
      foreach ($list as $name) {
        $dependency_data[$dependency_type][$name]['always']['index'][$this->id] = $this;
      }
    }
    $this->dependencies = $original_dependencies;

    // Include the field dependencies.
    $type_dependencies = array();
    foreach ($this->getFields() as $field_id => $field) {
      foreach ($field->getDependencies() as $dependency_type => $names) {
        foreach ($names as $name) {
          $dependency_data[$dependency_type][$name]['always']['fields'][$field_id] = $field;
        }
      }

      // Also take dependencies of the field's data type plugin into account.
      // (Since data type plugins cannot have configuration, this will always be
      // the same for a certain type, so we only have to compute this once per
      // type.)
      $type = $field->getType();
      if (!isset($type_dependencies[$type])) {
        $type_dependencies[$type] = array();
        $data_type = $field->getDataTypePlugin();
        if ($data_type && !$data_type->isDefault()) {
          $definition = $data_type->getPluginDefinition();
          $type_dependencies[$type]['module'][] = $definition['provider'];
          // Plugins can declare additional dependencies in their definition.
          if (!empty($definition['config_dependencies'])) {
            $type_dependencies[$type] = NestedArray::mergeDeep(
              $type_dependencies[$type],
              $definition['config_dependencies']
            );
          }
          // If a plugin is dependent, calculate its dependencies.
          if ($data_type instanceof DependentPluginInterface) {
            $type_dependencies[$type] = NestedArray::mergeDeep(
              $type_dependencies[$type],
              $data_type->calculateDependencies()
            );
          }
        }
      }
      foreach ($type_dependencies[$type] as $dependency_type => $list) {
        foreach ($list as $name) {
          $dependency_data[$dependency_type][$name]['optional']['fields'][$field_id] = $field;
        }
      }
    }

    // The server needs special treatment, since it is a dependency of the index
    // itself, and not one of its plugins.
    if ($this->hasValidServer()) {
      $name = $this->getServerInstance()->getConfigDependencyName();
      $dependency_data['config'][$name]['optional']['index'][$this->id] = $this;
    }

    // All other plugins can be treated uniformly.
    $plugins = $this->getAllPlugins();

    foreach ($plugins as $plugin_type => $type_plugins) {
      foreach ($type_plugins as $plugin_id => $plugin) {
        // Largely copied from
        // \Drupal\Core\Plugin\PluginDependencyTrait::calculatePluginDependencies().
        $definition = $plugin->getPluginDefinition();

        // First, always depend on the module providing the plugin.
        $dependency_data['module'][$definition['provider']]['always'][$plugin_type][$plugin_id] = $plugin;

        // Plugins can declare additional dependencies in their definition.
        if (isset($definition['config_dependencies'])) {
          foreach ($definition['config_dependencies'] as $dependency_type => $list) {
            foreach ($list as $name) {
              $dependency_data[$dependency_type][$name]['always'][$plugin_type][$plugin_id] = $plugin;
            }
          }
        }

        // Finally, add the dynamically-calculated dependencies of the plugin.
        foreach ($plugin->calculateDependencies() as $dependency_type => $list) {
          foreach ($list as $name) {
            $dependency_data[$dependency_type][$name]['optional'][$plugin_type][$plugin_id] = $plugin;
          }
        }
      }
    }

    return $dependency_data;
  }

  /**
   * Retrieves information about the dependencies of the indexed fields.
   *
   * @return string[][][]
   *   An associative array containing the dependencies of the indexed fields.
   *   The array is keyed by field ID and dependency type, the values are arrays
   *   with dependency names.
   */
  protected function getFieldDependencies() {
    $field_dependencies = array();

    foreach ($this->getDatasources() as $datasource_id => $datasource) {
      $fields = array();
      foreach ($this->getFieldsByDatasource($datasource_id) as $field_id => $field) {
        $fields[$field_id] = $field->getPropertyPath();
      }
      $field_dependencies += $datasource->getFieldDependencies($fields);
    }

    return $field_dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $changed = parent::onDependencyRemoval($dependencies);

    $all_plugins = $this->getAllPlugins();
    $dependency_data = $this->getDependencyData();
    // Make sure our dependency data has the exact same keys as $dependencies,
    // to simplify the subsequent code.
    $dependencies = array_filter($dependencies);
    $dependency_data = array_intersect_key($dependency_data, $dependencies);
    $dependency_data += array_fill_keys(array_keys($dependencies), array());
    $call_on_removal = array();

    foreach ($dependencies as $dependency_type => $dependency_objects) {
      // Annoyingly, modules and theme dependencies come not keyed by dependency
      // name here, while entities do. Flip the array for modules and themes to
      // make the code simpler.
      if (in_array($dependency_type, array('module', 'theme'))) {
        $dependency_objects = array_flip($dependency_objects);
      }
      $dependency_data[$dependency_type] = array_intersect_key($dependency_data[$dependency_type], $dependency_objects);
      foreach ($dependency_data[$dependency_type] as $name => $dependency_sources) {
        // We first remove all the "hard" dependencies.
        if (!empty($dependency_sources['always'])) {
          foreach ($dependency_sources['always'] as $plugin_type => $plugins) {
            // We can hardly remove the index itself.
            if ($plugin_type == 'index') {
              continue;
            }

            // This will definitely lead to a change.
            $changed = TRUE;

            if ($plugin_type == 'fields') {
              // Remove a field from the index that is being removed from the
              // system.
              /** @var \Drupal\search_api\Item\FieldInterface $field */
              foreach ($plugins as $field_id => $field) {
                // In case the field is locked, unlock it before removing.
                if ($field->isIndexedLocked()) {
                  $field->setIndexedLocked(FALSE);
                }
                $this->removeField($field_id);
              }
            }
            else {
              // For all other types, just remove the plugin from our list.
              $all_plugins[$plugin_type] = array_diff_key($all_plugins[$plugin_type], $plugins);
            }
          }
        }

        // Then, collect all the optional ones.
        if (!empty($dependency_sources['optional'])) {
          // However this plays out, it will lead to a change.
          $changed = TRUE;

          foreach ($dependency_sources['optional'] as $plugin_type => $plugins) {
            // Deal with the index right away, since that dependency can only be
            // the server.
            if ($plugin_type == 'index') {
              $this->setServer(NULL);
              continue;
            }

            // Fields can only have optional dependencies caused by their data
            // type plugin. Reset to the fallback type.
            if ($plugin_type == 'fields') {
              foreach ($plugins as $field) {
                $field->setType($field->getDataTypePlugin()->getFallbackType());
              }
              continue;
            }

            // Only include those plugins that have not already been removed.
            $plugins = array_intersect_key($plugins, $all_plugins[$plugin_type]);

            foreach ($plugins as $plugin_id => $plugin) {
              $call_on_removal[$plugin_type][$plugin_id][$dependency_type][$name] = $dependency_objects[$name];
            }
          }
        }
      }
    }

    // Now for all plugins with optional dependencies (stored in
    // $call_on_removal, mapped to their removed dependencies) call their
    // onDependencyRemoval() methods.
    $updated_config = array();
    foreach ($call_on_removal as $plugin_type => $plugins) {
      foreach ($plugins as $plugin_id => $plugin_dependencies) {
        $removal_successful = $all_plugins[$plugin_type][$plugin_id]->onDependencyRemoval($plugin_dependencies);
        // If the plugin was successfully changed to remove the dependency,
        // remember the new configuration to later set it. Otherwise, remove the
        // plugin from the index so the dependency still gets removed.
        if ($removal_successful) {
          $updated_config[$plugin_type][$plugin_id] = $all_plugins[$plugin_type][$plugin_id]->getConfiguration();
        }
        else {
          unset($all_plugins[$plugin_type][$plugin_id]);
        }
      }
    }

    // The handling of how we translate plugin changes back to the index varies
    // according to plugin type, unfortunately.
    // First, remove plugins that need to be removed.
    $this->processor_settings = array_intersect_key($this->processor_settings, $all_plugins['processors']);
    $this->processorInstances = array_intersect_key($this->processorInstances, $all_plugins['processors']);

    $this->datasource_settings = array_intersect_key($this->datasource_settings, $all_plugins['datasources']);
    $this->datasourceInstances = array_intersect_key($this->datasourceInstances, $all_plugins['datasources']);

    // There always needs to be a tracker so reset it back to the default
    // tracker.
    if (empty($all_plugins['tracker'])) {
      $default_tracker_id = \Drupal::config('search_api.settings')
        ->get('default_tracker');

      $this->tracker_settings = array(
        $default_tracker_id => array(
          'plugin_id' => $default_tracker_id,
          'settings' => array(),
        ),
      );
      // Reset $trackerInstance so it will get newly loaded from our reset
      // settings when required.
      $this->trackerInstance = NULL;
    }

    // There also always needs to be a datasource, but here we have no easy way
    // out – if we had to remove all datasources, the operation fails. Return
    // FALSE to indicate this, which will cause the index to be deleted.
    if (!$this->datasource_settings) {
      return FALSE;
    }

    // Then, update configuration as necessary.
    foreach ($updated_config as $plugin_type => $plugin_configs) {
      foreach ($plugin_configs as $plugin_id => $plugin_config) {
        switch ($plugin_type) {
          case 'processors':
            $this->processor_settings[$plugin_id]['settings'] = $plugin_config;
            break;

          case 'datasources':
            $this->datasource_settings[$plugin_id]['settings'] = $plugin_config;
            break;

          case 'tracker':
            $this->tracker_settings[$plugin_id]['settings'] = $plugin_config;
            break;

        }
      }
    }

    return $changed;
  }

  /**
   * Retrieves all the plugins contained in this index.
   *
   * @return \Drupal\search_api\Plugin\IndexPluginInterface[][]
   *   All plugins contained in this index, keyed by their property on the index
   *   and their plugin ID.
   */
  protected function getAllPlugins() {
    $plugins = array();

    if ($this->hasValidTracker()) {
      $plugins['tracker'][$this->getTrackerId()] = $this->getTrackerInstance();
    }
    $plugins['processors'] = $this->getProcessors();
    $plugins['datasources'] = $this->getDatasources();

    return $plugins;
  }

  /**
   * Implements the magic __sleep() method.
   *
   * Prevents the instantiated plugins and fields from being serialized.
   */
  public function __sleep() {
    $properties = get_object_vars($this);
    unset($properties['datasourceInstances']);
    unset($properties['trackerInstance']);
    unset($properties['serverInstance']);
    unset($properties['processorInstances']);
    unset($properties['fieldInstances']);
    return array_keys($properties);
  }

}
