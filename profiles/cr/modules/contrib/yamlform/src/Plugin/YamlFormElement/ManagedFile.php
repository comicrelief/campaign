<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\ManagedFile.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
// ISSUE: Below import statement is throwing "Error: Cannot use Drupal\Core\Url
// as Url because the name is already in use in" when executing any drush
// yamlform command that loads this file..
// use Drupal\Core\Url.
use Drupal\file\Entity\File;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\Component\Utility\Bytes;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'managed_file' element.
 *
 * @YamlFormElement(
 *   id = "managed_file",
 *   label = @Translation("Managed file")
 * )
 */
class ManagedFile extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $element['#upload_location'] = $this->getUploadLocation($element, $yamlform_submission->getYamlForm());
    $element['#upload_validators']['file_validate_size'] = [$this->getMaxFileSize($element)];
    $element['#upload_validators']['file_validate_extensions'] = [$this->getFileExtensions($element)];
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element, $default_value) {
    if (!empty($element['#default_value']) && !is_array($element['#default_value'])) {
      $element['#default_value'] = [$element['#default_value']];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $file = File::load($value);
    if (!$file) {
      return '';
    }
    $format = $this->getFormat($element);
    if ($format == 'link') {
      return [
        '#theme' => 'file_link',
        '#file' => $file,
      ];
    }
    else {
      return parent::formatHtml($element, $value, $options);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    $file = File::load($value);
    if (!$file) {
      return '';
    }
    $format = $this->getFormat($element);
    switch ($format) {
      case 'id':
        return $file->id();

      case 'url':
      default:
        return file_create_url($file->getFileUri());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'link';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'link' => $this->t('Link'),
      'id' => $this->t('File ID'),
      'url' => $this->t('URL'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Get current value and original value for this element.
    $key = $element['#key'];

    $original_data = $yamlform_submission->getOriginalData();
    $data = $yamlform_submission->getData();

    $value = isset($data[$key]) ? $data[$key] : NULL;
    $original_value = isset($original_data[$key]) ? $original_data[$key] : NULL;

    // Check the original submission and delete the old file upload.
    if ($original_value && $original_value != $value) {
      file_delete($original_value);
    }

    // Exit if there is no value (aka fid).
    if (!$value) {
      return;
    }

    $file = File::load($value);
    if (!$file) {
      return;
    }

    $source_uri = $file->getFileUri();

    // Replace /_sid_/ token with the submission id.
    if (strpos($source_uri, '/_sid_/')) {
      $destination_uri = str_replace('/_sid_/', '/' . $yamlform_submission->id() . '/', $source_uri);
      $destination_directory = drupal_dirname($destination_uri);
      file_prepare_directory($destination_directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
      $destination_uri = file_unmanaged_move($source_uri, $destination_uri);
      // Update the file's uri and save.
      $file->setFileUri($destination_uri);
      $file->save();
    }

    // Update file usage table.
    // Set file usage which will also make the file's status permanent.
    /** @var \Drupal\file\FileUsage\FileUsageInterface $file_usage */
    $file_usage = \Drupal::service('file.usage');
    $file_usage->delete($file, 'yamlform', 'yamlform_submission', $yamlform_submission->id(), 0);
    $file_usage->add($file, 'yamlform', 'yamlform_submission', $yamlform_submission->id());
  }

  /**
   * {@inheritdoc}
   */
  public function postDelete(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $yamlform = $yamlform_submission->getYamlForm();

    $data = $yamlform_submission->getData();
    $key = $element['#key'];

    // Delete managed file record.
    if (!empty($data[$key])) {
      file_delete($data[$key]);
    }

    // Remove the empty directory.
    file_unmanaged_delete_recursive('public://yamlform//' . $yamlform->id() . '/' . $yamlform_submission->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    $file_extensions = explode(' ', $this->getFileExtensions($element));
    $file_extension = $file_extensions[array_rand($file_extensions)];
    $upload_location = $this->getUploadLocation($element, $yamlform);
    $file_destination = $upload_location . '/' . $element['#key'] . '.' . $file_extension;

    // Look for an existing temp files that have not been uploaded.
    $fids = \Drupal::entityQuery('file')
      ->condition('status', 0)
      ->condition('uid', \Drupal::currentUser()->id())
      ->condition('uri', $upload_location . '/' . $element['#key'] . '.%', 'LIKE')
      ->execute();
    if ($fids) {
      return reset($fids);
    }

    // Otherwise generate a new temp file that can be uploaded.
    $file_uri = file_unmanaged_save_data('{empty}', $file_destination);
    $file = File::create([
      'uri' => $file_uri ,
      'uid' => \Drupal::currentUser()->id(),
    ]);
    $file->save();
    return $file->id();
  }

  /**
   * Get max file size for an element.
   *
   * @param array $element
   *   An element.
   *
   * @return int
   *   Max file size.
   */
  protected function getMaxFileSize(array $element) {
    // Set max file size.
    $max_filesize = \Drupal::config('yamlform.settings')->get('inputs.default_max_filesize') ?: file_upload_max_size();
    $max_filesize = Bytes::toInt($max_filesize);
    if (!empty($element['#max_filesize'])) {
      $max_filesize = min($max_filesize, Bytes::toInt($element['#max_filesize']) * 1024);
    }
    return $max_filesize;
  }

  /**
   * Get allowed file extensions for an element.
   *
   * @param array $element
   *   An element.
   *
   * @return int
   *   File extension.
   */
  protected function getFileExtensions(array $element) {
    // Set valid file extensions.
    $file_extensions = \Drupal::config('yamlform.settings')->get('inputs.default_file_extensions');
    if (!empty($element['#file_extensions'])) {
      $file_extensions = $element['#file_extensions'];
    }
    return $file_extensions;
  }

  /**
   * Get file upload location.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return string
   *   Upload location.
   */
  protected function getUploadLocation(array $element, YamlFormInterface $yamlform) {
    if (empty($element['#upload_location'])) {
      $upload_location = 'public://yamlform/' . $yamlform->id() . '/_sid_';
    }
    else {
      $upload_location = $element['#upload_location'];
    }

    // Make sure the upload location exists and is writable.
    file_prepare_directory($upload_location, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

    return $upload_location;
  }

  /**
   * Form API callback. Consolidate the array of fids for this field into a single fids.
   */
  public static function validate(array &$element, FormStateInterface $form_state) {
    if (!empty($element['#files'])) {
      $file = reset($element['#files']);
      $value = (int) $file->id();
    }
    else {
      $value = NULL;
    }
    $form_state->setValueForElement($element, $value);
  }

}
