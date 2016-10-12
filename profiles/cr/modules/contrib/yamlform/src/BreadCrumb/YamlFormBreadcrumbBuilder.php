<?php

namespace Drupal\yamlform\BreadCrumb;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a form breadcrumb builder.
 */
class YamlFormBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The current route's entity or plugin type.
   *
   * @var string
   */
  protected $type;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();
    // All routes must begin or contain 'yamlform.
    if (strpos($route_name, 'yamlform') === FALSE) {
      return FALSE;
    }

    $args = explode('.', $route_name);

    // Skip all config_translation routes except the overview
    // and allow Drupal to use the path as the breadcrumb.
    if (strpos($route_name, 'config_translation') !== FALSE && $route_name != 'entity.yamlform.config_translation_overview') {
      return FALSE;
    }

    if ($args[0] == 'entity' && ($args[2] == 'yamlform' ||  $args[2] == 'yamlform_submission')) {
      $this->type = 'yamlform_source_entity';
    }
    elseif (strpos($route_name, 'entity.yamlform.handler.') === 0) {
      $this->type = 'yamlform_handler';
    }
    elseif (strpos($route_name, 'entity.yamlform_ui.element') === 0) {
      $this->type = 'yamlform_element';
    }
    elseif (strpos($route_match->getRouteName(), 'yamlform.user.submissions') !== FALSE) {
      $this->type = 'yamlform_user_submissions';
    }
    elseif ($route_match->getParameter('yamlform_submission') instanceof YamlFormSubmissionInterface && strpos($route_name, 'yamlform.user.submission') !== FALSE) {
      $this->type = 'yamlform_user_submission';
    }
    elseif ($route_match->getParameter('yamlform_submission') instanceof YamlFormSubmissionInterface && $route_match->getParameter('yamlform_submission')->access('admin')) {
      $this->type = 'yamlform_submission';
    }
    elseif (($route_match->getParameter('yamlform') instanceof YamlFormInterface  && $route_match->getParameter('yamlform')->access('admin'))) {
      $this->type = 'yamlform';
    }
    else {
      $this->type = NULL;
    }

    return ($this->type) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();

    if ($this->type == 'yamlform_source_entity') {
      /** @var \Drupal\yamlform\YamlFormRequestInterface $request_handler */
      $request_handler = \Drupal::service('yamlform.request');
      $source_entity = $request_handler->getCurrentSourceEntity(['yamlform', 'yamlform_submission']);
      $entity_type = $source_entity->getEntityTypeId();
      $entity_id = $source_entity->id();

      $breadcrumb = new Breadcrumb();
      $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
      $breadcrumb->addLink($source_entity->toLink());
      if ($yamlform_submission = $route_match->getParameter('yamlform_submission')) {

        if (strpos($route_match->getRouteName(), 'yamlform.user.submission') !== FALSE) {
          $breadcrumb->addLink(Link::createFromRoute($this->t('Submissions'), "entity.$entity_type.yamlform.user.submissions", [$entity_type => $entity_id]));
        }
        elseif ($source_entity->access('yamlform_submission_view') || $yamlform_submission->access('view_any')) {
          $breadcrumb->addLink(Link::createFromRoute($this->t('Results'), "entity.$entity_type.yamlform.results_submissions", [$entity_type => $entity_id]));
        }
        elseif ($yamlform_submission->access('view_own')) {
          $breadcrumb->addLink(Link::createFromRoute($this->t('Results'), "entity.$entity_type.yamlform.user.submissions", [$entity_type => $entity_id]));
        }
      }
    }
    else {
      $breadcrumb = new Breadcrumb();
      $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
      $breadcrumb->addLink(Link::createFromRoute($this->t('Administration'), 'system.admin'));
      $breadcrumb->addLink(Link::createFromRoute($this->t('Structure'), 'system.admin_structure'));
      $breadcrumb->addLink(Link::createFromRoute($this->t('Forms'), 'entity.yamlform.collection'));
      switch ($this->type) {
        case 'yamlform_element':
          /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
          $yamlform = $route_match->getParameter('yamlform');
          $breadcrumb->addLink(Link::createFromRoute($yamlform->label(), 'entity.yamlform.canonical', ['yamlform' => $yamlform->id()]));
          $breadcrumb->addLink(Link::createFromRoute('Elements', 'entity.yamlform.edit_form', ['yamlform' => $yamlform->id()]));
          break;

        case 'yamlform_handler':
          if ($route_name != 'yamlform.handler_plugins') {
            /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
            $yamlform = $route_match->getParameter('yamlform');
            $breadcrumb->addLink(Link::createFromRoute($yamlform->label(), 'entity.yamlform.canonical', ['yamlform' => $yamlform->id()]));
            $breadcrumb->addLink(Link::createFromRoute('Emails / Handlers', 'entity.yamlform.handlers_form', ['yamlform' => $yamlform->id()]));
          }
          break;

        case 'yamlform_options':
          if ($route_name != 'entity.yamlform_options.collection') {
            $breadcrumb->addLink(Link::createFromRoute($this->t('Options'), 'entity.yamlform_options.collection'));
          }
          break;

        case 'yamlform_submission':
          /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
          $yamlform_submission = $route_match->getParameter('yamlform_submission');
          $yamlform = $yamlform_submission->getYamlForm();

          $breadcrumb->addLink(Link::createFromRoute($yamlform->label(), 'entity.yamlform.canonical', ['yamlform' => $yamlform->id()]));
          $breadcrumb->addLink(Link::createFromRoute($this->t('Results'), 'entity.yamlform.results_submissions', ['yamlform' => $yamlform->id()]));
          break;

        case 'yamlform_user_submissions':
          /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
          $yamlform = $route_match->getParameter('yamlform');

          $breadcrumb = new Breadcrumb();
          $breadcrumb->addLink(Link::createFromRoute($yamlform->label(), 'entity.yamlform.canonical', ['yamlform' => $yamlform->id()]));
          break;

        case 'yamlform_user_submission':
          /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
          $yamlform_submission = $route_match->getParameter('yamlform_submission');
          $yamlform = $yamlform_submission->getYamlForm();

          $breadcrumb = new Breadcrumb();
          $breadcrumb->addLink(Link::createFromRoute($yamlform->label(), 'entity.yamlform.canonical', ['yamlform' => $yamlform->id()]));
          $breadcrumb->addLink(Link::createFromRoute($this->t('Submissions'), 'entity.yamlform.user.submissions', ['yamlform' => $yamlform->id()]));
          break;

      }
    }

    // This breadcrumb builder is based on a route parameter, and hence it
    // depends on the 'route' cache context.
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
