<?php

/**
 * @file
 * Contains \Drupal\pathauto\PathautoManager.
 */

namespace Drupal\pathauto;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\Token;

/**
 * Provides methods for managing pathauto aliases and related entities.
 */
class PathautoManager implements PathautoManagerInterface {

  use StringTranslationTrait;

  /**
   * Calculated settings cache.
   *
   * @todo Split this up into separate properties.
   *
   * @var array
   */
  protected $cleanStringCache = array();

  /**
   * Punctuation characters cache.
   *
   * @var array
   */
  protected $punctuationCharacters = array();

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language manager.
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Calculated patterns for entities.
   *
   * @var array
   */
  protected $patterns = array();

  /**
   * The alias cleaner.
   *
   * @var \Drupal\pathauto\AliasCleanerInterface
   */
  protected $aliasCleaner;

  /**
   * The alias storage helper.
   *
   * @var \Drupal\pathauto\AliasStorageHelperInterface
   */
  protected $aliasStorageHelper;

  /**
   * The alias uniquifier.
   *
   * @var \Drupal\pathauto\AliasUniquifierInterface
   */
  protected $aliasUniquifier;

  /**
   * The messenger service.
   *
   * @var \Drupal\pathauto\MessengerInterface
   */
  protected $messenger;

  /**
   * Creates a new Pathauto manager.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Utility\Token $token
   *   The token utility.
   * @param \Drupal\pathauto\AliasCleanerInterface $alias_cleaner
   *   The alias cleaner.
   * @param \Drupal\pathauto\AliasStorageHelperInterface $alias_storage_helper
   *   The alias storage helper.
   * @param AliasUniquifierInterface $alias_uniquifier
   *   The alias uniquifier.
   * @param MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, Token $token, AliasCleanerInterface $alias_cleaner, AliasStorageHelperInterface $alias_storage_helper, AliasUniquifierInterface $alias_uniquifier, MessengerInterface $messenger, TranslationInterface $string_translation) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->cacheBackend = $cache_backend;
    $this->moduleHandler = $module_handler;
    $this->token = $token;
    $this->aliasCleaner = $alias_cleaner;
    $this->aliasStorageHelper = $alias_storage_helper;
    $this->aliasUniquifier = $alias_uniquifier;
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function cleanString($string, array $options = array()) {
    if (empty($this->cleanStringCache)) {
      // Generate and cache variables used in this method.
      $config = $this->configFactory->get('pathauto.settings');
      $this->cleanStringCache = array(
        'separator' => $config->get('separator'),
        'strings' => array(),
        'transliterate' => $config->get('transliterate'),
        'punctuation' => array(),
        'reduce_ascii' => (bool) $config->get('reduce_ascii'),
        'ignore_words_regex' => FALSE,
        'lowercase' => (bool) $config->get('case'),
        'maxlength' => min($config->get('max_component_length'), $this->aliasStorageHelper->getAliasSchemaMaxLength()),
      );

      // Generate and cache the punctuation replacements for strtr().
      $punctuation = $this->getPunctuationCharacters();
      foreach ($punctuation as $name => $details) {
        $action = $config->get('punctuation.' . $name);
        switch ($action) {
          case PathautoManagerInterface::PUNCTUATION_REMOVE:
            $cache['punctuation'][$details['value']] = '';
            $this->cleanStringCache;

          case PathautoManagerInterface::PUNCTUATION_REPLACE:
            $this->cleanStringCache['punctuation'][$details['value']] = $this->cleanStringCache['separator'];
            break;

          case PathautoManagerInterface::PUNCTUATION_DO_NOTHING:
            // Literally do nothing.
            break;
        }
      }

      // Generate and cache the ignored words regular expression.
      $ignore_words = $config->get('ignore_words');
      $ignore_words_regex = preg_replace(array('/^[,\s]+|[,\s]+$/', '/[,\s]+/'), array('', '\b|\b'), $ignore_words);
      if ($ignore_words_regex) {
        $this->cleanStringCache['ignore_words_regex'] = '\b' . $ignore_words_regex . '\b';
        if (function_exists('mb_eregi_replace')) {
          $this->cleanStringCache['ignore_words_callback'] = 'mb_eregi_replace';
        }
        else {
          $this->cleanStringCache['ignore_words_callback'] = 'preg_replace';
          $this->cleanStringCache['ignore_words_regex'] = '/' . $this->cleanStringCache['ignore_words_regex'] . '/i';
        }
      }
    }

    // Empty strings do not need any processing.
    if ($string === '' || $string === NULL) {
      return '';
    }

    $langcode = NULL;
    if (!empty($options['language'])) {
      $langcode = $options['language']->getId();
    }
    elseif (!empty($options['langcode'])) {
      $langcode = $options['langcode'];
    }

    // Check if the string has already been processed, and if so return the
    // cached result.
    if (isset($this->cleanStringCache['strings'][$langcode][(string) $string])) {
      return $this->cleanStringCache['strings'][$langcode][(string) $string];
    }

    // Remove all HTML tags from the string.
    $output = PlainTextOutput::renderFromHtml($string);

    // Optionally transliterate.
    if ($this->cleanStringCache['transliterate']) {
      // If the reduce strings to letters and numbers is enabled, don't bother
      // replacing unknown characters with a question mark. Use an empty string
      // instead.
      $output = \Drupal::service('transliteration')->transliterate($output, $langcode, $this->cleanStringCache['reduce_ascii'] ? '' : '?');
    }

    // Replace or drop punctuation based on user settings.
    $output = strtr($output, $this->cleanStringCache['punctuation']);

    // Reduce strings to letters and numbers.
    if ($this->cleanStringCache['reduce_ascii']) {
      $output = preg_replace('/[^a-zA-Z0-9\/]+/', $this->cleanStringCache['separator'], $output);
    }

    // Get rid of words that are on the ignore list.
    if ($this->cleanStringCache['ignore_words_regex']) {
      $words_removed = $this->cleanStringCache['ignore_words_callback']($this->cleanStringCache['ignore_words_regex'], '', $output);
      if (Unicode::strlen(trim($words_removed)) > 0) {
        $output = $words_removed;
      }
    }

    // Always replace whitespace with the separator.
    $output = preg_replace('/\s+/', $this->cleanStringCache['separator'], $output);

    // Trim duplicates and remove trailing and leading separators.
    $output = $this->aliasCleaner->getCleanSeparators($this->aliasCleaner->getCleanSeparators($output, $this->cleanStringCache['separator']));

    // Optionally convert to lower case.
    if ($this->cleanStringCache['lowercase']) {
      $output = Unicode::strtolower($output);
    }

    // Shorten to a logical place based on word boundaries.
    $output = Unicode::truncate($output, $this->cleanStringCache['maxlength'], TRUE);

    // Cache this result in the static array.
    $this->cleanStringCache['strings'][$langcode][(string) $string] = $output;

    return $output;
  }


  /**
   * {@inheritdoc}
   */
  public function getPunctuationCharacters() {
    if (empty($this->punctuationCharacters)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();

      $cid = 'pathauto:punctuation:' . $langcode;
      if ($cache = $this->cacheBackend->get($cid)) {
        $this->punctuationCharacters = $cache->data;
      }
      else {
        $punctuation = array();
        $punctuation['double_quotes']      = array('value' => '"', 'name' => t('Double quotation marks'));
        $punctuation['quotes']             = array('value' => '\'', 'name' => t("Single quotation marks (apostrophe)"));
        $punctuation['backtick']           = array('value' => '`', 'name' => t('Back tick'));
        $punctuation['comma']              = array('value' => ',', 'name' => t('Comma'));
        $punctuation['period']             = array('value' => '.', 'name' => t('Period'));
        $punctuation['hyphen']             = array('value' => '-', 'name' => t('Hyphen'));
        $punctuation['underscore']         = array('value' => '_', 'name' => t('Underscore'));
        $punctuation['colon']              = array('value' => ':', 'name' => t('Colon'));
        $punctuation['semicolon']          = array('value' => ';', 'name' => t('Semicolon'));
        $punctuation['pipe']               = array('value' => '|', 'name' => t('Vertical bar (pipe)'));
        $punctuation['left_curly']         = array('value' => '{', 'name' => t('Left curly bracket'));
        $punctuation['left_square']        = array('value' => '[', 'name' => t('Left square bracket'));
        $punctuation['right_curly']        = array('value' => '}', 'name' => t('Right curly bracket'));
        $punctuation['right_square']       = array('value' => ']', 'name' => t('Right square bracket'));
        $punctuation['plus']               = array('value' => '+', 'name' => t('Plus sign'));
        $punctuation['equal']              = array('value' => '=', 'name' => t('Equal sign'));
        $punctuation['asterisk']           = array('value' => '*', 'name' => t('Asterisk'));
        $punctuation['ampersand']          = array('value' => '&', 'name' => t('Ampersand'));
        $punctuation['percent']            = array('value' => '%', 'name' => t('Percent sign'));
        $punctuation['caret']              = array('value' => '^', 'name' => t('Caret'));
        $punctuation['dollar']             = array('value' => '$', 'name' => t('Dollar sign'));
        $punctuation['hash']               = array('value' => '#', 'name' => t('Number sign (pound sign, hash)'));
        $punctuation['at']                 = array('value' => '@', 'name' => t('At sign'));
        $punctuation['exclamation']        = array('value' => '!', 'name' => t('Exclamation mark'));
        $punctuation['tilde']              = array('value' => '~', 'name' => t('Tilde'));
        $punctuation['left_parenthesis']   = array('value' => '(', 'name' => t('Left parenthesis'));
        $punctuation['right_parenthesis']  = array('value' => ')', 'name' => t('Right parenthesis'));
        $punctuation['question_mark']      = array('value' => '?', 'name' => t('Question mark'));
        $punctuation['less_than']          = array('value' => '<', 'name' => t('Less-than sign'));
        $punctuation['greater_than']       = array('value' => '>', 'name' => t('Greater-than sign'));
        $punctuation['slash']              = array('value' => '/', 'name' => t('Slash'));
        $punctuation['back_slash']         = array('value' => '\\', 'name' => t('Backslash'));

        // Allow modules to alter the punctuation list and cache the result.
        $this->moduleHandler->alter('pathauto_punctuation_chars', $punctuation);
        $this->cacheBackend->set($cid, $punctuation);
        $this->punctuationCharacters = $punctuation;
      }
    }

    return $this->punctuationCharacters;
  }


