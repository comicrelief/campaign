<?php

namespace Drupal\cr_config_management\Service;

use Drupal\Core\StringTranslation\TranslationManager;

/**
 * Class ColourService
 * @package Drupal\cr_config_management\Service
 */
class ColourService
{

  /**
   * @var TranslationManager
   */
  private $translationManager;

  /**
   * ColourService constructor.
   * @param TranslationManager $translationManager
   */
  public function __construct(TranslationManager $translationManager)
  {
    $this->translationManager = $translationManager;
  }

  /**
   * Get an array of standard available colours.
   * @return array
   */
  public function getStandardColoursArray()
  {
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
