<?php

/**
 * @file
 * Contains \Drupal\config_readonly\EventSubscriber\ReadOnlyFormSubscriber.
 */

namespace Drupal\config_readonly\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\config_readonly\ReadOnlyFormEvent;

/**
 * Check if the given form should be read-only.
 */
class ReadOnlyFormSubscriber implements EventSubscriberInterface {

  /**
   * Form ids to mark as read only.
   */
  protected $readOnlyFormIds = [
    'config_single_import_form',
    'system_modules',
    'system_modules_uninstall',
  ];

  /**
   * {@inheritdoc}
   */
  public function onFormAlter(ReadOnlyFormEvent $event) {
    // Check if the form is a ConfigFormBase or a ConfigEntityListBuilder.
    $build_info = $event->getFormState()->getBuildInfo();
    $form_object = $build_info['callback_object'];
    $mark_form_read_only = $form_object instanceof ConfigFormBase || $form_object instanceof ConfigEntityListBuilder;

    if (!$mark_form_read_only) {
      $mark_form_read_only = in_array($form_object->getFormId(), $this->readOnlyFormIds);
    }

    // Check if the form is an EntityFormInterface and entity is a config entity.
    if (!$mark_form_read_only && $form_object instanceof EntityFormInterface) {
      $entity = $form_object->getEntity();
      $mark_form_read_only = $entity instanceof ConfigEntityInterface;
    }

    if ($mark_form_read_only) {
      $event->markFormReadOnly();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ReadOnlyFormEvent::NAME][] = ['onFormAlter', 200];
    return $events;
  }

}
