<?php

namespace Drupal\cr_colours\Service;

use Drupal\Core\StringTranslation\TranslationManager;

/**
 * Class ColourService
 *
 * @package Drupal\cr_colours\Service
 */
class ColourService {

  /**
   * @var TranslationManager
   */
  private $translationManager;

  /**
   * ColourService constructor.
   *
   * @param TranslationManager $translationManager
   */
  public function __construct(TranslationManager $translationManager) {
    $this->translationManager = $translationManager;
  }

  /**
   * Get an array of available button colours.
   * @return array
   */
  public function getStandardButtonColoursArray() {
    return [
      'btn--white-ghost' => $this->translationManager->translate('White ghost'),
      'btn--black-ghost' => $this->translationManager->translate('Black ghost'),
      'btn--red' => $this->translationManager->translate('Red'),
      'btn--blue' => $this->translationManager->translate('Blue'),
      'btn--yellow' => $this->translationManager->translate('Yellow'),
      'btn--green' => $this->translationManager->translate('Green'),
      'btn--teal' => $this->translationManager->translate('Teal'),
      'btn--royal-blue' => $this->translationManager->translate('Royal blue'),
      'btn--purple' => $this->translationManager->translate('Purple'),
      'btn--pink' => $this->translationManager->translate('Pink'),
      'btn--white' => $this->translationManager->translate('White'),
    ];
  }

  /**
   * Get an array of available colours.
   * @return array
   */
  public function getStandardColoursArray() {
    return [
      'bg--white' => $this->translationManager->translate('White'),
      'bg--black' => $this->translationManager->translate('Black'),
      'bg--red' => $this->translationManager->translate('Red'),
      'bg--blue' => $this->translationManager->translate('Blue'),
      'bg--yellow' => $this->translationManager->translate('Yellow'),
      'bg--green' => $this->translationManager->translate('Green'),
      'bg--teal' => $this->translationManager->translate('Teal'),
      'bg--royal-blue' => $this->translationManager->translate('Royal blue'),
      'bg--pink' => $this->translationManager->translate('Pink'),
      'bg--purple' => $this->translationManager->translate('Purple'),
      'bg--jasper-grey' => $this->translationManager->translate('Jasper grey'),
      'bg--gainsboro-grey' => $this->translationManager->translate('Gainsboro grey'),
      'bg--light-grey' => $this->translationManager->translate('Light grey'),
      'bg--smoke-grey' => $this->translationManager->translate('Smoke grey'),
      'bg--dark-blue' => $this->translationManager->translate('Dark blue'),
    ];
  }

}
