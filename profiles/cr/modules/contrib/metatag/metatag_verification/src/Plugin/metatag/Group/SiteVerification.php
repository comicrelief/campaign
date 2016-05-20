<?php
/**
 * @file
 * Contains \Drupal\metatag\Plugin\metatag\Group\SiteVerification.
 */

namespace Drupal\metatag\Plugin\metatag\Group;


/**
 * The Site Verification group.
 *
 * @MetatagGroup(
 *   id = "site_verification",
 *   label = @Translation("Site verification"),
 *   description = @Translation("These meta tags are used to confirm site ownership for search engines and other services."),
 *   weight = 10
 * )
 */
class SiteVerification extends GroupBase {
  // Inherits everything from Base.
}
