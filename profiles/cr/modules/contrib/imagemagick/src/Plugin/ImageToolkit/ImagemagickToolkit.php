<?php

namespace Drupal\imagemagick\Plugin\ImageToolkit;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\ImageToolkit\ImageToolkitBase;
use Drupal\Core\ImageToolkit\ImageToolkitOperationManagerInterface;
use Drupal\Core\Url;
use Drupal\imagemagick\ImagemagickFormatMapperInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides ImageMagick integration toolkit for image manipulation.
 *
 * @ImageToolkit(
 *   id = "imagemagick",
 *   title = @Translation("ImageMagick image toolkit")
 * )
 */
class ImagemagickToolkit extends ImageToolkitBase {

  /**
   * Whether we are running on Windows OS.
   *
   * @var bool
   */
  protected $isWindows;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The format mapper service.
   *
   * @var \Drupal\imagemagick\ImagemagickFormatMapperInterface
   */
  protected $formatMapper;

  /**
   * The app root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * The array of command line arguments to be used by 'convert'.
   *
   * @var string[]
   */
  protected $arguments = array();

  /**
   * The width of the image.
   *
   * @var int
   */
  protected $width;

  /**
   * The height of the image.
   *
   * @var int
   */
  protected $height;

  /**
   * The number of frames of the image, for multi-frame images (e.g. GIF).
   *
   * @var int
   */
  protected $frames;

  /**
   * The local filesystem path to the source image file.
   *
   * @var string
   */
  protected $sourceLocalPath = '';

  /**
   * The source image format.
   *
   * @var string
   */
  protected $sourceFormat = '';

  /**
   * Keeps a copy of source image EXIF information.
   *
   * @var array
   */
  protected $exifInfo = [];

  /**
   * The image destination URI/path on saving.
   *
   * @var string
   */
  protected $destination = NULL;

  /**
   * The local filesystem path to the image destination.
   *
   * @var string
   */
  protected $destinationLocalPath = '';

  /**
   * The image destination format on saving.
   *
   * @var string
   */
  protected $destinationFormat = '';

