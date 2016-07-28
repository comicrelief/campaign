<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsCreatorId.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\Core\Form\FormStateInterface;
use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards creator id metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_creator_id",
 *   label = @Translation("Creator's Twitter account ID"),
 *   description = @Translation("The numerical Twitter account ID for the content creator / author for this page."),
 *   name = "twitter:creator:id",
 *   group = "twitter_cards",
 *   weight = 4,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsCreatorId extends MetaPropertyBase {
}
