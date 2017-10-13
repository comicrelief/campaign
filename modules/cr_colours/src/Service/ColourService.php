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
  protected $translationManager;

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
      'btn btn--white-ghost' => $this->translationManager->translate('White ghost button'),
      'btn btn--black-ghost' => $this->translationManager->translate('Black ghost button'),
      'btn btn--red' => $this->translationManager->translate('Red button'),
      'btn btn--blue' => $this->translationManager->translate('Blue button'),
      'btn btn--yellow' => $this->translationManager->translate('Yellow button'),
      'btn btn--green' => $this->translationManager->translate('Green button'),
      'btn btn--teal' => $this->translationManager->translate('Teal button'),
      'btn btn--royal-blue' => $this->translationManager->translate('Royal blue button'),
      'btn btn--purple' => $this->translationManager->translate('Purple button'),
      'btn btn--pink' => $this->translationManager->translate('Pink button'),
      'btn btn--white' => $this->translationManager->translate('White button'),
      'link link--red' => $this->translationManager->translate('Red link'),
      'link link--blue' => $this->translationManager->translate('Blue link'),
      'link link--yellow' => $this->translationManager->translate('Yellow link'),
      'link link--green' => $this->translationManager->translate('Green link'),
      'link link--teal' => $this->translationManager->translate('Teal link'),
      'link link--royal-blue' => $this->translationManager->translate('Royal blue link'),
      'link link--purple' => $this->translationManager->translate('Purple link'),
      'link link--pink' => $this->translationManager->translate('Pink link'),
      'link link--black' => $this->translationManager->translate('Black link'),
      'link link--white' => $this->translationManager->translate('White link')
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
