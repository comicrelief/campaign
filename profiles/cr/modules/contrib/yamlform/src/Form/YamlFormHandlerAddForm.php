<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormHandlerManagerInterface;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an add form for form handler.
 */
class YamlFormHandlerAddForm extends YamlFormHandlerFormBase {

  /**
   * The form handler manager.
   *
   * @var \Drupal\yamlform\YamlFormHandlerManagerInterface
   */
  protected $yamlformHandlerManager;

  /**
   * Constructs a new YamlFormHandlerAddForm.
   *
   * @param \Drupal\yamlform\YamlFormHandlerManagerInterface $yamlform_handler
   *   The form handler manager.
   */
  public function __construct(YamlFormHandlerManagerInterface $yamlform_handler) {
    $this->yamlformHandlerManager = $yamlform_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $yamlform_handler = NULL) {
    $form = parent::buildForm($form, $form_state, $yamlform, $yamlform_handler);
    $form['#title'] = $this->t('Add @label handler', ['@label' => $this->yamlformHandler->label()]);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareYamlFormHandler($yamlform_handler) {
    $yamlform_handler = $this->yamlformHandlerManager->createInstance($yamlform_handler);
    // Initialize the handler an pass in the form.
    $yamlform_handler->init($this->yamlform);
    // Set the initial weight so this handler comes last.
    $yamlform_handler->setWeight(count($this->yamlform->getHandlers()));
    return $yamlform_handler;
  }

}
