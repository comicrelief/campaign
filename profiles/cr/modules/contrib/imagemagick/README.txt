
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

* Select a locale to be used when escaping command line arguments, in the
  'Execution options' box, 'Locale' field. The default, 'en_US.UTF-8', should
  work in most cases. If that is not available on the server, enter another
  locale. On *nix servers, type 'locale -a' in a shell window to see a list of
  all locales available. This is particularly needed if you are going to
  process image files with non-ASCII characters in the file name: without a
  locale defined, the non-ASCII characters will be dropped by the escape
  function, leading to errors.


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


-- IMAGEMAGICK AND DRUPAL'S IMAGE API REVEALED --

ImageMagick is a command line based image manipulation tool. It is executed
through calls to the operating system shell, rather than using PHP functions.
For this reason, the way the ImageMagick toolkit operates is very different
from, for example, the GD toolkit provided by Drupal core.
All the image manipulation performed by the operations provided by the Image
API (scale, resize, desaturate, etc.), in fact, have to be accumulated and
deferred to a single call of the 'convert' executable.
The way ImageMagick toolkit interacts with Drupal Image API is the following:
a) When an Image object is created, the toolkit calls ImageMagick's 'identify'
   command to retrieve information about the image itself (e.g. format, width,
   height, orientation).
b) When operations are applied to the Image object (typically as part of
   creating an image style derivative), the toolkit *both* adds arguments to
   the command line to be executed *and* keeps track of the changes occurring
   to the width/height/orientation. It does so based purely on the information
   retrieved sub (a), and the expected changes introduced by a specific
   operation, because we do not have an object in memory that can be tested
   against current values as we have in the GD toolkit.
c) When the Image object is 'saved' (typically at the end of the image style
   derivative creation process), then the toolkit actually executes
   ImageMagick's 'convert' command with the entire set of arguments that have
   been added by effects/operations so far.


-- DEBUGGING IMAGEMAGICK COMMANDS --

The toolkit provides some of options to facilitate debugging the execution of
ImageMagick commands.

- Display debugging information

  Go to Administration » Configuration » Media » Image toolkit and select the
  'Display debugging information' tickbox in the 'Execution options' box. This
  will result in logging all the parameters passed in input to the 'identify'
  and 'convert' binaries, and all output/errors produced by the execution. The
  same information will also be presented interactively to users with the
  'Administer site configuration' permission. This can be used for debugging
  purposes, as these entries can be used to execute separately the commands in
  a shell window.

  As an example, the following is logged when an image derivative is generated
  by the 'Thumbnail' image style:

   ImageMagick command: identify -format 'format:%m|width:%w|height:%h|exif_orientation:%[EXIF:Orientation]' 'core/modules/image/sample.png'
   ImageMagick output:  format:PNG|width:800|height:600|exif_orientation:
   ImageMagick command: convert 'core/modules/image/sample.png' -resize 100x75! -quality 75 '/[...]/sites/default/files/styles/thumbnail/public/core/modules/image/sample.png'
   ImageMagick command: identify -format 'format:%m|width:%w|height:%h|exif_orientation:%[EXIF:Orientation]' '/[...]/sites/default/files/styles/thumbnail/public/core/modules/image/sample.png'
   ImageMagick output:  format:PNG|width:100|height:75|exif_orientation:

- Prepend -debug argument

  Go to Administration » Configuration » Media » Image toolkit and enter, for
  example, '-debug All' in the 'Prepend arguments' text box. Also, enable
  'Display debugging information' as described above. This will instruct
  ImageMagick 'identify' and 'convert' binaries to produce a verbose log of
  their internal operations execution, that can be checked in case of issues.
  Also, a '-log' argument can be entered to specify how to format the log
  itself.
  For more details, see ImageMagick documentation online:
    https://www.imagemagick.org/script/command-line-options.php#debug
    https://www.imagemagick.org/script/command-line-options.php#log

  This requires some trials before getting the required level of detail. A good
  combination is "-debug All -log '%u: %d - %e'". Following on the example
  above, this will log something like (extract):

   ImageMagick command: convert 'core/modules/image/sample.png' -debug All -log '%u: %d - %e' -resize 100x75! -quality 75 '/[...]/sites/default/files/styles/thumbnail/public/core/modules/image/sample.png'
   ImageMagick error:
      [...]
      0.110u: Cache - destroy core/modules/image/sample.png[0]
      0.110u: Resource - Memory: 3.84MB/58.6KiB/25.46GiB
      0.110u: Policy - Domain: Coder; rights=Write; pattern="PNG" ...
      0.110u: Coder - Enter WritePNGImage()
      0.110u: Coder -   Enter WriteOnePNGImage()
      0.110u: Coder -     storage_class=DirectClass
      0.110u: Coder -     Enter BUILD_PALETTE:
      0.110u: Coder -       image->columns=100
      0.110u: Coder -       image->rows=75
      0.110u: Coder -       image->matte=0
      0.110u: Coder -       image->depth=8
      0.110u: Coder -       image->colors=0
      0.110u: Coder -         (zero means unknown)
      0.110u: Coder -       Regenerate the colormap
      0.110u: Coder -       Check colormap for background (65535,65535,65535)
      0.110u: Coder -       No room in the colormap to add background color
      0.110u: Coder -       image has more than 256 colors
      0.110u: Coder -       image->colors=0
      0.110u: Coder -       number_transparent     = 0
      0.110u: Coder -       number_opaque          > 256
      0.110u: Coder -       number_semitransparent = 0
      [...]


-- CONTACT --

Current maintainers:
* Daniel F. Kudwien 'sun' - https://www.drupal.org/u/sun
* 'mondrake' - https://www.drupal.org/u/mondrake - for the Drupal 8 branch
  only.
