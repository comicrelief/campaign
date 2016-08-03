<?php

/**
 * @file
 * Contains \Drupal\focal_point\FocalPoint.
 */

namespace Drupal\focal_point;

use Drupal\Core\Cache\Cache;
use Drupal\file\Entity\File;

/**
 * Defines the FocalPoint class.
 */
class FocalPoint {

  /**
   * The default value to use for focal point when non is specified.
   */
  const DEFAULT_VALUE = '50,50';

  /**
   * The file entity id to which this focal point object applies.
   *
   * @var int
   */
  private $fid;

  /**
   * The focal point coordinates.
   *
   * @var string
   *   A string in the form ##,##.
   */
  private $focalPoint;

  /**
   * Constructs a Focal Point object.
   *
   * @param int $fid
   */
  public function __construct($fid) {
    $this->fid = $fid;
    $this->focalPoint = $this->getFocalPoint();
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::getFocalPoint().
   *
   * Get the focal point value for a given file entity. If none is found, return
   * an empty string.
   *
   * @return string
   */
  public function getFocalPoint() {
    if (is_null($this->focalPoint)) {
      $result = self::getFocalPoints(array($this->fid));
      $this->focalPoint = isset($result[$this->fid]) ? $result[$this->fid] : '';
    }

    return $this->focalPoint;
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::getFocalPoints().
   *
   * Get the focal point values in an array keyed by fid for the given file
   * entities. If none is found for any of the given files, the value for that
   * file will be an empty string.
   *
   * @param array $fids
   *
   * @return array
   */
  public static function getFocalPoints(array $fids) {
    $focal_points =  &drupal_static(__METHOD__, array());

    $missing = array_diff($fids, array_keys($focal_points));
    if ($missing) {
      $result = db_query('SELECT fid, focal_point FROM {focal_point} WHERE fid IN (:fids[])', array(':fids[]' => $missing))->fetchAllKeyed();
      $focal_points += $result;
    }

    return array_intersect_key($focal_points, array_combine($fids, $fids));
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::getFromURI().
   *
   * Get the focal point value for a given file based on its URI. If none is
   * found, return an empty string.
   *
   * @param string $uri
   *
   * @return string
   *
   * @todo Figure out a better way of doing this. Right now its needed by the
   *   focal point image effect but it seems wrong.
   */
  public static function getFromURI($uri) {
    $query = db_select('focal_point', 'fp')
      ->fields('fp', array('focal_point'));
    $query->join('file_managed', 'fm', 'fp.fid = fm.fid');
    $query->condition('fm.uri', $uri);
    $focal_point = $query->execute()->fetchField();

    return $focal_point;
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::fid().
   *
   * Returns the file entity id to which this focal point object applies.
   *
   * @return int|null
   */
  public function fid() {
    return isset($this->fid) ? $this->fid : NULL;
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::setFocalPoint().
   *
   * Save the given focal point value for the given file to the database.
   *
   * @param string $focal_point
   */
  public function saveFocalPoint($focal_point) {
    // If the focal point has not changed, then there is nothing to see here.
    if ($this->focalPoint !== $focal_point) {
      \Drupal::database()->merge('focal_point')
        ->key(array('fid' => $this->fid))
        ->fields(array('focal_point' => $focal_point))
        ->execute();

      $this->flush($this->fid);

      // Clear caches and static variables.
      $focal_points =  &drupal_static('getFocalPoints', array());
      unset($focal_points[$this->fid]);
      Cache::invalidateTags(array('file:' . $this->fid));
    }
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::delete().
   *
   * Deletes the focal point values for the given file from the database.
   *
   * @param int $fid
   */
  public function delete($fid) {
    $this->flush($fid);

    db_delete('focal_point')
      ->condition('fid', $fid)
      ->execute();
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::flush().
   *
   * Flush all image derivatives for the given file.
   *
   * @param int $fid
   */
  public function flush($fid) {
    $file = File::load($fid);
    image_path_flush($file->getFileUri());
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::parse().
   *
   * Return the given focal point value broken out into its component pieces as
   * an array in the following form:
   *   - x-offset: x value
   *   - y-offset: y value
   * If all else fails, return the parsed default focal point value.
   *
   * @param string $focal_point
   *
   * @return array
   */
  public static function parse($focal_point) {
    if (empty($focal_point) || !self::validate($focal_point)) {
      $focal_point = self::DEFAULT_VALUE;
    }

    return array_combine(array('x-offset', 'y-offset'), explode(',', $focal_point));
  }

  /**
   * Implements \Drupal\focal_point\FocalPoint::validate().
   *
   * Decides if the given focal point value is valid.
   *
   * @param string $focal_point
   *
   * @return bool
   */
  public static function validate($focal_point) {
    if (preg_match('/^(100|[0-9]{1,2})(,)(100|[0-9]{1,2})$/', $focal_point)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}
