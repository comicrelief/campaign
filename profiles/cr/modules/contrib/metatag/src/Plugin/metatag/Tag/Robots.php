<?php
/**
 * @file
 * Contains \Drupal\metatag\Plugin\metatag\Tag\Robots.
 */

namespace Drupal\metatag\Plugin\metatag\Tag;

/**
 * The basic "Robots" meta tag.
 *
 * @MetatagTag(
 *   id = "robots",
 *   label = @Translation("Robots"),
 *   description = @Translation("Provides search engines with specific directions for what to do when this page is indexed."),
 *   name = "robots",
 *   group = "advanced",
 *   weight = 1,
 *   image = FALSE,
 *   multiple = FALSE
 * )
 */
class Robots extends MetaNameBase {

  /**
   * Sets the value of this tag.
   *
   * @param string|array $value
   *   The value to set to this tag.
   *   It can be an array if it comes from a form submission or from field
   *   defaults, in which case
   *   we transform it to a comma-separated string.
   */
  public function setValue($value) {
    if (is_array($value)) {
      $value = array_filter($value);
      $value = implode(', ', array_keys($value));
    }
    $this->value = $value;
  }



  /**
   * {@inheritdoc}
   */
  public function form(array $element = array()) {
    // Prepare the default value as it is stored as a string.
    $default_value = array();
    if (!empty($this->value)) {
      $default_value = explode(', ', $this->value);
    }

    $form = array(
      '#type' => 'checkboxes',
      '#title' => $this->label(),
      '#description' => $this->description(),
      '#options' => array(
        'index' => t('Allow search engines to index this page (assumed).'),
        'follow' => t('Allow search engines to follow links on this page (assumed).'),
        'noindex' => t('Prevents search engines from indexing this page.'),
        'nofollow' => t('Prevents search engines from following links on this page.'),
        'noarchive' => t('Prevents cached copies of this page from appearing in search results.'),
        'nosnippet' => t('Prevents descriptions from appearing in search results, and prevents page caching.'),
        'noodp' => t('Blocks the <a href=":opendirectory">Open Directory Project</a> description from appearing in search results.', array(':opendirectory' => 'http://www.dmoz.org/')),
        'noydir' => t('Prevents Yahoo! from listing this page in the <a href=":ydir">Yahoo! Directory</a>.', array(':ydir' => 'http://dir.yahoo.com/')),
        'noimageindex' => t('Prevent search engines from indexing images on this page.'),
        'notranslate' => t('Prevent search engines from offering to translate this page in search results.'),
      ),
      '#default_value' => $default_value,
      '#required' => isset($element['#required']) ? $element['#required'] : FALSE,
      '#element_validate' => array(array(get_class($this), 'validateTag')),
    );

    return $form;
  }

}