  /**
   * {@inheritdoc}
   */
  public function createAlias($module, $op, $source, $data, $type = NULL, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED) {
    $config = $this->configFactory->get('pathauto.settings');

    // Retrieve and apply the pattern for this content type.
    $pattern = $this->getPatternByEntity($module, $type, $langcode);

    // Allow other modules to alter the pattern.
    $context = array(
      'module' => $module,
      'op' => $op,
      'source' => $source,
      'data' => $data,
      'type' => $type,
      'language' => &$langcode,
    );
    $this->moduleHandler->alter('pathauto_pattern', $pattern, $context);

    if (empty($pattern)) {
      // No pattern? Do nothing (otherwise we may blow away existing aliases...)
      return NULL;
    }

    // Special handling when updating an item which is already aliased.
    $existing_alias = NULL;
    if ($op == 'update' || $op == 'bulkupdate') {
      if ($existing_alias = $this->aliasStorageHelper->loadBySource($source, $langcode)) {
        switch ($config->get('update_action')) {
          case PathautoManagerInterface::UPDATE_ACTION_NO_NEW:
            // If an alias already exists,
            // and the update action is set to do nothing,
            // then gosh-darn it, do nothing.
            return NULL;
        }
      }
    }

    // Replace any tokens in the pattern.
    // Uses callback option to clean replacements. No sanitization.
    // Pass empty BubbleableMetadata object to explicitly ignore cacheablity,
    // as the result is never rendered.
    $alias = $this->token->replace($pattern, $data, array(
      'clear' => TRUE,
      'callback' => array($this, 'cleanTokenValues'),
      'langcode' => $langcode,
      'pathauto' => TRUE,
    ), new BubbleableMetadata());

    // Check if the token replacement has not actually replaced any values. If
    // that is the case, then stop because we should not generate an alias.
    // @see token_scan()
    $pattern_tokens_removed = preg_replace('/\[[^\s\]:]*:[^\s\]]*\]/', '', $pattern);
    if ($alias === $pattern_tokens_removed) {
      return NULL;
    }

    $alias = $this->aliasCleaner->cleanAlias($alias);

    // Allow other modules to alter the alias.
    $context['source'] = &$source;
    $context['pattern'] = $pattern;
    $this->moduleHandler->alter('pathauto_alias', $alias, $context);

    // If we have arrived at an empty string, discontinue.
    if (!Unicode::strlen($alias)) {
      return NULL;
    }

    // If the alias already exists, generate a new, hopefully unique, variant.
    $original_alias = $alias;
    $this->aliasUniquifier->uniquify($alias, $source, $langcode);
    if ($original_alias != $alias) {
      // Alert the user why this happened.
      $this->messenger->addMessage($this->t('The automatically generated alias %original_alias conflicted with an existing alias. Alias changed to %alias.', array(
        '%original_alias' => $original_alias,
        '%alias' => $alias,
      )), $op);
    }

    // Return the generated alias if requested.
    if ($op == 'return') {
      return $alias;
    }

    // Build the new path alias array and send it off to be created.
    $path = array(
      'source' => $source,
      'alias' => $alias,
      'language' => $langcode,
    );

    return $this->aliasStorageHelper->save($path, $existing_alias, $op);
  }

