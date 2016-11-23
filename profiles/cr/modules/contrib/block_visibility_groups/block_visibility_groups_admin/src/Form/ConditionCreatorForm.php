<?php

namespace Drupal\block_visibility_groups_admin\Form;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ConditionCreatorForm extends FormBase {

  /**
   * @var  \Drupal\Component\Plugin\PluginManagerInterface $manager ; */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block_visibility_groups_admin.condition_creator')
    );
  }

  /**
   * ConditionCreatorForm constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   */
  public function __construct(PluginManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'block_visibility_groups_admin_creator';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $route_name = NULL, $parameters = NULL) {
    $parameters = Json::decode($parameters);
    if (empty($route_name)) {
      // @todo Throw error
    }
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#description' => $this->t("Label for the Block Visibility Group."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#machine_name' => array(
        'exists' => '\Drupal\block_visibility_groups\Entity\BlockVisibilityGroup::load',
      ),
    );

    $form['conditions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Conditions'),
      '#description' => $this->t('Select at least one condition that applies to the current page.'),
    ];
    $form['conditions'] += $this->conditionOptions($route_name, $parameters);
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create new Group'),
    ];

    $form['route_name'] = [
      '#type' => 'value',
      '#value' => $route_name,
    ];

    $form['parameters'] = [
      '#type' => 'value',
      '#value' => $parameters,
    ];
    return $form;
  }

  /**
   *
   */
  protected function conditionOptions($route_name, $parameters) {
    $elements = [
      '#tree' => TRUE,
    ];
    $this->manager->getDefinitions();
    $definitions = $this->manager->getDefinitions();
    foreach ($definitions as $id => $info) {
      /** @var \Drupal\block_visibility_groups_admin\Plugin\ConditionCreatorInterface $creator */
      $creator = $this->manager->createInstance(
        $id,
        [
          'route_name' => $route_name,
          'parameters' => $parameters,
        ]
      );

      if ($label = $creator->getNewConditionLabel()) {

        $elements[$id] = $creator->createConditionElements();
      }
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $conditions = $this->getConditionValues($form_state);
    $route_name = $form_state->getValue('route_name');
    $parameters = $form_state->getValue('parameters');
    foreach ($conditions as $plugin_id => $plugin_info) {
      /** @var \Drupal\block_visibility_groups_admin\Plugin\ConditionCreatorInterface $plugin */
      $plugin = $this->manager->createInstance(
        $plugin_id,
        [
          'route_name' => $route_name,
          'parameters' => $parameters,
        ]
      );

      if ($plugin->itemSelected($plugin_info)) {
        // At least 1 condition setting selected.
        return;
      }
    }
    $form_state->setErrorByName('conditions', $this->t('At least one condition must be selected'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getValue('id');
    $label = $form_state->getValue('label');
    $route_name = $form_state->getValue('route_name');

    $conditions = $this->getConditionValues($form_state);
    $configs = [];
    foreach ($conditions as $plugin_id => $plugin_info) {
      /** @var \Drupal\block_visibility_groups_admin\Plugin\ConditionCreatorInterface $plugin */
      $plugin = $this->manager->createInstance($plugin_id, ['route_name' => $route_name]);

      if ($plugin->itemSelected($plugin_info)) {
        $configs[] = $plugin->createConditionConfig($plugin_info);
      }
    }
    $group = $this->createGroup($id, $label, $configs);
    $form_state->setRedirect(
      'entity.block_visibility_group.edit_form',
      [
        'block_visibility_group' => $group->id(),
      ]

    );
  }

  /**
   *
   */
  protected function getConditionValues(FormStateInterface $form_state) {
    return $form_state->cleanValues()->getValue('conditions');
  }

  /**
   * @param $id
   * @param $label
   * @param $configs
   *
   * @return \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup
   */
  protected function createGroup($id, $label, $configs) {
    /** @var BlockVisibilityGroup $group */
    $group = BlockVisibilityGroup::create(
      [
        'id' => $id,
        'label' => $label,
      ]
    );

    $group->save();
    foreach ($configs as $config) {
      $group->addCondition($config);
    }
    $group->save();
    return $group;
  }

}
