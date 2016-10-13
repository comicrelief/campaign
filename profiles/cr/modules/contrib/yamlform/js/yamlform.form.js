/**
 * @file
 * Javascript behaviors for forms.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Autofocus first input.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the form autofocusing.
   */
  Drupal.behaviors.yamlFormAutofocus = {
    attach: function (context) {
      $(context).find('.yamlform-submission-form.js-yamlform-autofocus :input:visible:enabled:first').focus();
    }
  };

  /**
   * Prevent form autosubmit on wizard pages.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for disabling form autosubmit.
   */
  Drupal.behaviors.yamlFormDisableAutoSubmit = {
    attach: function (context) {
      // @see http://stackoverflow.com/questions/11235622/jquery-disable-form-submit-on-enter
      $(context).find('.yamlform-submission-form.js-yamlform-disable-autosubmit input').once().on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
          e.preventDefault();
          return false;
        }
      });
    }
  };

  /**
   * Skip client-side validation when submit button is pressed.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the skipping client-side validation.
   */
  Drupal.behaviors.yamlFormSubmitNoValidate = {
    attach: function (context) {
      $(context).find(':button.js-yamlform-novalidate').once().on('click', function () {
        $(this.form).attr('novalidate', 'novalidate');
      });
    }
  };

  /**
   * Disable validate when save draft submit button is clicked.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the form draft submit button.
   */
  Drupal.behaviors.yamlFormDraft = {
    attach: function (context) {
      $(context).find('#edit-draft').once().on('click', function () {
        $(this.form).attr('novalidate', 'novalidate');
      });
    }
  };

  /**
   * Filters the form element list by a text input search string.
   *
   * The text input will have the selector `input.yamlform-form-filter-text`.
   *
   * The target element to do searching in will be in the selector
   * `input.yamlform-form-filter-text[data-element]`
   *
   * The text source where the text should be found will have the selector
   * `.yamlform-form-filter-text-source`
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the form element filtering.
   */
  Drupal.behaviors.yamlformFilterByText = {
    attach: function (context, settings) {
      var $input = $('input.yamlform-form-filter-text').once('yamlform-form-filter-text');
      var $table = $($input.attr('data-element'));
      var $filter_rows;

      /**
       * Filters the form element list.
       *
       * @param {jQuery.Event} e
       *   The jQuery event for the keyup event that triggered the filter.
       */
      function filterElementList(e) {
        var query = $(e.target).val().toLowerCase();

        /**
         * Shows or hides the form element entry based on the query.
         *
         * @param {number} index
         *   The index in the loop, as provided by `jQuery.each`
         * @param {HTMLElement} label
         *   The label of the yamlform.
         */
        function toggleEntry(index, label) {
          var $label = $(label);
          var $row = $label.parent().parent();
          var textMatch = $label.text().toLowerCase().indexOf(query) !== -1;
          $row.toggle(textMatch);
        }

        // Filter if the length of the query is at least 2 characters.
        if (query.length >= 2) {
          $filter_rows.each(toggleEntry);
        }
        else {
          $filter_rows.each(function (index) {
            $(this).parent().parent().show();
          });
        }
      }

      if ($table.length) {
        $filter_rows = $table.find('div.yamlform-form-filter-text-source');
        $input.on('keyup', filterElementList);
      }
    }
  };

})(jQuery, Drupal);
