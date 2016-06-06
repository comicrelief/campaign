<?php

/**
 * @todo #2311679, this is a stop-gap workaround
 * remove this once core has a solution in place.
 */

namespace Drupal\imagemagick;

use Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser;

/**
 * Makes possible to guess the MIME type of a file using its extension.
 */
class Todo2311679 extends ExtensionMimeTypeGuesser {

  public function getExtensionsForMimeType($mimetype) {
    if ($this->mapping === NULL) {
      $mapping = $this->defaultMapping;
      // Allow modules to alter the default mapping.
      $this->moduleHandler->alter('file_mimetype_mapping', $mapping);
      $this->mapping = $mapping;
    }
    if (!in_array($mimetype, $this->mapping['mimetypes'])) {
      return [];
    }
    $key = array_search($mimetype, $this->mapping['mimetypes']);
    $extensions = array_keys($this->mapping['extensions'], $key, TRUE);
    sort($extensions);
    return $extensions;
  }

  public function getMimeTypes() {
    if ($this->mapping === NULL) {
      $mapping = $this->defaultMapping;
      // Allow modules to alter the default mapping.
      $this->moduleHandler->alter('file_mimetype_mapping', $mapping);
      $this->mapping = $mapping;
    }
    return array_values($this->mapping['mimetypes']);
  }

}
