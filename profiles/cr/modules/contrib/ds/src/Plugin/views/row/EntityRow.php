<?php

namespace Drupal\ds\Plugin\views\row;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\PluginBase;
use Drupal\views\Plugin\views\row\EntityRow as ViewsEntityRow;

/**
 * Generic entity row plugin to provide a common base for all entity types.
 *
 * @ViewsRow(
 *   id = "ds_entity",
 *   deriver = "Drupal\ds\Plugin\Derivative\DsEntityRow"
 * )
 */
class EntityRow extends ViewsEntityRow {

  /**
   * Contains an array of render arrays, one for each rendered entity.
   *
   * @var array
   */
  protected $build = array();

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['alternating_fieldset'] = array(
      'contains' => array(
        'alternating' => array('default' => FALSE, 'bool' => TRUE),
        'allpages' => array('default' => FALSE, 'bool' => TRUE),
        'item' => array(
          'default' => array(),
        ),
      ),
    );
    $options['grouping_fieldset'] = array(
      'contains' => array(
        'group' => array('default' => FALSE, 'bool' => TRUE),
        'group_field' => array('default' => ''),
        'group_field_function' => array('default' => ''),
      ),
    );
    $options['advanced_fieldset'] = array(
      'contains' => array(
        'advanced' => array('default' => FALSE, 'bool' => TRUE),
      ),
    );
    $options['switch_fieldset'] = array(
      'contains' => array(
        'switch' => array('default' => FALSE, 'bool' => TRUE),
      ),
    );
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Use view mode of display settings.
    if ($this->entityType == 'node' && \Drupal::moduleHandler()->moduleExists('ds_switch_view_mode')) {
      $form['switch_fieldset'] = array(
        '#type' => 'details',
        '#title' => $this->t('Use view mode of display settings'),
        '#open' => $this->options['switch_fieldset']['switch'],
      );
      $form['switch_fieldset']['switch'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Use view mode of display settings'),
        '#default_value' => $this->options['switch_fieldset']['switch'],
        '#description' => $this->t('Use the alternative view mode selected in the display settings tab.'),
      );
    }

    // Alternating view modes.
    $form['alternating_fieldset'] = array(
      '#type' => 'details',
      '#title' => $this->t('Alternating view mode'),
      '#open' => $this->options['alternating_fieldset']['alternating'],
    );
    $form['alternating_fieldset']['alternating'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use the changing view mode selector'),
      '#default_value' => $this->options['alternating_fieldset']['alternating'],
    );
    $form['alternating_fieldset']['allpages'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use this configuration on every page. Otherwhise the default view mode is used as soon you browse away from the first page of this view.'),
      '#default_value' => (isset($this->options['alternating_fieldset']['allpages'])) ? $this->options['alternating_fieldset']['allpages'] : FALSE,
    );

    $pager = $this->view->display_handler->getPlugin('pager');
    $limit = $pager->getItemsPerPage();
    if ($limit == 0 || $limit > 20) {
      $form['alternating_fieldset']['disabled'] = array(
        '#markup' => $this->t('This option is disabled because you have unlimited items or listing more than 20 items.'),
      );
      $form['alternating_fieldset']['alternating']['#disabled'] = TRUE;
      $form['alternating_fieldset']['allpages']['#disabled'] = TRUE;
    }
    else {
      $i = 1;
      $a = 0;
      while ($limit != 0) {
        $form['alternating_fieldset']['item_' . $a] = [
          '#title' => $this->t('Item @nr', array('@nr' => $i)),
          '#type' => 'select',
          '#default_value' => (isset($this->options['alternating_fieldset']['item_' . $a])) ? $this->options['alternating_fieldset']['item_' . $a] : 'teaser',
          '#options' => \Drupal::service('entity_display.repository')->getViewModeOptions($this->entityTypeId),
          '#states' => array(
            'visible' => array(
              ':input[name="row_options[alternating_fieldset][alternating]"]' => array('checked' => TRUE),
            ),
          ),
        ];
        $limit--;
        $a++;
        $i++;
      }
    }

    // Grouping rows.
    $sorts = $this->view->display_handler->getOption('sorts');
    $groupable = !empty($sorts) && $this->options['grouping_fieldset']['group'];

    $form['grouping_fieldset'] = array(
      '#type' => 'details',
      '#title' => $this->t('Group data'),
      '#open' => $groupable,
    );
    $form['grouping_fieldset']['group'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Group data on a field. The value of this field will be displayed too.'),
      '#default_value' => $groupable,
    );

    if (!empty($sorts)) {
      $sort_options = array();
      foreach ($sorts as $sort) {
        $sort_name = Unicode::ucfirst($sort['field']);
        $sort_options[$sort['table'] . '|' . $sort['field']] = $sort_name;
      }

      $form['grouping_fieldset']['group_field'] = array(
        '#type' => 'select',
        '#options' => $sort_options,
        '#default_value' => isset($this->options['grouping_fieldset']['group_field']) ? $this->options['grouping_fieldset']['group_field'] : '',
      );
      $form['grouping_fieldset']['group_field_function'] = array(
        '#type' => 'textfield',
        '#title' => 'Heading function',
        '#description' => Html::escape(t('The value of the field can be in a very raw format (eg, date created). Enter a custom function which you can use to format that value. The value and the object will be passed into that function eg. custom_function($raw_value, $object);')),
        '#default_value' => isset($this->options['grouping_fieldset']['group_field_function']) ? $this->options['grouping_fieldset']['group_field_function'] : '',
      );
    }
    else {
      $form['grouping_fieldset']['group']['#disabled'] = TRUE;
      $form['grouping_fieldset']['group']['#description'] = $this->t('Grouping is disabled because you do not have any sort fields.');
    }

    // Advanced function.
    $form['advanced_fieldset'] = array(
      '#type' => 'details',
      '#title' => $this->t('Advanced view mode'),
      '#open' => $this->options['advanced_fieldset']['advanced'],
    );
    $form['advanced_fieldset']['advanced'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use the advanced view mode selector'),
      '#description' => $this->t('This gives you the opportunity to have full control of a list for really advanced features.<br /> There is no UI for this, you need to create a hook named like this: hook_ds_views_row_render_entity($entity, $view_mode).', array('@VIEWSNAME' => $this->view->storage->id())),
      '#default_value' => $this->options['advanced_fieldset']['advanced'],
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTranslationRenderer() {
    if (!isset($this->entityLanguageRenderer)) {
      $view = $this->getView();
      $rendering_language = $view->display_handler->getOption('rendering_language');
      $langcode = NULL;
      $dynamic_renderers = array(
        '***LANGUAGE_entity_translation***' => 'TranslationLanguageRenderer',
        '***LANGUAGE_entity_default***' => 'DefaultLanguageRenderer',
      );
      if (isset($dynamic_renderers[$rendering_language])) {
        // Dynamic language set based on result rows or instance defaults.
        $renderer = $dynamic_renderers[$rendering_language];
      }
      else {
        if (strpos($rendering_language, '***LANGUAGE_') !== FALSE) {
          $langcode = PluginBase::queryLanguageSubstitutions()[$rendering_language];
        }
        else {
          // Specific langcode set.
          $langcode = $rendering_language;
        }
        $renderer = 'ConfigurableLanguageRenderer';
      }
      $class = '\Drupal\ds\Plugin\views\Entity\Render\\' . $renderer;
      $entity_type = \Drupal::service('entity_type.manager')->getDefinition($this->getEntityTypeId());
      $this->entityLanguageRenderer = new $class($view, $this->getLanguageManager(), $entity_type, $langcode);
    }
    return $this->entityLanguageRenderer;
  }

}
