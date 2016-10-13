<?php

namespace Drupal\yamlform\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides route responses for form help.
 */
class YamlFormHelpController {

  /**
   * Returns dedicated help page with a video.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $id
   *   The id of the dedicated help section.
   *
   * @return array
   *   A renderable array containing a dedicated help page with a video.
   */
  public function index(Request $request, $id) {
    $id = str_replace('-', '_', $id);
    $help = _yamlform_help();
    if (!isset($help[$id])) {
      throw new NotFoundHttpException();
    }

    $build = [];
    if (is_array($help[$id]['content'])) {
      $build['content'] = $help[$id]['content'];
    }
    else {
      $build['content'] = [
        '#markup' => $help[$id]['content'],
      ];
    }
    if ($help[$id]['youtube_id']) {
      $build['video'] = [
        '#theme' => 'yamlform_help_video_youtube',
        '#youtube_id' => $help[$id]['youtube_id'],
      ];
    }
    return $build;
  }

  /**
   * Route title callback.
   *
   * @param string $id
   *   The id of the dedicated help section.
   *
   * @return string
   *   The dedicated help section's title.
   */
  public function title(Request $request, $id) {
    $id = str_replace('-', '_', $id);
    $help = _yamlform_help();
    return (isset($help[$id])) ? $help[$id]['title'] : t('Watch video');
  }

}
