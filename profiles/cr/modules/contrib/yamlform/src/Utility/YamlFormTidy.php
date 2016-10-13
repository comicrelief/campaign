<?php

namespace Drupal\yamlform\Utility;

use Symfony\Component\Yaml\Unescaper;

/**
 * Provides YAML tidy function.
 */
class YamlFormTidy {

  /**
   * Tidy export YAML includes tweaking array layout and multiline strings.
   *
   * @param string $yaml
   *   The output generated from \Drupal\Component\Serialization\Yaml::encode.
   *
   * @return string
   *   The encoded data.
   */
  public static function tidy($yaml) {
    static $unescaper;
    if (!isset($unescaper)) {
      $unescaper = new Unescaper();
    }

    // Remove return after array delimiter.
    $yaml = preg_replace('#(\n[ ]+-)\n[ ]+#', '\1 ', $yaml);

    // Support YAML newlines preserved syntax via pipe (|).
    $lines = explode("\n", $yaml);
    foreach ($lines as $index => $line) {
      if (empty($line) || strpos($line, '\n') === FALSE) {
        continue;
      }

      if (preg_match('/^([ ]*(?:- )?)([a-z_]+|\'[^\']+\'|"[^"]+"): (\'|")(.+)\3$/', $line, $match)) {
        $prefix = $match[1];
        $indent = str_repeat(' ', strlen($prefix));
        $name = $match[2];
        $quote = $match[3];
        $value = $match[4];

        if ($quote == "'") {
          $value = rtrim($unescaper->unescapeSingleQuotedString($value));
        }
        else {
          $value = rtrim($unescaper->unescapeDoubleQuotedString($value));
        }

        if (strpos($value, '<') === FALSE) {
          $lines[$index] = $prefix . $name . ": |\n$prefix  " . str_replace("\n", "\n$prefix  ", $value);
        }
        else {
          $value = str_replace("\n\n", "\n", $value);
          $value = preg_replace('#\s*</p>#', '</p>', $value);
          $value = str_replace("\n", "\n$indent  ", $value);
          $lines[$index] = $prefix . $name . ": |\n$indent  " . $value;
        }
      }
    }
    $yaml = implode("\n", $lines);
    return $yaml;
  }

}
