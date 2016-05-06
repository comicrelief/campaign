<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormHandlerAddForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormHandlerManager;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an add form for YAML form handler.
 */
class YamlFormHandlerAddForm extends YamlFormHandlerFormBase {

  /**
   * The YAML form handler manager.
   *
   * @var \Drupal\yamlform\YamlFormHandlerManager
   */
  protected $yamlformHandlerManager;

  /**
   * Constructs a new YamlFormHandlerAddForm.
   *
   * @param \Drupal\yamlform\YamlFormHandlerManager $yamlform_handler
   *   The YAML form handler manager.
   */
  public function __construct(YamlFormHandlerManager $yamlform_handler) {
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

    $form['#title'] = $this->t('Add %label handler', ['%label' => $this->yamlformHandler->label()]);
    $form['actions']['submit']['#value'] = $this->t('Add');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareYamlFormHandler($yamlform_handler) {
    $yamlform_handler = $this->yamlformHandlerManager->createInstance($yamlform_handler);
    // Initialize the handler an pass in the YAML form.
    $yamlform_handler->init($this->yamlform);
    // Set the initial weight so this handler comes last.
    $yamlform_handler->setWeight(count($this->yamlform->getHandlers()));
    return $yamlform_handler;
  }

}
