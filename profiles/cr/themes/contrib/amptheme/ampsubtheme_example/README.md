# ExAMPle Subtheme

This ExAMPle Subtheme serves as a demonstration for how you can provide a
subtheme that extends the AMP Base theme to provide custom styles.

Please refer to the README at the root of amptheme for full installation
instructions to get your site ready for AMP.

To create your own custom subtheme, make sure to do the following additional
steps beyond what you would normally do to create a subtheme:
- Create a theme that has the value of `base theme:` set to `amptheme`. This
  lets the AMP Base theme provide markup defaults that carry through to a custom
  theme.
- Copy the `html.html.twig` template from `amptheme` into your custom theme.
- Find the line that says
  `% include '@amptheme/amp-css/amp-custom-styles.html.twig' %}`
- Change `@amptheme` to @ plus the machine name of your custom theme: the
  machine name is whatever the directory name is for your custom theme.
- In your custom theme directory, create a directory named `amp-css`.
- Add a file named `amp-custom-styles.html.twig` to your `amp-css` directory.
- Custom CSS can be placed into the `amp-custom-styles.html.twig`.

Make sure to follow guidelines at https://www.ampproject.org/ on allowed styles
and markup in order to have valid HTML. Please note that CSS and JS added in a
libraries.yml file will not be loaded on AMP-enabled pages.

Install your custom theme and set it as the theme that will be used on
AMP-enabled pages at `/admin/config/content/amp`: the AMP module must be
installed to view this configration page.

## Sponsored by
- Google for creating the AMP Project and sponsoring development
- Lullabot for development of the module, theme, and library to work with the
  specifications
