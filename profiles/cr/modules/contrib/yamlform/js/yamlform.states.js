/**
 * @file
 * Javascript behaviors for custom form #states.
 */

(function ($, Drupal) {

  'use strict';

  // Make absolutely sure the below event handlers are triggered after
  // the state.js event handlers by attaching them after DOM load.
  $(function () {
    var $document = $(document);
    $document.on('state:visible', function (e) {
      if (e.trigger && !e.value) {
        // @see https://www.sitepoint.com/jquery-function-clear-form-data/
        $(':input', e.target).andSelf().each(function() {
          var type = this.type;
          var tag = this.tagName.toLowerCase(); // normalize case
          if (type == 'checkbox' || type == 'radio') {
            $(this)
              .prop('checked', false)
              .trigger('change')
              .trigger('blur');
          }
          else if (tag == 'select') {
            if ($(this).find('option[value=""]').length) {
              $(this).val('');
            }
            else {
              this.selectedIndex = -1;
            }
            $(this)
              .trigger('change')
              .trigger('blur');
          }
          else if (type != 'submit' && type != 'button') {
            switch (type) {
              case 'color':
                this.value = '#000000';
                break;

              default:
                this.value = '';
                break;
            }

            $(this)
              .trigger('input')
              .trigger('change')
              .trigger('keydown')
              .trigger('keyup')
              .trigger('blur');
          }
        });
      }
    });

    $document.on('state:disabled', function (e) {
      if (e.trigger) {
        $(e.target).trigger('yamlform:disabled')
          .find('select, input, textarea').trigger('yamlform:disabled');
      }
    });
  });

})(jQuery, Drupal);
