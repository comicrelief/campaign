(function ($, Drupal) {

  'use strict';

  function getLabelForRadio(selector) {
    var inputId = document.querySelector(selector).id;
    return document.querySelector('label[for=' + inputId + ']').textContent;
  }

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
        return getLabelForRadio('input[name=status]:checked');
      });

      $('[data-drupal-selector="edit-mapping"]').drupalSetSummary(function () {
        if (document.querySelector('select[name="mapping[type]"]').value === 'simple') {
          var domain = document.querySelector('input[name="mapping[simple][domain]"]').value;
          return Drupal.t('Simple: !domain', {'!domain': domain ? domain : Drupal.t('none configured yet') });
        }
        else {
          return Drupal.t('Advanced: <code>cdn.settings.yml</code>');
        }
      });

    }
  };

})(jQuery, Drupal);