  /**
   * {@inheritdoc}
   */
  public function getPatternByEntity($entity_type_id, $bundle = '', $language = LanguageInterface::LANGCODE_NOT_SPECIFIED) {
    $config = $this->configFactory->get('pathauto.pattern');

    $pattern_id = "$entity_type_id:$bundle:$language";
    if (!isset($this->patterns[$pattern_id])) {
      $pattern = '';
      $variables = array();
      if ($language != LanguageInterface::LANGCODE_NOT_SPECIFIED) {
        $variables[] = "{$entity_type_id}.bundles.{$bundle}.languages.{$language}";
      }
      if ($bundle) {
        $variables[] = "{$entity_type_id}.bundles.{$bundle}.default";
      }
      $variables[] = "{$entity_type_id}.default";

      foreach ($variables as $variable) {
        if ($pattern = trim($config->get('patterns.' . $variable))) {
          break;
        }
      }

      $this->patterns[$pattern_id] = $pattern;
    }

    return $this->patterns[$pattern_id];
  }

  /**
   * Resets internal caches.
   */
  public function resetCaches() {
    $this->patterns = array();
    $this->cleanStringCache = array();
  }

  /**
   * {@inheritdoc}
   */
  public function updateAlias(EntityInterface $entity, $op, array $options = array()) {
    // Skip if the entity does not have the path field.
    if (!($entity instanceof ContentEntityInterface) || !$entity->hasField('path')) {
      return NULL;
    }

    // Skip if pathauto processing is disabled.
    if (isset($entity->path->pathauto) && empty($entity->path->pathauto) && empty($options['force'])) {
      return NULL;
    }

    $options += array('language' => $entity->language()->getId());
    $type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    // Skip processing if the entity has no pattern.
    if (!$this->getPatternByEntity($type, $bundle, $options['language'])) {
      return NULL;
    }

    // Deal with taxonomy specific logic.
    if ($type == 'taxonomy_term') {

      $config_forum = $this->configFactory->get('forum.settings');
      if ($entity->getVocabularyId() == $config_forum->get('vocabulary')) {
        $type = 'forum';
      }
    }

    $result = $this->createAlias(
      $type, $op, '/' . $entity->urlInfo()->getInternalPath(), array($type => $entity), $bundle, $options['language']);

    if ($type == 'taxonomy_term' && empty($options['is_child'])) {
      // For all children generate new aliases.
      $options['is_child'] = TRUE;
      unset($options['language']);
      foreach ($this->getTermTree($entity->getVocabularyId(), $entity->id(), NULL, TRUE) as $subterm) {
        $this->updateAlias($subterm, $op, $options);
      }
    }

    return $result;
  }

