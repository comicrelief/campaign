/**
 * @file
 * Javascript behaviors for other elements.
 */

(function ($, Drupal) {

  "use strict";

  /**
   * Toggle other input (text) field.
   *
   * @param {boolean} show
   *   TRUE will display the text field. FALSE with hide and clear the text field.
   * @param {object} $input
   *   The input (text) field to be toggled.
   */
  function toggleOther(show, $input) {
    if (show) {
      // Limit the other inputs width to the parent's container.
      $input.width($input.parent().width());
      // Display the input.
      $input.slideDown().find('input').focus().prop('required', true);
      // Refresh CodeMirror used as other element.
      $input.parent().find('.CodeMirror').each(function (index, $element) {
        $element.CodeMirror.refresh();
      });
    }
    else {
      $input.slideUp();
      $input.find('input').val('').prop('required', false);
    }
  }

  /**
   * Attach handlers to select other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormSelectOther = {
    attach: function (context) {
      $(context).find('.form-type-yamlform-select-other').once().each(function () {
        var $element = $(this);

        var $select = $element.find('.form-type-select');
        var $otherOption = $element.find('option[value="_other_"]');
        var $input = $element.find('.js-yamlform-select-other-input');

        if ($otherOption.is(':selected')) {
          $input.show().find('input').prop('required', true);
        }

        $select.on('change', function () {
          toggleOther($otherOption.is(':selected'), $input);
        });
      });
    }
  };

  /**
   * Attach handlers to checkboxes other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormCheckboxesOther = {
    attach: function (context) {
      $(context).find('.form-type-yamlform-checkboxes-other').once().each(function () {
        var $element = $(this);
        var $checkbox = $element.find('input[value="_other_"]');
        var $input = $element.find('.js-yamlform-checkboxes-other-input');

        if ($checkbox.is(':checked')) {
          $input.show().find('input').prop('required', true);
        }

        $checkbox.on('change', function () {
          toggleOther(this.checked, $input);
        });
      });
    }
  };

  /**
   * Attach handlers to radios other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormRadiosOther = {
    attach: function (context) {
      $(context).find('.form-type-yamlform-radios-other').once().each(function () {
        var $element = $(this);

        var $radios = $element.find('input[type="radio"]');
        var $input = $element.find('.js-yamlform-radios-other-input');

        if ($radios.filter(':checked').val() === '_other_') {
          $input.show().find('input').prop('required', true);
        }

        $radios.on('change', function () {
          toggleOther(($radios.filter(':checked').val() === '_other_'), $input);
        });
      });
    }
  };

})(jQuery, Drupal);
