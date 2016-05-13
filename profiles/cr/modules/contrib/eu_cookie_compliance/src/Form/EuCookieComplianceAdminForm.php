<?php

/**
 * @file
 * Contains \Drupal\eu_cookie_compliance\Form\EuCookieComplianceAdminForm.
 */

namespace Drupal\eu_cookie_compliance\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides form for cookie control popup.
 */
class EuCookieComplianceAdminForm extends ConfigFormBase {

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * Constructs an EuCookieComplianceAdminForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AliasManagerInterface $alias_manager, PathValidatorInterface $path_validator, RequestContext $request_context) {
    parent::__construct($config_factory);

    $this->aliasManager = $alias_manager;
    $this->pathValidator = $path_validator;
    $this->requestContext = $request_context;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('path.alias_manager'),
      $container->get('path.validator'),
      $container->get('router.request_context')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eu_cookie_compliance_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'eu_cookie_compliance.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('eu_cookie_compliance.settings');

    $form['domain'] = array(
      '#type' => 'textfield',
      '#title' => t('Domain'),
      '#default_value' => $config->get('domain'),
      '#description' => t('Sets the domain of the cookie to a specific url.  Used when you need consistency across domains.  This is language independent.'),
    );

    $form['eu_cookie_compliance'] = array(
      '#type'  => 'details',
      '#title' => t('Settings'),
      '#open' => TRUE,
    );

    $form['eu_cookie_compliance']['popup_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable popup'),
      '#default_value' => $config->get('popup_enabled'),
    );

    $form['eu_cookie_compliance']['popup_clicking_confirmation'] = array(
      '#type' => 'checkbox',
      '#title' => t('Consent by clicking'),
      '#default_value' => $config->get('popup_clicking_confirmation'),
      '#description' => t('By default by clicking any link on the website the visitor accepts the cookie policy. Uncheck this box if you do not require this functionality. You may want to edit the pop-up message below accordingly.'),
    );

    $form['eu_cookie_compliance']['popup_position'] = array(
      '#type' => 'checkbox',
      '#title' => t('Place the pop-up at the top of the website'),
      '#default_value' => $config->get('popup_position'),
      '#description' => t('By default the pop-up appears at the bottom of the website. Tick this box if you want it to appear at the top'),
    );

    $form['eu_cookie_compliance']['popup_agree_button_message'] = array(
      '#type' => 'textfield',
      '#title' => t('Agree button message'),
      '#default_value' => $config->get('popup_agree_button_message'),
      '#size' => 30,
      '#required' => TRUE,
    );

    $form['eu_cookie_compliance']['popup_disagree_button_message'] = array(
      '#type' => 'textfield',
      '#title' => t('Disagree button message'),
      '#default_value' => $config->get('popup_disagree_button_message'),
      '#size' => 30,
      '#required' => TRUE,
    );

    $form['eu_cookie_compliance']['popup_info'] = array(
      '#type' => 'text_format',
      '#title' => t('Popup message - requests consent'),
      '#default_value' => $config->get('popup_info.value'),
      '#required' => TRUE,
      '#format' => !empty($config->get('popup_info.format')) ? $config->get('popup_info.format') : filter_default_format(),
    );

    $form['eu_cookie_compliance']['popup_agreed_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable thank you message'),
      '#default_value' => $config->get('popup_agreed_enabled'),
    );

    $form['eu_cookie_compliance']['popup_hide_agreed'] = array(
      '#type' => 'checkbox',
      '#title' => t('Clicking hides thank you message'),
      '#default_value' => $config->get('popup_hide_agreed'),
      '#description' => t('Clicking a link hides the thank you message automatically.'),
    );

    $form['eu_cookie_compliance']['popup_find_more_button_message'] = array(
      '#type' => 'textfield',
      '#title' => t('Find more button message'),
      '#default_value' => $config->get('popup_find_more_button_message'),
      '#size' => 30,
      '#required' => TRUE,
    );

    $form['eu_cookie_compliance']['popup_hide_button_message'] = array(
      '#type' => 'textfield',
      '#title' => t('Hide button message'),
      '#default_value' => $config->get('popup_hide_button_message'),
      '#size' => 30,
      '#required' => TRUE,
    );

    $form['eu_cookie_compliance']['popup_agreed'] = array(
      '#type' => 'text_format',
      '#title' => t('Popup message - thanks for giving consent'),
      '#default_value' => !empty($config->get('popup_agreed')['value']) ? $config->get('popup_agreed')['value'] : '',
      '#required' => TRUE,
      '#format' => !empty($config->get('popup_agreed')['format']) ? $config->get('popup_agreed')['format'] : filter_default_format(),
    );

    // In the current popup-link is an internal link, try to load the alias for
    // that path.
    $link = !empty($config->get('popup_link')) ? $config->get('popup_link') : '';
    if ($link && parse_url($link, PHP_URL_SCHEME) === 'internal') {
      $link = explode(':', $link, 2)[1];
      $link = $this->aliasManager->getAliasByPath($link);
    }
    $form['eu_cookie_compliance']['popup_link'] = array(
      '#type' => 'textfield',
      '#title' => t('Privacy policy link'),
      '#default_value' => $link,
      '#size' => 60,
      '#maxlength' => 220,
      '#required' => TRUE,
      '#description' => t('Enter link to your privacy policy or other page that will explain cookies to your users. Internal links should start with a forward slash (/), external links should start with http:// or https://.'),
    );

    $form['eu_cookie_compliance']['popup_link_new_window'] = array(
      '#type' => 'checkbox',
      '#title' => t('Open privacy policy link in a new window'),
      '#default_value' => $config->get('popup_link_new_window'),
    );

    $form['eu_cookie_compliance']['popup_height'] = array(
      '#type' => 'number',
      '#title' => t('Popup height in pixels'),
      '#default_value' => !empty($config->get('popup_height')) ? $config->get('popup_height') : '',
      '#field_suffix' => ' ' . t('pixels'),
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => FALSE,
      '#description' => t('Enter an integer value for a desired height in pixels or leave empty for automatically adjusted height'),
    );

    $form['eu_cookie_compliance']['popup_width'] = array(
      '#type' => 'textfield',
      '#title' => t('Popup width in pixels or a percentage value'),
      '#default_value' => $config->get('popup_width'),
      '#field_suffix' => ' ' . t('px or %'),
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => TRUE,
      '#description' => t('Set the width of the popup. This can be either an integer value or percentage of the screen width. For example: 200 or 50%'),
    );

    $form['eu_cookie_compliance']['popup_delay'] = array(
      '#type' => 'number',
      '#title' => t('Popup time delay in seconds'),
      '#default_value' => $config->get('popup_delay'),
      '#field_suffix' => ' ' . t('seconds'),
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => TRUE,
    );

    $form_color_picker_type = 'textfield';

    if (\Drupal::moduleHandler()->moduleExists('jquery_colorpicker')) {
      $form_color_picker_type = 'jquery_colorpicker';
    }

    $form['eu_cookie_compliance']['popup_bg_hex'] = array(
      '#type' => $form_color_picker_type,
      '#title' => t('Background Color'),
      // Garland colors :).
      '#default_value' => $config->get('popup_bg_hex'),
      '#description' => t('Change the background color of the popup. Provide HEX value without the #'),
      '#element_validate' => array('eu_cookie_compliance_validate_hex'),
    );

    $form['eu_cookie_compliance']['popup_text_hex'] = array(
      '#type' => $form_color_picker_type,
      '#title' => t('Text Color'),
      '#default_value' => $config->get('popup_text_hex'),
      '#description' => t('Change the text color of the popup. Provide HEX value without the #'),
      '#element_validate' => array('eu_cookie_compliance_validate_hex'),
    );
    // Adding option to add/remove popup on specified domains
    $exclude_domains_option_active = array(
      0 => t('Add'),
      1 => t('Remove'),
    );
    $form['eu_cookie_compliance']['domains_option'] = array(
      '#type' => 'radios',
      '#title' => t('Add/Remove popup on specified domains'),
      '#default_value' => $config->get('domains_option'),
      '#options' => $exclude_domains_option_active,
      '#description' => t("Specify if you want to add or remove popup on the listed below domains."),
    );
    $form['eu_cookie_compliance']['domains_list'] = array(
      '#type' => 'textarea',
      '#title' => t('Domains list'),
      '#default_value' => $config->get('domains_list'),
      '#description' => t("Specify domains with protocol (e.g. http or https). Enter one domain per line."),
    );

    $form['eu_cookie_compliance']['exclude_paths'] = array(
      '#type' => 'textarea',
      '#title' => t('Exclude paths'),
      '#default_value' => !empty($config->get('exclude_paths')) ? $config->get('exclude_paths') : '',
      '#description' => t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array('%blog' => '/blog', '%blog-wildcard' => '/blog/*', '%front' => '<front>')),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->isValueEmpty('popup_link')) {
      $popup_link = $form_state->getValue('popup_link');

      // If the popup link points does not point to an external resource,
      // prepend the 'internal:' scheme.
      if (parse_url($popup_link, PHP_URL_SCHEME) === NULL) {
        if (!in_array($popup_link[0], ['/'])) {
          $form_state->setErrorByName('popup_link', t('Internal paths should start with a "/".'));
        }
        else {
          $popup_link = 'internal:' . $popup_link;

          // If the link contains a fragment then check if it validates then
          // rewrite link with full url.
          if ((strpos($popup_link, '#') !== FALSE)) {
            $fragment = explode('#', $popup_link);
            $link = Url::fromUri($fragment[0], array(
              'fragment' => $fragment[1],
              'absolute' => TRUE,
            ));
            $form_state->setErrorByName('popup_link', t('Looks like your privacy policy link contains fragment #, you should make this an absolute url eg @link', array('@link' => $link->toString())));
          }

          $form_state->setValueForElement($form['eu_cookie_compliance']['popup_link'], $this->aliasManager->getPathByAlias($popup_link));
        }
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('eu_cookie_compliance.settings')
      ->set('domain', $form_state->getValue('domain'))
      ->set('popup_enabled', $form_state->getValue('popup_enabled'))
      ->set('popup_clicking_confirmation', $form_state->getValue('popup_clicking_confirmation'))
      ->set('popup_position', $form_state->getValue('popup_position'))
      ->set('popup_agree_button_message', $form_state->getValue('popup_agree_button_message'))
      ->set('popup_disagree_button_message', $form_state->getValue('popup_disagree_button_message'))
      ->set('popup_info', $form_state->getValue('popup_info'))
      ->set('popup_agreed_enabled', $form_state->getValue('popup_agreed_enabled'))
      ->set('popup_hide_agreed', $form_state->getValue('popup_hide_agreed'))
      ->set('popup_find_more_button_message', $form_state->getValue('popup_find_more_button_message'))
      ->set('popup_hide_button_message', $form_state->getValue('popup_hide_button_message'))
      ->set('popup_agreed', $form_state->getValue('popup_agreed'))
      ->set('popup_link', $form_state->getValue('popup_link'))
      ->set('popup_link_new_window', $form_state->getValue('popup_link_new_window'))
      ->set('popup_height', $form_state->getValue('popup_height'))
      ->set('popup_width', $form_state->getValue('popup_width'))
      ->set('popup_delay', $form_state->getValue('popup_delay'))
      ->set('popup_bg_hex', $form_state->getValue('popup_bg_hex'))
      ->set('popup_text_hex', $form_state->getValue('popup_text_hex'))
      ->set('domains_option', $form_state->getValue('domains_option'))
      ->set('domains_list', $form_state->getValue('domains_list'))
      ->set('exclude_paths', $form_state->getValue('exclude_paths'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
