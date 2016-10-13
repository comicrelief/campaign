/**
 * @file
 * Javascript behaviors for details element.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attach handler to toggle details open/close state.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormDetailsToggle = {
    attach: function (context) {
      $('form.yamlform-details-toggle', context).once().each(function () {
        var $form = $(this);
        var $details = $form.find('details');

        // Toggle is only useful when there are two or more details elements.
        if ($details.length < 2) {
          return;
        }

        // Add toggle state link to first details element.
        $details.first().before($('<button type="button" class="link yamlform-details-toggle-state"></button>')
          .attr('title', Drupal.t('Toggle details widget state.'))
          .on('click', function (e) {
            var open;
            if (isFormDetailsOpen($form)) {
              $form.find('details').removeAttr('open');
              open = 0;
            }
            else {
              $form.find('details').attr('open', 'open');
              open = 1;
            }
            setDetailsToggleLabel($form);

            // Set the saved states for all the details elements.
            // @see yamlform.element.details.save.js
            if (Drupal.yamlFormDetailsSaveGetName) {
              $form.find('details').each(function() {
                var name = Drupal.yamlFormDetailsSaveGetName($(this));
                if (name) {
                  localStorage.setItem(name, open);
                }
              });
            }
          })
          .wrap('<div class="yamlform-details-toggle-state-wrapper"></div>')
          .parent()
        );

        setDetailsToggleLabel($form);
      });
    }
  };

  /**
   * Determine if a form's details are all opened.
   *
   * @param $form
   *   A form.
   *
   * @returns {boolean}
   *   TRUE if a form's details are all opened.
   */
  function isFormDetailsOpen($form) {
    return ($form.find('details[open]').length == $form.find('details').length)
  }

  /**
   * Set a form's details toggle state widget label.
   *
   * @param $form
   *   A form.
   */
  function setDetailsToggleLabel($form) {
    var label = (isFormDetailsOpen($form)) ? Drupal.t('Collapse all') : Drupal.t('Expand all');
    $form.find('.yamlform-details-toggle-state').html(label);
  }

})(jQuery, Drupal);
