/**
 * @file
 * Javascript behaviors for details element.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attach handler to save details open/close state.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormDetailsSave = {
    attach: function (context) {
      if (!window.localStorage) {
        return;
      }

      // Summary click event handler.
      $('details > summary', context).once().click(function () {
        var $details = $(this).parent();

        var name = Drupal.yamlFormDetailsSaveGetName($details);
        if (!name) {
          return;
        }

        var open = ($details.attr('open') != 'open') ? 1 : 0;
        localStorage.setItem(name, open);
      });

      // Initialize details open state via local storage.
      $('details', context).once().each(function () {
        var $details = $(this);

        var name = Drupal.yamlFormDetailsSaveGetName($details);
        if (!name) {
          return;
        }

        var open = localStorage.getItem(name);
        if (open === null) {
          return;
        }

        if (open == 1) {
          $details.attr('open', 'open');
        }
        else {
          $details.removeAttr('open');
        }
      });
    }

  };

  /**
   * Get the name used to store the state of details element.
   *
   * @param $details
   *   A details element.
   *
   * @returns string
   *   The name used to store the state of details element.
   */
  Drupal.yamlFormDetailsSaveGetName = function($details) {
    if (!window.localStorage) {
      return '';
    }

    // Any details element not included a form must have define its own id.
    var yamlformId = $details.attr('data-yamlform-element-id');
    if (yamlformId) {
      return 'yamlform.' + yamlformId.replace('--', '.');
    }

    var detailsId = $details.attr('id');
    if (!detailsId) {
      return '';
    }

    var $form = $details.parents('form');
    if (!$form.length || !$form.attr('id')) {
      return '';
    }

    var formId = $form.attr('id');
    if (!formId) {
      return '';
    }

    // ISSUE: When Drupal renders a form  in a modal dialog it appends a unique
    // identifier to form ids and details ids. (ie my-form--FeSFISegTUI)
    // WORKAROUND: Remove the unique id that delimited using double dashes.
    formId = formId.replace(/--.+?$/, '').replace(/-/g, '_');
    detailsId = detailsId.replace(/--.+?$/, '').replace(/-/g, '_');
    return 'yamlform.' + formId + '.' + detailsId;
  }


})(jQuery, Drupal);
