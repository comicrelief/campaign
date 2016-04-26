<?php

/**
 * @file
 * Settings administration UI.
 */


//----------------------------------------------------------------------------
// Form API callbacks.

/**
 * Form definition; general settings.
 */
function cdn_admin_general_settings_form($form, &$form_state) {
  $form = array();
  _cdn_settings_form_prepare($form, $form_state);


  return system_settings_form($form);
}

/**
 * Form definition; details.
 */
function cdn_admin_details_form($form, &$form_state) {
  $form = array();
  _cdn_settings_form_prepare($form, $form_state);

  $form['settings'][CDN_BASIC_FARFUTURE_VARIABLE] = array(
    '#type' => 'checkbox',
    '#title' => t('Far Future expiration'),
    '#description' => _cdn_help('admin-details-mode-pull-far-future') .
      t('Mark all files served from the CDN to expire in the far future —
      improves client-side cacheability.<br /><strong>Note:</strong> this
      requires the !preprocess-css-link performance setting to be enabled (or
      your site will break).<br><strong>Note:</strong> only use Far Future
      expiration when using a CDN or a reverse proxy.',
      array(
        '!preprocess-css-link' => l(
          '"Aggregate and compress CSS files"',
          'admin/config/development/performance',
          array('fragment' => 'edit-bandwidth-optimization')
        ),
      )
    ),
    '#default_value' => variable_get(CDN_BASIC_FARFUTURE_VARIABLE, CDN_BASIC_FARFUTURE_DEFAULT),
    '#states' => array(
      'visible' => array(
        ':input[name="' . CDN_MODE_VARIABLE . '"]' => array('value' => CDN_MODE_BASIC),
      )
    ),
  );

  $format_variables = array(
    '@format-directory'                => '<' . t('directory') . '>',
    '@format-extensions'               => '<' . t('extensions') . '>',
    '@format-unique-identifier-method' => '<' . t('unique identifier method') . '>',
  );

  $methods = array();
  $ufi_info = module_invoke_all('cdn_unique_file_identifier_info');
  foreach ($ufi_info as $ufi_method => $ufi) {
    $methods[] = $ufi['label']
                 . ' (<code>' . $ufi_method . '</code>): '
                 . $ufi['description'];
  }
  $format_variables['!ufi-methods'] = theme('item_list', array('items' => $methods));

  $form['settings'][CDN_BASIC_FARFUTURE_UNIQUE_IDENTIFIER_MAPPING_VARIABLE] = array(
    '#type'          => 'textarea',
    '#title'         => t('Unique file identifier generation'),
    '#description'   => _cdn_help('admin-details-mode-pull-ufi') . t('Define how unique file identifiers (UFIs) are generated.'),
    '#size'          => 35,
    '#default_value' => variable_get(CDN_BASIC_FARFUTURE_UNIQUE_IDENTIFIER_MAPPING_VARIABLE, CDN_BASIC_FARFUTURE_UNIQUE_IDENTIFIER_MAPPING_DEFAULT),
    '#states' => array(
      'visible' => array(
        ':input[name="' . CDN_MODE_VARIABLE . '"]' => array('value' => CDN_MODE_BASIC),
        ':input[name="' . CDN_BASIC_FARFUTURE_VARIABLE . '"]' => array('checked' => TRUE),
      )
    ),
  );

  $form['settings']['ufis'] = array(
    '#type'        => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed'   => TRUE,
    '#title'       => t('Available UFI methods'),
    '#input'       => TRUE,
    '#id'          => 'ufi-fs-id',
    '#prefix'      => '<div id="ufi-fs-id-wrapper">',
    '#suffix'      => '</div>',
    '#states'      => array(
      'visible' => array(
        ':input[name="' . CDN_MODE_VARIABLE . '"]' => array('value' => CDN_MODE_BASIC),
        ':input[name="' . CDN_BASIC_FARFUTURE_VARIABLE . '"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['settings']['ufis']['content'] = array(
    '#markup' => t('Available UFI methods: !ufi-methods', $format_variables) .
                '<p>' . t('Note that if no UFI method is specified for a file
                          (because no rule matches), the CDN module will fall
                          back to the mtime method.') . '</p>',
    '#prefix' => '<div>',
    '#suffix' => '</div>',
  );

  return system_settings_form($form);
}

/**
 * Form definition; other settings.
 */
function cdn_admin_other_settings_form($form, &$form_state) {
  $form = array();
  _cdn_settings_form_prepare($form, $form_state);



  $form['cdn_seo'] = array(
    '#type' => 'fieldset',
    '#title' => t('SEO: duplicate content prevention'),
    '#description' => t(
      "By default most CDNs will cache full HTML pages if accessed. This means
      that a copy of your site may appear in search engines. This is confusing, unprofessional and potentially bad for SEO.
      <br />
      <em>Duplicate content prevention</em> is enabled by default and ensures the CDN will redirect users to your actual ('canonical') site."
    )
  );
  $form['cdn_seo'][CDN_SEO_REDIRECT_VARIABLE] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable duplicate content prevention'),
    '#default_value' => variable_get(CDN_SEO_REDIRECT_VARIABLE, CDN_SEO_REDIRECT_DEFAULT),
  );
  $form['cdn_seo'][CDN_SEO_USER_AGENTS_VARIABLE] = array(
    '#type' => 'textarea',
    '#title' => t('CDN user agents'),
    '#description' => t(
      'A case-insensitive list of CDN user agents. These will be substring-matched against the detected user agent of a request. One per line.'
    ),
    '#default_value' => variable_get(CDN_SEO_USER_AGENTS_VARIABLE, CDN_SEO_USER_AGENTS_DEFAULT),
    '#states' => array(
      'visible' => array(
        ':input[name="' . CDN_SEO_REDIRECT_VARIABLE . '"]' => array('checked' => TRUE),
      )
    ),
  );

  return system_settings_form($form);
}

/**
 * Default validate callback for the details form.
 */
function cdn_admin_details_form_validate($form, &$form_state) {

  // When in Origin Pull mode, check the CDN mapping for CDNs/reverse proxies.
  if ($form_state['values'][CDN_MODE_VARIABLE] == CDN_MODE_BASIC) {
    $domains = cdn_get_domains();
    $token = md5(rand());
    variable_set('cdn_reverse_proxy_test', $token);
    $yays = array();
    $nays = array();
    foreach ($domains as $domain) {
      $url = 'http://' . $domain . base_path() . 'cdn/farfuture/reverse-proxy-test/' . $token;
      $r1 = drupal_http_request($url);
      $r2 = drupal_http_request($url);
      unset($r1->headers);
      unset($r2->headers);
      $args = array('%domain' => $domain);
      if ($r1 == $r2) {
        $yays[] = t('%domain is a CDN or a reverse proxy.', $args);
      }
      else {
        if ($r1->code == 404) {
          $nays[] = t('%domain is a static file server.', $args);
        }
        else {
          $nays[] = t('%domain uses the same web server as this Drupal site.', $args);
        }
      }
    }
    variable_set('cdn_reverse_proxy_test', FALSE);
    if (!empty($yays)) {
      drupal_set_message(t('Perfect domains: !yay-list',
        array(
          '!yay-list' => theme('item_list', array('items' => $yays)),
        )
      ));
    }
    if (!empty($nays)) {
      drupal_set_message(t('Potentially problematic domains: !nay-list',
        array(
          '!nay-list' => theme('item_list', array('items' => $nays)),
        )
      ), 'warning');
    }
  }
}


//----------------------------------------------------------------------------
// Private functions.

/**
 * Helper function to check if the requirements of the CDN module have been
 * met. If any requirement errors exist, they are aggregated into a single
 * error message and are subsequently displayed.
 *
 * @return
 *   The number of requirement errors.
 */
function _cdn_admin_check_requirements() {
  // Check run-time requirements of the CDN integration module.
  module_load_install('cdn');
  $requirements = cdn_requirements('runtime');
  $problematic_statuses = array(REQUIREMENT_WARNING, REQUIREMENT_ERROR);

  // Filter out the requirement errors and display these, with links back to
  // the admin/reports/status page.
  $errors = array();
  foreach ($requirements as $requirement => $details) {
    if (in_array($details['severity'], $problematic_statuses)) {
      $errors[] = $details['description'];
    }
  }
  if (!empty($errors)) {
    drupal_set_message(
      t(
          'The CDN module has detected the following <em>potential</em>
          problems in its configuration:<br />
          !error-list
          You can also see them on the !status-report.',
          array(
            '!status-report' => l(t('status report'), 'admin/reports/status'),
            '!error-list' => theme('item_list', array('items' => $errors)),
          )
      ),
      'error'
    );
  }

  return count($errors);
}

function _cdn_settings_form_prepare(&$form, $form_state) {
  // Do some checks, but prevent them from showing up twice.
  if (empty($form_state['input'])) {
    _cdn_admin_check_requirements();

    // Advanced help.
    _cdn_check_advanced_help();
    $form['#attributes']['class'] = 'cdn-settings';
    $form['#attached']['css'][] = drupal_get_path('module', 'cdn') . '/cdn.admin.css';
  }
}

function _cdn_check_advanced_help() {
  if (!module_exists('advanced_help')) {
    $sql = "SELECT filename
            FROM {system}
            WHERE type = 'module'
            AND name = 'advanced_help'";
    $filename = db_query($sql)->fetchField();
    if ($filename && file_exists($filename)) {
      drupal_set_message(t(
        'If you enable the <a href="@modules-url">Advanced Help</a> module,
        the CDN module will provide more and better help.',
        array('@modules-url' => url('admin/modules'))
      ));
    }
    else {
      drupal_set_message(t(
        'If you install the <a href="@adv-help-url">Advanced Help</a> module,
        the CDN module will provide more and better help.',
        array('@adv-help-url' => url('http://drupal.org/project/advanced_help'))
      ));
    }
  }
}

function _cdn_help($topic) {
  if (!module_exists('advanced_help')) {
    return '';
  }
  else {
    return theme(
      'advanced_help_topic',
      array('module' => 'cdn', 'topic' => $topic)
    );
  }
}
