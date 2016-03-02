<?php

/**
 * @file
 * Contains \Drupal\pathauto\Form\PathautoSettingsForm.
 */

namespace Drupal\pathauto\Form;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Url;
use Drupal\pathauto\PathautoGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure file system settings for this site.
 */
class PathautoSettingsForm extends ConfigFormBase {

  /**
   * Case should be left as is in the generated path.
   */
  const CASE_LEAVE_ASIS = 0;

  /**
   * Case should be lowercased in the generated path.
   */
  const CASE_LOWER = 1;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pathauto_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pathauto.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    module_load_include('inc', 'pathauto');
    $config = $this->config('pathauto.settings');

    $form = array();

    $form['verbose'] = array(
      '#type' => 'checkbox',
      '#title' => t('Verbose'),
      '#default_value' => $config->get('verbose'),
      '#description' => t('Display alias changes (except during bulk updates).'),
    );

    $form['separator'] = array(
      '#type' => 'textfield',
      '#title' => t('Separator'),
      '#size' => 1,
      '#maxlength' => 1,
      '#default_value' => $config->get('separator'),
      '#description' => t('Character used to separate words in titles. This will replace any spaces and punctuation characters. Using a space or + character can cause unexpected results.'),
    );

    $form['case'] = array(
      '#type' => 'radios',
      '#title' => t('Character case'),
      '#default_value' => $config->get('case'),
      '#options' => array(
        self::CASE_LEAVE_ASIS => t('Leave case the same as source token values.'),
        self::CASE_LOWER => t('Change to lower case'),
      ),
    );

    $max_length = \Drupal::service('pathauto.alias_storage_helper')->getAliasSchemaMaxlength();

    $help_link = '';
    if (\Drupal::moduleHandler()->moduleExists('help')) {
      $help_link = ' ' . t('See <a href=":pathauto-help">Pathauto help</a> for details.', [':pathauto-help' => Url::fromRoute('help.page', ['name' => 'pathauto'])->toString()]);
    }

    $form['max_length'] = array(
      '#type' => 'number',
      '#title' => t('Maximum alias length'),
      '#size' => 3,
      '#maxlength' => 3,
      '#default_value' => $config->get('max_length'),
      '#min' => 1,
      '#max' => $max_length,
      '#description' => t('Maximum length of aliases to generate. 100 is the recommended length. @max is the maximum possible length.', array('@max' => $max_length)) . $help_link,
    );

    $form['max_component_length'] = array(
      '#type' => 'number',
      '#title' => t('Maximum component length'),
      '#size' => 3,
      '#maxlength' => 3,
      '#default_value' => $config->get('max_component_length'),
      '#min' => 1,
      '#max' => $max_length,
      '#description' => t('Maximum text length of any component in the alias (e.g., [title]). 100 is the recommended length. @max is the maximum possible length.', ['@max' => $max_length]) . $help_link,
    );

    $description = t('What should Pathauto do when updating an existing content item which already has an alias?');
    if (\Drupal::moduleHandler()->moduleExists('redirect')) {
      $description .= ' ' . t('The <a href=":url">Redirect module settings</a> affect whether a redirect is created when an alias is deleted.', array(':url' => \Drupal::url('redirect.settings')));
    }
    else {
      $description .= ' ' . t('Considering installing the <a href=":url">Redirect module</a> to get redirects when your aliases change.', array(':url' => 'http://drupal.org/project/redirect'));
    }

    $form['update_action'] = array(
      '#type' => 'radios',
      '#title' => t('Update action'),
      '#default_value' => $config->get('update_action'),
      '#options' => array(
        PathautoGeneratorInterface::UPDATE_ACTION_NO_NEW => t('Do nothing. Leave the old alias intact.'),
        PathautoGeneratorInterface::UPDATE_ACTION_LEAVE => t('Create a new alias. Leave the existing alias functioning.'),
        PathautoGeneratorInterface::UPDATE_ACTION_DELETE => t('Create a new alias. Delete the old alias.'),
      ),
      '#description' => $description,
    );

    $form['transliterate'] = array(
      '#type' => 'checkbox',
      '#title' => t('Transliterate prior to creating alias'),
      '#default_value' => $config->get('transliterate'),
      '#description' => t('When a pattern includes certain characters (such as those with accents) should Pathauto attempt to transliterate them into the US-ASCII alphabet?'),
    );

    $form['reduce_ascii'] = array(
      '#type' => 'checkbox',
      '#title' => t('Reduce strings to letters and numbers'),
      '#default_value' => $config->get('reduce_ascii'),
      '#description' => t('Filters the new alias to only letters and numbers found in the ASCII-96 set.'),
    );

    $form['ignore_words'] = array(
      '#type' => 'textarea',
      '#title' => t('Strings to Remove'),
      '#default_value' => $config->get('ignore_words'),
      '#description' => t('Words to strip out of the URL alias, separated by commas. Do not use this to remove punctuation.'),
      '#wysiwyg' => FALSE,
    );

    $form['punctuation'] = array(
      '#type' => 'fieldset',
      '#title' => t('Punctuation'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $punctuation = \Drupal::service('pathauto.alias_cleaner')->getPunctuationCharacters();

    foreach ($punctuation as $name => $details) {
      // Use the value from config if it exists.
      if ($config->get('punctuation.' . $name) !== NULL) {
        $details['default'] = $config->get('punctuation.' . $name) !== NULL;
      }
      else {
        // Otherwise use the correct default.
        $details['default'] = $details['value'] == $config->get('separator') ? PathautoGeneratorInterface::PUNCTUATION_REPLACE : PathautoGeneratorInterface::PUNCTUATION_REMOVE;
      }
      $form['punctuation'][$name] = array(
        '#type' => 'select',
        '#title' => $details['name'] . ' (<code>' . SafeMarkup::checkPlain($details['value']) . '</code>)',
        '#default_value' => $details['default'],
        '#options' => array(
          PathautoGeneratorInterface::PUNCTUATION_REMOVE => t('Remove'),
          PathautoGeneratorInterface::PUNCTUATION_REPLACE => t('Replace by separator'),
          PathautoGeneratorInterface::PUNCTUATION_DO_NOTHING => t('No action (do not replace)'),
        ),
      );
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('pathauto.settings');

    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $value) {
      $config->set($key, $value);
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
