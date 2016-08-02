(function ($) {

/**
 * Provide the summary information for the menu attributes vertical tabs.
 */
Drupal.behaviors.menuAttributesOptionsSummary = {
  attach: function (context) {
    // Menu item title.
    $('#edit-title', context).drupalSetSummary(function (context) {
      if (!$('input[type="checkbox"]:checked', context).val()) {
        return Drupal.t('Disabled');
      }
      var value = $('.form-textarea', context).val();
      if (!value) {
        return Drupal.t('No title');
      }
      else {
        return Drupal.checkPlain(value);
      }
    });

    // Menu item ID.
    $('#edit-id', context).drupalSetSummary(function (context) {
      if (!$('input[type="checkbox"]:checked', context).val()) {
        return Drupal.t('Disabled');
      }
      var value = $('.form-text', context).val();
      if (!value) {
        return Drupal.t('No ID');
      }
      else {
        return Drupal.checkPlain(value);
      }
    });

    // Menu item name.
    $('#edit-name', context).drupalSetSummary(function (context) {
      if (!$('input[type="checkbox"]:checked', context).val()) {
        return Drupal.t('Disabled');
      }
      var value = $('.form-text', context).val();
      if (!value) {
        return Drupal.t('No name');
      }
      else {
        return Drupal.checkPlain(value);
      }
    });

    // Menu item relationship.
    $('#edit-rel', context).drupalSetSummary(function (context) {
      if (!$('input[type="checkbox"]:checked', context).val()) {
        return Drupal.t('Disabled');
      }
      var value = $('.form-text', context).val();
      if (!value) {
        return Drupal.t('No relationship');
      }
      else {
        return Drupal.checkPlain(value);
      }
    });

    // Menu item classes.
    $('#edit-class', context).drupalSetSummary(function (context) {
      if (!$('input[type="checkbox"]:checked', context).val()) {
        return Drupal.t('Disabled');
      }
      var value = $('.form-text', context).val();
      if (!value) {
        return Drupal.t('No classes');
      }
      else {
        return Drupal.checkPlain(value.replace(/\s/g, ', '));
      }
    });

    // Menu item style.
    $('#edit-style', context).drupalSetSummary(function (context) {
      if (!$('input[type="checkbox"]:checked', context).val()) {
        return Drupal.t('Disabled');
      }
      var value = $('.form-text', context).val();
      if (!value) {
        return Drupal.t('No style');
      }
      else {
        return Drupal.checkPlain(value);
      }
    });

    // Menu item target.
    $('#edit-target', context).drupalSetSummary(function (context) {
      if (!$('input[type="checkbox"]:checked', context).val()) {
        return Drupal.t('Disabled');
      }

      var value = $('.form-select option:selected', context).text();
      return Drupal.checkPlain(value);
    });

    // Menu item access key.
    $('#edit-accesskey', context).drupalSetSummary(function (context) {
      if (!$('input[type="checkbox"]:checked', context).val()) {
        return Drupal.t('Disabled');
      }
      var value = $('.form-text', context).val();
      if (!value) {
        return Drupal.t('No access key');
      }
      else {
        return Drupal.checkPlain(value);
      }
    });

  }
};

})(jQuery);
