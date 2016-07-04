<?php

/**
 * @file
 * API documentation for the ImageMagick module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the settings before an image is parsed by the ImageMagick toolkit.
 *
 * ImageMagick does not support stream wrappers so this hook allows modules to
 * resolve URIs of image files to paths on the local filesystem.
 * Modules can also decide to move files from remote systems to the local
 * file system to allow processing.
 *
 * @param \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit $toolkit
 *   The Imagemagick toolkit instance to alter.
 *
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::parseFile()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getSource()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::setSourceLocalPath()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getSourceLocalPath()
 */
function hook_imagemagick_pre_parse_file_alter(\Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit $toolkit) {
}

/**
 * Alter an image after it has been converted by the ImageMagick toolkit.
 *
 * ImageMagick does not support remote file systems, so modules can decide
 * to move temporary files from the local file system to remote destination
 * systems.
 *
 * @param \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit $toolkit
 *   The Imagemagick toolkit instance to alter.
 *
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getDestination()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getDestinationLocalPath()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::save()
 */
function hook_imagemagick_post_save_alter(\Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit $toolkit) {
}

/**
 * Alter the arguments to ImageMagick command-line executables.
 *
 * This hook is executed just before Imagemagick executables are called.
 * It allows to change file paths for source and destination image files,
 * and/or to alter the command line arguments that are passed to the binaries.
 * The toolkit provides methods to prepend, add, find, get and reset
 * arguments that have already been set by image effects.
 *
 * ImageMagick automatically converts the target image to the format denoted by
 * the file extension. However, since changing the file extension is not always
 * an option, you can specify an alternative image format via
 * $toolkit->setDestinationFormat('format'), where 'format' is a string
 * denoting an Imagemagick supported format.
 * When the destination format is set, it is passed to ImageMagick's convert
 * binary with the syntax "[format]:[destination]".
 *
 * @param \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit $toolkit
 *   The Imagemagick toolkit instance to alter.
 * @param string $command
 *   The Imagemagick binary being called.
 *
 * @see http://www.imagemagick.org/script/command-line-processing.php#output
 * @see http://www.imagemagick.org/Usage/files/#save
 *
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getArguments()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::prependArgument()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::addArgument()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::findArgument()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::resetArguments()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getSource()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::setSourceLocalPath()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getSourceLocalPath()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getDestination()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::setDestinationLocalPath()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::getDestinationLocalPath()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::convert()
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::identify()
 */
function hook_imagemagick_arguments_alter(\Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit $toolkit, $command) {
}
