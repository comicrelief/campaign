<?php

/**
 * @file
 * Contains \Drupal\config_devel\Event\ConfigDevelSaveEvent
 */

namespace Drupal\config_devel\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The ConfigDevelSaveEvent class.
 */
class ConfigDevelSaveEvent extends Event {

  /**
   * An array of files names that will  be written.
   *
   * @var array
   */
  protected $fileNames = [];

  /**
   * An array representing the config object to be written.
   *
   * @var array
   */
  protected $data;

  /**
   * ConfigDevelSaveEvent constructor.
   *
   * @param array $file_names
   *   The config file names.
   * @param array $data
   *   The config data.
   */
  public function __construct(array $file_names, array $data) {
    $this->fileNames = $file_names;
    $this->data = $data;
  }

  /**
   * Gets the filenames that will be saved.
   *
   * @return array
   *   An array of file names.
   */
  public function getFileNames() {
    return $this->fileNames;
  }

  /**
   * Sets the file names.
   *
   * @param array $file_names
   *   An array of file names to be written out
   */
  public function setFileNames(array $file_names) {
    $this->fileNames = $file_names;
  }

  /**
   * Gets the data representing the config object.
   *
   * @return array
   *   An array of config data.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Sets the data for the config object.
   *
   * @param array $data
   *   The config data.
   */
  public function setData(array $data) {
    $this->data = $data;
  }

}
