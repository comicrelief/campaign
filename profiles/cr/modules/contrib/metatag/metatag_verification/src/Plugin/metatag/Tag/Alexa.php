<?php
/**
 * @file
 * Contains \Drupal\metatag_verification\Plugin\metatag\Tag\Alexa.
 */

namespace Drupal\metatag_verification\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaNameBase;

/**
 * Provides a plugin for the 'alexaVerifyID' meta tag.
 *
 * @MetatagTag(
 *   id = "alexa",
 *   label = @Translation("Alexa"),
 *   description = @Translation("A string provided by <a href=':alexa'>Alexa</a>, which can be obtained from the <a href=':verify_url'>Alexa 'Claim Your Site' page</a>.", arguments = { ":alexa" = "http://www.alexa.com/", ":verify_url" = "http://www.alexa.com/siteowners/claim" }),
 *   name = "alexaVerifyID",
 *   group = "site_verification",
 *   weight = 1,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class Alexa extends MetaNameBase {
}
