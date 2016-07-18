<?php

/**
 * @file
 * Contains \Drupal\simple_sitemap\Form\SimplesitemapEntitiesForm.
 */

namespace Drupal\simple_sitemap\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\simple_sitemap\Form;

/**
 * SimplesitemapSettingsFrom
 */
class SimplesitemapEntitiesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'simple_sitemap_entities_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['simple_sitemap.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $sitemap = \Drupal::service('simple_sitemap.generator');

    $form['simple_sitemap_entities']['entities'] = array(
      '#title' => t('Sitemap entities'),
      '#type' => 'fieldset',
      '#markup' => '<p>' . t("Simple XML sitemap settings will be added only to entity forms of entity types enabled here. For all entity types featuring bundles (e.g. <em>node</em>) inclusion settings have to be set on their bundle pages (e.g. <em>page</em>). Disabling an entity type on this page will delete its sitemap settings including per-entity overrides.") . '</p>',
    );

    $entity_type_labels = [];
    foreach (Simplesitemap::getSitemapEntityTypes() as $entity_type_id => $entity_type) {
      $entity_type_labels[$entity_type_id] = $entity_type->getLabel() ? : $entity_type_id;
    }
    asort($entity_type_labels);

    $entity_types = $sitemap->getConfig('entity_types');
    $f = new Form();

    foreach ($entity_type_labels as $entity_type_id => $entity_type_label) {
      $entity_type_enabled = isset($entity_types[$entity_type_id]);
      $form['simple_sitemap_entities']['entities'][$entity_type_id] = [
      '#type' => 'details',
      '#title' => $entity_type_label,
      '#open' => $entity_type_enabled,
    ];
      $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_enabled'] = [
        '#type' => 'checkbox',
        '#title' => t('Enable @entity_type_label support', array('@entity_type_label' => strtolower($entity_type_label))),
        '#description' => t('Sitemap settings for this entity type can be set on its bundle pages and overridden on its entity pages.'),
        '#default_value' => $entity_type_enabled,
      ];
      if (Simplesitemap::entityTypeIsAtomic($entity_type_id)) {
        $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_enabled']['#description'] = t('Sitemap settings for this entity type can be set below and overridden on its entity pages.');
        $f->setEntityCategory('bundle');
        $f->setEntityTypeId($entity_type_id);
        $f->setBundleName($entity_type_id);
        $f->displayEntitySitemapSettings($form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_settings'], TRUE);
      }
    }
    $f->displaySitemapRegenerationSetting($form['simple_sitemap_entities']['entities']);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $sitemap = \Drupal::service('simple_sitemap.generator');
    $entity_types = $sitemap->getConfig('entity_types');
    $values = $form_state->getValues();
    foreach($values as $field_name => $value) {
      if (substr($field_name, -strlen('_enabled')) == '_enabled') {
        $entity_type_id = substr($field_name, 0, -8);
        if ($value) {
          if (empty($entity_types[$entity_type_id])) {
            if (Simplesitemap::entityTypeIsAtomic($entity_type_id))
              // As entity type has no bundles, making it index by default with set priority.
              $entity_types[$entity_type_id][$entity_type_id] = ['index' => 1, 'priority' => $values[$entity_type_id . '_simple_sitemap_priority']];
            else // As entity has bundles, enabling settings on its bundle pages.
              $entity_types[$entity_type_id] = [];
          }
        }
        else {
          unset($entity_types[$entity_type_id]);
        }
      }
    }
    $sitemap->saveConfig('entity_types', $entity_types);
    parent::submitForm($form, $form_state);

    // Regenerate sitemaps according to user setting.
    if ($form_state->getValue('simple_sitemap_regenerate_now')) {
      $sitemap->generateSitemap();
    }
  }
}
