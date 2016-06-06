
-- SUMMARY --

Provides ImageMagick integration.

For a full description of the module, visit the project page:
  https://drupal.org/project/imagemagick
To submit bug reports and feature suggestions, or to track changes:
  https://drupal.org/project/issues/imagemagick


-- REQUIREMENTS --

* Either ImageMagick (http://www.imagemagick.org) or GraphicsMagick
  (http://www.graphicsmagick.org) need to be installed on your server
  and the convert binary needs to be accessible and executable from PHP.

* The PHP configuration must allow invocation of proc_open() (which is
  security-wise identical to exec()).

Consult your server administrator or hosting provider if you are unsure about
these requirements.


-- INSTALLATION --

* Install as usual, see https://drupal.org/node/70151 for further information.


-- CONFIGURATION --

* Go to Administration » Configuration » Media » Image toolkit and change the
  image toolkit to ImageMagick.

* Select the graphics package (ImageMagick or GraphicsMagick) you want to use
  with the toolkit.

* If the convert binary cannot be found in the default shell path, you need to
  enter the path to the executables, including the trailing slash/backslash.

* Enable and/or disable the image formats that the toolkit needs to support,
  see below.


-- ENABLE/DISABLE SUPPORTED IMAGE FORMATS --

ImageMagick and GraphicsMagick support a wide range of image formats. The image
toolkits need to declare the image file extensions they support. This module
allows to configure the image file extensions the toolkit supports, by mapping
an 'internal' ImageMagick format code to its MIME type. The file extensions
associated to the MIME type are then used to built the full list of supported
extensions.

* Go to Administration » Configuration » Media » Image toolkit and expand the
  'Format list' section in the 'Image formats' box of the ImageMagick toolkit
  configuration. This list shows the 'internal' image formats supported by the
  *installed* ImageMagick package. Note that this list depends on the libraries
  that are used when building the package.

* Enter the list of image formats you want to support in the 'Enable/Disable
  Image Formats' box. Each format need to be typed following a YAML syntax,
  like e.g.:

    JPEG:
      mime_type: image/jpeg
      enabled: true
      weight: 0
      exclude_extensions: jpe, jpg

  The 'internal' format should be entered with no spaces in front, and with a
  trailing colon. For each format there are more variables that can be
  associated. Each variable should be entered with two leading spaces, followed
  by a colon, followed by a space, followed by the variable's value.
  The variables are:
  'mime_type': (MUST) the MIME type of the image format. This will be used to
  resolve the supported file extensions, i.e. ImageMagick 'JPEG' format is
  mapped to MIME type 'image/jpeg' which in turn will be mapped to 'jpeg jpg
  jpe' image file extensions.
  'enabled': (OPTIONAL) if the format is enabled in the toolkit. Defaults to
  true.
  'weight': (OPTIONAL), defaults to 0. This is used in edge cases where an
  image file extension is mapped to more than one ImageMagick format. It is
  needed in file format conversions, e.g. in conversion from 'png' to 'gif',
  to decide if 'GIF' or 'GIF87' internal Imagemagick format be used.
  'exclude_extensions': (OPTIONAL) it can be used to limit the file extensions
  to be supported by the toolkit if the mapping MIME type <-> file extension
  returns more extensions than needed and we do not want to alter the MIME type
  mapping.


-- CONTACT --

Current maintainers:
* Daniel F. Kudwien 'sun' - https://www.drupal.org/u/sun
* 'mondrake' - https://www.drupal.org/u/mondrake - for the Drupal 8 branch
  only.
