<?php

/**
 * @file
 * Contains Drupal\cookieconsent\Form\SettingsForm.
 */

namespace Drupal\cookieconsent\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\cookieconsent\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cookieconsent.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cookieconsent_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cookieconsent.settings');

    $form['minified'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use the minified version (cookieconsent.min.js) of the Cookie Consent javascript plugin'),
      '#description' => $this->t('If you want to be able to debug the javascript (by using cookieconsent.js), uncheck this box.'),
      '#default_value' => $config->get('minified'),
    ];
    $form['theme'] = [
      '#type' => 'select',
      '#options' => array(
        'none' => $this->t('- None -'),
        'dark-top' => $this->t('Dark Top'),
        'dark-floating' => $this->t('Dark Floating'),
        'dark-bottom' => $this->t('Dark Bottom'),
        'light-floating' => $this->t('Light Floating'),
        'light-top' => $this->t('Light Top'),
        'light-bottom' => $this->t('Light Bottom'),
      ),
      '#title' => $this->t('Choose your theme'),
      '#description' => $this->t('Select the theme you wish to use.'),
      '#default_value' => $config->get('theme'),
    ];
    $form['theme_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to custom CSS file'),
      '#description' => $this->t('Specify the path to the custom CSS file to use (e.g. <em>themes/[your-theme]/css/cookie-consent.css</em>). If you haven\'t selected a theme above, this custom CSS file is the only one loaded.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('theme_path'),
    ];
    $form['texts'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#title' => $this->t('Custom texts'),
    ];
    $form['texts']['customise'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Customise the text'),
      '#description' => $this->t('Customise the text used on the cookie bar'),
      '#default_value' => $config->get('customise'),
    ];
    $form['texts']['headline_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Headline Text'),
      '#description' => $this->t('The message shown by the plugin.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('headline_text'),
      '#states' => array(
        'visible' => array(
          ':input[name="customise"]' => array('checked' => TRUE),
        ),
      ),
    ];
    $form['texts']['accept_button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accept button text'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('accept_button_text'),
      '#states' => array(
        'visible' => array(
          ':input[name="customise"]' => array('checked' => TRUE),
        ),
      ),
    ];
    $form['texts']['read_more_button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Read more button text'),
      '#description' => $this->t('The text shown on the link to the cookie policy (requires the Cookie policy option to also be set)'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('read_more_button_text'),
      '#states' => array(
        'visible' => array(
          ':input[name="customise"]' => array('checked' => TRUE),
        ),
      ),
    ];
    $form['cookie_policy'] = [
      '#type' => 'url',
      '#title' => $this->t('Your cookie policy'),
      '#description' => $this->t('If you already have a cookie policy, link to it here.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('cookie_policy'),
    ];
    $form['container'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Container Element'),
      '#description' => $this->t('The element you want the Cookie Consent notification to be appended to. When left empty, the Cookie Consent plugin is appended to the body.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('container'),
    ];
    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#description' => $this->t('The path for the consent cookie that Cookie Consent uses, to remember that users have consented to cookies. Use to limit consent to a specific path within your website.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('path'),
    ];
    $form['expiry'] = [
      '#type' => 'number',
      '#title' => $this->t('Expiry days'),
      '#description' => $this->t('The number of days Cookie Consent should store the user’s consent information for.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('expiry'),
    ];
    $form['target'] = [
      '#type' => 'select',
      '#options' => [
        '_blank' => t('_blank (a new window or tab)'),
        '_self' => t('_self (the same frame as it was clicked)'),
        '_parent' => t('_parent (the parent frame)'),
        '_top' => t('_top (the full body of the window)'),
      ],
      '#title' => $this->t('Target'),
      '#description' => $this->t('The <em>target</em> of the link to your cookie policy. Use to open a link in a new window, if you wish.'),
      '#default_value' => !empty($config->get('target')) ? $config->get('target') : '_self',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('cookieconsent.settings')
      ->set('minified', $form_state->getValue('minified'))
      ->set('theme', $form_state->getValue('theme'))
      ->set('theme_path', $form_state->getValue('theme_path'))
      ->set('customise', $form_state->getValue('customise'))
      ->set('headline_text', $form_state->getValue('headline_text'))
      ->set('accept_button_text', $form_state->getValue('accept_button_text'))
      ->set('read_more_button_text', $form_state->getValue('read_more_button_text'))
      ->set('cookie_policy', $form_state->getValue('cookie_policy'))
      ->set('container', $form_state->getValue('container'))
      ->set('path', $form_state->getValue('path'))
      ->set('expiry', $form_state->getValue('expiry'))
      ->set('target', $form_state->getValue('target'))
      ->save();
  }

}
