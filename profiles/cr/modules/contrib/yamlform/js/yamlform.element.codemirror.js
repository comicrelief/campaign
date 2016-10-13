/**
 * @file
 * Javascript behaviors for CodeMirror integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize CodeMirror editor.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormCodeMirror = {
    attach: function (context) {

      // Form CodeMirror editor.
      $(context).find('textarea.js-yamlform-codemirror').once('yamlform-codemirror').each(function () {
        var $input = $(this);

        // Open all closed details, so that editor height is correctly calculated.
        var $details = $(this).parents('details:not([open])');
        $details.attr('open', 'open');

        // #59 HTML5 required attribute breaks hack for form submission.
        // https://github.com/marijnh/CodeMirror-old/issues/59
        $(this).removeAttr('required');

        var editor = CodeMirror.fromTextArea(this, {
          mode: $(this).attr('data-yamlform-codemirror-mode'),
          lineNumbers: true,
          viewportMargin: Infinity,
          readOnly: $(this).prop('readonly') ? true : false,
          // Setting for using spaces instead of tabs - https://github.com/codemirror/CodeMirror/issues/988
          extraKeys: {
            Tab: function (cm) {
              var spaces = Array(cm.getOption('indentUnit') + 1).join(' ');
              cm.replaceSelection(spaces, 'end', '+element');
            }
          }
        });

        // Now, close details.
        $details.removeAttr('open');

        // Issue #2764443: CodeMirror is not setting submitted value when
        // rendered within a form UI dialog.
        editor.on('blur', function (event){
          editor.save();
        });

        // Update CodeMirror when the textarea's value has changed.
        // @see yamlform.states.js
        $input.on('change', function () {
          editor.getDoc().setValue($input.val());
        });

        // Set CodeMirror to be readonly when the textarea is disabled.
        // @see yamlform.states.js
        $input.on('yamlform:disabled', function () {
          editor.setOption("readOnly", $input.is(':disabled'));
        });

      });

      // Form CodeMirror syntax coloring.
      $(context).find('.js-yamlform-codemirror-runmode').once('yamlform-codemirror-runmode').each(function () {
        // Mode Runner - http://codemirror.net/demo/runmode.html
        CodeMirror.runMode($(this).addClass('cm-s-default').html(), $(this).attr('data-yamlform-codemirror-mode'), this);
      });

    }
  };

  // Workaround: When a dialog opens we need to reference all CodeMirror
  // editors to make sure they are properly initialized and sized.
  $(window).on('dialog:aftercreate', function (dialog, $element, settings) {
    // Delay refreshing CodeMirror for 10 millisecond while the dialog is
    // still being rendered.
    // @see http://stackoverflow.com/questions/8349571/codemirror-editor-is-not-loading-content-until-clicked
    setTimeout(function() {
      $('.CodeMirror').each(function (index, $element) {
        var $details = $(this).parents('details:not([open])');
        $details.attr('open', 'open');
        $element.CodeMirror.refresh();
        // Now, close details.
        $details.removeAttr('open');
      });
    }, 10);
  });

  // On state:visible refresh CodeMirror elements.
  $(document).on('state:visible', function(event) {
    var $element = $(event.target);
    if ($element.hasClass('js-yamlform-codemirror')) {
      $element.parent().find('.CodeMirror').each(function (index, $element) {
        $element.CodeMirror.refresh();
      });
    }
  });

})(jQuery, Drupal);
