<?php

namespace Drupal\yamlform_ui\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormOptionsInterface;

/**
 * Defines the custom access control handler for the form UI.
 */
class YamlFormUiAccess {

  /**
   * Check that form source can be updated by a user.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkYamlFormSourceAccess(YamlFormInterface $yamlform, AccountInterface $account) {
    return AccessResult::allowedIf($yamlform->access('update', $account) && $account->hasPermission('edit yamlform source'));
  }

  /**
   * Check that form option source can be updated by a user.
   *
   * @param \Drupal\yamlform\YamlFormOptionsInterface $yamlform_options
   *   A form options entity.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkYamlFormOptionSourceAccess(YamlFormOptionsInterface $yamlform_options, AccountInterface $account) {
    return AccessResult::allowedIf($yamlform_options->access('update', $account) && $account->hasPermission('edit yamlform source'));
  }

  /**
   * Check that form can be updated by a user.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkYamlFormEditAccess(YamlFormInterface $yamlform, AccountInterface $account) {
    return AccessResult::allowedIf(!$yamlform->hasTranslations() && $yamlform->access('update', $account));
  }

}
