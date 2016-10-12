/**
 * @file
 * Javascript behaviors for HTML editor integration.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Initialize HTML Editor.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormHtmlEditor = {
    attach: function (context) {
      $(context).find('.js-form-type-yamlform-html-editor textarea').once().each(function () {
        var $textarea = $(this);

        CKEDITOR.replace(this.id, {
          // Turn off external config and styles.
          customConfig: '',
          stylesSet: false,
          contentsCss: [],
          // Set height, hide the status bar, and remove plugins.
          height: '100px',
          resize_enabled: false,
          removePlugins: 'elementspath,magicline',
          // Toolbar settings.
          format_tags: 'p;h2;h3;h4;h5;h6',
          toolbar: [
            { name: 'styles', items: ['Format', 'Font', 'FontSize' ] },
            { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Subscript', 'Superscript' ] },
            { name: 'insert', items: [ 'SpecialChar' ] },
            { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
            { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
            { name: 'links', items: [ 'Link', 'Unlink'] },
            { name: 'tools', items: [ 'Source', '-', 'Maximize' ] }
          ]
        }).on('change', function(evt) {
          // Save data onchange since AJAX dialogs don't execute form.onsubmit.
          $textarea.val(evt.editor.getData().trim());
        });
      })
    }
  };

})(jQuery, Drupal, drupalSettings);
