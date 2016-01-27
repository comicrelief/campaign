SUMMARY - YouTube Field
========================
The YouTube field module provides a simple field that allows you to add a
YouTube video to a content type, user, or any entity.

Display types include:

 * YouTube videos of various sizes and options.
 * YouTube thumbnails with image styles.


REQUIREMENTS
-------------
All dependencies of this module are enabled by default in Drupal 8.x.


INSTALLATION
-------------
Install this module as usual. Please see
http://drupal.org/documentation/install/modules-themes/modules-8


USAGE
-------
To use this module create a new field of type 'YouTube video'. This field will
accept YouTube URLs of the following formats:

 * http://youtube.com/watch?v=[video_id]
 * http://youtu.be/[video_id]
 * http://youtube.com/v/[video_id]
 * http://youtube.com/embed/[video_id]
 * http://youtube.com/?v=[video_id]

All formats listed above can also be provided without 'http://', with 'www.',
or with 'https://' rather than 'http://'. The last format can be provided with
additional parameters (ignored) and v does not have to be the first parameter.


CONFIGURATION
--------------
Global module settings can be found at admin/config/media/youtube.

The video output of a YouTube field can be manipulated in three ways:
 * global parameters found on the configuration page mentioned above
 * field-specific parameters found in that particular field's display settings
 * Views settings for the specific field

The thumbnail of the YouTube image can also be used and can link to either the
content, the video on YouTube, or nothing at all.

To configure the field settings:

 1. click 'manage display' on the listing of entity types
 2. click the configuration gear to the right of the YouTube field


SUPPORT
--------
Please use the issue queue to report bugs or request support:
http://drupal.org/project/issues/youtube
