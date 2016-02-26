<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsLayout.
 */

namespace Drupal\ds\Plugin;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ds\Ds;
use Drupal\layout_plugin\Plugin\Layout\LayoutBase;

/**
 * Layout class for all Display Suite layouts.
 */
class DsLayout extends LayoutBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'wrappers' => [],
      'outer_wrapper' => 'div',
      'attributes' => '',
      'link_attribute' => '',
      'link_custom' => '',
      'classes' => [
        'layout_class' => [],
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $configuration = $this->getConfiguration();
    $regions = $this->getRegionDefinitions();

    // Add wrappers
    $wrapper_options = array(
      'div' => 'Div',
      'span' => 'Span',
      'section' => 'Section',
      'article' => 'Article',
      'header' => 'Header',
      'footer' => 'Footer',
      'aside' => 'Aside',
      'figure' => 'Figure'
    );
    $form['region_wrapper'] = array(
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => t('Custom wrappers'),
      '#description' => t('Choose a wrapper. All Display Suite layouts support this option.'),
      '#tree' => TRUE,
    );

    foreach ($regions as $region_name => $region_definition) {
      $form['region_wrapper'][$region_name] = array(
        '#type' => 'select',
        '#options' => $wrapper_options,
        '#title' => t('Wrapper for @region', array('@region' => $region_definition['label'])),
        '#default_value' => !empty($configuration['wrappers'][$region_name]) ?  $configuration['wrappers'][$region_name] : 'div',
      );
    }

    $form['region_wrapper']['outer_wrapper'] = array(
      '#type' => 'select',
      '#options' => $wrapper_options,
      '#title' => t('Outer wrapper'),
      '#default_value' => $configuration['outer_wrapper'],
      '#weight' => 10,
    );

    $form['region_wrapper']['attributes'] = array(
      '#type' => 'textfield',
      '#title' => t('Layout attributes'),
      '#description' => 'E.g. role|navigation,data-something|some value',
      '#default_value' => $configuration['attributes'],
      '#weight' => 11,
    );

    $form['region_wrapper']['link_attribute'] = array(
      '#type' => 'select',
      '#options' => array(
        '' => t('No link'),
        'content' => t('Link to content'),
        'custom' => t('Custom'),
        'tokens' => t('Tokens')
      ),
      '#title' => t('Add link'),
      '#description' => t('This will add an onclick attribute on the layout wrapper.'),
      '#default_value' => $configuration['link_attribute'],
      '#weight' => 12,
    );

    $form['region_wrapper']['link_custom'] = array(
      '#type' => 'textfield',
      '#title' => t('Custom link'),
      '#description' => t('You may use tokens for this link if you selected tokens.'),
      '#default_value' => $configuration['link_custom'],
      '#weight' => 13,
      '#states' => array(
        'visible' => array(array(
          ':input[name="region_wrapper[link_attribute]"]' => array(array("value" => "tokens"), array("value" => "custom")),
        )),
      ),
    );

    if (\Drupal::moduleHandler()->moduleExists('token')) {
      $form['region_wrapper']['tokens'] = array(
        '#title' => t('Tokens'),
        '#type' => 'container',
        '#weight' => 14,
        '#states' => array(
          'visible' => array(
            ':input[name="region_wrapper[link_attribute]"]' => array("value" => "tokens"),
          ),
        ),
      );
      $form['region_wrapper']['tokens']['help'] = array(
        '#theme' => 'token_tree',
        '#token_types' => 'all',
        '#global_types' => FALSE,
        '#dialog' => TRUE,
      );
    }

    // Add extra classes for the regions to have more control while theming.
    $form['ds_classes'] = array(
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => t('Custom classes'),
      '#tree' => TRUE,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $classes_access = (\Drupal::currentUser()->hasPermission('admin_classes'));
    $classes = Ds::getClasses();
    if (!empty($classes)) {

      $form['ds_classes']['layout_class'] = array(
        '#type' => 'select',
        '#multiple' => TRUE,
        '#options' => $classes,
        '#title' => t('Class for layout'),
        '#default_value' => !empty($configuration['classes']['layout_class']) ? $configuration['classes']['layout_class'] : [],
      );

      foreach ($regions as $region_name => $region_definition) {
        $form['ds_classes'][$region_name] = array(
          '#type' => 'select',
          '#multiple' => TRUE,
          '#options' => $classes,
          '#title' => t('Class for @region', array('@region' => $region_definition['label'])),
          '#default_value' => isset($configuration['classes'][$region_name]) ? $configuration['classes'][$region_name] : [],
        );
      }
      if ($classes_access) {
        $url = Url::fromRoute('ds.classes');
        $destination = \Drupal::destination()->getAsArray();
        $url->setOption('query', $destination);
        $form['ds_classes']['info'] = array('#markup' => \Drupal::l(t('Manage region and field CSS classes'), $url));
      }
    }
    else {
      if ($classes_access) {
        $url = Url::fromRoute('ds.classes');
        $destination  = \Drupal::destination()->getAsArray();
        $url->setOption('query', $destination);
        $form['ds_classes']['info'] = array('#markup' => '<p>' . t('You have not defined any CSS classes which can be used on regions.') . '</p><p>' . \Drupal::l(t('Manage region and field CSS classes'), $url) . '</p>');      }
      else {
        $form['ds_classes']['#access'] = FALSE;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['wrappers'] = $form_state->getValue('region_wrapper');
    foreach (['outer_wrapper', 'attributes', 'link_attribute', 'link_custom'] as $name) {
      $this->configuration[$name] = $this->configuration['wrappers'][$name];
      unset($this->configuration['wrappers'][$name]);
    }

    // Apply Xss::filter to attributes.
    $this->configuration['attributes'] = Xss::filter($this->configuration['attributes']);

    // In case classes is missing entirely, use the defaults.
    $defaults = $this->defaultConfiguration();
    $this->configuration['classes'] = $form_state->getValue('ds_classes', $defaults['classes']);

    // Do not save empty classes.
    foreach ($this->configuration['classes'] as $region_name => &$classes) {
      foreach ($classes as $class) {
        if (empty($class)) {
          unset($classes[$class]);
        }
      }
    }
  }

}
