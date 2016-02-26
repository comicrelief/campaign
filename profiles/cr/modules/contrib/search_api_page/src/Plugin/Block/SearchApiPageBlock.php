<?php

/**
 * @file
 * Contains \Drupal\search_api_page\Plugin\Block\SearchApiPageBlock.
 */

namespace Drupal\search_api_page\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\search_api_page\Entity\SearchApiPage;

/**
 * Provides a 'Search Api page form' block.
 *
 * @Block(
 *   id = "search_api_page_form_block",
 *   admin_label = @Translation("Search Api Page search block form"),
 *   category = @Translation("Forms")
 * )
 */
class SearchApiPageBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $options = array();

    $search_api_pages = \Drupal::entityManager()->getStorage('search_api_page')->loadMultiple();
    foreach ($search_api_pages as $search_api_page) {
      $options[$search_api_page->id()] = $search_api_page->label();
    }

    $form['search_api_page'] = array(
      '#type' => 'select',
      '#title' => $this->t('Search page'),
      '#default_value' => !empty($this->configuration['search_api_page']) ? $this->configuration['search_api_page'] : '',
      '#description' => $this->t('Select to which search page a submission of this form will redirect to'),
      '#options' => $options,
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['search_api_page'] = $form_state->getValue('search_api_page');
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    /* @var $search_api_page \Drupal\search_api_page\SearchApiPageInterface */
    $search_api_page = SearchApiPage::load($this->configuration['search_api_page']);
    $config_name = $search_api_page->getConfigDependencyName();
    return ['config' => [$config_name]];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $args = array(
      'search_api_page' => $this->configuration['search_api_page'],
    );
    return \Drupal::formBuilder()->getForm('Drupal\search_api_page\Form\SearchApiPageBlockForm', $args);
  }

}
