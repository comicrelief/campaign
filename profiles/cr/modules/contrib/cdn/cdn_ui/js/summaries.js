(function ($, Drupal) {

  'use strict';

  /**
   * Provides the summaries for the vertical tabs in the CDN UI's settings form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   */
  Drupal.behaviors.cdnSettingsSummary = {
    attach: function () {
      $('[data-drupal-selector="edit-status"]').drupalSetSummary(function () {
        return document.querySelector('input[name="status"]').checked ? Drupal.t('Enabled') : Drupal.t('Disabled');
      });

      $('[data-drupal-selector="edit-mapping"]').drupalSetSummary(function () {
        if (document.querySelector('select[name="mapping[type]"]').value === 'simple') {
          var domain = document.querySelector('input[name="mapping[simple][domain]"]').value;
          return Drupal.t('Simple: !domain', {'!domain': domain ? domain : Drupal.t('none configured yet')});
        }
        else {
          return Drupal.t('Advanced: <code>cdn.settings.yml</code>');
        }
      });

      $('[data-drupal-selector="edit-farfuture"]').drupalSetSummary(function () {
        return document.querySelector('input[name="farfuture[status]"]').checked ? Drupal.t('Enabled') : Drupal.t('Disabled');
      });
    }
  };

})(jQuery, Drupal);
