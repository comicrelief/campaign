<?php

namespace Drupal\block_visibility_groups\Form;

use Drupal\block_visibility_groups\BlockVisibilityGroupInterface;
use Drupal\block_visibility_groups\ConditionRedirectTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base form for editing and adding a condition.
 */
abstract class ConditionFormBase extends FormBase {

  use ContextAwarePluginAssignmentTrait;

  use ConditionRedirectTrait;

  /**
   * The block_visibility_group entity this condition belongs to.
   *
   * @var \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup
   */
  protected $block_visibility_group;

  /**
   * The condition used by this form.
   *
   * @var \Drupal\Core\Condition\ConditionInterface
   */
  protected $condition;

  /**
   * The context repository service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * ConditionFormBase constructor.
   *
   * @param ContextRepositoryInterface $contextRepository
   */
  public function __construct(ContextRepositoryInterface $context_repository) {
    $this->contextRepository = $context_repository;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('context.repository')
    );
  }

  /**
   * Prepares the condition used by this form.
   *
   * @param string $condition_id
   *   Either a condition ID, or the plugin ID used to create a new
   *   condition.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   The condition object.
   */
  abstract protected function prepareCondition($condition_id);

  /**
   * Returns the text to use for the submit button.
   *
   * @return string
   *   The submit button text.
   */
  abstract protected function submitButtonText();

  /**
   * Returns the text to use for the submit message.
   *
   * @return string
   *   The submit message text.
   */
  abstract protected function submitMessageText();

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BlockVisibilityGroupInterface $block_visibility_group = NULL, $condition_id = NULL, $redirect = NULL) {
    $this->block_visibility_group = $block_visibility_group;
    $this->condition = $this->prepareCondition($condition_id);

    $this->setRedirectValue($form, $redirect);
    // Store the gathered contexts in the form state for other objects to use
    // during form building.
    $form_state->setTemporaryValue('gathered_contexts', $this->contextRepository->getAvailableContexts());

    // Allow the condition to add to the form.
    $form['condition'] = $this->condition->buildConfigurationForm([], $form_state);
    $form['condition']['#tree'] = TRUE;

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->submitButtonText(),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Allow the condition to validate the form.
    $condition_values = (new FormState())->setValues($form_state->getValue('condition'));
    $this->condition->validateConfigurationForm($form, $condition_values);
    // Update the original form values.
    $form_state->setValue('condition', $condition_values->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Allow the condition to submit the form.
    $condition_values = (new FormState())->setValues($form_state->getValue('condition'));
    $this->condition->submitConfigurationForm($form, $condition_values);
    // Update the original form values.
    $form_state->setValue('condition', $condition_values->getValues());

    if ($this->condition instanceof ContextAwarePluginInterface) {
      $this->condition->setContextMapping($condition_values->getValue('context_mapping', []));
    }

    // Set the submission message.
    drupal_set_message($this->submitMessageText());

    $configuration = $this->condition->getConfiguration();
    // If this condition is new, add it to the block_visibility_group.
    if (!isset($configuration['uuid'])) {
      $this->block_visibility_group->addCondition($configuration);
    }

    // Save the block_visibility_group entity.
    $this->block_visibility_group->save();

    $this->setConditionRedirect($form_state);

  }

}
