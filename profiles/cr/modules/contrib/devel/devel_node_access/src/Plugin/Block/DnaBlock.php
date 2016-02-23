<?php
/**
 * @file
 * Contains \Drupal\devel_node_access\Plugin\Block\DnaBlock.
 */

namespace Drupal\devel_node_access\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides the "Devel Node Access" block.
 *
 * @Block(
 *   id = "devel_dna_block",
 *   admin_label = @Translation("Devel Node Access"),
 *   category = @Translation("Devel Node Access")
 * )
 */
class DnaBlock extends BlockBase implements ContainerFactoryPluginInterface {

  private $node_access = array('@node_access' => 'node_access');

  use RedirectDestinationTrait;

  /**
   * The FormBuilder object.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The Current User object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs a new DnaBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $current_user, EntityStorageInterface $user_storage, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->currentUser = $current_user;
    $this->userStorage = $user_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @noinspection PhpParamsInspection */
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('entity.manager')->getStorage('user'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, DNA_ACCESS_VIEW);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#cache'] = ['max-age' => 0];

//    $headers = array(
//      array('data' => t('node'), 'style' => 'text-align:right;'),
//      array('data' => t('prio'), 'style' => 'text-align:right;'),
//      t('status'),
//      t('realm'),
//      array('data' => t('gid'), 'style' => 'text-align:right;'),
//      t('view'),
//      t('update'),
//      t('delete'),
//      t('explained'),
//    );
//    $rows[] = array('data' => array(array('data' => 1, 'style' => 'background-color: red; color: blue'),
//                                    "CACHE 5", 3, 4, 5));

    if (empty(self::visibleNodes())) {
      $request_uri = \Drupal::request()->getRequestUri();
      if ($request_uri === '/' || $request_uri === '/node') {
        $build['#markup'] = t('@node_access information is not available for the nodes on this page due to caching &mdash; flush your caches to display it.', $this->node_access);
      }
      return $build;
    }

    $build['#title'] = t('@node_access entries for nodes shown on this page', $this->node_access);
//    $build['test_table'] = array(
//      '#type' => 'table',
//      '#header' => $headers,
//      //      array(
//      //        t('Column 0'),
//      //        array('data' => t('Active'), 'colspan' => '2'),
//      //        array('data' => t('Staged'), 'colspan' => '2'),
//      //      ),
//      '#rows' => $rows,
//      '#empty' => $this->t('No records found!'),
//      '#responsive' => FALSE,
//      '#attributes' => array(
//        'class'       => array(),
//        'style'       => 'text-align: left;',
//      ),
//    );

