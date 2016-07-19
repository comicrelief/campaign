<?php

namespace Drupal\devel\Tests;

use Drupal\Component\Render\FormattableMarkup;

use Drupal\simpletest\WebTestBase;

/**
 * Tests devel error handler.
 *
 * @group devel
 */
class DevelErrorHandlerTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['devel'];

  /**
   * Tests devel error handler.
   */
  public function testErrorHandler() {
    $error_notice = [
      '%type' => 'Notice',
      '@message' => 'Undefined variable: undefined',
      '%function' => 'Drupal\devel\Form\SettingsForm->demonstrateErrorHandlers()',
    ];
    $error_warning = [
      '%type' => 'Warning',
      '@message' => 'Division by zero',
      '%function' => 'Drupal\devel\Form\SettingsForm->demonstrateErrorHandlers()',
    ];

    $config = $this->config('system.logging');
    $config->set('error_level', ERROR_REPORTING_DISPLAY_VERBOSE)->save();

    $admin_user = $this->drupalCreateUser(['administer site configuration', 'access devel information']);
    $this->drupalLogin($admin_user);

    // Ensures that the error handler config is present on the config page and
    // by default the standard error handler is selected.
    $error_handlers = \Drupal::config('devel.settings')->get('error_handlers');
    $this->assertEqual($error_handlers, [DEVEL_ERROR_HANDLER_STANDARD => DEVEL_ERROR_HANDLER_STANDARD]);
    $this->drupalGet('admin/config/development/devel');
    $this->assertOptionSelected('edit-error-handlers', DEVEL_ERROR_HANDLER_STANDARD);

    // Ensures that selecting the DEVEL_ERROR_HANDLER_NONE option no error
    // (raw or message) is shown on the site in case of php errors.
    $edit = [
      'error_handlers[]' => DEVEL_ERROR_HANDLER_NONE,
    ];
    $this->drupalPostForm('admin/config/development/devel', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'));
    $error_handlers = \Drupal::config('devel.settings')->get('error_handlers');
    $this->assertEqual($error_handlers, [DEVEL_ERROR_HANDLER_NONE => DEVEL_ERROR_HANDLER_NONE]);
    $this->assertOptionSelected('edit-error-handlers', DEVEL_ERROR_HANDLER_NONE);

    $this->clickLink('notice+warning');
    $this->assertResponse(200, 'Received expected HTTP status code.');
    $this->assertNoRawErrorMessage($error_notice);
    $this->assertNoRawErrorMessage($error_warning);
    $this->assertNoErrorMessage($error_notice);
    $this->assertNoErrorMessage($error_warning);

    // Ensures that selecting the DEVEL_ERROR_HANDLER_BACKTRACE_KINT option a
    // backtrace above the rendered page is shown on the site in case of php
    // errors.
    $edit = [
      'error_handlers[]' => DEVEL_ERROR_HANDLER_BACKTRACE_KINT,
    ];
    $this->drupalPostForm('admin/config/development/devel', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'));
    $error_handlers = \Drupal::config('devel.settings')->get('error_handlers');
    $this->assertEqual($error_handlers, [DEVEL_ERROR_HANDLER_BACKTRACE_KINT => DEVEL_ERROR_HANDLER_BACKTRACE_KINT]);
    $this->assertOptionSelected('edit-error-handlers', DEVEL_ERROR_HANDLER_BACKTRACE_KINT);

    $this->clickLink('notice+warning');
    $this->assertResponse(200, 'Received expected HTTP status code.');
    $this->assertRawErrorMessage($error_notice);
    $this->assertRawErrorMessage($error_warning);

    // Ensures that selecting the DEVEL_ERROR_HANDLER_BACKTRACE_DPM option a
    // backtrace in the message area is shown on the site in case of php errors.
    $edit = [
      'error_handlers[]' => DEVEL_ERROR_HANDLER_BACKTRACE_DPM,
    ];
    $this->drupalPostForm('admin/config/development/devel', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'));
    $error_handlers = \Drupal::config('devel.settings')->get('error_handlers');
    $this->assertEqual($error_handlers, [DEVEL_ERROR_HANDLER_BACKTRACE_DPM => DEVEL_ERROR_HANDLER_BACKTRACE_DPM]);
    $this->assertOptionSelected('edit-error-handlers', DEVEL_ERROR_HANDLER_BACKTRACE_DPM);

    $this->clickLink('notice+warning');
    $this->assertResponse(200, 'Received expected HTTP status code.');
    $this->assertErrorMessage($error_notice);
    $this->assertErrorMessage($error_warning);

    // Ensures that when multiple handlers are selected, the output produced by
    // every handler is shown on the site in case of php errors.
    $edit = [
      'error_handlers[]' => [
        DEVEL_ERROR_HANDLER_BACKTRACE_DPM => DEVEL_ERROR_HANDLER_BACKTRACE_DPM,
        DEVEL_ERROR_HANDLER_BACKTRACE_KINT => DEVEL_ERROR_HANDLER_BACKTRACE_KINT,
      ]
    ];
    $this->drupalPostForm('admin/config/development/devel', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'));
    $error_handlers = \Drupal::config('devel.settings')->get('error_handlers');
    $this->assertEqual($error_handlers, [
      DEVEL_ERROR_HANDLER_BACKTRACE_DPM => DEVEL_ERROR_HANDLER_BACKTRACE_DPM,
      DEVEL_ERROR_HANDLER_BACKTRACE_KINT => DEVEL_ERROR_HANDLER_BACKTRACE_KINT,
    ]);
    $this->assertOptionSelected('edit-error-handlers', DEVEL_ERROR_HANDLER_BACKTRACE_DPM);
    $this->assertOptionSelected('edit-error-handlers', DEVEL_ERROR_HANDLER_BACKTRACE_KINT);

    $this->clickLink('notice+warning');
    $this->assertResponse(200, 'Received expected HTTP status code.');
    $this->assertRawErrorMessage($error_notice);
    $this->assertRawErrorMessage($error_warning);
    $this->assertErrorMessage($error_notice);
    $this->assertErrorMessage($error_warning);

    // Ensures that setting the error reporting to all the output produced by
    // handlers is shown on the site in case of php errors.
    $config->set('error_level', ERROR_REPORTING_DISPLAY_ALL)->save();
    $this->clickLink('notice+warning');
    $this->assertResponse(200, 'Received expected HTTP status code.');
    $this->assertRawErrorMessage($error_notice);
    $this->assertRawErrorMessage($error_warning);
    $this->assertErrorMessage($error_notice);
    $this->assertErrorMessage($error_warning);

    // Ensures that setting the error reporting to some the output produced by
    // handlers is shown on the site in case of php errors.
    $config->set('error_level', ERROR_REPORTING_DISPLAY_SOME)->save();
    $this->assertResponse(200, 'Received expected HTTP status code.');
    $this->clickLink('notice+warning');
    $this->assertRawErrorMessage($error_notice);
    $this->assertRawErrorMessage($error_warning);
    $this->assertErrorMessage($error_notice);
    $this->assertErrorMessage($error_warning);

    // Ensures that setting the error reporting to none the output produced by
    // handlers is not shown on the site in case of php errors.
    $config->set('error_level', ERROR_REPORTING_HIDE)->save();
    $this->clickLink('notice+warning');
    $this->assertResponse(200, 'Received expected HTTP status code.');
    $this->assertNoRawErrorMessage($error_notice);
    $this->assertNoRawErrorMessage($error_warning);
    $this->assertNoErrorMessage($error_notice);
    $this->assertNoErrorMessage($error_warning);

    // The errors are expected. Do not interpret them as a test failure.
    // Not using File API; a potential error must trigger a PHP warning.
    unlink(\Drupal::root() . '/' . $this->siteDirectory . '/error.log');
  }

  /**
   * Helper function: assert that the error message is found.
   *
   * @param array $error
   *   The error to check.
   */
  protected function assertRawErrorMessage(array $error) {
    $message = new FormattableMarkup('%type: @message in %function (line ', $error);
    $this->assertRaw($message, new FormattableMarkup('Found raw error message: @message.', ['@message' => $message]));
  }

  /**
   * Helper function: assert that the error message is not found.
   *
   *
   * @param array $error
   *   The error to check.
   */
  protected function assertNoRawErrorMessage(array $error) {
    $message = new FormattableMarkup('%type: @message in %function (line ', $error);
    $this->assertNoRaw($message, new FormattableMarkup('Did not find raw error message: @message.', ['@message' => $message]));
  }

  /**
   * Helper function: assert that the error message is found.
   *
   * @param array $error
   *   The error to check.
   */
  protected function assertErrorMessage(array $error) {
    $pattern = '//div[contains(@class, "messages--warning")]//pre[contains(., :content)]';
    $message = new FormattableMarkup('%type: @message in %function (line ', $error);
    $message = html_entity_decode(strip_tags((string) $message));
    $xpath = $this->xpath($pattern, [':content' => $message]);
    $this->assertTrue(!empty($xpath), new FormattableMarkup('Found error message: @message.', ['@message' => $message]));
  }

  /**
   * Helper function: assert that the error message is not found.
   *
   * @param array $error
   *   The error to check.
   */
  protected function assertNoErrorMessage(array $error) {
    $pattern = '//div[contains(@class, "messages--warning")]//pre[contains(., :content)]';
    $message = new FormattableMarkup('%type: @message in %function (line ', $error);
    $message = html_entity_decode(strip_tags((string) $message));
    $xpath = $this->xpath($pattern, [':content' => $message]);
    $this->assertTrue(empty($xpath), new FormattableMarkup('Found error message: @message.', ['@message' => $message]));
  }

}
