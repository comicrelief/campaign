<?php

namespace Drupal\yamlform;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Manages and provides HTML email support.
 */
class YamlFormEmailProvider implements YamlFormEmailProviderInterface {

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler to load includes.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new YamlFormEmailProvider.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler class to use for loading includes.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getModules() {
    return [
      // Mail System - https://www.drupal.org/project/mailsystem
      'mailsystem',
      // SMTP Authentication Support - https://www.drupal.org/project/smtp
      'smtp',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function check() {
    // Don't override the system.mail.interface.yamlform if the default interface
    // is the 'test_mail_collector'.
    if ($this->configFactory->get('system.mail')->get('interface.default') == 'test_mail_collector') {
      return $this->uninstall();
    }

    // Check if a contrib module is handling sending email.
    $mail_modules = $this->getModules();
    foreach ($mail_modules as $module) {
      if ($this->moduleHandler->moduleExists($module)) {
        return $this->uninstall();
      }
    }

    // Finally, check if the default mail interface and see if it still uses the
    // php_mail. This check allow unknown contrib modules to handle sending
    // HTML emails.
    if ($this->configFactory->get('system.mail')->get('interface.default') == 'php_mail') {
      return $this->install();
    }
    else {
      return $this->uninstall();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function install() {
    $config = $this->configFactory->getEditable('system.mail');
    $mail_plugins = $config->get('interface');
    $mail_plugins['yamlform'] = 'yamlform_php_mail';
    $config->set('interface', $mail_plugins)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall() {
    $config = $this->configFactory->getEditable('system.mail');
    $mail_plugins = $config->get('interface');
    unset($mail_plugins['yamlform']);
    $config->set('interface', $mail_plugins)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getModule() {
    if ($this->installed()) {
      return 'yamlform';
    }
    else {
      $modules = $this->getModules();
      foreach ($modules as $module) {
        if ($this->moduleHandler->moduleExists($module)) {
          return $module;
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleName() {
    return ($module = $this->getModule()) ? $this->moduleHandler->getName($module) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMailPluginId() {
    $config = $this->configFactory->get('system.mail');
    return $config->get('interface.yamlform') ?: $config->get('interface.default') ?: FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function installed() {
    return ($this->configFactory->get('system.mail')->get('interface.yamlform') == 'yamlform_php_mail');
  }

}
