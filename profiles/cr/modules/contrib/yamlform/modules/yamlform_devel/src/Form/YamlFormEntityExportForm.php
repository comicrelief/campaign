<?php

namespace Drupal\yamlform_devel\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Utility\YamlFormTidy;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export form configuration.
 */
class YamlFormEntityExportForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['yaml'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t("Here is your form's configuration:"),
      '#description' => $this->t('Filename: %file', ['%file' => $this->getConfigName() . '.yml']),
      '#default_value' => $this->getYaml(),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element['download'] = [
      '#type' => 'submit',
      '#value' => $this->t('Download'),
      '#button_type' => 'primary',
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $content = $this->getYaml();
    $filename = $this->getConfigName() . '.yml';
    $headers = [
      'Content-Type' => 'text/yaml',
      'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
    ];
    $response = new Response($content, 200, $headers);
    $form_state->setResponse($response);
  }

  /**
   * Get the form's raw data.
   *
   * @return string
   *   The form's raw data.
   */
  protected function getYaml() {
    $config_name = $this->getConfigName();
    $data = $this->config($config_name)->getRawData();
    $yaml = Yaml::encode($data);
    return YamlFormTidy::tidy($yaml);
  }

  /**
   * Get the form's config file name (without *.yml).
   *
   * @return string
   *   The form's config file name (without *.yml).
   */
  protected function getConfigName() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->entity;
    $definition = $this->entityTypeManager->getDefinition('yamlform');
    return $definition->getConfigPrefix() . '.' . $yamlform->getConfigTarget();
  }

}
