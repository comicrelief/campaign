<?php

/**
 * @file
 * Contains Drupal\yamlform\YamlFormEntityHandlersForm.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for YAML form handlers.
 */
class YamlFormEntityHandlersForm extends EntityForm {

  /**
   * The YAML form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $entity;

  /**
   * The YAML form handler manager service.
   *
   * @var \Drupal\yamlform\YamlFormHandlerManager
   */
  protected $yamlFormHandlerManager;

  /**
   * Constructs an YamlFormEntityHandlersForm object.
   *
   * @param \Drupal\yamlform\YamlFormHandlerManager $yamlform_handler_manager
   *   The YAML form handler manager service.
   */
  public function __construct(YamlFormHandlerManager $yamlform_handler_manager) {
    $this->yamlFormHandlerManager = $yamlform_handler_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $user_input = $form_state->getUserInput();

    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'yamlform/yamlform';

    // Build the list of existing YAML form handlers for this YAML form.
    $form['handlers'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Title / Description'),
        $this->t('ID'),
        $this->t('Summary'),
        $this->t('Status'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'yamlform-handler-order-weight',
        ],
      ],
      '#attributes' => [
        'id' => 'yamlform-handlers',
      ],
      '#empty' => $this->t('There are currently no handlers in this YAML form. Add one by selecting an option below.'),
      // Render handlers below parent elements.
      '#weight' => 5,
    ];
    foreach ($this->entity->getHandlers() as $handler) {
      $key = $handler->getHandlerId();
      $form['handlers'][$key]['#attributes']['class'][] = 'draggable';
      $form['handlers'][$key]['#weight'] = isset($user_input['handlers']) ? $user_input['handlers'][$key]['weight'] : NULL;
      $form['handlers'][$key]['handler'] = [
        '#tree' => FALSE,
        'data' => [
          'label' => [
            '#markup' => '<b>' . $handler->label() . '</b>: ' . $handler->description(),
          ],
        ],
      ];

      $form['handlers'][$key]['id'] = [
        'data' => ['#markup' => $handler->getHandlerId()],
      ];
      $form['handlers'][$key]['summary'] = $handler->getSummary();
      $form['handlers'][$key]['status'] = [
        'data' => ['#markup' => ($handler->isEnabled()) ? $this->t('Enabled') : $this->t('Disabled')],
      ];
      $form['handlers'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $handler->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $handler->getWeight(),
        '#attributes' => [
          'class' => ['yamlform-handler-order-weight'],
        ],
      ];

      $links = [];
      $links['edit'] = [
        'title' => $this->t('Edit'),
        'url' => Url::fromRoute('yamlform.handler_edit_form', [
            'yamlform' => $this->entity->id(),
            'yamlform_handler' => $key,
          ]),
      ];
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('yamlform.handler_delete_form', [
          'yamlform' => $this->entity->id(),
          'yamlform_handler' => $key,
        ]),
      ];
      $form['handlers'][$key]['operations'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
    }

    // Build the new image handler addition form and add it to the handler list.
    $new_handler_options = [];
    $handlers = $this->yamlFormHandlerManager->getDefinitions();
    uasort($handlers, function ($a, $b) {
      return strcasecmp($a['id'], $b['id']);
    });
    foreach ($handlers as $handler => $definition) {
      $cardinality = $definition['cardinality'];
      if ($cardinality === YamlFormHandlerInterface::CARDINALITY_UNLIMITED || $cardinality > $this->entity->getHandlers($handler)->count()) {
        $new_handler_options[$handler] = $definition['label'];
      }
    }
    $form['handlers']['new'] = [
      '#tree' => FALSE,
      '#weight' => isset($user_input['weight']) ? $user_input['weight'] : NULL,
      '#attributes' => ['class' => ['draggable']],
    ];
    $form['handlers']['new']['handler'] = [
      'data' => [
        'new' => [
          '#type' => 'select',
          '#title' => $this->t('Handler'),
          '#title_display' => 'invisible',
          '#options' => $new_handler_options,
          '#empty_option' => $this->t('Select a new handler'),
        ],
        [
          'add' => [
            '#type' => 'submit',
            '#value' => $this->t('Add'),
            '#validate' => ['::handlerValidate'],
            '#submit' => ['::submitForm', '::handlerSave'],
          ],
        ],
      ],
      '#wrapper_attributes' => [
        'colspan' => 4,
      ],
      '#prefix' => '<div class="yamlform-new">',
      '#suffix' => '</div>',
    ];

    $form['handlers']['new']['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight for new handler'),
      '#title_display' => 'invisible',
      '#default_value' => count($this->entity->getHandlers()) + 1,
      '#attributes' => ['class' => ['yamlform-handler-order-weight']],
    ];
    $form['handlers']['new']['operations'] = [
      'data' => [],
    ];

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $form = parent::actionsElement($form, $form_state);
    unset($form['delete']);
    return $form;
  }

  /**
   * Validate handler for YAML form handler.
   */
  public function handlerValidate($form, FormStateInterface $form_state) {
    if (!$form_state->getValue('new')) {
      $form_state->setErrorByName('new', $this->t('Select a handler to add.'));
    }
  }

  /**
   * Submit handler for YAML form handler.
   */
  public function handlerSave($form, FormStateInterface $form_state) {
    $this->save($form, $form_state);
    $form_state->setRedirect(
      'yamlform.handler_add_form',
      [
        'yamlform' => $this->entity->id(),
        'yamlform_handler' => $form_state->getValue('new'),
      ],
      ['query' => ['weight' => $form_state->getValue('weight')]]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Update YAML form handler weights.
    if (!$form_state->isValueEmpty('handlers')) {
      $this->updateHandlerWeights($form_state->getValue('handlers'));
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();
    $yamlform->save();

    $this->logger('yamlform')->notice('YAML form @label handlers saved.', ['@label' => $yamlform->label()]);
    drupal_set_message($this->t('YAML form %label handlers saved.', ['%label' => $yamlform->label()]));
  }

  /**
   * Updates YAML form handler weights.
   *
   * @param array $handlers
   *   Associative array with handlers having handler ids as keys and array
   *   with handler data as values.
   */
  protected function updateHandlerWeights(array $handlers) {
    foreach ($handlers as $handler_id => $handler_data) {
      if ($this->entity->getHandlers()->has($handler_id)) {
        $this->entity->getHandler($handler_id)->setWeight($handler_data['weight']);
      }
    }
  }

}