  /**
   * Create a hierarchical representation of a vocabulary.
   *
   * @param int $vid
   *   The vocabulary ID to generate the tree for.
   * @param int $parent
   *   The term ID under which to generate the tree. If 0, generate the tree
   *   for the entire vocabulary.
   * @param int $max_depth
   *   The number of levels of the tree to return. Leave NULL to return all levels.
   * @param bool $load_entities
   *   If TRUE, a full entity load will occur on the term objects. Otherwise they
   *   are partial objects queried directly from the {taxonomy_term_field_data}
   *   table to save execution time and memory consumption when listing large
   *   numbers of terms. Defaults to FALSE.
   *
   * @return array
   *   An array of all term objects in the tree. Each term object is extended
   *   to have "depth" and "parents" attributes in addition to its normal ones.
   *   Results are statically cached. Term objects will be partial or complete
   *   depending on the $load_entities parameter.
   */
  protected function getTermTree($vid, $parent = 0, $max_depth = NULL, $load_entities = FALSE) {
    return \Drupal::entityManager()->getStorage('taxonomy_term')->loadTree($vid, $parent, $max_depth, $load_entities);
  }

  /**
   * {@inheritdoc}
   */
  public function cleanTokenValues(&$replacements, $data = array(), $options = array()) {
    foreach ($replacements as $token => $value) {
      // Only clean non-path tokens.
      if (!preg_match('/(path|alias|url|url-brief)\]$/', $token)) {
        $replacements[$token] = $this->cleanString($value, $options);
      }
    }
  }
}
