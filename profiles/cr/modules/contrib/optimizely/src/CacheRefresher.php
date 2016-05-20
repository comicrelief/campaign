<?php

namespace Drupal\optimizely;

class CacheRefresher {


  /**
   * doRefresh()
   *
   * @parm
   *   $path_array - An array of the target paths entries that the cache needs to
   *   be cleared. Each entry can also contain wildcards /* or variables "<front>".
   */
  public static function doRefresh($path_array, $original_path_array = NULL) {

    // Determine protocol
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
    $cid_base = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/';

    // If update of project that includes changes to the path, clear cache on all
    // paths to add/remove Optimizely javascript call
    if (isset($original_path_array)) {
      $path_array = array_merge($path_array, $original_path_array);
    }

    // Loop through every path value
    foreach ($path_array as $path_count => $path) {

      $recursive = NULL;

      // Apply to all paths when there's a '*' path entry (default project entry
      // for example) or it's an exclude path entry (don't even try to figure out
      // the paths, just flush all page cache
      if (strpos($path, '*') !== 0) {

        if (strpos($path, '<front>') === 0) {
          $frontpage = \Drupal::config('system.site')->get('page.front');
          $frontpage = $frontpage ? $frontpage : 'node';

          $cid = $cid_base . '/' . $frontpage;
          $recursive = FALSE;
        }
        elseif (strpos($path, '/*') > 0)  {
          $cid = $cid_base . substr($path, 0, strlen($path) - 2);
          $recursive = TRUE;
        }
        else {
          $cid = $cid_base . $path;
          $recursive = FALSE;
        }

        // D7, was: cache_clear_all($cid, 'cache_page', $recursive);
        // N.B. We really need to revisit this call to deleteAll() 
        // because of possible performance hits. EF
        $cache = \Drupal::cache('render');
        $recursive ? $cache->deleteAll() : $cache->delete($cid);
      }
      else {
        // D7, was: cache_clear_all('*', 'cache_page', TRUE);
        $cache = \Drupal::cache('render');
        $cache->deleteAll();
        break;
      }

    }

    // Varnish
    // if (module_exists('varnish')) {
    if (\Drupal::moduleHandler()->moduleExists('varnish')) {
      varnish_expire_cache($path_array);
      drupal_set_message(t('Successfully purged cached page from Varnish.'));
    }

    drupal_set_message(t('"Render" cache has been cleared based on the project path settings.'), 'status');

  }

}