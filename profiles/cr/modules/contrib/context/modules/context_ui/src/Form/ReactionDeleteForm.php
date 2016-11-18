<?php

namespace Drupal\context_ui\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\context\ContextManager;
use Drupal\context\ContextInterface;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\context\ContextReactionInterface;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

class ReactionDeleteForm extends ConfirmFormBase implements ContainerInjectionInterface {

  /**
   * @var ContextInterface
   */
  protected $context;

  /**
   * @var ContextReactionInterface
   */
  protected $reaction;

  /**
   * The Context module context manager.
   *
   * @var ContextManager
   */
  protected $contextManager;

  /**
   * Construct.
   *
   * @param ContextManager $contextManager
   */
  function __construct(ContextManager $contextManager) {
    $this->contextManager = $contextManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('context.manager')
    );
  }

  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove the @reaction reaction.', [
      '@reaction' => $this->reaction->getPluginDefinition()['label'],
    ]);
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {
    return $this->context->urlInfo();
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'context_reaction_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContextInterface $context = NULL, $reaction_id = NULL) {
    $this->context = $context;
    $this->reaction = $this->context->getReaction($reaction_id);

    $form = parent::buildForm($form, $form_state);

    // Remove the cancel button if this is an AJAX request since Drupals built
    // in modal dialogues does not handle buttons that are not a primary
    // button very well.
    if ($this->getRequest()->isXmlHttpRequest()) {
      unset($form['actions']['cancel']);
    }

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::submitFormAjax',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $definition = $this->reaction->getPluginDefinition();

    $this->context->removeReaction($this->reaction->getPluginId());

    $this->context->save();

    // If this is not an AJAX request then redirect and show a message.
    if (!$this->getRequest()->isXmlHttpRequest()) {
      drupal_set_message($this->t('The @label context reaction has been removed.', [
          '@label' => $definition['label']]
      ));

      $form_state->setRedirectUrl($this->getCancelUrl());
    }
  }

  /**
   * Handle when the form is submitted through AJAX.
   *
   * @return AjaxResponse
   */
  public function submitFormAjax() {
    $response = new AjaxResponse();

    $contextForm = $this->contextManager->getForm($this->context, 'edit');

    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand(new ReplaceCommand('#context-reactions', $contextForm['reactions']));

    return $response;
  }
}