  /**
   * Constructs an ImagemagickToolkit object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\ImageToolkit\ImageToolkitOperationManagerInterface $operation_manager
   *   The toolkit operation manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\imagemagick\ImagemagickFormatMapperInterface $format_mapper
   *   The format mapper service.
   * @param string $app_root
   *   The app root.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ImageToolkitOperationManagerInterface $operation_manager, LoggerInterface $logger, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, ImagemagickFormatMapperInterface $format_mapper, $app_root) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $operation_manager, $logger, $config_factory);
    $this->moduleHandler = $module_handler;
    $this->formatMapper = $format_mapper;
    $this->appRoot = $app_root;
    $this->isWindows = substr(PHP_OS, 0, 3) === 'WIN';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('image.toolkit.operation.manager'),
      $container->get('logger.channel.image'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('imagemagick.format_mapper'),
      $container->get('app.root')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('imagemagick.settings');
    $package = $this->configFactory->get('imagemagick.settings')->get('binaries');
    $suite = $package === 'imagemagick' ? $this->t('ImageMagick') : $this->t('GraphicsMagick');

    $form['imagemagick'] = array(
      '#markup' => $this->t("<a href=':im-url'>ImageMagick</a> and <a href=':gm-url'>GraphicsMagick</a> are stand-alone packages for image manipulation. At least one of them must be installed on the server, and you need to know where it is located. Consult your server administrator or hosting provider for details.", [
        ':im-url' => 'http://www.imagemagick.org',
        ':gm-url' => 'http://www.graphicsmagick.org',
      ]),
    );
    $form['quality'] = array(
      '#type' => 'number',
      '#title' => $this->t('Image quality'),
      '#size' => 10,
      '#min' => 0,
      '#max' => 100,
      '#maxlength' => 3,
      '#default_value' => $config->get('quality'),
      '#field_suffix' => '%',
      '#description' => $this->t('Define the image quality of processed images. Ranges from 0 to 100. Higher values mean better image quality but bigger files.'),
    );

    // Graphics suite to use.
    $form['suite'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#collapsible' => FALSE,
      '#title' => $this->t('Graphics package'),
    );
    $options = [
      'imagemagick' => $this->t("ImageMagick"),
      'graphicsmagick' => $this->t("GraphicsMagick"),
    ];
    $form['suite']['binaries'] = [
      '#type' => 'radios',
      '#title' => $this->t('Suite'),
      '#default_value' => $config->get('binaries'),
      '#options' => $options,
      '#required' => TRUE,
      '#description' => $this->t("Select the graphics package to use."),
    ];
    // Path to binaries.
    $form['suite']['path_to_binaries'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Path to the package executables'),
      '#default_value' => $config->get('path_to_binaries'),
      '#required' => FALSE,
      '#description' => $this->t('If needed, the path to the package executables (<kbd>convert</kbd>, <kbd>identify</kbd>, <kbd>gm</kbd>, etc.), <b>including</b> the trailing slash/backslash. For example: <kbd>/usr/bin/</kbd> or <kbd>C:\Program Files\ImageMagick-6.3.4-Q16\</kbd>.'),
    );
    // Version information.
    $status = $this->checkPath($config->get('path_to_binaries'));
    if (empty($status['errors'])) {
      $version_info = explode("\n", preg_replace('/\r/', '', Html::escape($status['output'])));
    }
    else {
      $version_info = $status['errors'];
    }
    $form['suite']['version'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => $this->t('Version information'),
      '#description' => '<pre>' . implode('<br />', $version_info) . '</pre>',
    ];

    // Image formats.
    $form['formats'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#collapsible' => FALSE,
      '#title' => $this->t('Image formats'),
    ];
    // Use 'identify' command.
    $form['formats']['use_identify'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use "identify"'),
      '#default_value' => $config->get('use_identify'),
      '#description' => $this->t('Use the <kbd>identify</kbd> command to parse image files to determine image format and dimensions. If not selected, the PHP <kbd>getimagesize</kbd> function will be used, BUT this will limit the image formats supported by the toolkit.'),
    );
    // Image formats enabled in the toolkit.
    $form['formats']['enabled'] = [
      '#type' => 'item',
      '#title' => $this->t('Enabled images'),
      '#description' => $this->t("@suite formats: %formats<br />Image file extensions: %extensions", [
        '%formats' => implode(', ', $this->formatMapper->getEnabledFormats()),
        '%extensions' => Unicode::strtolower(implode(', ', static::getSupportedExtensions())),
        '@suite' => $suite,
      ]),
    ];
    // Image formats map.
    $form['formats']['mapping'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => $this->t('Enable/disable image formats'),
      '#description' => $this->t("Edit the map below to enable/disable image formats. Enabled image file extensions will be determined by the enabled formats, through their MIME types. More information in the module's README.txt"),
    ];
    $form['formats']['mapping']['image_formats'] = [
      '#type' => 'textarea',
      '#rows' => 15,
      '#default_value' => Yaml::encode($config->get('image_formats')),
    ];
    // Image formats supported by the package.
    if (empty($status['errors'])) {
      $command = $package === 'imagemagick' ? 'convert' : 'gm';
      $this->addArgument('-list format');
      $this->imagemagickExec($command, $output);
      $this->resetArguments();
      $formats_info = implode('<br />', explode("\n", preg_replace('/\r/', '', Html::escape($output))));
      $form['formats']['list'] = [
        '#type' => 'details',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#title' => $this->t('Format list'),
        '#description' => $this->t("Supported image formats returned by executing <kbd>'convert -list format'</kbd>. <b>Note:</b> these are the formats supported by the installed @suite executable, <b>not</b> by the toolkit.<br /><br />", ['@suite' => $suite]),
      ];
      $form['formats']['list']['list'] = [
        '#markup' => "<pre>" . $formats_info . "</pre>",
      ];
    }

    // Execution options.
    $form['exec'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#collapsible' => FALSE,
      '#title' => $this->t('Execution options'),
    ];
    // Prepend arguments.
    $form['exec']['prepend'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Prepend arguments'),
      '#default_value' => $config->get('prepend'),
      '#required' => FALSE,
      '#description' => $this->t('Use this to add e.g. <kbd>-limit</kbd> or <kbd>-debug</kbd> arguments in front of the others when executing the <kbd>identify</kbd> and <kbd>convert</kbd> commands.'),
    );
    // Locale.
    $form['exec']['locale'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Locale'),
      '#default_value' => $config->get('locale'),
      '#required' => FALSE,
      '#description' => $this->t("The locale to be used to prepare the command passed to executables. The default, <kbd>'en_US.UTF-8'</kbd>, should work in most cases. If that is not available on the server, enter another locale. On *nix servers, type <kbd>'locale -a'</kbd> in a shell window to see a list of all locales available."),
    );
    // Log warnings.
    $form['exec']['log_warnings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Log warnings'),
      '#default_value' => $config->get('log_warnings'),
      '#description' => $this->t('Log a warning entry in the watchdog when the execution of a command returns with a non-zero code, but no error message.'),
    ];
    // Debugging.
    $form['exec']['debug'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Display debugging information'),
      '#default_value' => $config->get('debug'),
      '#description' => $this->t('Shows commands and their output to users with the %permission permission.', array(
        '%permission' => $this->t('Administer site configuration'),
      )),
    );

    // Advanced image settings.
    $form['advanced'] = array(
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => $this->t('Advanced image settings'),
    );
    $form['advanced']['density'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Change image resolution to 72 ppi'),
      '#default_value' => $config->get('advanced.density'),
      '#return_value' => 72,
      '#description' => $this->t("Resamples the image <a href=':help-url'>density</a> to a resolution of 72 pixels per inch, the default for web images. Does not affect the pixel size or quality.", array(
        ':help-url' => 'http://www.imagemagick.org/script/command-line-options.php#density',
      )),
    );
    $form['advanced']['colorspace'] = array(
      '#type' => 'select',
      '#title' => $this->t('Convert colorspace'),
      '#default_value' => $config->get('advanced.colorspace'),
      '#options' => array(
        'RGB' => $this->t('RGB'),
        'sRGB' => $this->t('sRGB'),
        'GRAY' => $this->t('Gray'),
      ),
      '#empty_value' => 0,
      '#empty_option' => $this->t('- Original -'),
      '#description' => $this->t("Converts processed images to the specified <a href=':help-url'>colorspace</a>. The color profile option overrides this setting.", array(
        ':help-url' => 'http://www.imagemagick.org/script/command-line-options.php#colorspace',
      )),
      '#states' => array(
        'enabled' => array(
          ':input[name="imagemagick[advanced][profile]"]' => array('value' => ''),
        ),
      ),
    );
    $form['advanced']['profile'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Color profile path'),
      '#default_value' => $config->get('advanced.profile'),
      '#description' => $this->t("The path to a <a href=':help-url'>color profile</a> file that all processed images will be converted to. Leave blank to disable. Use a <a href=':color-url'>sRGB profile</a> to correct the display of professional images and photography.", array(
        ':help-url' => 'http://www.imagemagick.org/script/command-line-options.php#profile',
        ':color-url' => 'http://www.color.org/profiles.html',
      )),
    );

    return $form;
  }

  /**
   * Verifies file path of the executable binary by checking its version.
   *
   * @param string $path
   *   The user-submitted file path to the convert binary.
   * @param string $package
   *   (optional) The graphics package to use.
   *
   * @return array
   *   An associative array containing:
   *   - output: The shell output of 'convert -version', if any.
   *   - errors: A list of error messages indicating if the executable could
   *     not be found or executed.
   */
  public function checkPath($path, $package = NULL) {
    $status = array(
      'output' => '',
      'errors' => array(),
    );

    // Execute gm or convert based on settings.
    $package = $package ?: $this->configFactory->get('imagemagick.settings')->get('binaries');
    $suite = $package === 'imagemagick' ? $this->t('ImageMagick') : $this->t('GraphicsMagick');
    $command = $package === 'imagemagick' ? 'convert' : 'gm';

    // If a path is given, we check whether the binary exists and can be
    // invoked.
    if (!empty($path)) {
      $executable = $this->getExecutable($command, $path);

      // Check whether the given file exists.
      if (!is_file($executable)) {
        $status['errors'][] = $this->t('The @suite executable %file does not exist.', array('@suite' => $suite, '%file' => $executable));
      }
      // If it exists, check whether we can execute it.
      elseif (!is_executable($executable)) {
        $status['errors'][] = $this->t('The @suite file %file is not executable.', array('@suite' => $suite, '%file' => $executable));
      }
    }

    // In case of errors, check for open_basedir restrictions.
    if ($status['errors'] && ($open_basedir = ini_get('open_basedir'))) {
      $status['errors'][] = $this->t('The PHP <a href=":php-url">open_basedir</a> security restriction is set to %open-basedir, which may prevent to locate the @suite executable.', array(
        '@suite' => $suite,
        '%open-basedir' => $open_basedir,
        ':php-url' => 'http://php.net/manual/en/ini.core.php#ini.open-basedir',
      ));
    }

    // Unless we had errors so far, try to invoke convert.
    if (!$status['errors']) {
      $error = NULL;
      $this->addArgument('-version');
      $this->imagemagickExec($command, $status['output'], $error, $path);
      $this->resetArguments();
      if ($error !== '') {
        // $error normally needs check_plain(), but file system errors on
        // Windows use a unknown encoding. check_plain() would eliminate the
        // entire string.
        $status['errors'][] = $error;
      }
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    try {
      // Check that the format map contains valid YAML.
      $image_formats = Yaml::decode($form_state->getValue(['imagemagick', 'formats', 'mapping', 'image_formats']));
      // Validate the enabled image formats.
      $errors = $this->formatMapper->validateMap($image_formats);
      if ($errors) {
        $form_state->setErrorByName('imagemagick][formats][mapping][image_formats', new FormattableMarkup("<pre>@errors</pre>", ['@errors' => Yaml::encode($errors)]));
      }
    }
    catch (InvalidDataTypeException $e) {
      // Invalid YAML detected, show details.
      $form_state->setErrorByName('imagemagick][formats][mapping][image_formats', $this->t("YAML syntax error: @error", ['@error' => $e->getMessage()]));
    }
    // Validate the binaries path only if this toolkit is selected, otherwise
    // it will prevent the entire image toolkit selection form from being
    // submitted.
    if ($form_state->getValue(['image_toolkit']) === 'imagemagick') {
      $status = $this->checkPath($form_state->getValue(['imagemagick', 'suite', 'path_to_binaries']), $form_state->getValue(['imagemagick', 'suite', 'binaries']));
      if ($status['errors']) {
        $form_state->setErrorByName('imagemagick][suite][path_to_binaries', new FormattableMarkup(implode('<br />', $status['errors']), []));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('imagemagick.settings')
      ->set('quality', $form_state->getValue(array('imagemagick', 'quality')))
      ->set('binaries', $form_state->getValue(array('imagemagick', 'suite', 'binaries')))
      ->set('path_to_binaries', $form_state->getValue(array('imagemagick', 'suite', 'path_to_binaries')))
      ->set('use_identify', $form_state->getValue(array('imagemagick', 'formats', 'use_identify')))
      ->set('image_formats', Yaml::decode($form_state->getValue(['imagemagick', 'formats', 'mapping', 'image_formats'])))
      ->set('prepend', $form_state->getValue(array('imagemagick', 'exec', 'prepend')))
      ->set('locale', $form_state->getValue(['imagemagick', 'exec', 'locale']))
      ->set('log_warnings', (bool) $form_state->getValue(['imagemagick', 'exec', 'log_warnings']))
      ->set('debug', $form_state->getValue(array('imagemagick', 'exec', 'debug')))
      ->set('advanced.density', $form_state->getValue(array('imagemagick', 'advanced', 'density')))
      ->set('advanced.colorspace', $form_state->getValue(array('imagemagick', 'advanced', 'colorspace')))
      ->set('advanced.profile', $form_state->getValue(array('imagemagick', 'advanced', 'profile')))
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function isValid() {
    return ((bool) $this->getMimeType());
  }

  /**
   * Gets the local filesystem path to the image file.
   *
   * @return string
   *   A filesystem path.
   */
  public function getSourceLocalPath() {
    return $this->sourceLocalPath;
  }

  /**
   * Sets the local filesystem path to the image file.
   *
   * @param string $path
   *   A filesystem path.
   *
   * @return $this
   */
  public function setSourceLocalPath($path) {
    $this->sourceLocalPath = $path;
    return $this;
  }

  /**
   * Gets the source image format.
   *
   * @return string
   *   The source image format.
   */
  public function getSourceFormat() {
    return $this->sourceFormat;
  }

  /**
   * Sets the source image format.
   *
   * @param string $format
   *   The image format.
   *
   * @return $this
   */
  public function setSourceFormat($format) {
    $this->sourceFormat = $this->formatMapper->isFormatEnabled($format) ? $format : '';
    return $this;
  }

  /**
   * Sets the source image format from an image file extension.
   *
   * @param string $extension
   *   The image file extension.
   *
   * @return $this
   */
  public function setSourceFormatFromExtension($extension) {
    $format = $this->formatMapper->getFormatFromExtension($extension);
    $this->sourceFormat = $format ?: '';
    return $this;
  }

  /**
   * Gets the source EXIF orientation.
   *
   * @return integer
   *   The source EXIF orientation.
   */
  public function getExifOrientation() {
    if (empty($this->exifInfo)) {
      $this->parseExifData();
    }
    return isset($this->exifInfo['Orientation']) ? $this->exifInfo['Orientation'] : NULL;
  }

  /**
   * Sets the source EXIF orientation.
   *
   * @param integer|null $exif_orientation
   *   The EXIF orientation.
   *
   * @return $this
   */
  public function setExifOrientation($exif_orientation) {
    $this->exifInfo['Orientation'] = !empty($exif_orientation) ? ((int) $exif_orientation !== 0 ? (int) $exif_orientation : NULL) : NULL;
    return $this;
  }

  /**
   * Gets the source image number of frames.
   *
   * @return integer
   *   The number of frames of the image.
   */
  public function getFrames() {
    return $this->frames;
  }

  /**
   * Sets the source image number of frames.
   *
   * @param integer|null $frames
   *   The number of frames of the image.
   *
   * @return $this
   */
  public function setFrames($frames) {
    $this->frames = $frames;
    return $this;
  }

  /**
   * Gets the image destination URI/path on saving.
   *
   * @return string
   *   The image destination URI/path.
   */
  public function getDestination() {
    return $this->destination;
  }

  /**
   * Sets the image destination URI/path on saving.
   *
   * @param string $destination
   *   The image destination URI/path.
   *
   * @return $this
   */
  public function setDestination($destination) {
    $this->destination = $destination;
    return $this;
  }

  /**
   * Gets the local filesystem path to the destination image file.
   *
   * @return string
   *   A filesystem path.
   */
  public function getDestinationLocalPath() {
    return $this->destinationLocalPath;
  }

  /**
   * Sets the local filesystem path to the destination image file.
   *
   * @param string $path
   *   A filesystem path.
   *
   * @return $this
   */
  public function setDestinationLocalPath($path) {
    $this->destinationLocalPath = $path;
    return $this;
  }

  /**
   * Gets the image destination format.
   *
   * When set, it is passed to the convert binary in the syntax
   * "[format]:[destination]", where [format] is a string denoting an
   * ImageMagick's image format.
   *
   * @return string
   *   The image destination format.
   */
  public function getDestinationFormat() {
    return $this->destinationFormat;
  }

  /**
   * Sets the image destination format.
   *
   * When set, it is passed to the convert binary in the syntax
   * "[format]:[destination]", where [format] is a string denoting an
   * ImageMagick's image format.
   *
   * @param string $format
   *   The image destination format.
   *
   * @return $this
   */
  public function setDestinationFormat($format) {
    $this->destinationFormat = $this->formatMapper->isFormatEnabled($format) ? $format : '';
    return $this;
  }

  /**
   * Sets the image destination format from an image file extension.
   *
   * When set, it is passed to the convert binary in the syntax
   * "[format]:[destination]", where [format] is a string denoting an
   * ImageMagick's image format.
   *
   * @param string $extension
   *   The destination image file extension.
   *
   * @return $this
   */
  public function setDestinationFormatFromExtension($extension) {
    $format = $this->formatMapper->getFormatFromExtension($extension);
    $this->destinationFormat = $format ?: '';
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidth() {
    return $this->width;
  }

  /**
   * Sets image width.
   *
   * @param int $width
   *   The image width.
   *
   * @return $this
   */
  public function setWidth($width) {
    $this->width = $width;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeight() {
    return $this->height;
  }

  /**
   * Sets image height.
   *
   * @param int $height
   *   The image height.
   *
   * @return $this
   */
  public function setHeight($height) {
    $this->height = $height;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMimeType() {
    return $this->formatMapper->getMimeTypeFromFormat($this->getSourceFormat());
  }

  /**
   * Gets the command line arguments for the binary.
   *
   * @return string[]
   *   The array of command line arguments.
   */
  public function getArguments() {
    return $this->arguments ?: array();
  }

  /**
   * Adds a command line argument.
   *
   * @param string $arg
   *   The command line argument to be added.
   *
   * @return $this
   */
  public function addArgument($arg) {
    $this->arguments[] = $arg;
    return $this;
  }

  /**
   * Prepends a command line argument.
   *
   * @param string $arg
   *   The command line argument to be prepended.
   *
   * @return $this
   */
  public function prependArgument($arg) {
    array_unshift($this->arguments, $arg);
    return $this;
  }

  /**
   * Finds if a command line argument exists.
   *
   * @param string $arg
   *   The command line argument to be found.
   *
   * @return bool
   *   Returns the array key for the argument if it is found in the array,
   *   FALSE otherwise.
   */
  public function findArgument($arg) {
    foreach ($this->getArguments() as $i => $a) {
      if (strpos($a, $arg) === 0) {
        return $i;
      }
    }
    return FALSE;
  }

  /**
   * Removes a command line argument.
   *
   * @param int $index
   *   The index of the command line argument to be removed.
   *
   * @return $this
   */
  public function removeArgument($index) {
    if (isset($this->arguments[$index])) {
      unset($this->arguments[$index]);
    }
    return $this;
  }

  /**
   * Resets the command line arguments.
   *
   * @return $this
   */
  public function resetArguments() {
    $this->arguments = array();
    return $this;
  }

  /**
   * Returns the count of command line arguments.
   *
   * @return $this
   */
  public function countArguments() {
    return count($this->arguments);
  }

  /**
   * Escapes a string.
   *
   * PHP escapeshellarg() drops non-ascii characters, this is a replacement.
   *
   * Stop-gap replacement until core issue #1561214 has been solved. Solution
   * proposed in #1502924-8.
   *
   * PHP escapeshellarg() on Windows also drops % (percentage sign) characters.
   * We prevent this by replacing it with a pattern that should be highly
   * unlikely to appear in the string itself and does not contain any
   * "dangerous" character at all (very wide definition of dangerous). After
   * escaping we replace that pattern back with a % character.
   *
   * @param string $arg
   *   The string to escape.
   *
   * @return string
   *   An escaped string for use in the ::imagemagickExec method.
   */
  public function escapeShellArg($arg) {
    static $percentage_sign_replace_pattern = '1357902468IMAGEMAGICKPERCENTSIGNPATTERN8642097531';

    // Put the configured locale in a static to avoid multiple config get calls
    // in the same request.
    static $config_locale;

    if (!isset($config_locale)) {
      $config_locale = $this->configFactory->get('imagemagick.settings')->get('locale');
      if (empty($config_locale)) {
        $config_locale = FALSE;
      }
    }

    if ($this->isWindows) {
      // Temporarily replace % characters.
      $arg = str_replace('%', $percentage_sign_replace_pattern, $arg);
    }

    // If no locale specified in config, return with standard.
    if ($config_locale === FALSE) {
      $arg_escaped = escapeshellarg($arg);
    }
    else {
      // Get the current locale.
      $current_locale = setlocale(LC_CTYPE, 0);
      if ($current_locale != $config_locale) {
        // Temporarily swap the current locale with the configured one.
        setlocale(LC_CTYPE, $config_locale);
        $arg_escaped = escapeshellarg($arg);
        setlocale(LC_CTYPE, $current_locale);
      }
      else {
        $arg_escaped = escapeshellarg($arg);
      }
    }

    // Get our % characters back.
    if ($this->isWindows) {
      $arg_escaped = str_replace($percentage_sign_replace_pattern, '%', $arg_escaped);
    }

    return $arg_escaped;
  }

  /**
   * {@inheritdoc}
   */
  public function save($destination) {
    $this->setDestination($destination);
    if ($ret = $this->convert()) {
      // Allow modules to alter the destination file.
      $this->moduleHandler->alter('imagemagick_post_save', $this);
      // Reset local path to allow saving to other file.
      $this->setDestinationLocalPath('');
    }
    return $ret;
  }

  /**
   * {@inheritdoc}
   */
  public function parseFile() {
    // Allow modules to alter the source file.
    $this->moduleHandler->alter('imagemagick_pre_parse_file', $this);
    if ($this->configFactory->get('imagemagick.settings')->get('use_identify')) {
      return $this->parseFileViaIdentify();
    }
    else {
      return $this->parseFileViaGetImageSize();
    }
  }

  /**
   * Parses the image file using the 'identify' executable.
   *
   * @return bool
   *   TRUE if the file could be found and is an image, FALSE otherwise.
   */
  protected function parseFileViaIdentify() {
    $this->addArgument('-format ' . $this->escapeShellArg("format:%[magick]|width:%[width]|height:%[height]|exif_orientation:%[EXIF:Orientation]\\n"));
    if ($identify_output = $this->identify()) {
      $frames = explode("\n", $identify_output);

      // Remove empty items at the end of the array.
      while (empty($frames[count($frames) - 1])) {
        array_pop($frames);
      }

      // If remaining items are more than one, we have a multi-frame image.
      if (count($frames) > 1) {
        $this->setFrames(count($frames));
      }

      // Take information from the first frame.
      $info = explode('|', $frames[0]);
      $data = [];
      foreach ($info as $item) {
        list($key, $value) = explode(':', $item);
        $data[trim($key)] = trim($value);
      }
      $format = isset($data['format']) ? $data['format'] : NULL;
      if ($this->formatMapper->isFormatEnabled($format)) {
        $this
          ->setSourceFormat($format)
          ->setWidth((int) $data['width'])
          ->setHeight((int) $data['height'])
          ->setExifOrientation($data['exif_orientation']);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Parses the image file using the PHP getimagesize() function.
   *
   * @return bool
   *   TRUE if the file could be found and is an image, FALSE otherwise.
   */
  protected function parseFileViaGetImageSize() {
    if ($data = @getimagesize($this->getSourceLocalPath())) {
      $format = $this->formatMapper->getFormatFromExtension(image_type_to_extension($data[2], FALSE));
      if ($format) {
        $this
          ->setSourceFormat($format)
          ->setWidth($data[0])
          ->setHeight($data[1]);
        return TRUE;
      }
    };
    return FALSE;
  }

  /**
   * Parses the image file EXIF data using the PHP read_exif_data() function.
   *
   * @return $this
   */
  protected function parseExifData() {
    $continue = TRUE;
    // Test to see if EXIF is supported by the image format.
    $mime_type = $this->getMimeType();
    if (!in_array($mime_type, ['image/jpeg', 'image/tiff'])) {
      // Not an EXIF enabled image.
      $continue = FALSE;
    }
    $local_path = $this->getSourceLocalPath();
    if ($continue && empty($local_path)) {
      // No file path available. Most likely a new image from scratch.
      $continue = FALSE;
    }
    if ($continue && !function_exists('exif_read_data')) {
      // No PHP EXIF extension enabled, return.
      $this->logger->error('The PHP EXIF extension is not installed. The \'imagemagick\' toolkit is unable to automatically determine image orientation.');
      $continue = FALSE;
    }
    if ($continue && ($exif_data = @exif_read_data($this->getSourceLocalPath()))) {
      $this->exifInfo = $exif_data;
      return $this;
    }
    $this->setExifOrientation(NULL);
    return $this;
  }

  /**
   * Calls the identify executable on the specified file.
   *
   * @return bool
   *   TRUE if the file could be identified, FALSE otherwise.
   */
  protected function identify() {
    // Allow modules to alter the command line parameters.
    $command = 'identify';
    $this->moduleHandler->alter('imagemagick_arguments', $this, $command);

    // Execute the 'identify' command.
    $output = NULL;
    $ret = $this->imagemagickExec($command, $output);
    $this->resetArguments();
    return ($ret === TRUE) ? $output : FALSE;
  }

  /**
   * Calls the convert executable with the specified arguments.
   *
   * @return bool
   *   TRUE if the file could be converted, FALSE otherwise.
   */
  protected function convert() {
    // Allow modules to alter the command line parameters.
    $command = $this->configFactory->get('imagemagick.settings')->get('binaries') === 'imagemagick' ? 'convert' : 'gm';
    $this->moduleHandler->alter('imagemagick_arguments', $this, $command);

    // Execute the 'convert' or 'gm' command.
    return $this->imagemagickExec($command) === TRUE ? file_exists($this->getDestinationLocalPath()) : FALSE;
  }

  /**
   * Executes the convert executable as shell command.
   *
   * @param string $command
   *   The executable to run.
   * @param string $command_args
   *   A string containing arguments to pass to the command, which must have
   *   been passed through $this->escapeShellArg() already.
   * @param string &$output
   *   (optional) A variable to assign the shell stdout to, passed by reference.
   * @param string &$error
   *   (optional) A variable to assign the shell stderr to, passed by reference.
   * @param string $path
   *   (optional) A custom file path to the executable binary.
   *
   * @return mixed
   *   The return value depends on the shell command result:
   *   - Boolean TRUE if the command succeeded.
   *   - Boolean FALSE if the shell process could not be executed.
   *   - Error exit status code integer returned by the executable.
   */
  protected function imagemagickExec($command, &$output = NULL, &$error = NULL, $path = NULL) {
    $suite = $this->configFactory->get('imagemagick.settings')->get('binaries') === 'imagemagick' ? 'ImageMagick' : 'GraphicsMagick';

    $cmd = $this->getExecutable($command, $path);
    if ($this->isWindows) {
      // Use Window's start command with the /B flag to make the process run in
      // the background and avoid a shell command line window from showing up.
      // @see http://us3.php.net/manual/en/function.exec.php#56599
      // Use /D to run the command from PHP's current working directory so the
      // file paths don't have to be absolute.
      $cmd = 'start "' . $suite . '" /D ' . $this->escapeShellArg($this->appRoot) . ' /B ' . $this->escapeShellArg($cmd);
    }

    if ($source_path = $this->getSourceLocalPath()) {
      $source_path = $this->escapeShellArg($source_path);
    }

    if ($destination_path = $this->getDestinationLocalPath()) {
      $destination_path = $this->escapeShellArg($destination_path);
      // If the format of the derivative image has to be changed, concatenate
      // the new image format and the destination path, delimited by a colon.
      // @see http://www.imagemagick.org/script/command-line-processing.php#output
      if (($format = $this->getDestinationFormat()) !== '') {
        $destination_path = $format . ':' . $destination_path;
      }
    }

    switch($command) {
      case 'identify':
        $cmdline = $cmd . ' ' . implode(' ', $this->getArguments()) . ' ' . $source_path;
        break;

      case 'convert':
        // ImageMagick arguments:
        // convert input [arguments] output
        // @see http://www.imagemagick.org/Usage/basics/#cmdline
        $cmdline = $cmd . ' ' . $source_path . ' ' . implode(' ', $this->getArguments()) . ' ' . $destination_path;
        break;

      case 'gm':
        // GraphicsMagick arguments:
        // gm convert [arguments] input output
        // @see http://www.graphicsmagick.org/GraphicsMagick.html
        $cmdline = $cmd . ' convert ' . implode(' ', $this->getArguments()) . ' '  . $source_path . ' ' . $destination_path;
        break;

    }

    $descriptors = array(
      // stdin
      0 => array('pipe', 'r'),
      // stdout
      1 => array('pipe', 'w'),
      // stderr
      2 => array('pipe', 'w'),
    );
    if ($h = proc_open($cmdline, $descriptors, $pipes, $this->appRoot)) {
      $output = '';
      while (!feof($pipes[1])) {
        $output .= fgets($pipes[1]);
      }
      $output = utf8_encode($output);
      $error = '';
      while (!feof($pipes[2])) {
        $error .= fgets($pipes[2]);
      }
      $error = utf8_encode($error);

      fclose($pipes[0]);
      fclose($pipes[1]);
      fclose($pipes[2]);
      $return_code = proc_close($h);

      // Display debugging information to authorized users.
      if ($this->configFactory->get('imagemagick.settings')->get('debug')) {
        $current_user = \Drupal::currentUser();
        if ($current_user->hasPermission('administer site configuration')) {
          debug($cmdline, $this->t('@suite command', ['@suite' => $suite]), TRUE);
          if ($output !== '') {
            debug($output, $this->t('@suite output', ['@suite' => $suite]), TRUE);
          }
          if ($error !== '') {
            debug($error, $this->t('@suite error', ['@suite' => $suite]), TRUE);
          }
        }
      }

      // If the executable returned a non-zero code, log to the watchdog.
      if ($return_code != 0) {
        if ($error === '') {
          // If there is no error message, and allowed in config, log a
          // warning.
          if ($this->configFactory->get('imagemagick.settings')->get('log_warnings') === TRUE) {
            $this->logger->warning("@suite returned with code @code [command: @cmdline]", [
              '@suite' => $suite,
              '@code' => $return_code,
              '@cmdline' => $cmdline,
            ]);
          }
        }
        else {
          // Log $error with context information.
          $this->logger->error("@suite error @code: @error [command: @cmdline]", [
            '@suite' => $suite,
            '@code' => $return_code,
            '@error' => $error,
            '@cmdline' => $cmdline,
          ]);
        }
        // Executable exited with an error code, return it.
        return $return_code;
      }

      // The shell command was executed successfully.
      return TRUE;
    }
    // The shell command could not be executed.
    return FALSE;
  }

  /**
   * Returns the full path to the executable.
   *
   * @param string $command
   *   The program to execute, typically 'convert', 'identify' or 'gm'.
   * @param string $path
   *   (optional) A custom path to the folder of the executable. When left
   *   empty, the setting imagemagick.settings.path_to_binaries is taken.
   *
   * @return string
   *   The full path to the executable.
   */
  public function getExecutable($command, $path = NULL) {
    // $path is only passed from the validation of the image toolkit form, on
    // which the path to convert is configured. @see ::checkPath()
    if (!isset($path)) {
      $path = $this->configFactory->get('imagemagick.settings')->get('path_to_binaries');
    }

    $executable = $command;
    if ($this->isWindows) {
      $executable .= '.exe';
    }

    return $path . $executable;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequirements() {
    $reported_info = [];
    if (stripos(ini_get('disable_functions'), 'proc_open') !== FALSE) {
      // proc_open() is disabled.
      $severity = REQUIREMENT_ERROR;
      $reported_info[] = $this->t("The <a href=':proc_open_url'>proc_open()</a> PHP function is disabled. It must be enabled for the toolkit to work. Edit the <a href=':disable_functions_url'>disable_functions</a> entry in your php.ini file, or consult your hosting provider.", [
        ':proc_open_url' => 'http://php.net/manual/en/function.proc-open.php',
        ':disable_functions_url' => 'http://php.net/manual/en/ini.core.php#ini.disable-functions',
      ]);
    }
    else {
      $status = $this->checkPath($this->configFactory->get('imagemagick.settings')->get('path_to_binaries'));
      if (!empty($status['errors'])) {
        // Can not execute 'convert'.
        $severity = REQUIREMENT_ERROR;
        foreach ($status['errors'] as $error) {
          $reported_info[] = $error;
        }
        $reported_info[] = $this->t('Go to the <a href=":url">Image toolkit</a> page to configure the toolkit.', [':url' => Url::fromRoute('system.image_toolkit_settings')->toString()]);
      }
      else {
        // No errors, report the version information.
        $severity = REQUIREMENT_INFO;
        $version_info = explode("\n", preg_replace('/\r/', '', Html::escape($status['output'])));
        $more_info_available = FALSE;
        foreach ($version_info as $key => $item) {
          if (stripos($item, 'feature') !== FALSE || $key > 4) {
            $more_info_available = TRUE;
            break;

          }
          $reported_info[] = $item;
        }
        if ($more_info_available) {
          $reported_info[] = $this->t('To display more information, go to the <a href=":url">Image toolkit</a> page, and expand the \'Version information\' section.', [':url' => Url::fromRoute('system.image_toolkit_settings')->toString()]);
        }
        $reported_info[] = '';
        $reported_info[] = $this->t("Enabled image file extensions: %extensions", [
          '%extensions' => Unicode::strtolower(implode(', ', static::getSupportedExtensions())),
        ]);
      }
    }
    return [
      'imagemagick' => [
        'title' => $this->t('ImageMagick'),
        'description' => [
          '#markup' => implode('<br />', $reported_info),
        ],
        'severity' => $severity,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function isAvailable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSupportedExtensions() {
    return \Drupal::service('imagemagick.format_mapper')->getEnabledExtensions();
  }

}
