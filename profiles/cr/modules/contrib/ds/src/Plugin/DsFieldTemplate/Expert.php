<?php

namespace Drupal\ds\Plugin\DsFieldTemplate;

/**
 * Plugin for the expert field template.
 *
 * @DsFieldTemplate(
 *   id = "expert",
 *   title = @Translation("Expert"),
 *   theme = "ds_field_expert",
 * )
 */
class Expert extends DsFieldTemplateBase {

  /**
   * {@inheritdoc}
   */
  public function alterForm(&$form) {
    $config = $this->getConfiguration();

    // Add label.
    $form['lb'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#size' => '10',
      '#default_value' => $config['lb'],
    );

    // Add prefix.
    $form['prefix'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Prefix'),
      '#size' => '100',
      '#description' => $this->t('You can enter any html in here.'),
      '#default_value' => isset($config['prefix']) ? $config['prefix'] : '',
      '#prefix' => '<div class="field-prefix">',
      '#suffix' => '</div>',
    );

    $wrappers = array(
      'lbw' => array('title' => $this->t('Label wrapper')),
      'ow' => array('title' => $this->t('Outer wrapper')),
      'fis' => array('title' => $this->t('Field items')),
      'fi' => array('title' => $this->t('Field item')),
    );

    foreach ($wrappers as $wrapper_key => $value) {
      $form[$wrapper_key] = array(
        '#type' => 'checkbox',
        '#title' => $value['title'],
        '#prefix' => '<div class="ft-group ' . $wrapper_key . '">',
        '#default_value' => $config[$wrapper_key],
      );
      $form[$wrapper_key . '-el'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Element'),
        '#size' => '10',
        '#description' => $this->t('E.g. div, span, h2 etc.'),
        '#default_value' => $config[$wrapper_key . '-el'],
        '#states' => array(
          'visible' => array(
            ':input[name$="[' . $wrapper_key . ']"]' => array('checked' => TRUE),
          ),
        ),
      );
      $form[$wrapper_key . '-cl'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Classes'),
        '#size' => '10',
        '#default_value' => $config[$wrapper_key . '-cl'],
        '#description' => $this->t('E.g. field-expert'),
        '#states' => array(
          'visible' => array(
            ':input[name$="[' . $wrapper_key . ']"]' => array('checked' => TRUE),
          ),
        ),
      );
      $form[$wrapper_key . '-at'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Attributes'),
        '#size' => '20',
        '#default_value' => $config[$wrapper_key . '-at'],
        '#description' => $this->t('E.g. name="anchor"'),
        '#states' => array(
          'visible' => array(
            ':input[name$="[' . $wrapper_key . ']"]' => array('checked' => TRUE),
          ),
        ),
      );

      // Hide colon.
      if ($wrapper_key == 'lbw') {
        $form['lb-col'] = array(
          '#type' => 'checkbox',
          '#title' => $this->t('Show label colon'),
          '#default_value' => $config['lb-col'],
          '#attributes' => array(
            'class' => array('colon-checkbox'),
          ),
          '#states' => array(
            'visible' => array(
              ':input[name$="[' . $wrapper_key . ']"]' => array('checked' => TRUE),
            ),
          ),
        );
      }
      if ($wrapper_key != 'lbw') {
        $form[$wrapper_key . '-def-at'] = array(
          '#type' => 'checkbox',
          '#title' => $this->t('Add default attributes'),
          '#default_value' => $config[$wrapper_key . '-def-at'],
          '#suffix' => ($wrapper_key == 'ow') ? '' : '</div><div class="clearfix"></div>',
          '#states' => array(
            'visible' => array(
              ':input[name$="[' . $wrapper_key . ']"]' => array('checked' => TRUE),
            ),
          ),
        );
      }
      else {
        $form['ft'][$wrapper_key . '-def-at'] = array(
          '#markup' => '</div><div class="clearfix"></div>',
        );
      }

      // Default classes for outer wrapper.
      if ($wrapper_key == 'ow') {
        $form[$wrapper_key . '-def-cl'] = array(
          '#type' => 'checkbox',
          '#title' => $this->t('Add default classes'),
          '#default_value' => $config[$wrapper_key . '-def-cl'],
          '#suffix' => '</div><div class="clearfix"></div>',
          '#states' => array(
            'visible' => array(
              ':input[name$="[' . $wrapper_key . ']"]' => array('checked' => TRUE),
            ),
          ),
        );
      }
    }
    // Add suffix.
    $form['suffix'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Suffix'),
      '#size' => '100',
      '#description' => $this->t('You can enter any html in here.'),
      '#default_value' => isset($config['suffix']) ? $config['suffix'] : '',
      '#prefix' => '<div class="field-suffix">',
      '#suffix' => '</div>',
    );

    // Token support.
    if (\Drupal::moduleHandler()->moduleExists('token')) {
      $form['tokens'] = array(
        '#title' => $this->t('Tokens'),
        '#type' => 'container',
        '#states' => array(
          'invisible' => array(
            'input[name="use_token"]' => array('checked' => FALSE),
          ),
        ),
      );
      $form['tokens']['help'] = array(
        '#theme' => 'token_tree_link',
        '#token_types' => 'all',
        '#global_types' => FALSE,
        '#dialog' => TRUE,
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = array();
    $config['lb'] = '';
    $config['lb-col'] = \Drupal::config('ds.settings')->get('ft-show-colon');

    $wrappers = array(
      'lb' => array('title' => $this->t('Label')),
      'lbw' => array('title' => $this->t('Label wrapper')),
      'ow' => array('title' => $this->t('Outer wrapper')),
      'fis' => array('title' => $this->t('Field items')),
      'fi' => array('title' => $this->t('Field item')),
    );
    foreach ($wrappers as $wrapper_key => $value) {
      $config[$wrapper_key] = FALSE;
      $config[$wrapper_key . '-el'] = '';
      $config[$wrapper_key . '-at'] = '';
      $config[$wrapper_key . '-cl'] = '';

      $config[$wrapper_key . '-def-at'] = FALSE;
      $config[$wrapper_key . '-def-cl'] = FALSE;
    }

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function massageRenderValues(&$field_settings, $values) {
    if (!empty($values['lb'])) {
      $field_settings['lb'] = $values['lb'];
    }
    if (!(empty($values['lb-col']))) {
      $field_settings['lb-col'] = TRUE;
    }

    $wrappers = array(
      'lbw' => $this->t('Label wrapper'),
      'ow' => $this->t('Wrapper'),
      'fis' => $this->t('Field items'),
      'fi' => $this->t('Field item'),
    );

    foreach ($wrappers as $wrapper_key => $title) {
      if (!empty($values[$wrapper_key])) {
        // Enable.
        $field_settings[$wrapper_key] = TRUE;
        // Element.
        $field_settings[$wrapper_key . '-el'] = !(empty($values[$wrapper_key . '-el'])) ? $values[$wrapper_key . '-el'] : 'div';
        // Classes.
        $field_settings[$wrapper_key . '-cl'] = !(empty($values[$wrapper_key . '-cl'])) ? $values[$wrapper_key . '-cl'] : '';
        // Default Classes.
        if (in_array($wrapper_key, array('ow', 'lb'))) {
          $field_settings[$wrapper_key . '-def-cl'] = !(empty($values[$wrapper_key . '-def-cl'])) ? TRUE : FALSE;
        }
        // Attributes.
        $field_settings[$wrapper_key . '-at'] = !(empty($values[$wrapper_key . '-at'])) ? $values[$wrapper_key . '-at'] : '';
        // Default attributes.
        $field_settings[$wrapper_key . '-def-at'] = !(empty($values[$wrapper_key . '-def-at'])) ? TRUE : FALSE;
        // Token replacement.
        /* @var \Drupal\Core\Entity\EntityInterface $entity */
        if ($entity = $this->getEntity()) {
          // Tokens.
          $apply_to = array(
            'prefix',
            $wrapper_key . '-el',
            $wrapper_key . '-cl',
            $wrapper_key . '-at',
            'suffix',
          );

          foreach ($apply_to as $identifier) {
            if (!empty($field_settings[$identifier])) {
              $field_settings[$identifier] = \Drupal::token()->replace(
                $field_settings[$identifier],
                array($entity->getEntityTypeId() => $entity),
                array('clear' => TRUE)
              );
            }
          }
        }
      }
    }
  }

}
