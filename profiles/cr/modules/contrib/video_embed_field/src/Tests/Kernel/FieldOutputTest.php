<?php

/**
 * @file
 * Contains \Drupal\video_embed_field\Tests\Kernel\FieldOutputTest.
 */

namespace Drupal\video_embed_field\Tests\Kernel;

use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Thumbnail;
use Drupal\video_embed_field\Tests\KernelTestBase;

/**
 * Test the embed field formatters are functioning.
 *
 * @group video_embed_field
 */
class FieldOutputTest extends KernelTestBase {

  /**
   * The test cases.
   */
  public function renderedFieldTestCases() {
    return [
      'YouTube: Thumbnail' => [
        'https://www.youtube.com/watch?v=fdbFVWupSsw',
        [
          'type' => 'video_embed_field_thumbnail',
          'settings' => [],
        ],
        [
          '#theme' => 'image',
          '#uri' => 'public://video_thumbnails/fdbFVWupSsw.jpg',
        ],
      ],
      'YouTube: Embed Code' => [
        'https://www.youtube.com/watch?v=fdbFVWupSsw',
        [
          'type' => 'video_embed_field_video',
          'settings' => [
            'width' => '100%',
            'height' => '100%',
            'autoplay' => TRUE,
          ],
        ],
        [
          '#type' => 'video_embed_iframe',
          '#provider' => 'youtube',
          '#url' => 'https://www.youtube.com/embed/fdbFVWupSsw',
          '#query' => [
            'autoplay' => '1',
            'start' => '0',
            'rel' => '0',
          ],
          '#attributes' => [
            'width' => '100%',
            'height' => '100%',
            'frameborder' => '0',
            'allowfullscreen' => 'allowfullscreen',
          ],
          '#cache' => [
            'contexts' => [
              'user.permissions',
            ],
          ],
        ],
      ],
      'YouTube: Time-index Embed Code' => [
        'https://www.youtube.com/watch?v=fdbFVWupSsw&t=100',
        [
          'type' => 'video_embed_field_video',
          'settings' => [
            'width' => '100%',
            'height' => '100%',
            'autoplay' => TRUE,
          ],
        ],
        [
          '#type' => 'video_embed_iframe',
          '#provider' => 'youtube',
          '#url' => 'https://www.youtube.com/embed/fdbFVWupSsw',
          '#query' => [
            'autoplay' => '1',
            'start' => '100',
            'rel' => '0',
          ],
          '#attributes' => [
            'width' => '100%',
            'height' => '100%',
            'frameborder' => '0',
            'allowfullscreen' => 'allowfullscreen',
          ],
          '#cache' => [
            'contexts' => [
              'user.permissions',
            ],
          ],
        ],
      ],
      'Vimeo: Thumbnail' => [
        'https://vimeo.com/80896303',
        [
          'type' => 'video_embed_field_thumbnail',
          'settings' => [],
        ],
        [
          '#theme' => 'image',
          '#uri' => 'public://video_thumbnails/80896303.jpg',
        ],
      ],
      'Vimeo: Embed Code' => [
        'https://vimeo.com/80896303',
        [
          'type' => 'video_embed_field_video',
          'settings' => [
            'width' => '100%',
            'height' => '100%',
            'autoplay' => TRUE,
          ],
        ],
        [
          '#type' => 'video_embed_iframe',
          '#provider' => 'vimeo',
          '#url' => 'https://player.vimeo.com/video/80896303',
          '#query' => [
            'autoplay' => '1',
          ],
          '#attributes' => [
            'width' => '100%',
            'height' => '100%',
            'frameborder' => '0',
            'allowfullscreen' => 'allowfullscreen',
          ],
          '#cache' => [
            'contexts' => [
              'user.permissions',
            ],
          ],
        ],
      ],
      'Linked Thumbnail: Content' => [
        'https://vimeo.com/80896303',
        [
          'type' => 'video_embed_field_thumbnail',
          'settings' => ['link_image_to' => Thumbnail::LINK_CONTENT],
        ],
        [
          '#type' => 'link',
          '#title' => [
            '#theme' => 'image',
            '#uri' => 'public://video_thumbnails/80896303.jpg',
          ],
          '#url' => 'entity.entity_test.canonical',
        ],
      ],
      'Linked Thumbnail: Provider' => [
        'https://vimeo.com/80896303',
        [
          'type' => 'video_embed_field_thumbnail',
          'settings' => ['link_image_to' => Thumbnail::LINK_PROVIDER],
        ],
        [
          '#type' => 'link',
          '#title' => [
            '#theme' => 'image',
            '#uri' => 'public://video_thumbnails/80896303.jpg',
          ],
          '#url' => 'https://vimeo.com/80896303',
        ],
      ],
      'Colorbox Modal: Linked Image & Autoplay' => [
        'https://vimeo.com/80896303',
        [
          'type' => 'video_embed_field_colorbox',
          'settings' => [
            'link_image_to' => Thumbnail::LINK_PROVIDER,
            'autoplay' => TRUE,
            'width' => 500,
            'height' => 500,
          ],
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'data-video-embed-field-modal' => '<iframe width="500" height="500" frameborder="0" allowfullscreen="allowfullscreen" src="https://player.vimeo.com/video/80896303?autoplay=1"></iframe>',
            'class' => ['video-embed-field-launch-modal'],
          ],
          '#attached' => ['library' => ['video_embed_field/colorbox']],
          'children' => [
            '#type' => 'link',
            '#title' => [
              '#theme' => 'image',
              '#uri' => 'public://video_thumbnails/80896303.jpg',
            ],
            '#url' => 'https://vimeo.com/80896303',
          ],
        ],
      ],
      'Video: Responsive' => [
        'https://vimeo.com/80896303',
        [
          'type' => 'video_embed_field_video',
          'settings' => [
            'width' => '100px',
            'height' => '100px',
            'autoplay' => TRUE,
            'responsive' => TRUE,
          ],
        ],
        [
          '#type' => 'video_embed_iframe',
          '#provider' => 'vimeo',
          '#url' => 'https://player.vimeo.com/video/80896303',
          '#query' => [
            'autoplay' => '1',
          ],
          '#attributes' => [
            'width' => '100px',
            'height' => '100px',
            'frameborder' => '0',
            'allowfullscreen' => 'allowfullscreen',
          ],
          '#cache' => [
            'contexts' => [
              'user.permissions',
            ],
          ],
        ],
        [
          'class' => ['video-embed-field-responsive-video'],
        ],
        [
          'library' => ['video_embed_field/responsive-video'],
        ],
      ],
    ];
  }

  /**
   * @dataProvider renderedFieldTestCases
   *
   * Test the embed field.
   */
  public function testEmbedField($url, $settings, $expected_field_item_output, $field_attributes = NULL, $field_attachments = NULL) {

    $field_output = $this->getPreparedFieldOutput($url, $settings);

    // Assert the specific field output at delta 1 matches the expected test
    // data.
    $this->assertEquals($expected_field_item_output, $field_output[0]);

    // Allow us to assert subsets of the whole field output, instead of having
    // to use the verbose field renderable array in our test data.
    if ($field_attributes) {
      $this->assertEquals($field_attributes, $field_output['#attributes']);
    }
    if ($field_attachments) {
      $this->assertEquals($field_attachments, $field_output['#attached']);
    }
  }

  /**
   * Get and prepare the output of a field.
   *
   * @param string $url
   *   The video URL.
   * @param array $settings
   *   An array of formatter settings.
   *
   * @return array
   *   The rendered prepared field output.
   */
  protected function getPreparedFieldOutput($url, $settings) {
    $entity = EntityTest::create();
    $entity->{$this->fieldName}->value = $url;
    $entity->save();

    $field_output = $entity->{$this->fieldName}->view($settings);

    // Prepare the field output to make it easier to compare our test data
    // values against.
    array_walk_recursive($field_output[0], function (&$value) {
      // Prevent circular references with comparing field output that
      // contains url objects.
      if ($value instanceof Url) {
        $value = $value->isRouted() ? $value->getRouteName() : $value->getUri();
      }
      // Trim to prevent stray whitespace for the colorbox formatters with
      // early rendering.
      $value = trim($value);
    });

    return $field_output;
  }

}
