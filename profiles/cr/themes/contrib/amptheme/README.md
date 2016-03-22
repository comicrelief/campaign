# AMP Drupal theme

The AMP theme works together with the AMP module
(https://www.drupal.org/project/amp) and AMP library
(https://github.com/Lullabot/amp-library) to convert Drupal pages into pages
that comply with the AMP standard (https://www.ampproject.org/).

This project includes the AMP Base theme, which handles markup changes needed
for all AMP-based themes, and the ExAMPle Subtheme, which shows how to set
custom styles with AMP. The ExAMPle theme extends the AMP Base theme.
You can create your own subtheme that extends the AMP Base theme to create a
custom design for the AMP pages on your site.

## Initial setup (Drupal 8)
- Download the AMP theme to your site: place it in the `themes` directory at the
  root of your site, optionally within a  `contrib` subdirectory.
- Install the AMP Base theme and optionally the ExAMPle Subtheme: you could also
  choose to install a custom subtheme. The AMP module will try to set the
  ExAMPle Subtheme as the default AMP theme, if it is installed.
- Follow the instructions at `https://www.drupal.org/project/amp` to download
  and install the AMP module and the associated AMP library. Then follow the
  instructions to configure your site for AMP. Without doing so, the AMP theme
  will not provide valid AMP-compliant markup.

## Viewing AMP pages
- Once configured, the AMP module, AMP library and AMP theme work together to
  provide valid AMP markup for nodes with AMP-enabled content types at paths with
  `/amp` at the end of the URL.

## Provide feedback
- This theme and the associated module and library are still in development.
  Try them out! We welcome your feedback.

## Drupal 8 and Drupal 7
- Initial development has focused on the Drupal 8 versions of the module and
  theme.
- Drupal 7 versions of the module and theme are under development.

## Sponsored by
- Google for creating the AMP Project and sponsoring development
- Lullabot for development of the module, theme, and library to work with the
  specifications
