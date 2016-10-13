<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base for controller for form third party settings.
 */
class YamlFormEntityThirdPartySettingsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager */
    $third_party_settings_manager = \Drupal::service('yamlform.third_party_settings_manager');
    $form = $third_party_settings_manager->buildForm($form, $form_state);
    $form_state->set('yamlform', $this->getEntity());
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);
    // Don't display delete button.
    unset($element['delete']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();
    $third_party_settings = $form_state->getValue('third_party_settings');
    foreach ($third_party_settings as $module => $third_party_setting) {
      foreach ($third_party_setting as $key => $value) {
        $yamlform->setThirdPartySetting($module, $key, $value);
      }
    }
    $yamlform->save();

    $this->logger('yamlform')->notice('Form settings @label saved.', ['@label' => $yamlform->label()]);
    drupal_set_message($this->t('Form settings %label saved.', ['%label' => $yamlform->label()]));
  }

}
