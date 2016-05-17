<?php
/**
 * @file
 * Contains \Drupal\cdn_ui\Form\CdnSettingsForm.
 */

namespace Drupal\cdn_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configure CDN settings for this site.
 */
class CdnSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cdn_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cdn.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cdn.settings');

    $form['cdn_settings'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-mapping',
      '#attached' => [
        'library' => [
          'cdn_ui/summaries',
        ],
      ],
    ];

    $form['status'] = [
      '#type' => 'details',
      '#title' => $this->t('Status'),
      '#group' => 'cdn_settings',
    ];
    $form['status']['status'] = [
      '#type' => 'radios',
      '#title' => $this->t('Status'),
      '#title_display' => 'invisible',
      '#description' => t(
        'If you do not want to use the CDN to serve files to your visitors yet,
        but you do want to see if it is working well for your site, then enable
        testing mode.<br />Users will only get the files from the CDN if they
        have the <a href=":perm-url"><em>Access files on the CDN when in testing
        mode</em> permission</a>.',
        [
          ':perm-url' => Url::fromRoute('user.admin_permissions')->setOption('fragment', 'module-cdn_ui')->toString(TRUE)->getGeneratedUrl(),
        ]
      ),
      '#required' => TRUE,
      '#options' => [
        0 => $this->t('Disabled'),
        1 => $this->t('Testing mode'),
        2 => $this->t('Enabled'),
      ],
      '#default_value' => $config->get('status'),
    ];

    $form['mapping'] = [
      '#type' => 'details',
      '#title' => $this->t('Mapping'),
      '#group' => 'cdn_settings',
      '#tree' => TRUE,
    ];

    $form['mapping']['type'] = [
      '#field_prefix' => $this->t('Use'),
      '#field_suffix' => $this->t('mapping'),
      '#type' => 'select',
      '#title' => $this->t('Mapping type'),
      '#title_display' => 'invisible',
      '#options' => [
        'simple' => $this->t('simple'),
        'advanced' => $this->t('advanced'),
      ],
      '#required' => TRUE,
      '#wrapper_attributes' => ['class' => ['container-inline']],
      '#attributes' => ['class' => ['container-inline']],
      '#default_value' => $config->get('mapping.type') === 'simple' ?: 'advanced',
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['mapping']['simple'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ':input[name="mapping[type]"]' => ['value' => 'simple'],
        ],
      ],
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['mapping']['simple']['extensions_condition_toggle'] = [
      '#type' => 'select',
      '#title' => $this->t('Limit by file extension'),
      '#title_display' => 'invisible',
      '#field_prefix' => $this->t('Serve'),
      '#options' => [
        'all' => $this->t('all files'),
        'limited' => $this->t('only files'),
      ],
      '#default_value' => empty($config->get('mapping.conditions.extensions')) ? 'all' : 'limited',
    ];
    $form['mapping']['simple']['extensions_condition_value'] = [
      '#field_prefix' => $this->t('with the extension'),
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions'),
      '#title_display' => 'invisible',
      '#placeholder' => 'css js jpg jpeg png zip',
      '#size' => 30,
      '#default_value' => implode(' ', $config->get('mapping.conditions.extensions') ?: []),
      '#states' => [
        'visible' => [
          ':input[name="mapping[simple][extensions_condition_toggle]"]' => ['value' => 'limited'],
        ],
      ],
    ];
    $form['mapping']['simple']['domain'] = [
      '#field_prefix' => $this->t('from'),
      '#type' => 'textfield',
      '#placeholder' => 'example.com',
      '#title' => $this->t('Domain'),
      '#title_display' => 'FALSE',
      '#size' => 25,
      '#default_value' => $config->get('mapping.domain'),
    ];
    $form['mapping']['advanced'] = [
      '#type' => 'item',
      '#markup' => '<em>' . $this->t('Not configurable through the UI. Modify <code>cdn.settings.yml</code> directly, and <a href=":url">import it</a>. It is safe to edit all other settings via the UI.', [':url' => Url::fromRoute('config.import_single')->toString(TRUE)->getGeneratedUrl()]) . '</em>',
      '#states' => [
        'visible' => [
          ':input[name="mapping[type]"]' => ['value' => 'advanced'],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cdn.settings');

    // Vertical tab: 'Status'.
    $config->set('status', $form_state->getValue('status'));

    // Vertical tab: 'Mapping'.
    if ($form_state->getValue(['mapping', 'type']) === 'simple') {
      $simple_mapping = $form_state->getValue(['mapping', 'simple']);
      $config->set('mapping', []);
      $config->set('mapping.type', 'simple');
      $config->set('mapping.domain', $simple_mapping['domain']);
      // Only the 'extensions' condition is supported in this UI, to KISS.
      if ($simple_mapping['extensions_condition_toggle'] === 'limited') {
        // Set the 'extensions' condition unconditionally.
        $config->set('mapping.conditions.extensions', explode(' ', trim($simple_mapping['extensions_condition_value'])));
      }
      else {
        // Remove the 'extensions' condition if it is set.
        $conditions = $config->getOriginal('mapping.type') === 'simple' ? $config->getOriginal('mapping.conditions') : [];
        if (isset($conditions['extensions'])) {
          unset($conditions['extensions']);
        }
        $config->set('mapping.conditions', $conditions);
      }
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
