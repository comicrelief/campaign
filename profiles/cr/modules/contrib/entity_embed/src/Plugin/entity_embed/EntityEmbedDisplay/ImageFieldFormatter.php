<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\ImageFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity Embed Display reusing image field formatters.
 *
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
 *
 * @EntityEmbedDisplay(
 *   id = "image",
 *   label = @Translation("Image"),
 *   entity_types = {"file"},
 *   deriver = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "image",
 *   provider = "image"
 * )
 */
class ImageFieldFormatter extends FileFieldFormatter {

  /**
    * The image factory.
    *
    * @var \Drupal\Core\Image\ImageFactory
    */
    protected $imageFactory;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Field\FormatterPluginManager $formatter_plugin_manager
   *   The field formatter plugin manager.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, FormatterPluginManager $formatter_plugin_manager, TypedDataManager $typed_data_manager, ImageFactory $image_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $formatter_plugin_manager, $typed_data_manager);
    $this->imageFactory = $image_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('plugin.manager.field.formatter'),
      $container->get('typed_data_manager'),
      $container->get('image.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue() {
    $value = parent::getFieldValue();
    // File field support descriptions, but images do not.
    unset($value['description']);
    $value += array_intersect_key($this->getAttributeValues(), array('alt' => '', 'title' => ''));
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    if (!parent::access($account)) {
      return FALSE;
    }

    if ($entity = $this->getEntityFromContext()) {
      return $this->imageFactory->get($entity->getFileUri())->isValid();
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // File field support descriptions, but images do not.
    unset($form['description']);

    // Ensure that the 'Link image to: Content' setting is not available.
    if ($this->getDerivativeId() == 'image') {
      unset($form['image_link']['#options']['content']);
    }

    $entity_element = $form_state->get('entity_element');
    // The alt attribute is *required*, but we allow users to opt-in to empty
    // alt attributes for the very rare edge cases where that is valid by
    // specifying two double quotes as the alternative text in the dialog.
    // However, that *is* stored as an empty alt attribute, so if we're editing
    // an existing image (which means the src attribute is set) and its alt
    // attribute is empty, then we show that as two double quotes in the dialog.
    // @see https://www.drupal.org/node/2307647
    // Alt attribute behavior is taken from the Core image dialog to ensure a
    // consistent UX across various forms.
    // @see Drupal\editor\Form\EditorImageDialog::buildForm()
    $alt = $this->getAttributeValue('alt', '');
    if ($alt === '') {
      // Do not change empty alt text to two double quotes if the previously
      // used Entity Embed Display plugin was not 'image:image'. That means that
      // some other plugin was used so if this image formatter is selected at a
      // later stage, then this should be treated as a new edit. We show two
      // double quotes in place of empty alt text only if that was filled
      // intentionally by the user.
      if (!empty($entity_element) && $entity_element['data-entity-embed-display'] == 'image:image') {
        $alt = '""';
      }
    }

    // Add support for editing the alternate and title text attributes.
    $form['alt'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Alternate text'),
      '#default_value' => $alt,
      '#description' => $this->t('This text will be used by screen readers, search engines, or when the image cannot be loaded.'),
      '#parents' => array('attributes', 'alt'),
      '#required' => TRUE,
      '#required_error' => $this->t('Alternative text is required.<br />(Only in rare cases should this be left empty. To create empty alternative text, enter <code>""</code> — two double quotes without any content).'),
      '#maxlength' => 512,
    );
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->getAttributeValue('title', ''),
      '#description' => t('The title is used as a tool tip when the user hovers the mouse over the image.'),
      '#parents' => array('attributes', 'title'),
      '#maxlength' => 1024,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // When the alt attribute is set to two double quotes, transform it to the
    // empty string: two double quotes signify "empty alt attribute". See above.
    if (trim($form_state->getValue(array('attributes', 'alt'))) === '""') {
      $form_state->setValue(array('attributes', 'alt'), '');
    }
  }

}
