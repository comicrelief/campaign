# Theme customisation and override documentation

## Prerequisite knowledge

* Sass Preprocessor http://sass-lang.com/guide
* SMACSS https://smacss.com/
* Responsive layouts with Zen https://www.previousnext.com.au/blog/responsive-layouts-zen-5x

## What is a sub-theme?

Sub-themes allow Drupal themes to inherit stylesheets, template files, regions, screen shots, logo, favicon, and theme/preprocess functions from a parent theme. More information can be found on Drupal.org:
https://www.drupal.org/node/225125

Starterkits are pre-built sub-theme templates that allow you to replace the uppercase name of the theme e.g. "STARTERKIT" with your theme's machine name e.g. "my_theme"

## Themes that come with agov

**zen**
The [Zen](http://drupal.org/project/zen) base theme is Drupal's most popular contributed theme. This theme is rarely (if ever) enabled as it is just a collection of template and css files that improve Drupal's default markup and styling. *DO NOT ENABLE THIS THEME.*

**agov_zen**
A sub-theme of the Zen base-theme that includes markup and styling changes to customize aGov's presentation layer, add accessibility improvements, and provided functionality through javascript. Low-level theme changes like colors and css3 enhancements are meant to be provided by the following sub-theme layer.

**agov_base**
A ready-made example of a theme that has been lightly customized. It inherits from *ENABLE this theme to see a standard representation of aGov*

**AGOV_STARTERKIT**
An example sub-theme that contains all the designs in agov_base and that you can use as a starting point to creating a sub-theme for your website.

**STARTERKIT**
Zen's starter kit. agov_zen and agov_base were built using this theme. You should use Zen's STARTERKIT directly if the design components of your custom theme deviate strongly from those in aGov.

## Theme Hierarchy diagram:

<pre>
+-----------------+  +--------------+
|                 |  |              |
| AGOV_STARTERKIT |  | agov_whitlam |
|                 |  |              |
+---+-------------+  +--+-----------+
    |                   |
    | +-----------------+
    | |
    v v
+----------+         +--------------+
|          |         |              |
| agov_zen |         |  STARTERKIT  |
|          |         |              |
+---+------+         +--------------+
    |                   |
    | +-----------------+
    | |
    v v
+----------+
|          |
|   zen    |
|          |
+----------+
</pre>
