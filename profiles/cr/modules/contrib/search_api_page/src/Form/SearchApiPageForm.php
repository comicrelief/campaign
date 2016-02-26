<?php

/**
 * @file
 * Contains Drupal\search_api_page\Form\SearchApiPageForm.
 */

namespace Drupal\search_api_page\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Entity\Index;

/**
 * Class SearchApiPageForm.
 *
 * @package Drupal\search_api_page\Form
 */
class SearchApiPageForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /* @var $search_api_page \Drupal\search_api_page\SearchApiPageInterface */
    $search_api_page = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $search_api_page->label(),
      '#required' => TRUE,
      '#description' => $this->t('This will also be used as the page title.'),
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $search_api_page->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\search_api_page\Entity\SearchApiPage::load',
      ),
      '#disabled' => !$search_api_page->isNew(),
    );

    // Default index and states.
    $default_index = $search_api_page->getIndex();
    $default_index_states = array(
      'visible' => array(
        ':input[name="index"]' => array('value' => $default_index),
      ),
    );

    $index_options = array();
    $search_api_indexes = $this->entityTypeManager->getStorage('search_api_index')->loadMultiple();
    /* @var  $search_api_index \Drupal\search_api\IndexInterface */
    foreach ($search_api_indexes as $search_api_index) {
      $index_options[$search_api_index->id()] = $search_api_index->label();
    }

    $form['index_fieldset'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Index'),
    );

    $form['index_fieldset']['index'] = array(
      '#type' => 'select',
      '#title' => $this->t('Search API index'),
      '#options' => $index_options,
      '#default_value' => $default_index,
      '#required' => TRUE,
    );

    $form['index_fieldset']['previous_index'] = array(
      '#type' => 'value',
      '#value' => $default_index,
    );

    $searched_fields = $search_api_page->getFullTextFields();
    $form['index_fieldset']['searched_fields'] = array(
      '#type' => 'select',
      '#multiple' => TRUE,
      '#options' => $searched_fields,
      '#size' => min(4, count($searched_fields)),
      '#title' => $this->t('Searched fields'),
      '#description' => $this->t('Select the fields that will be searched. If no fields are selected, all available fulltext fields will be searched.'),
      '#default_value' => $search_api_page->getSearchedFields(),
      '#access' => !empty($default_index),
      '#states' => $default_index_states,
    );

    $form['page_fieldset'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Page'),
      '#states' => array(
        'visible' => array(':input[name="index"]' => array('value' => $default_index)),
      ),
      '#access' => !empty($default_index),
    );

    $form['page_fieldset']['path'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#maxlength' => 255,
      '#default_value' => $search_api_page->getPath(),
      '#description' => $this->t("Do not include a trailing slash."),
      '#required' => TRUE,
      '#access' => !empty($default_index),
      '#states' => $default_index_states,
    );

    $form['page_fieldset']['previous_path'] = array(
      '#type' => 'value',
      '#value' => $search_api_page->getPath(),
      '#access' => !empty($default_index),
    );

    $form['page_fieldset']['clean_url'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use clean URL\'s'),
      '#default_value' => $search_api_page->getCleanUrl(),
      '#access' => !empty($default_index),
      '#states' => $default_index_states,
    );

    $form['page_fieldset']['previous_clean_url'] = array(
      '#type' => 'value',
      '#default_value' => $search_api_page->getCleanUrl(),
    );

    $form['page_fieldset']['limit'] = array(
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#default_value' => $search_api_page->getLimit(),
      '#min' => 1,
      '#required' => TRUE,
      '#access' => !empty($default_index),
      '#states' => $default_index_states,
    );

    $form['page_fieldset']['show_search_form'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show search form above results'),
      '#default_value' => $search_api_page->showSearchForm(),
      '#access' => !empty($default_index),
      '#states' => $default_index_states,
    );

    $form['page_fieldset']['show_all_when_no_keys'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show all results when no search is performed'),
      '#default_value' => $search_api_page->showAllResultsWhenNoSearchIsPerformed(),
      '#access' => !empty($default_index),
      '#states' => $default_index_states,
    );

    $form['page_fieldset']['style'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Style'),
      '#options' => array(
        'view_modes' => $this->t('View modes'),
        'search_results' => $this->t('Search results'),
      ),
      '#default_value' => $search_api_page->getStyle(),
      '#required' => TRUE,
      '#access' => !empty($default_index),
      '#states' => $default_index_states,
    );

    if (!empty($default_index)) {
      $form['view_mode_configuration'] = array(
        '#type' => 'fieldset',
        '#tree' => TRUE,
        '#title' => $this->t('View modes'),
        '#states' => array(
          'visible' => array(':input[name="style"]' => array('value' => 'view_modes'), ':input[name="index"]' => array('value' => $default_index)),
        ),
      );

      /* @var $index \Drupal\search_api\IndexInterface */
      $index = Index::load($search_api_page->getIndex());
      $view_mode_configuration = $search_api_page->getViewModeConfiguration();
      foreach ($index->getDatasources() as $datasource_id => $datasource) {
        $bundles = $datasource->getBundles();
        foreach ($bundles as $bundle_id => $bundle_label) {
          $view_modes = $datasource->getViewModes($bundle_id);
          $form['view_mode_configuration'][$datasource_id . '_' . $bundle_id] = array(
            '#type' => 'select',
            '#title' => $this->t('View mode for %datasource » %bundle', array('%datasource' => $datasource->label(), '%bundle' => $bundle_label)),
            '#options' => $view_modes,
          );
          if (isset($view_mode_configuration[$datasource_id . '_' . $bundle_id])) {
            $form['view_mode_configuration'][$datasource_id . '_' . $bundle_id]['#default_value'] = $view_mode_configuration[$datasource_id . '_' . $bundle_id];
          }
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    /* @var $search_api_page \Drupal\search_api_page\SearchApiPageInterface */
    $search_api_page = $this->entity;
    if ($search_api_page->isNew()) {
      $actions['submit']['#value'] = $this->t('Next');
    }

    $default_index = $search_api_page->getIndex();
    if (!empty($default_index)) {

      // Add an update button that shows up when changing the index.
      $default_index_states_invisible = array(
        'invisible' => array(
          ':input[name="index"]' => array('value' => $default_index),
        ),
      );
      $actions['update'] = $actions['submit'];
      $actions['update']['#value'] = $this->t('Update');
      $actions['update']['#states'] = $default_index_states_invisible;

      // Hide the Save button when the index changes.
      $default_index_states_visible = array(
        'visible' => array(
          ':input[name="index"]' => array('value' => $default_index),
        ),
      );
      $actions['submit']['#states'] = $default_index_states_visible;
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var $search_api_page \Drupal\search_api_page\SearchApiPageInterface */
    $search_api_page = $this->entity;

    // Reset view mode configuration.
    if (!$search_api_page->renderAsViewModes()) {
      $search_api_page->set('view_mode_configuration', array());
    }

    // Check searched fields. In case nothing has been selected, select all
    // the available fields.
    $has_selection = FALSE;
    $searched_fields = $form_state->getValue('searched_fields');
    foreach ($searched_fields as $key => $value) {
      if ($key === $value) {
        $has_selection = TRUE;
        break;
      }
    }
    if (!$has_selection) {
      $key_values = array_keys($form['index_fieldset']['searched_fields']['#options']);
      $searched_fields = array_combine($key_values, $key_values);
      $search_api_page->set('searched_fields', $searched_fields);
    }

    $status = $search_api_page->save();

    switch ($status) {
      case SAVED_NEW:
        // Redirect to edit form so the rest can be configured.
        $form_state->setRedirectUrl($search_api_page->toUrl('edit-form'));
        break;

      default:
        // Set redirect to overview if the index is the same, otherwise, go to
        // the edit form again.
        if ($form_state->getValue('index') == $form_state->getValue('previous_index')) {
          $form_state->setRedirectUrl($search_api_page->toUrl('collection'));
          drupal_set_message($this->t('Saved the %label Search page.', [
            '%label' => $search_api_page->label(),
          ]));
        }
        else {
          $form_state->setRedirectUrl($search_api_page->toUrl('edit-form'));
          drupal_set_message($this->t('Updated the index for the %label Search page.', [
            '%label' => $search_api_page->label(),
          ]));
        }

    }

    // Trigger a router rebuild if:
    // - path is different than previous_path.
    // - clean_url is different than previous_clean_url.
    if ($form_state->getValue('path') != $form_state->getValue('previous_path') || $form_state->getValue('clean_url') != $form_state->getValue('previous_clean_url')) {
      \Drupal::service('router.builder')->rebuild();
    }

  }

}
