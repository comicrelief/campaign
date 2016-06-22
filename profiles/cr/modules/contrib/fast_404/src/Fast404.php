<?php

namespace Drupal\fast404;

use Drupal\Core\Site\Settings;
use Drupal\Core\Database\Database;
use Drupal\Component\Utility\SafeMarkup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Fast404 {

  public $respond_404 = FALSE;

  public $request;

  public $event;

  public $load_html = TRUE;

  public function __construct(Request $request) {
    $this->request = $request;
  }

  public function extensionCheck() {
    // Get the path from the request.
    $path = $this->request->getPathInfo();

    // Ignore calls to the homepage, to avoid unnecessary processing.
    if (!isset($path) || $path == '/') {
      return;
    }

    // Check to see if the URL is that of an image derivative.
    // If this file does not already exist, it will be handled via Drupal.
    if (strpos($path, 'styles/')) {

      // Check to see if we will allow anon users to access this page.
      if (!Settings::get('fast404_allow_anon_imagecache', TRUE)) {
        $cookies = $this->request->cookies->all();

        // At this stage of the game we don't know if the user is logged in via
        // regular function calls. Simply look for a session cookie. If we find
        // one we'll assume they're logged in
        if (isset($cookies) && is_array($cookies)) {
          foreach ($cookies as $cookie) {
            if (stristr($cookie, 'SESS')) {
              return;
            }
          }
        }
      }

      // We're allowing anyone to hit non-existing image derivative URLs
      // (default behavior).
      else {
        return;
      }
    }

    // If we are using URL whitelisting then determine if the current URL is
    // whitelisted before running the extension check.
    // Check for exact URL matches and assume it's fine if we get one.
    if(Settings::get('fast404_url_whitelisting', FALSE)) {
      $trimmed_path = ltrim($path, '/');
      $allowed = Settings::get('fast404_whitelist', array());
      if (in_array($trimmed_path, $allowed)) {
        // URL is whitelisted. Assumed good.
        return TRUE;
      }
    }

    // Check for whitelisted strings in the URL.
    $string_whitelist = Settings::get('fast404_string_whitelisting', FALSE);
    if (is_array($string_whitelist)) {
      foreach ($string_whitelist as $str) {
        if (strstr($path, $str) !== FALSE) {
          return;
        }
      }
    }

    $extensions =  Settings::get('fast404_exts', '/^(?!robots).*\.(txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i');
    // Determine if URL contains a blacklisted extension.
    if (isset($extensions) && preg_match($extensions, $path, $m)) {
      $this->load_html = FALSE;
      $this->blockPath();
      return;
    }

  }

  public function pathCheck() {
    // Since the path check is a lot more aggressive in its blocking we should
    // actually check that the user wants it to be done.
    if (!Settings::get('fast404_path_check', FALSE)) {
      return;
    }
    // Get the path from the request.
    $path = $this->request->getPathInfo();

    // Ignore calls to the homepage, to avoid unnecessary processing.
    if (!isset($path) || $path == '/') {
      return;
    }

    // If we have a database connection we can use it, otherwise we might be
    // initialising it.

    // We remove '/' from the list of possible patterns as it exists in the router
    // by default. This means that the query would match any path (/%) which is
    // undesirable when we're only looking to match some paths.
    $sql = "SELECT pattern_outline FROM {router} WHERE :path LIKE CONCAT(pattern_outline, '%') AND pattern_outline != '/'";
    $result = Database::getConnection()->query($sql, array(':path' => $path))->fetchField();
    if ($result) {
      return;
    }

    // Check the URL alias table for anything that's not a standard Drupal path.
    $sql = "SELECT pid FROM {url_alias} WHERE :alias = CONCAT('/', alias)";
    $result = Database::getConnection()->query($sql, array(':alias' => $path))->fetchField();
    if ($result) {
      return;
    }

    // If we get to here it means nothing has matched the request so we assume
    // it's a bad path and block it.

    $this->blockPath();

  }

  public function blockPath() {
    $this->respond_404 = TRUE;
  }

  public function isPathBlocked() {
    if (PHP_SAPI === 'cli') {
      return FALSE;
    }
    return $this->respond_404;
  }

  public function response($return = FALSE) {
    $message = Settings::get('fast404_html', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server (Fast 404).</p></body></html>');
    $return_gone = Settings::get('fast404_return_gone', FALSE);
    $custom_404_path = Settings::get('fast404_HTML_error_page',FALSE);
    if ($return_gone){
      header((Settings::get('fast_404_HTTP_status_method', 'mod_php') == 'FastCGI' ? 'Status:' : 'HTTP/1.0') . ' 410 Gone');
    } else {
      header((Settings::get('fast_404_HTTP_status_method', 'mod_php') == 'FastCGI' ? 'Status:' : 'HTTP/1.0') . ' 404 Not Found');
    }
    // If a file is set to provide us with fast_404 joy, load it
    if(($this->load_html || Settings::get('fast_404_HTML_error_all_paths',FALSE) === TRUE) && file_exists($custom_404_path)) {
      $message = @file_get_contents($custom_404_path, FALSE);
    }
    $response = new Response(SafeMarkup::format($message, array('@path' => $this->request->getPathInfo())), 404);
    if ($return) {
      return $response;
    }
    else {
      $response->send();
    }
  }
}
