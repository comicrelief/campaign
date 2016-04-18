<?php

/**
 * @file
 * Contains \Drupal\diff\Form\RevisionOverviewForm
 *
 * This form displays all the revisions of a node and allows the selection
 * of two of them for comparison.
 */

namespace Drupal\diff\Form;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Render\RendererInterface;

/**
 * Provides a form for revision overview page.
 */
class RevisionOverviewForm extends FormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The date service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $date;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface $language_manager
   */
  protected $languageManager;

  /**
   * Wrapper object for writing/reading simple configuration from diff.settings.yml
   */
  protected $config;


  /**
   * Constructs a RevisionOverviewForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Datetime\DateFormatter $date
   *   The date service.
   * @param  \Drupal\Core\Render\RendererInterface
   *   The renderer service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EntityManagerInterface $entityManager, AccountInterface $currentUser, DateFormatter $date, RendererInterface $renderer, LanguageManagerInterface $language_manager) {
    $this->entityManager = $entityManager;
    $this->currentUser = $currentUser;
    $this->date = $date;
    $this->renderer = $renderer;
    $this->languageManager = $language_manager;
    $this->config = $this->config('diff.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('current_user'),
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'revision_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $account = $this->currentUser;
    $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $langname = $this->languageManager->getLanguageName($langcode);
    $languages = $node->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $node_storage = $this->entityManager->getStorage('node');
    $type = $node->getType();
    $vids = array_reverse($node_storage->revisionIds($node));
    $revision_count = count($vids);

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $node->label()]) : $this->t('Revisions for %title', ['%title' => $node->label()]);
    $build['nid'] = array(
      '#type' => 'hidden',
      '#value' => $node->id(),
    );

    $table_header = array(
      'revision' => $this->t('Revision'),
      'operations' => $this->t('Operations'),
    );

    // Allow comparisons only if there are 2 or more revisions.
    if ($revision_count > 1) {
      $table_header += array(
        'select_column_one' => '',
        'select_column_two' => '',
      );
    }

    $rev_revert_perm = $account->hasPermission("revert $type revisions") ||
      $account->hasPermission('revert all revisions') ||
      $account->hasPermission('administer nodes');
    $rev_delete_perm = $account->hasPermission("delete $type revisions") ||
      $account->hasPermission('delete all revisions') ||
      $account->hasPermission('administer nodes');
    $revert_permission = $rev_revert_perm && $node->access('update');
    $delete_permission = $rev_delete_perm && $node->access('delete');

    // Contains the table listing the revisions.
    $build['node_revisions_table'] = array(
      '#type' => 'table',
      '#header' => $table_header,
      '#attributes' => array('class' => array('diff-revisions')),
    );

    $build['node_revisions_table']['#attached']['library'][] = 'diff/diff.general';
    $build['node_revisions_table']['#attached']['drupalSettings']['diffRevisionRadios'] = $this->config->get('general_settings.radio_behavior');

    $latest_revision = TRUE;

    // Add rows to the table.
    foreach ($vids as $vid) {
      if ($revision = $node_storage->loadRevision($vid)) {
        if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
          $username = array(
            '#theme' => 'username',
            '#account' => $revision->getRevisionAuthor(),
          );
          $revision_date = $this->date->format($revision->getRevisionCreationTime(), 'short');
          // Use revision link to link to revisions that are not active.
          if ($vid != $node->getRevisionId()) {
            $link = $this->l($revision_date, new Url('entity.node.revision', ['node' => $node->id(), 'node_revision' => $vid]));
          }
          else {
            $link = $node->link($revision_date);
          }

          // Default revision.
          if ($latest_revision) {
            $row = array(
              'revision' => array(
                '#type' => 'inline_template',
                '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
                '#context' => [
                  'date' => $link,
                  'username' => $this->renderer->renderPlain($username),
                  'message' => ['#markup' => $revision->revision_log->value, '#allowed_tags' => Xss::getHtmlTagList()],
                ],
              ),
            );
            // Allow comparisons only if there are 2 or more revisions.
            if ($revision_count > 1) {
              $row += array(
                'select_column_one' => array(
                  '#type' => 'radio',
                  '#title_display' => 'invisible',
                  '#name' => 'radios_left',
                  '#return_value' => $vid,
                  '#default_value' => FALSE,
                ),
                'select_column_two' => array(
                  '#type' => 'radio',
                  '#title_display' => 'invisible',
                  '#name' => 'radios_right',
                  '#default_value' => $vid,
                  '#return_value' => $vid,
                ),
              );
            }
            $row['operations'] = array(
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
              '#attributes' => array(
                'class' => array('revision-current'),
              )
            );
            $latest_revision = FALSE;
          }
          else {
            $route_params = array(
              'node' => $node->id(),
              'node_revision' => $vid,
              'langcode' => $langcode,
            );
            $links = array();
            if ($revert_permission) {
              $links['revert'] = [
                'title' => $this->t('Revert'),
                'url' => $has_translations ?
                  Url::fromRoute('node.revision_revert_translation_confirm', ['node' => $node->id(), 'node_revision' => $vid, 'langcode' => $langcode]) :
                  Url::fromRoute('node.revision_revert_confirm', ['node' => $node->id(), 'node_revision' => $vid]),
              ];
            }
            if ($delete_permission) {
              $links['delete'] = array(
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('node.revision_delete_confirm', $route_params)
              );
            }

            // Here we don't have to deal with 'only one revision' case because
            // if there's only one revision it will also be the default one,
            // entering on the first branch of this if else statement.
            $row = array(
              'revision' => array(
                '#type' => 'inline_template',
                '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
                '#context' => [
                  'date' => $link,
                  'username' => $this->renderer->renderPlain($username),
                  'message' => ['#markup' => $revision->revision_log->value, '#allowed_tags' => Xss::getHtmlTagList()],
                ],
              ),
              'select_column_one' => array(
                '#type' => 'radio',
                '#title_display' => 'invisible',
                '#name' => 'radios_left',
                '#return_value' => $vid,
                '#default_value' => isset ($vids[1]) ? $vids[1] : FALSE,
              ),
              'select_column_two' => array(
                '#type' => 'radio',
                '#title_display' => 'invisible',
                '#name' => 'radios_right',
                '#return_value' => $vid,
                '#default_value' => FALSE,
              ),
              'operations' => array(
                '#type' => 'operations',
                '#links' => $links,
              ),
            );
          }
          // Add the row to the table.
          $build['node_revisions_table'][] = $row;
        }
      }
    }

    // Allow comparisons only if there are 2 or more revisions.
    if ($revision_count > 1) {
      $build['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Compare'),
        '#attributes' => array(
          'class' => array(
            'diff-button',
          ),
        ),
      );
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $input = $form_state->getUserInput();
    $vid_left = $input['radios_left'];
    $vid_right = $input['radios_right'];
    if ($vid_left == $vid_right || !$vid_left || !$vid_right) {
      // @todo Radio-boxes selection resets if there are errors.
      $form_state->setErrorByName('node_revisions_table', $this->t('Select different revisions to compare.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $input = $form_state->getUserInput();
    $vid_left = $input['radios_left'];
    $vid_right = $input['radios_right'];
    $nid = $input['nid'];

    // Always place the older revision on the left side of the comparison
    // and the newer revision on the right side (however revisions can be
    // compared both ways if we manually change the order of the parameters).
    if ($vid_left > $vid_right) {
      $aux = $vid_left;
      $vid_left = $vid_right;
      $vid_right = $aux;
    }
    // Builds the redirect Url.
    $redirect_url = Url::fromRoute(
      'diff.revisions_diff',
      array(
        'node' => $nid,
        'left_vid' => $vid_left,
        'right_vid' => $vid_right,
      )
    );
    $form_state->setRedirectUrl($redirect_url);
  }

}