    $build['dna_form'] = $this->formBuilder->getForm('\Drupal\devel_node_access\Form\DnaForm');
    return $build;
  }

  /**
   * Builds and returns the node information.
   *
   * @param bool $debug_mode
   *   The level of detail to include.
   *
   * @return array
   */
  public static function buildNodeInfo($debug_mode) {
    global $user;

    $visible_nodes = self::visibleNodes();
    if (count($visible_nodes) == 0) {
      return array();
    }
    else {
      $single_nid = reset($visible_nodes);
    }

    // Find out whether our DnaUser block is active or not.
    //dpm($blocks = \Drupal::entityTypeManager()->getStorage('block')->load());
    $user_block_active = FALSE;
    //foreach ($blocks as $block) {
    //  if ($block->get('plugin') == 'devel_dna_user_block') {
    //    $user_block_active = TRUE;
    //  }
    //}


    // Include rows where nid == 0.
    $nids = array_merge(array(0 => 0), $visible_nodes);
    $query = \Drupal::database()->select('node_access', 'na');
    $query
      ->fields('na')
      ->condition('na.nid', $nids, 'IN');
    $query
      ->orderBy('na.nid')
      ->orderBy('na.realm')
      ->orderBy('na.gid');
    $nodes = Node::loadMultiple($nids);

    if (!$debug_mode) {
      $headers = array('node', 'realm', 'gid', 'view', 'update', 'delete', 'explained');
      $rows = array();
      foreach ($query->execute() as $row) {
        $explained = \Drupal::moduleHandler()->invokeAll('node_access_explain', [$row]);
        $node_title = self::get_node_title($nodes[$row->nid]);
        $title_attribute = \Drupal::request()->getRequestUri();
        if (Unicode::strlen($node_title) > 20) {
          $title_attribute = $title_attribute . ': ' . $node_title;
          $node_title = Unicode::substr($node_title, 0, 18) . '...';
        }

        $rows[]     = array(
          (empty($row->nid) ? '0'
            : Link::fromTextAndUrl(
              $node_title,
              Url::fromUri(
                \Drupal::request()->getUri(),
                [
                  'fragment' => 'node-' . $row->nid,
                  'attributes' => ['title' => $title_attribute]
                ]
              )
            )
          ),
          $row->realm,
          $row->gid,
          $row->grant_view,
          $row->grant_update,
          $row->grant_delete,
          implode('<br />', $explained),
        );
      }
      $output[] = array(
        '#theme'      => 'table',
        '#header'     => $headers,
        '#rows'       => $rows,
        '#attributes' => array('style' => 'text-align: left'),
      );
    }
    else {
      $tr = 't';
      $variables = array('!na' => '{node_access}');
      $states = array(
        'default'      => array(t('default'),      'ok',      t('Default record supplied by core in the absence of any other non-empty records; in !na.', $variables)),
        'ok'           => array(t('ok'),           'ok',      t('Highest priority record; in !na.', $variables)),
        'removed'      => array(t('removed'),      '',        t('Was removed in @func; not in !na.', $variables + array('@func' => 'hook_node_access_records_alter()'))),
        'static'       => array(t('static'),       'ok',      t('Non-standard record in !na.', $variables)),
        'unexpected'   => array(t('unexpected'),   'warning', t('The 0/all/0/... record applies to all nodes and all users -- usually it should not be present in !na if any node access module is active!')),
        'ignored'      => array(t('ignored'),      'warning', t('Lower priority record; not in !na and thus ignored.', $variables)),
        'empty'        => array(t('empty'),        'warning', t('Does not grant any access, but could block lower priority records; not in !na.', $variables)),
        'wrong'        => array(t('wrong'),        'error',   t('Is rightfully in !na but at least one access flag is wrong!', $variables)),
        'missing'      => array(t('missing'),      'error',   t("Should be in !na but isn't!", $variables)),
        'removed!'     => array(t('removed!'),     'error',   t('Was removed in @func; should NOT be in !na!', $variables + array('@func' => 'hook_node_access_records_alter()'))),
        'illegitimate' => array(t('illegitimate'), 'error',   t('Should NOT be in !na because of lower priority!', $variables)),
        'alien'        => array(t('alien'),        'error',   t('Should NOT be in !na because of unknown origin!', $variables)),
      );
      $active_states = array('default', 'ok', 'static', 'unexpected', 'wrong', 'illegitimate', 'alien');
      $headers = array(t('node'), t('prio'), t('status'), t('realm'), t('gid'), t('view'), t('update'), t('delete'), t('explained'));
      $headers = self::format_row($headers);
      $active_records = array();
      foreach ($query->execute() as $active_record) {
        $active_records[$active_record->nid][$active_record->realm][$active_record->gid] = $active_record;
      }
      $all_records = $grants_data = $checked_grants = $grants = array();

      foreach (array('view', 'update', 'delete') as $op) {
        $grants[$op] = self::simulate_module_invoke_all('node_grants', $user, $op);
        // Call all hook_node_grants_alter() implementations.
        $grants_data[$op] = self::simulate_node_grants_alter($grants[$op], $user, $op);
      }

      foreach ($nids as $nid) {
        $top_priority = -99999;
        $acquired_records_nid = array();
        if ($node = Node::load($nid)) {
          // Check node_access_acquire_grants().
          $records = self::simulate_module_invoke_all('node_access_records', $node);
          // Check drupal_alter('node_access_records').
          $data = self::simulate_node_access_records_alter($records, $node);
          if (!empty($data)) {
            foreach ($data as $data_by_realm) {
              foreach ($data_by_realm as $data_by_realm_gid) {
                if (isset($data_by_realm_gid['current'])) {
                  $record = $data_by_realm_gid['current'];
                }
                elseif (isset($data_by_realm_gid['original'])) {
                  $record = $data_by_realm_gid['original'];
                  $record['#removed'] = 1;
                }
                else {
                  continue;
                }
                $priority = intval(isset($record['priority']) ? $record['priority'] : 0);
                $top_priority = (isset($top_priority) ? max($top_priority, $priority) : $priority);
                $record['priority'] = (isset($record['priority']) ? $priority : '&ndash;&nbsp;');
                $record['history'] = $data_by_realm_gid;
                $acquired_records_nid[$priority][$record['realm']][$record['gid']] = $record + array(
                    '#title'  => self::get_node_title($node),
                    '#module' => (isset($record['#module']) ? $record['#module'] : ''),
                  );
              }
            }
            krsort($acquired_records_nid);
          }
          //dpm($acquired_records_nid, "acquired_records_nid =");

          // Check node_access_grants().
          if ($node->id()) {
            foreach (array('view', 'update', 'delete') as $op) {
              $checked_grants[$nid][$op] = array_merge(array('all' => array(0)), $grants[$op]);
            }
          }
        }

        // Check for records in the node_access table that aren't returned by
        // node_access_acquire_grants().

        if (isset($active_records[$nid])) {
          foreach ($active_records[$nid] as $realm => $active_records_realm) {
            foreach ($active_records_realm as $gid => $active_record) {
              $found = FALSE;
              $count_nonempty_records = 0;
              foreach ($acquired_records_nid as $priority => $acquired_records_nid_priority) {
                if (isset($acquired_records_nid_priority[$realm][$gid])) {
                  $found = TRUE;
                }
              }
              // Take the highest priority only.
              // TODO This has changed in D8!
              if ($acquired_records_nid_priority = reset($acquired_records_nid)) {
                foreach ($acquired_records_nid_priority as $acquired_records_nid_priority_realm) {
                  foreach ($acquired_records_nid_priority_realm as $acquired_records_nid_priority_realm_gid) {
                    $count_nonempty_records += (!empty($acquired_records_nid_priority_realm_gid['grant_view']) || !empty($acquired_records_nid_priority_realm_gid['grant_update']) || !empty($acquired_records_nid_priority_realm_gid['grant_delete']));
                  }
                }
              }
              $fixed_record = (array) $active_record;
              if ($count_nonempty_records == 0 && $realm == 'all' && $gid == 0) {
                $fixed_record += array(
                  'priority' => '&ndash;',
                  'state'    => 'default',
                );
              }
              elseif (!$found) {
                $acknowledged = self::simulate_module_invoke_all('node_access_acknowledge', $fixed_record);
                if (empty($acknowledged)) {
                  // No module acknowledged this record, mark it as alien.
                  $fixed_record += array(
                    'priority' => '?',
                    'state'    => 'alien',
                  );
                }
                else {
                  // At least one module acknowledged the record,
                  // attribute it to the first one.
                  $fixed_record += array(
                    'priority' => '&ndash;',
                    'state'    => 'static',
                    '#module'  => reset(array_keys($acknowledged)),
                  );
                }
              }
              else {
                continue;
              }
              $fixed_record += array(
                'nid'    => $nid,
                '#title' => self::get_node_title($node),
              );
              $all_records[] = $fixed_record;
            }
          }
        }

        // Order records and evaluate their status.
        foreach ($acquired_records_nid as $priority => $acquired_records_priority) {
          ksort($acquired_records_priority);
          foreach ($acquired_records_priority as $realm => $acquired_records_realm) {
            ksort($acquired_records_realm);
            foreach ($acquired_records_realm as $gid => $acquired_record) {
              // TODO: Handle priority.
              //if ($priority == $top_priority) {
              if (empty($acquired_record['grant_view']) && empty($acquired_record['grant_update']) && empty($acquired_record['grant_delete'])) {
                $acquired_record['state'] = 'empty';
              }
              else {
                if (isset($active_records[$nid][$realm][$gid])) {
                  $acquired_record['state'] = (isset($acquired_record['#removed']) ? 'removed!' : 'ok');
                }
                else {
                  $acquired_record['state'] = (isset($acquired_record['#removed']) ? 'removed' : 'missing');
                }
                if ($acquired_record['state'] == 'ok') {
                  foreach (array('view', 'update', 'delete') as $op) {
                    $active_record = (array) $active_records[$nid][$realm][$gid];
                    if (empty($acquired_record["grant_$op"]) != empty($active_record["grant_$op"])) {
                      $acquired_record["grant_$op!"] = $active_record["grant_$op"];
                    }
                  }
                }
              }
              //}
              //else {
              //  $acquired_record['state'] = (isset($active_records[$nid][$realm][$gid]) ? 'illegitimate' : 'ignored');
              //}
              $all_records[] = $acquired_record + array('nid' => $nid);
            }
          }
        }
      }

      // Fill in the table rows.
      $rows = array();
      $error_count = 0;
      foreach ($all_records as $record) {
        $row = new \stdClass();
        $row->nid = $record['nid'];
        $row->title = $record['#title'];
        $row->priority = $record['priority'];
        $row->state = array('data' => $states[$record['state']][0], 'title' => $states[$record['state']][2]);
        $row->realm = $record['realm'];
        $row->gid = $record['gid'];
        $row->grant_view = $record['grant_view'];
        $row->grant_update = $record['grant_update'];
        $row->grant_delete = $record['grant_delete'];
        $row->explained = implode('<br />', \Drupal::moduleHandler()->invokeAll('node_access_explain', $row));
        unset($row->title);
        if ($row->nid == 0 && $row->gid == 0 && $row->realm == 'all' && count($all_records) > 1) {
          $row->state = array('data' => $states['unexpected'][0], 'title' => $states['unexpected'][2]);
          $class = $states['unexpected'][1];
        }
        else {
          $class = $states[$record['state']][1];
        }
        $row = (array) $row;
        foreach (array('view', 'update', 'delete') as $op) {
          $row["grant_$op"] = array('data' => $row["grant_$op"]);
          if ((isset($checked_grants[$record['nid']][$op][$record['realm']]) && in_array($record['gid'], $checked_grants[$record['nid']][$op][$record['realm']]) || ($row['nid'] == 0 && $row['gid'] == 0 && $row['realm'] == 'all')) && !empty($row["grant_$op"]['data']) && in_array($record['state'], $active_states)) {
            $row["grant_$op"]['data'] .= '&prime;';
            $row["grant_$op"]['title'] = t('This entry grants access to this node to this user.');
          }
          if (isset($record["grant_$op!"])) {
            $row["grant_$op"]['data'] = $record["grant_$op!"] . '&gt;' . (!$row["grant_$op"]['data'] ? 0 : $row["grant_$op"]['data']);
            $row["grant_$op"]['class'][] = 'error';
            if ($class == 'ok') {
              $row['state'] = array('data' => $states['wrong'][0], 'title' => $states['wrong'][2]);
              $class = $states['wrong'][1];
            }
          }
        }
        $error_count += ($class == 'error');
        $row['nid'] = array(
          'data'  => '<a href="#node-' . $record['nid'] . '">' . $row['nid'] . '</a>',
          'title' => $record['#title'],
        );
        if (empty($record['#module']) || strpos($record['realm'], $record['#module']) === 0) {
          $row['realm'] = $record['realm'];
        }
        else {
          $row['realm'] = array(
            'data' => '(' . $record['#module'] . '::) ' . $record['realm'],
            'title' => t("The '@module' module fails to adhere to the best practice of naming its realm(s) after itself.", array('@module' => $record['#module'])),
          );
        }

        // Prepend information from the D7 hook_node_access_records_alter().
        $next_style = array();
        if (isset($record['history'])) {
          $history = $record['history'];
          if (($num_changes = count($history['changes']) - empty($history['current'])) > 0) {
            $first_row = TRUE;
            while (isset($history['original']) || $num_changes--) {
              if (isset($history['original'])) {
                $this_record = $history['original'];
                $this_action = '[ Original by ' . $this_record['#module'] . ':';
                unset($history['original']);
              }
              else {
                $change = $history['changes'][0];
                $this_record = $change['record'];
                $this_action = ($first_row ? '[ ' : '') . $change['op'] . ':';
                array_shift($history['changes']);
              }
              $rows[] = array(
                'data'  => array(
                  'data'  => array(
                    'data'    => $this_action,
                    'style'   => array('padding-bottom: 0;'),
                  ),
                ),
                'style' => array_merge(($first_row ? array() : array('border-top-style: dashed;', 'border-top-width: 1px;')), array('border-bottom-style: none;')),
              );
              $next_style = array('border-top-style: none;');
              if (count($history['changes'])) {
                $g = $this_record;
                $rows[] = array(
                  'data'  => array('v', $g['priority'], '', $g['realm'], $g['gid'], $g['grant_view'], $g['grant_update'], $g['grant_delete'], 'v'),
                  'style' => array('border-top-style: none;', 'border-bottom-style: dashed;'),
                );
                $next_style = array('border-top-style: dashed;');
              }
              $first_row = FALSE;
            }
          }
        }

        // Fix up the main row cells with the proper class (needed for Bartik).
        foreach ($row as $key => $value) {
          if (!is_array($value)) {
            $row[$key] = array('data' => $value);
          }
          $row[$key]['class'] = array($class);
        }
        // Add the main row.
        $will_append = empty($history['current']) && !empty($history['changes']);
        $rows[] = array(
          'data'  => array_values($row),
          'class' => array($class),
          'style' => array_merge($next_style, ($will_append ? array('border-bottom-style: none;') : array())),
        );

        // Append information from the D7 hook_node_access_records_alter().
        if ($will_append) {
          $last_change = end($history['changes']);
          $rows[] = array(
            'data'  => array(
              'data'  => array(
                'data'    => $last_change['op'] . ' ]',
                'style' => array('padding-top: 0;'),
              ),
            ),
            'style' => array('border-top-style: none;'),
          );
        }
      }

      foreach ($rows as $i => $row) {
        $rows[$i] = self::format_row($row);
      }

      $output[] = array(
        '#theme'      => 'table',
        '#header'     => $headers,
        '#rows'       => $rows,
        '#attributes' => array(
          'class'       => array('system-status-report'),
          'style'       => 'text-align: left;',
        ),
      );

      $output[] = array(
        '#theme'       => 'form_element',
        '#description' => t('(Some of the table elements provide additional information if you hover your mouse over them.)'),
      );

      if ($error_count > 0) {
        $variables['!Rebuild_permissions'] = '<a href="' . url('admin/reports/status/rebuild') . '">' . $tr('Rebuild permissions') . '</a>';
        $output[] = array(
          '#prefix' => "\n<span class=\"error\">",
          '#markup' => t("You have errors in your !na table! You may be able to fix these for now by running !Rebuild_permissions, but this is likely to destroy the evidence and make it impossible to identify the underlying issues. If you don't fix those, the errors will probably come back again. <br /> DON'T do this just yet if you intend to ask for help with this situation.", $variables),
          '#suffix' => "</span><br />\n",
        );
      }

      // Explain whether access is granted or denied, and why
      // (using code from node_access()).
      $tr = 't';
      array_shift($nids);  // Remove the 0.
      $accounts = array();
      $variables += array(
        //'!username' => '<em class="placeholder">' . theme('username', array('account' => $user)) . '</em>',
        '!username' => '<em class="placeholder">' . $user->getDisplayName() . '</em>',
        '%uid'      => $user->id(),
      );

      if (\Drupal::currentUser()->hasPermission('bypass node access')) {
        $variables['%bypass_node_access'] = $tr('bypass node access');
        $output[] = array(
          '#markup' => t('!username has the %bypass_node_access permission and thus full access to all nodes.', $variables),
          '#suffix' => '<br />&nbsp;',
        );
      }
      else {
        $variables['!list'] = '<div style="margin-left: 2em">' . self::get_grant_list($grants_data['view']) . '</div>';
        $variables['%access'] = 'view';
        $output[] = array(
          '#prefix' => "\n<div style='text-align: left' title='" . t('These are the grants returned by hook_node_grants() for this user.') . "'>",
          '#markup' => t('!username (user %uid) can use these grants (if they are present above) for %access access: !list', $variables),
          '#suffix' => "</div>\n",
        );
        $accounts[] = $user;
      }

      if (isset($single_nid) && !$user_block_active) {
        // Only for single nodes.
        if (\Drupal::currentUser()->isAuthenticated()) {
          $accounts[] = User::load(0);  // Anonymous, too.
        }
        foreach ($accounts as $account) {
          $nid_items = array();
          foreach ($nids as $nid) {
            $op_items = array();
            foreach (array('create', 'view', 'update', 'delete') as $op) {
              $explain = self::explainAccess($op, Node::load($nid), $account);
              $op_items[] = "<div style='width: 5em; display: inline-block'>" . t('%op:', array('%op' => $op)) . ' </div>' . $explain[2];
            }
            $nid_items[] = array(
              '#theme'  => 'item_list',
              '#items'  => $op_items,
              '#type'   => 'ul',
              '#prefix' => t('to node !nid:', array('!nid' => l($nid, 'node/' . $nid))) . "\n<div style='margin-left: 2em'>",
              '#suffix' => '</div>',
            );
          }
          if (count($nid_items) == 1) {
            $account_items = $nid_items[0];
          }
          else {
            $account_items = array(
              '#theme'  => 'item_list',
              '#items'  => $nid_items,
              '#type'   => 'ul',
              '#prefix' => "\n<div style='margin-left: 2em'>",
              '#suffix' => '</div>',
            );
          }
          $variables['!username'] = '<em class="placeholder">' . theme('username', array('account' => $account)) . '</em>';
          $output[] = array(
            '#prefix' => "\n<div style='text-align: left'>",
            '#type'   => 'item',
            'lead-in' => array('#markup' => t("!username has the following access", $variables) . ' '),
            'items'   => $account_items,
            '#suffix' => "\n</div>\n",
          );
        }
      }
    }

    return $output;
  }

  /**
   * Builds and returns the by-user information.
   *
   * @return array|null
   */
  public static function buildByUserInfo() {
    global $user;

    $output = array();
    return $output;

    // Show which users can access this node.
    $menu_item = menu_get_item();
    $map = $menu_item['original_map'];
    if ($map[0] != 'node' || !isset($map[1]) || !is_numeric($map[1]) || isset($map[2])) {
      // Ignore anything but node/%.
      return NULL;
    }

    if (isset($menu_item['map'][1]) && is_object($node = $menu_item['map'][1])) {
      // We have the node.
    }
    elseif (is_numeric($menu_item['original_map'][1])) {
      $node = node_load($menu_item['original_map'][1]);
    }
    if (isset($node)) {
      $nid = $node->id();
      $langcode = $node->langcode->value;
      $language = language_load($langcode);
      $node_type = node_type_load($node->bundle());
      $headers = array(t('username'), '<span title="' . t("Create '@langname'-language nodes of the '@Node_type' type.", array('@langname' => $language->name, '@Node_type' => $node_type->name)) . '">' . t('create') . '</span>', t('view'), t('update'), t('delete'));
      $rows = array();
      // Determine whether to use Ajax or pre-populate the tables.
      if ($ajax = \Drupal::config('devel_node_access.settings')->get('user_ajax')) {
        $output['#attached']['library'][] = 'devel_node_access/node_access';
      }
      // Find all users. The following operations are very inefficient, so we
      // limit the number of users returned.  It would be better to make a
      // pager query, or at least make the number of users configurable.  If
      // anyone is up for that please submit a patch.
      $query = db_select('users', 'u')
        ->fields('u', array('uid'))
        ->orderBy('u.access', 'DESC')
        ->range(0, 9);
      $uids = $query->execute()->fetchCol();
      array_unshift($uids, 0);
      $accounts = user_load_multiple($uids);
      foreach ($accounts as $account) {
        $username = theme('username', array('account' => $account));
        $uid = $account->id();
        if ($uid == $user->id()) {
          $username = '<strong>' . $username . '</strong>';
        }
        $rows[] = array(
          $username,
          array(
            'id' => 'create-' . $nid . '-' . $uid,
            'class' => 'dna-permission',
            'data' => $ajax ? NULL : theme('dna_permission', array('permission' => self::explainAccess('create', $node, $account, $langcode))),
          ),
          array(
            'id' => 'view-' . $nid . '-' . $uid,
            'class' => 'dna-permission',
            'data' => $ajax ? NULL : theme('dna_permission', array('permission' => self::explainAccess('view', $node, $account, $langcode))),
          ),
          array(
            'id' => 'update-' . $nid . '-' . $uid,
            'class' => 'dna-permission',
            'data' => $ajax ? NULL : theme('dna_permission', array('permission' => self::explainAccess('update', $node, $account, $langcode))),
          ),
          array(
            'id' => 'delete-' . $nid . '-' . $uid,
            'class' => 'dna-permission',
            'data' => $ajax ? NULL : theme('dna_permission', array('permission' => self::explainAccess('delete', $node, $account, $langcode))),
          ),
        );
      }
      if (count($rows)) {
        $output['title'] = array(
          '#prefix' => '<h2>',
          '#markup' => t('Access permissions by user for the %langname language', array('%langname' => $language->name)),
          '#postfix' => '</h2>',
        );
        $output[] = array(
          '#theme'      => 'table',
          '#header'     => $headers,
          '#rows'       => $rows,
          '#attributes' => array('style' => 'text-align: left'),
        );
        $output[] = array(
          '#theme'        => 'form_element',
          '#description'  => t('(This table lists the most-recently active users. Hover your mouse over each result for more details.)'),
        );
      }
    }
    return $output;
  }

  /**
   * Helper function to mimic \Drupal::moduleHandler()->invokeAll() and include
   * the name of the responding module(s).
   *
   * @param $hook
   *   The name of the hook.
   *
   * @return array
   *   An array of results.
   *   In the case of scalar results, the array is keyed by the name of the
   *   modules that returned the result (rather than by numeric index), and
   *   in the case of array results, a '#module' key is added.
   */
  private static function simulate_module_invoke_all($hook) {
    $args = func_get_args();
    // Remove $hook from the arguments.
    array_shift($args);
    $return = array();
    foreach (\Drupal::moduleHandler()->getImplementations($hook) as $module) {
      $function = $module . '_' . $hook;
      $result = call_user_func_array($function, $args);
      if (isset($result)) {
        if (is_array($result)) {
          foreach ($result as $key => $value) {
            // Add name of module that returned the value.
            $result[$key]['#module'] = $module;
          }
        }
        else {
          // Build array with result keyed by $module.
          $result = array($module => $result);
        }
        $return = \Drupal\Component\Utility\NestedArray::mergeDeep($return, $result);
      }
    }
    return $return;
  }

  /**
   * Helper function to mimic hook_node_access_records_alter() and trace what
   * each module does with it.
   *
   * @param array $records
   *   An indexed array of NA records, augmented by the '#module' key,
   *   as created by simulate_module_invoke_all('node_access_records').
   *   This array is updated by the hook_node_access_records_alter()
   *   implementations.
   * @param $node
   *   The node that the NA records belong to.
   *
   * @return array
   *   A tree representation of the NA records in $records including their
   *   history:
   *   $data[$realm][$gid]
   *     ['original']  - NA record before processing
   *     ['current']   - NA record after processing (if still present)
   *     ['changes'][]['op']     - change message (add/change/delete by $module)
   *                  ['record'] - NA record after change (unless deleted)
   */
  private static function simulate_node_access_records_alter(&$records, $node) {
    //dpm($records, 'simulate_node_access_records_alter(): records IN');
    $hook = 'node_access_records_alter';

    // Build the initial tree (and check for duplicates).
    $data = self::build_node_access_records_data($records, $node, 'hook_node_access_records()');

    // Simulate drupal_alter('node_access_records', $records, $node).
    foreach (\Drupal::moduleHandler()->getImplementations($hook) as $module) {
      // Call hook_node_access_records_alter() for one module at a time
      // and analyze.
      $function = $module . '_' . $hook;
      $function($records, $node);

      foreach ($records as $i => $record) {
        if (empty($data[$record['realm']][$record['gid']]['current'])) {
          // It's an added record.
          $data[$record['realm']][$record['gid']]['current'] = $record;
          $data[$record['realm']][$record['gid']]['current']['#module'] = $module;
          $data[$record['realm']][$record['gid']]['changes'][] = array(
            'op'     => 'added by ' . $module,
            'record' => $record,
          );
          $records[$i]['#module'] = $module;
        }
        else {
          // It's an existing record, check for changes.
          $view = $update = $delete = FALSE;
          foreach (array('view', 'update', 'delete') as $op) {
            $$op = $record["grant_$op"] - $data[$record['realm']][$record['gid']]['current']["grant_$op"];
          }
          $old_priority = isset($record['priority']) ? $record['priority'] : 0;
          $new_priority = isset($data[$record['realm']][$record['gid']]['current']['priority']) ? $data[$record['realm']][$record['gid']]['current']['priority'] : 0;
          if ($view || $update || $delete || $old_priority != $new_priority) {
            // It was changed.
            $data[$record['realm']][$record['gid']]['current'] = $record;
            $data[$record['realm']][$record['gid']]['current']['#module'] = $module;
            $data[$record['realm']][$record['gid']]['changes'][] = array(
              'op'     => 'altered by ' . $module,
              'record' => $record,
            );
            $records[$i]['#module'] = $module;
          }
        }
        $data[$record['realm']][$record['gid']]['found'] = TRUE;
      }

      // Check for newly introduced duplicates.
      self::build_node_access_records_data($records, $node, 'hook_node_access_records_alter()');

      // Look for records that have disappeared.
      foreach ($data as $realm => $data2) {
        foreach ($data2 as $gid => $data3) {
          if (empty($data[$realm][$gid]['found']) && isset($data[$realm][$gid]['current'])) {
            unset($data[$realm][$gid]['current']);
            $data[$realm][$gid]['changes'][] = array('op' => 'removed by ' . $module);
          }
          else {
            unset($data[$realm][$gid]['found']);
          }
        }
      }
    }
    //dpm($data, 'simulate_node_access_records_alter() returns');
    //dpm($records, 'simulate_node_access_records_alter(): records OUT');
    return $data;
  }

  /**
   * Helper function to build an associative array of node access records and
   * their history. If there are duplicate records, display an error message.
   *
   * @param $records
   *   An indexed array of node access records, augmented by the '#module' key,
   *   as created by simulate_module_invoke_all('node_access_records').
   * @param $node
   *   The node that the NA records belong to.
   * @param $function
   *   The name of the hook that produced the records array, in case we need to
   *   display an error message.
   *
   * @return array
   *   See _devel_node_access_nar_alter() for the description of the result.
   */
  private static function build_node_access_records_data($records, $node, $function) {
    $data = array();
    $duplicates = array();
    foreach ($records as $record) {
      if (empty($data[$record['realm']][$record['gid']])) {
        $data[$record['realm']][$record['gid']] = array('original' => $record, 'current' => $record, 'changes' => array());
      }
      else {
        if (empty($duplicates[$record['realm']][$record['gid']])) {
          $duplicates[$record['realm']][$record['gid']][] = $data[$record['realm']][$record['gid']]['original'];
        }
        $duplicates[$record['realm']][$record['gid']][] = $record;
      }
    }
    if (!empty($duplicates)) {
      // Generate an error message.
      $msg = t('Devel Node Access has detected duplicate records returned from %function:', array('%function' => $function));
      $msg .= '<ul>';
      foreach ($duplicates as $realm => $data_by_realm) {
        foreach ($data_by_realm as $gid => $data_by_realm_gid) {
          $msg .= '<li><ul>';
          foreach ($data_by_realm_gid as $record) {
            $msg .= "<li>$node->id()/$realm/$gid/" . ($record['grant_view'] ? 1 : 0) . ($record['grant_update'] ? 1 : 0) . ($record['grant_delete'] ? 1 : 0) . ' by ' . $record['#module'] . '</li>';
          }
          $msg .= '</ul></li>';
        }
      }
      $msg .= '</ul>';
      drupal_set_message($msg, 'error', FALSE);
    }
    return $data;
  }

  /**
   * Helper function to mimic hook_node_grants_alter() and trace what
   * each module does with it.
   *
   * @param array $grants
   *   An indexed array of grant records, augmented by the '#module' key,
   *   as created by simulate_module_invoke_all('node_grants').
   *   This array is updated by the hook_node_grants_alter()
   *   implementations.
   * @param $account
   *   The user account requesting access to content.
   * @param $op
   *   The operation being performed, 'view', 'update' or 'delete'.
   *
   * @return array
   *   A tree representation of the grant records in $grants including their
   *   history:
   *   $data[$realm][$gid]
   *     ['cur']    - TRUE or FALSE whether the gid is present or not
   *     ['ori'][]  - array of module names that contributed this grant (if any)
   *     ['chg'][]  - array of changes, such as
   *                     - 'added' if module name is a prefix if the $realm,
   *                     - 'added by module' otherwise, or
   *                     - 'removed by module'
   */
  private static function simulate_node_grants_alter(&$grants, $account, $op) {
    //dpm($grants, "simulate_node_grants_alter($account->name, $op): grants IN");
    $hook = 'node_grants_alter';

    // Build the initial structure.
    $data = array();
    foreach ($grants as $realm => $gids) {
      foreach ($gids as $i => $gid) {
        if ($i !== '#module') {
          $data[$realm][$gid]['cur'] = TRUE;
          $data[$realm][$gid]['ori'][] = $gids['#module'];
        }
      }
      unset($grants[$realm]['#module']);
    }

    // Simulate drupal_alter('node_grants', $grants, $account, $op).
    foreach (\Drupal::moduleHandler()->getImplementations($hook) as $module) {
      // Call hook_node_grants_alter() for one module at a time and analyze.
      $function = $module . '_' . $hook;
      $function($grants, $account, $op);

      // Check for new gids.
      foreach ($grants as $realm => $gids) {
        foreach ($gids as $i => $gid) {
          if (empty($data[$realm][$gid]['cur'])) {
            $data[$realm][$gid]['cur'] = TRUE;
            $data[$realm][$gid]['chg'][] = 'added by ' . $module;
          }
        }
      }

      // Check for removed gids.
      foreach ($data as $realm => $gids) {
        foreach  ($gids as $gid => $history) {
          if ($history['cur'] && array_search($gid, $grants[$realm]) === FALSE) {
            $data[$realm][$gid]['cur'] = FALSE;
            $data[$realm][$gid]['chg'][] = 'removed by ' . $module;
          }
        }
      }
    }

    //dpm($data, "simulate_node_grants_alter($account->name, $op) returns");
    //dpm($grants, "simulate_node_grants_alter($account->name, $op): grants OUT");
    return $data;
  }

  /**
   * Helper function to create a list of grants returned by hook_node_grants().
   */
  private static function get_grant_list($grants_data) {
    //dpm($grants_data, "get_grant_list() IN:");
    $grants_data = array_merge(array('all' => array(0 => array('cur' => TRUE, 'ori' => array('all')))), $grants_data);
    $items = array();
    if (count($grants_data)) {
      foreach ($grants_data as $realm => $gids) {
        ksort($gids);
        $gs = array();
        foreach ($gids as $gid => $history) {
          if ($history['cur']) {
            if (isset($history['ori'])) {
              $g = $gid;                     // Original grant, still active.
            }
            else {
              $g = '<u>' . $gid . '</u>';    // New grant, still active.
            }
          }
          else {
            $g = '<del>' . $gid . '</del>';  // Deleted grant.
          }

          $ghs = array();
          if (isset($history['ori']) && strpos($realm, $history['ori'][0]) !== 0) {
            $realm = '(' . $history['ori'][0] . '::) ' . $realm;
          }
          if (isset($history['chg'])) {
            foreach ($history['chg'] as $h) {
              $ghs[] = $h;
            }
          }
          if (!empty($ghs)) {
            $g .= ' (' . implode(', ', $ghs) . ')';
          }
          $gs[] = $g;
        }
        $items[] = $realm . ': ' . implode(', ', $gs);
      }
      if (!empty($items)) {
        return theme('item_list', array('items' => $items, 'type' => 'ul'));
      }
    }
    return '';
  }

  /**
   * Helper function to return a sanitized node title.
   */
  private static function get_node_title(Node $node) {
    if (isset($node)) {
      $nid = $node->id();
      if ($node_title = $node->getTitle()) {
        return $node_title;
      }
      elseif ($nid) {
        return $nid;
      }
    }
    return '—';
  }

  /**
   * Helper function to apply common formatting to a debug-mode table row.
   */
  private static function format_row($row, $may_unpack = TRUE) {
    if ($may_unpack && isset($row['data'])) {
      $row['data'] = self::format_row($row['data'], FALSE);
      $row['class'][] = 'even';
      return $row;
    }
    if (count($row) == 1) {
      if (is_scalar($row['data'])) {
        $row['data'] = array('data' => $row['data']);
      }
      $row['data']['colspan'] = 9;
    }
    else {
      $row = array_values($row);
      foreach (array(0, 1, 4) as $j) {  // node, prio, gid
        if (is_scalar($row[$j])) {
          $row[$j] = array('data' => $row[$j]);
        }
        dpm($j);
        dpm($row);
//        $row[$j]['style'][] = 'text-align: right;';
      }
    }
    return $row;
  }


  /**
   * Helper function that mimics node.module's node_access() function.
   *
   * Unfortunately, this needs to be updated manually whenever node.module
   * changes!
   *
   * @param string $op
   *   Operation to check.
   * @param NodeInterface $node
   *   Node to check.
   * @param AccountInterface $account
   *   (optional) The user object for the user whose access is being checked. If
   *   omitted, the current user is used. Defaults to NULL.
   * @param string $langcode
   *   (optional) The language code for which access is being checked. If
   *   omitted, the default language is used. Defaults to NULL.
   *
   * @return array
   *   An array suitable for theming with theme_dna_permission().
   */
  public static function explainAccess($op, NodeInterface $node, AccountInterface $account = NULL, $langcode = NULL) {
    $user = Drupal::currentUser();

    if (!$node) {
      return array(
        FALSE,
        '???',
        t('No node passed to node_access(); this should never happen!'),
      );
    }
    if (!in_array($op, array('view', 'update', 'delete', 'create'), TRUE)) {
      return array(
        FALSE,
        t('!NO: invalid $op', array('!NO' => t('NO'))),
        t("'@op' is an invalid operation!", array('@op' => $op)),
      );
    }

    if ($op == 'create' && is_object($node)) {
      $node = $node->bundle();
    }

    if (!empty($account)) {
      // To try to get the most authentic result we impersonate the given user!
      // This may reveal bugs in other modules, leading to contradictory
      // results.
      /* @var \Drupal\Core\Session\AccountSwitcherInterface $account_switcher */
      $account_switcher = Drupal::service('account_switcher');
      $account_switcher->switchTo($account);
      $result = DnaBlock::explainAccess($op, $node, NULL, $langcode);
      $account_switcher->switchBack();
      $access_handler = Drupal::entityTypeManager()->getAccessControlHandler('node');
      $second_opinion = $access_handler->access($node, $op, $account);
      if ($second_opinion != $result[0]) {
        $result[1] .= '<span class="' . ($second_opinion ? 'ok' : 'error') . '" title="Core seems to disagree on this item. This is a bug in either DNA or Core and should be fixed! Try to look at this node as this user and check whether there is still disagreement.">*</span>';
      }
      return $result;
    }

    if (empty($langcode)) {
      $langcode = (is_object($node) && $node->id()) ? $node->language()->getId() : '';
    }

    $variables = array(
      '!NO'                 => t('NO'),
      '!YES'                => t('YES'),
      '!bypass_node_access' => t('bypass node access'),
      '!access_content'     => t('access content'),
    );

    if (Drupal::currentUser()->hasPermission('bypass node access')) {
      return array(
        TRUE,
        t('!YES: bypass node access', $variables),
        t("!YES: This user has the '!bypass_node_access' permission and may do everything with nodes.", $variables),
      );
    }

    if (!Drupal::currentUser()->hasPermission('access content')) {
      return array(
        FALSE,
        t('!NO: access content', $variables),
        t("!NO: This user does not have the '!access_content' permission and is denied doing anything with content.", $variables),
      );
    }

    foreach (Drupal::moduleHandler()->getImplementations('node_access') as $module) {
      $function = $module . '_node_access';
      if (function_exists($function)) {
        $result = $function($node, $op, $user, $langcode);
        if ($module == 'node') {
          $module = 'node (permissions)';
        }
        if (isset($result)) {
          /* TODO
          if ($result === NODE_ACCESS_DENY) {
            $denied_by[] = $module;
          }
          elseif ($result === NODE_ACCESS_ALLOW) {
            $allowed_by[] = $module;
          }
          */
          $access[] = $result;
        }
      }
    }
    $variables += array(
      '@deniers'  => (empty($denied_by) ? NULL : implode(', ', $denied_by)),
      '@allowers' => (empty($allowed_by) ? NULL : implode(', ', $allowed_by)),
    );
    if (!empty($denied_by)) {
      $variables += array(
        '%module' => $denied_by[0] . (count($denied_by) > 1 ? '+' : ''),
      );
      return [
        FALSE,
        t('!NO: by %module', $variables),
        empty($allowed_by)
          ? t("!NO: hook_node_access() of the following module(s) denies this: @deniers.", $variables)
          : t("!NO: hook_node_access() of the following module(s) denies this: @deniers &ndash; even though the following module(s) would allow it: @allowers.", $variables),
      ];
    }
    if (!empty($allowed_by)) {
      $variables += array(
        '%module' => $allowed_by[0] . (count($allowed_by) > 1 ? '+' : ''),
        '!view_own_unpublished_content' => t('view own unpublished content'),
      );
      return array(
        TRUE,
        t('!YES: by %module', $variables),
        t("!YES: hook_node_access() of the following module(s) allows this: @allowers.", $variables),
      );
    }

    // TODO if ($op == 'view' && !$node->get('status', $langcode) && \Drupal::currentUser()->hasPermission('view own unpublished content') && $user->uid == $node->get('uid', $langcode) && $user->uid != 0) {
    if ($op == 'view' && !$node->isPublished() && Drupal::currentUser()->hasPermission('view own unpublished content') && $user->id() == $node->getRevisionAuthor()->id() && $user->id() != 0) {
      return array(
        TRUE,
        t('!YES: view own unpublished content', $variables),
        t("!YES: The node is unpublished, but the user has the '!view_own_unpublished_content' permission.", $variables),
      );
    }

    if ($op != 'create' && $node->id()) {
      $access_handler = Drupal::entityTypeManager()->getAccessControlHandler('node');
      // TODO if (node_access($op, $node, $user, $langcode)) {  // delegate this part
      if ($access_handler->access($node, $op, $user)) {
        // Delegate this part.
        $variables['@node_access_table'] = '{node_access}';
        return array(
          TRUE,
          t('!YES: @node_access_table', $variables),
          t('!YES: Node access allows this based on one or more records in the @node_access_table table (see the other DNA block!).', $variables),
        );
      }
    }

    return array(
      FALSE,
      t('!NO: no reason', $variables),
      t("!NO: None of the checks resulted in allowing this, so it's denied.", $variables)
      . ($op == 'create' ? ' ' . t('This is most likely due to a withheld permission.') : ''),
    );
  }

  /**
   * Collects the IDs of the visible nodes on the current page.
   *
   * @param int|null $nid
   *   A node ID to save.
   *
   * @return array
   *   The array of saved node IDs.
   */
  public static function visibleNodes($nid = NULL) {
    static $nids = array();
    if (isset($nid)) {
      $nids[$nid] = $nid;
    }
    elseif (empty($nids)) {
      ///** @var NodeInterface $node */
      //$node = NULL;
      if ($node = \Drupal::routeMatch()->getParameter('node')) {
        $nid = $node->id();
        $nids[$nid] = $nid;
      }
    }
    return $nids;
  }


}
