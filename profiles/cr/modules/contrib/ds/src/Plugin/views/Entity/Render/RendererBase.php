<?php

namespace Drupal\ds\Plugin\views\Entity\Render;

use Drupal\Component\Utility\Unicode;
use Drupal\views\Entity\Render\EntityTranslationRendererBase;

/**
 * Renders entities in the current language.
 */
abstract class RendererBase extends EntityTranslationRendererBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(array $result) {
    parent::preRender($result);
    $this->dsPreRender($result);
  }

  /**
   * Pre renders all the Display Suite rows.
   */
  protected function dsPreRender(array $result, $translation = FALSE) {
    if ($result) {
      // Get all entities which will be used to render in rows.
      $view_builder = $this->view->rowPlugin->entityManager->getViewBuilder($this->entityType->id());

      $i = 0;
      $grouping = array();
      $rendered = FALSE;

      foreach ($result as $row) {
        $group_value_content = '';
        $entity = $row->_entity;
        $entity->view = $this->view;
        /* @var $entity \Drupal\Core\Entity\EntityInterface */
        $entity_id = $entity->id();
        $langcode = $this->getLangcode($row);

        // Default view mode.
        $view_mode = $this->view->rowPlugin->options['view_mode'];

        // Display settings view mode.
        if ($this->view->rowPlugin->options['switch_fieldset']['switch']) {
          $switch = $entity->get('ds_switch')->value;
          if (!empty($switch)) {
            $view_mode = $switch;
          }
        }

        // Change the view mode per row.
        if ($this->view->rowPlugin->options['alternating_fieldset']['alternating']) {
          // Check for paging to determine the view mode.
          $page = \Drupal::request()->get('page');
          if (!empty($page) && isset($this->view->rowPlugin->options['alternating_fieldset']['allpages']) && !$this->view->rowPlugin->options['alternating_fieldset']['allpages']) {
            $view_mode = $this->view->rowPlugin->options['view_mode'];
          }
          else {
            $view_mode = isset($this->view->rowPlugin->options['alternating_fieldset']['item_' . $i]) ? $this->view->rowPlugin->options['alternating_fieldset']['item_' . $i] : $this->view->rowPlugin->options['view_mode'];
          }
          $i++;
        }

        // The advanced selector invokes hook_ds_views_row_render_entity.
        if ($this->view->rowPlugin->options['advanced_fieldset']['advanced']) {
          $modules = \Drupal::moduleHandler()->getImplementations('ds_views_row_render_entity');
          foreach ($modules as $module) {
            if ($content = \Drupal::moduleHandler()->invoke($module, 'ds_views_row_render_entity', array($entity, $view_mode))) {
              if (!$translation) {
                $this->build[$entity_id] = $content;
              }
              else {
                $this->build[$entity_id][$langcode] = $content;
              }
              $rendered = TRUE;
            }
          }
        }

        // Give modules a chance to alter the $view_mode. Use $view_mode by ref.
        $view_name = $this->view->storage->id();
        $context = array(
          'entity' => $entity,
          'view_name' => $view_name,
          'display' => $this->view->getDisplay(),
        );
        \Drupal::moduleHandler()->alter('ds_views_view_mode', $view_mode, $context);

        if (!$rendered) {
          if (!$translation) {
            if (!empty($view_mode)) {
              $this->build[$entity_id] = $view_builder->view($entity, $view_mode, $langcode);
            }
            else {
              $this->build[$entity_id] = $view_builder->view($entity, 'full', $langcode);
            }
          }
          else {
            if (!empty($view_mode)) {
              $this->build[$entity_id][$langcode] = $view_builder->view($entity, $view_mode, $langcode);
            }
            else {
              $this->build[$entity_id][$langcode] = $view_builder->view($entity, 'full', $langcode);
            }
          }
        }

        $context = array(
          'row' => $row,
          'view' => &$this->view,
          'view_mode' => $view_mode,
        );
        \Drupal::moduleHandler()->alter('ds_views_row_render_entity', $this->build[$entity_id], $context);

        // Keep a static grouping for this view.
        if ($this->view->rowPlugin->options['grouping_fieldset']['group']) {

          $group_field = $this->view->rowPlugin->options['grouping_fieldset']['group_field'];

          // New way of creating the alias.
          if (strpos($group_field, '|') !== FALSE) {
            list(, $ffield) = explode('|', $group_field);
            $group_field = $this->view->sort[$ffield]->tableAlias . '_' . $this->view->sort[$ffield]->realField;
          }

          // Note, the keys in the $row object are cut of at 60 chars.
          // see views_plugin_query_default.inc.
          if (Unicode::strlen($group_field) > 60) {
            $group_field = Unicode::substr($group_field, 0, 60);
          }

          $raw_group_value = isset($row->{$group_field}) ? $row->{$group_field} : '';
          $group_value = $raw_group_value;

          // Special function to format the heading value.
          if (!empty($this->view->rowPlugin->options['grouping_fieldset']['group_field_function'])) {
            $function = $this->view->rowPlugin->options['grouping_fieldset']['group_field_function'];
            if (function_exists($function)) {
              $group_value = $function($raw_group_value, $row->_entity);
            }
          }

          if (!isset($grouping[$group_value])) {
            $group_value_content = array(
              '#markup' => '<h2 class="grouping-title">' . $group_value . '</h2>',
              '#weight' => -5,
            );
            $grouping[$group_value] = $group_value;
          }
        }

        // Grouping.
        if (!empty($grouping)) {
          if (!empty($group_value_content)) {
            if (!$translation) {
              $this->build[$entity_id] = array(
                'title' => $group_value_content,
                'content' => $this->build[$entity_id],
              );
            }
            else {
              $this->build[$entity_id][$langcode] = array(
                'title' => $group_value_content,
                'content' => $this->build[$entity_id][$langcode],
              );
            }
          }
        }
      }
    }
  }

}
