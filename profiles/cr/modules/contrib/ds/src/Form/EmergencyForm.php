<?php

namespace Drupal\ds\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Emergency form for DS.
 */
class EmergencyForm extends ConfigFormBase {

  /**
   * State object.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\ds\Form\EmergencyForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(ConfigFactory $config_factory, ModuleHandlerInterface $module_handler, StateInterface $state) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ds_emergy_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['ds_fields_error'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Fields error'),
    );

    $form['ds_fields_error']['disable'] = array(
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('In case you get an error after configuring a layout printing a message like "Fatal error: Unsupported operand types", you can temporarily disable adding fields from DS. You probably are trying to render an node inside a node, for instance through a view, which is simply not possible. See <a href="http://drupal.org/node/1264386">http://drupal.org/node/1264386</a>.'),
    );

    $form['ds_fields_error']['submit'] = array(
      '#type' => 'submit',
      '#value' => ($this->state->get('ds.disabled', FALSE) ? $this->t('Enable attaching fields') : $this->t('Disable attaching fields')),
      '#submit' => array('::submitFieldAttach'),
      '#weight' => 1,
    );

    if ($this->moduleHandler->moduleExists('ds_extras')) {
      $region_blocks = $this->config('ds_extras.settings')->get('region_blocks');
      if (!empty($region_blocks)) {

        $region_blocks_options = array();
        foreach ($region_blocks as $key => $info) {
          $region_blocks_options[$key] = $info['title'];
        }

        $form['region_to_block'] = array(
          '#type' => 'fieldset',
          '#title' => $this->t('Block regions'),
        );

        $form['region_to_block']['remove_block_region'] = array(
          '#type' => 'checkboxes',
          '#options' => $region_blocks_options,
          '#description' => $this->t('In case you renamed a content type, you will not see the configured block regions anymore, however the block on the block settings page is still available. On this screen you can remove orphaned block regions.'),
        );

        $form['region_to_block']['submit'] = array(
          '#type' => 'submit',
          '#value' => $this->t('Remove block regions'),
          '#submit' => array('::submitRegionToBlock'),
          '#weight' => 1,
        );
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // empty.
  }

  /**
   * Submit callback for the fields error form.
   */
  public function submitFieldAttach(array &$form, FormStateInterface $form_state) {
    $this->state->set('ds.disabled', ($this->state->get('ds.disabled', FALSE) ? FALSE : TRUE));
    drupal_set_message(t('The configuration options have been saved.'));
  }

  /**
   * Submit callback for the region to block form.
   */
  public function submitRegionToBlock(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('remove_block_region')) {
      $save = FALSE;
      $region_blocks = $this->config('ds_extras.settings')->get('region_blocks');
      $remove = $form_state->getValue('remove_block_region');
      foreach ($remove as $key => $value) {
        if ($value !== 0 && $key == $value) {
          $save = TRUE;

          // Make sure there is no active block instance for this ds block
          // region.
          if (\Drupal::moduleHandler()->moduleExists('block')) {
            $ids = \Drupal::entityQuery('block')
              ->condition('plugin', 'ds_region_block:' . $key)
              ->execute();
            /* @var \Drupal\block\BlockInterface $block_storage */
            $block_storage = \Drupal::service('entity_type.manager')->getStorage('block');
            foreach ($block_storage->loadMultiple($ids) as $block) {
              $block->delete();
            }
          }

          unset($region_blocks[$key]);
        }
      }

      if ($save) {
        drupal_set_message(t('Block regions were removed.'));

        // Clear cached block and ds plugin definitions.
        \Drupal::service('plugin.manager.block')->clearCachedDefinitions();
        \Drupal::service('plugin.manager.ds')->clearCachedDefinitions();

        $this->config('ds_extras.settings')->set('region_blocks', $region_blocks)->save();
      }
    }
    else {
      drupal_set_message($this->t('No block regions were removed.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array(
      'ds_extras.settings',
    );
  }

}
