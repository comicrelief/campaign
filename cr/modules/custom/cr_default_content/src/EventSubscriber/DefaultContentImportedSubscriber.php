<?php

namespace Drupal\cr_default_content\EventSubscriber;

use Drupal\default_content\Event\DefaultContentEvents;
use Drupal\default_content\Event\ImportEvent;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the default content imported event.
 */
class DefaultContentImportedSubscriber implements EventSubscriberInterface {

  /**
   * Process the imported entities so we can add the appropriate menu links.
   *
   * @param \Drupal\default_content\Event\ImportEvent $event
   *   The import event.
   */
  public function processImportedEntities(ImportEvent $event) {

    // We only care about creating menu links for our own content.
    if ($event->getModule() !== 'cr_default_content') {
      return;
    }

    // Create out static links, maybe we shouldn't do this always?
    $this->createMenuLink('Home', 'internal:/<front>', -2);

    $entities = $event->getImportedEntities();
    $map = [
      '6ebef494-de4e-44fe-be4e-745916b90722' => ['label' => 'Fundraise (Landing)'],
      '4b1d6619-1f9d-452a-b538-56eb186d0f1e' => ['label' => "What's going on"],
      '65bdf725-10c0-46bf-8703-3b36cb21c746' => ['label' => "FAQ"],
      'd9d66c76-bd43-40c7-8b16-2266f13e1a14' => ['label' => "Legal"],
    ];

    $links_from_map = function ($map, $parent = NULL) use (&$links_from_map, $entities) {
      $weight = 0;
      foreach ($map as $uuid => $link) {
        if (isset($entities[$uuid])) {
          $saved_link = $this->createMenuLink($link['label'], 'entity:node/' . $entities[$uuid]->id(), $weight++, 'main', $parent);
          if (isset($link['children'])) {
            $links_from_map($link['children'], $saved_link->uuid());
          }
        }
      }
    };

    $links_from_map($map);

  	// Set front page to our new fundraise page.
  	\Drupal::configFactory()->getEditable('system.site')->set('page.front', '/fundraise')->save(TRUE);
  }

  /**
   * Creates a menu link given text and path.
   *
   * @param string $text
   *   The menu link text.
   * @param string $path
   *   The menu link path.
   * @param int $weight
   *   The menu link weight.
   * @param string $menu
   *   The menu to add the link to.
   * @param string $parent
   *   The parent menu item to attach the link to.
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContent
   *   The saved menu link.
   */
  protected function createMenuLink($text, $path, $weight = 0, $menu = 'main', $parent = NULL) {
    $menu_link = MenuLinkContent::create([
      'title' => $text,
      'link' => ['uri' => $path],
      'menu_name' => $menu,
      'weight' => $weight,
      'expanded' => '0',
    ]);
    if ($parent !== NULL) {
      $menu_link->set('parent', 'menu_link_content:' . $parent);
    }
    $menu_link->save();
    return $menu_link;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[DefaultContentEvents::IMPORT][] = ['processImportedEntities'];

    return $events;
  }

}
