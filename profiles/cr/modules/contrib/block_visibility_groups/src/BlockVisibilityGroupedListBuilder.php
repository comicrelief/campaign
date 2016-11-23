<?php

namespace Drupal\block_visibility_groups;

use Drupal\block\BlockListBuilder;
use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends BlockListBuilder to add our elements only show certain blocks.
 */
class BlockVisibilityGroupedListBuilder extends BlockListBuilder {
  use BlockVisibilityLister;
  use ConditionsSetFormTrait;

  /**
   * Used in query string to denote blocks that don't have a group set.
   */
  const UNSET_GROUP = 'UNSET-GROUP';
  /**
   * Used in Query string to denote showing all blocks.
   */
  const ALL_GROUP = 'ALL-GROUP';
  /**
   * The entity storage class for Block Visibility Groups.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $group_storage;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a new BlockVisibilityGroupedListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, ThemeManagerInterface $theme_manager, FormBuilderInterface $form_builder, EntityStorageInterface $block_visibility_group_storage, StateInterface $state) {
    parent::__construct($entity_type, $storage, $theme_manager, $form_builder);

    $this->group_storage = $block_visibility_group_storage;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('theme.manager'),
      $container->get('form_builder'),
      $container->get('entity.manager')->getStorage('block_visibility_group'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $group_options = $this->getBlockVisibilityGroupOptions();
    $default_value = $this->getCurrentBlockVisibilityGroup();
    $current_block_visibility_group = NULL;
    if (!in_array($default_value, [
      BlockVisibilityGroupedListBuilder::ALL_GROUP,
      BlockVisibilityGroupedListBuilder::UNSET_GROUP,
    ])
    ) {
      $current_block_visibility_group = $default_value;
    }
    $options = [];

    foreach ($group_options as $key => $group_option) {
      if ($default_value == $key) {
        $default_value = $group_option['path'];
      }
      $options[$group_option['path']] = $group_option['label'];
    }
    $form['block_visibility_group'] = array(
      '#weight' => -100,
    );
    $form['block_visibility_group']['select'] = array(
      '#type' => 'select',
      '#title' => $this->t('Block Visibility Group'),
      '#options' => $options,
      '#default_value' => $default_value,
      // @todo Is there a better way to do this?
      '#attributes' => ['onchange' => 'this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value)'],
    );
    $description = $this->t('Block Visibility Groups allow you to control the visibility of multiple blocks in one place.');

    if (!$this->groupsExist()) {
      $description .= ' ' . $this->t('No Groups have been created yet.');
      $form['block_visibility_group']['create'] = array(
        '#type' => 'link',
        '#title' => t('Create a Group'),
        '#url' => Url::fromRoute('entity.block_visibility_group.add_form'),
      );
    }
    else {
      if ($current_block_visibility_group) {

        $form['block_visibility_group']['block_visibility_group_show_global'] = array(
          '#type' => 'checkbox',
          '#title' => $this->t('Show Global Blocks'),
          '#default_value' => $this->getShowGlobalWithGroup(),
          '#description' => $this->t('Show global blocks when viewing a visibility group.'),
          '#attributes' => ['onchange' => 'this.form.submit()'],
        );

        /** @var \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup $group */
        $group = $this->group_storage->load($current_block_visibility_group);
        $form['block_visibility_group']['help'] = $this->createHelp($group);

        $conditions_element = $this->createConditionsSet($form, $group, 'layout');
        $conditions_element['#type'] = 'details';
        if ($this->request->query->get('show_conditions')) {
          $conditions_element['#open'] = TRUE;
        }
        else {
          $conditions_element['#open'] = FALSE;
        }

