/**
 * @file
 * JavaScript behaviors for documentation.
 */

(function ($) {

  "use strict";

  /**
   * Redirect 'Download' link to D.O.
   */
  $('a:contains("Download")').filter(function (index) {
    return $(this).text() === 'Download';
  }).attr('href', 'https://www.drupal.org/project/yamlform/releases');

  /**
   * Image Enlarge
   *
   * Inspired by: http://stackoverflow.com/questions/25023199/bootstrap-open-image-in-modal
   */
  $('a[href$=".png"], a[href$=".jpg"]').click(function (event) {
    var $img = $(this).find('img');
    var title = $(this).attr('title') || ($img.length) ? ($img.attr('title') || $img.attr('alt')) : '';
    $('#modal-lightbox img').attr('src', $(this).attr('href'));
    $('#modal-lightbox h4').html(title);
    $('#modal-lightbox').modal('show');
    event.preventDefault();
  });

})(jQuery);
