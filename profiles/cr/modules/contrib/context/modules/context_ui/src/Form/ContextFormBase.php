<?php

namespace Drupal\context_ui\Form;

use Drupal\Core\Form\FormState;
use Drupal\context\ContextManager;
use Drupal\context\Entity\Context;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ContextFormBase extends EntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\context\ContextInterface
   */
  protected $entity;

  /**
   * The Context module context manager.
   *
   * @var ContextManager
   */
  protected $contextManager;

  /**
   * The Drupal context repository.
   *
   * @var ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * Construct a new context form.
   *
   * @param ContextManager $contextManager
   * @param ContextRepositoryInterface $contextRepository
   */
  function __construct(ContextManager $contextManager, ContextRepositoryInterface $contextRepository) {
    $this->contextManager = $contextManager;
    $this->contextRepository = $contextRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('context.manager'),
      $container->get('context.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $formState) {

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General details'),
    ];

    $form['general']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->getLabel(),
      '#required' => TRUE,
      '#description' => $this->t('Enter label for this context.'),
    ];

    $form['general']['name'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $this->entity->getName(),
      '#machine_name' => [
        'source' => ['general', 'label'],
        'exists' => [$this, 'contextExists'],
      ],
    ];

    $form['general']['group'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Group'),
      '#default_value' => $this->entity->getGroup(),
      '#description' => $this->t('Enter name of the group this context should belong to.'),
      '#autocomplete_route_name' => 'context.groups.autocomplete',
    ];

    $form['general']['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->getDescription(),
      '#description' => $this->t('Enter a description for this context definition.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->hasValue('reactions')) {
      $this->validateReactions($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save entity values that the built in submit handler cant take care of.
    if ($form_state->hasValue('require_all_conditions')) {
      $this->entity->setRequireAllConditions($form_state->getValue('require_all_conditions'));
    }

    if ($form_state->hasValue('conditions')) {
      $this->handleConditions($form, $form_state);
    }

    if ($form_state->hasValue('reactions')) {
      $this->handleReactions($form, $form_state);
    }

    // If the group is empty set it to the context no group value,
    // otherwise Drupal will save it as an empty string instead.
    if ($form_state->hasValue('group') && empty($form_state->getValue('group'))) {
      $form_state->setValue('group', Context::CONTEXT_GROUP_NONE);
    }

    // Run the default submit method.
    parent::submitForm($form, $form_state);
  }

  /**
   * Handle submitting the condition plugins configuration forms.
   *
   * @param array $form
   *   The rendered form.
   *
   * @param FormStateInterface $form_state
   *   The current form state.
   */
  private function handleConditions(array &$form, FormStateInterface $form_state) {
    $conditions = $form_state->getValue('conditions', []);

    // Loop trough each condition and update the configuration values by
    // submitting the conditions form.
    foreach ($conditions as $condition_id => $configuration) {
      $condition = $this->entity->getCondition($condition_id);

      $condition_values = (new FormState())->setValues($configuration);
      $condition->submitConfigurationForm($form, $condition_values);

      // If the condition is context aware then add context mapping to
      // the condition.
      if ($condition instanceof ContextAwarePluginInterface) {
        $condition->setContextMapping($condition_values->getValue('context_mapping', []));
      }
    }
  }

  /**
   * Handle submitting the context reaction plugins configuration forms.
   *
   * @param array $form
   *   The rendered form.
   *
   * @param FormStateInterface $form_state
   *   The current form state.
   */
  private function handleReactions(array &$form, FormStateInterface $form_state) {
    $reactions = $form_state->getValue('reactions', []);

    // Loop trough each reaction and update the configuration values by
    // submitting the reactions form.
    foreach ($reactions as $reaction_id => $configuration) {
      $reaction = $this->entity->getReaction($reaction_id);

      $reaction_values = (new FormState())->setValues($configuration);
      $reaction->submitConfigurationForm($form, $reaction_values);
    }
  }

  /**
   * Validate the context reaction plugins configuration forms.
   *
   * @param array $form
   *   The rendered form.
   *
   * @param FormStateInterface $form_state
   *   The current form state.
   */
  private function validateReactions(array &$form, FormStateInterface $form_state) {
    $reactions = $form_state->getValue('reactions', []);

    // Loop trough each reaction and update the configuration values by
    // validating the reactions form.
    foreach ($reactions as $reaction_id => $configuration) {
      $reaction = $this->entity->getReaction($reaction_id);

      $reaction_values = (new FormState())->setValues($configuration);
      $reaction->validateConfigurationForm($form, $reaction_values);
      if ($reaction_values->hasAnyErrors()) {
        // In case of errors, copy them back from the dummy FormState to the
        // master form.
        foreach ($reaction_values->getErrors() as $element => $error) {
          $form_state->setErrorByName("reactions][$reaction_id][$element", $error);
        }
      }
    }
  }

  /**
   * Check to see if a context already exists with the specified name.
   *
   * @param  string $name
   *   The machine name to check for.
   *
   * @return bool
   */
  public function contextExists($name) {
    return $this->contextManager->contextExists($name);
  }
}