        $form['block_visibility_group']['conditions_section'] = $conditions_element;

      }

    }
    $form['block_visibility_group']['select']['#description'] = $description;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $show_global = $form_state->getValue('block_visibility_group_show_global', 1);
    $this->state->set('block_visibility_group_show_global', $show_global);
    // Prevent sending an empty value, which would unset all blocks.
    if (!empty($form_state->getValue('blocks'))) {
      parent::submitForm($form, $form_state);
    }
  }

  /**
   * Get the group from the query string.
   *
   * @return mixed|string
   */
  protected function getCurrentBlockVisibilityGroup() {
    $request_id = $this->request->query->get('block_visibility_group');
    if (!$request_id) {
      $request_id = BlockVisibilityGroupedListBuilder::ALL_GROUP;
    }
    return $request_id;
  }

  /**
   * Get Group options info to group select dropdown.
   *
   * @return array
   *    Keys = Group keys
   *    Values array with keys
   *       label
   *       path - URL to redirect to Group page.
   */
  protected function getBlockVisibilityGroupOptions() {

    $route_options = [
      BlockVisibilityGroupedListBuilder::UNSET_GROUP => ['label' => $this->t('- Global blocks -')],
      BlockVisibilityGroupedListBuilder::ALL_GROUP => ['label' => $this->t('- All Blocks -')],
    ];
    $block_visibility_group_labels = $this->getBlockVisibilityLabels($this->group_storage);
    foreach ($block_visibility_group_labels as $id => $label) {
      $route_options[$id] = ['label' => $label];
    }
    foreach ($route_options as $key => &$route_option) {

      $url = Url::fromRoute('block.admin_display_theme', [
        'theme' => $this->theme,
      ],
        [
          'query' => ['block_visibility_group' => $key],
        ]
      );
      $route_option['path'] = $url->toString();
    }

    return $route_options;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildBlocksForm() {
    $form = parent::buildBlocksForm();
    $show_global_in_group = $this->getShowGlobalWithGroup();
    if ($block_visibility_group = $this->getBlockVisibilityGroup(TRUE)) {
      foreach ($form as $row_key => &$row_info) {
        if (isset($row_info['title']['#url'])) {
          /** @var \Drupal\Core\Url $url */
          $url = $row_info['title']['#url'];
          $query = $url->getOption('query');
          $url = Url::fromRoute('block_visibility_groups.admin_library',
            [
              'theme' => $this->getThemeName(),
              'block_visibility_group' => $block_visibility_group,
            ],
            [
              'query' => $query,
            ]);
$row_info['title']['#url'] = $url;
// $query['block_visibility_group'] = $this->getBlockVisibilityGroup();
// $url->setOption('query', $query);.
        }
        if (isset($row_info['operations']['#links']) && $row_info['operations']['#links']) {
          foreach ($row_info['operations']['#links'] as $op => &$op_info) {
            $url = $op_info['url'];
            $query = $url->getOption('query');
            $query['block_visibility_group'] = $block_visibility_group;
            $url->setOption('query', $query);
          }

        }

      }
    }

    // If viewing all blocks, add a column indicating the visibility group.
    if ($this->getBlockVisibilityGroup() == static::ALL_GROUP
      || $block_visibility_group && $show_global_in_group
    ) {
      $this->addGroupColumn($form);
    }

    return $form;

  }

  /**
   * Get the Block Visibility Group for this page request.
   *
   * @param bool|false $groups_only
   *   Should this function return only group key
   *   or also a constant value if no group.
   *
   * @return string|null
   */
  protected function getBlockVisibilityGroup($groups_only = FALSE) {
    $group = $this->request->query->get('block_visibility_group');
    if ($groups_only && in_array($group, [
      $this::ALL_GROUP,
      $this::UNSET_GROUP,
    ])
    ) {
      return NULL;
    }
    return $group;
  }

  /**
   * {@inheritdoc}
   *
   * Unset blocks that should not be shown with current group.
   */
  protected function getEntityIds() {
    $entity_ids = parent::getEntityIds();
    $current_block_visibility_group = $this->getCurrentBlockVisibilityGroup();
    $show_global_in_group = $this->getShowGlobalWithGroup();
    if (!empty($current_block_visibility_group)
      && $current_block_visibility_group != $this::ALL_GROUP
    ) {
      $entities = $this->storage->loadMultipleOverrideFree($entity_ids);
      /** @var Block $block */
      foreach ($entities as $block) {
        $config_block_visibility_group = $this->getGroupForBlock($block);

        if (static::UNSET_GROUP == $current_block_visibility_group) {
          if (!empty($config_block_visibility_group)) {
            unset($entity_ids[$block->id()]);
          }
        }
        elseif ($config_block_visibility_group != $current_block_visibility_group
          && !(empty($config_block_visibility_group) && $show_global_in_group)
        ) {
          unset($entity_ids[$block->id()]);
        }
      }
    }
    return $entity_ids;
  }

  /**
   * Determine if any groups exist.
   *
   * @return bool
   */
  protected function groupsExist() {
    return !empty($this->group_storage->loadMultiple());
  }

  /**
   * Add Column to show Visibility Group.
   *
   * @param $form
   */
  protected function addGroupColumn(&$form) {
    $entity_ids = [];
    foreach (array_keys($form) as $row_key) {
      if (strpos($row_key, 'region-') !== 0) {
        $entity_ids[] = $row_key;
      }
    }
    $entities = $this->storage->loadMultipleOverrideFree($entity_ids);
    if (!empty($entities)) {
      $labels = $this->getBlockVisibilityLabels($this->group_storage);
      /** @var Block $block */
      foreach ($entities as $block) {
        if (!empty($form[$block->id()])) {
          // Get visibility group label.
          $visibility_group = $this->t('Global');
          $conditions = $block->getVisibilityConditions();
          if ($conditions->has('condition_group')) {
            $condition_config = $conditions->get('condition_group')
              ->getConfiguration();
            if (isset($labels[$condition_config['block_visibility_group']])) {
              $visibility_group = '<strong>' . $labels[$condition_config['block_visibility_group']] . '</strong>';
            }

          }
          $row = &$form[$block->id()];
          // Insert visibility group at correct position.
          foreach (Element::children($row) as $i => $child) {
            $row[$child]['#weight'] = $i;
          }
          $row['block_visibility_group'] = [
            '#markup' => $visibility_group,
            '#weight' => 1.5,
          ];
          $row['#sorted'] = FALSE;
        }
      }
      // Adjust header.
      array_splice($form['#header'], 2, 0, array($this->t('Visibility group')));
      // Increase colspan.
      foreach (Element::children($form) as $child) {
        foreach (Element::children($form[$child]) as $gchild) {
          if (isset($form[$child][$gchild]['#wrapper_attributes']['colspan'])) {
            $form[$child][$gchild]['#wrapper_attributes']['colspan'] =
              $form[$child][$gchild]['#wrapper_attributes']['colspan'] + 1;
          }
        }
      }
    }
  }

  /**
   * Determine if global(unset) blocks should be shown when viewing a group.
   *
   * @return mixed
   */
  protected function getShowGlobalWithGroup() {
    return $this->state->get('block_visibility_group_show_global', 1);
  }

  /**
   * @param array $form
   * @param $group
   *
   * @return array
   */
  protected function createHelp(BlockVisibilityGroup $group) {
    $help = '<strong>' . $this->t('Currently viewing') . ': <em>' . $group->label() . '</em></strong>';
    if ($group->getLogic() == 'and') {
      $help .= '<p>' . $this->t('All conditions must pass.') . '</p>';
    }
    else {
      $help .= '<p>' . $this->t('Only one condition must pass.') . '</p>';
    }

    if ($group->isAllowOtherConditions()) {
      $help .= '<p>' . $this->t('Blocks in this group may have other conditions.') . '</p>';
    }
    else {
      $help .= '<p>' . $this->t('Blocks in this group may <strong>not</strong> have other conditions.') . '</p>';
    }

    $help_group = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Group Settings'),
      'text' => [
        '#type' => 'markup',
        '#markup' => $help,
      ],
      'edit' => [
        '#type' => 'link',
        '#title' => t('Edit Group Settings'),
        '#url' => $group->urlInfo('edit-form'),
      ],
    ];
    return $help_group;
  }

}
