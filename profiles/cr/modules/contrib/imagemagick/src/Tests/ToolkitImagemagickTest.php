<?php

namespace Drupal\imagemagick\Tests;

use Drupal\Core\Image\ImageInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests that core image manipulations work properly through Imagemagick.
 *
 * @group Imagemagick
 */
class ToolkitImagemagickTest extends WebTestBase {

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * A directory for image test file results.
   *
   * @var string
   */
  protected $testDirectory;

  // Colors that are used in testing.
  protected $black       = array(0, 0, 0, 0);
  protected $red         = array(255, 0, 0, 0);
  protected $green       = array(0, 255, 0, 0);
  protected $blue        = array(0, 0, 255, 0);
  protected $yellow      = array(255, 255, 0, 0);
  protected $white       = array(255, 255, 255, 0);
  protected $transparent = array(0, 0, 0, 127);
  // Used as rotate background colors.
  protected $fuchsia            = array(255, 0, 255, 0);
  protected $rotateTransparent = array(255, 255, 255, 127);

  protected $width = 40;
  protected $height = 20;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['system', 'simpletest', 'file_test', 'imagemagick'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create an admin user.
    $admin_user = $this->drupalCreateUser(array(
      'administer site configuration',
    ));
    $this->drupalLogin($admin_user);

    // Set the image factory.
    $this->imageFactory = $this->container->get('image.factory');

    // Prepare a directory for test file results.
    $this->testDirectory = 'public://imagetest';
    file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
  }

  /**
   * Function for finding a pixel's RGBa values.
   */
  protected function getPixelColor(ImageInterface $image, $x, $y) {
    $toolkit = $image->getToolkit();
    $color_index = imagecolorat($toolkit->getResource(), $x, $y);

    $transparent_index = imagecolortransparent($toolkit->getResource());
    if ($color_index == $transparent_index) {
      return array(0, 0, 0, 127);
    }

    return array_values(imagecolorsforindex($toolkit->getResource(), $color_index));
  }

  /**
   * Test image toolkit operations.
   *
   * Since PHP can't visually check that our images have been manipulated
   * properly, build a list of expected color values for each of the corners and
   * the expected height and widths for the final images.
   */
  public function testManipulations() {
    // Change the toolkit.
    \Drupal::configFactory()->getEditable('system.image')
      ->set('toolkit', 'imagemagick')
      ->save();
    \Drupal::configFactory()->getEditable('imagemagick.settings')
      ->set('debug', TRUE)
      ->set('quality', 100)
      ->save();

    // Set the toolkit on the image factory.
    $this->imageFactory->setToolkitId('imagemagick');

    // Test that the image factory is set to use the Imagemagick toolkit.
    $this->assertEqual($this->imageFactory->getToolkitId(), 'imagemagick', 'The image factory is set to use the \'imagemagick\' image toolkit.');

    // The test can only be executed if the ImageMagick 'convert' is
    // available on the shell path.
    $status = \Drupal::service('image.toolkit.manager')->createInstance('imagemagick')->checkPath('');
    if (!empty($status['errors'])) {
      // Bots running automated test on d.o. do not have ImageMagick
      // installed, so there's no purpose to try and run this test there;
      // it can be run locally where ImageMagick is installed.
      debug('Tests for the Imagemagick toolkit cannot run because the \'convert\' executable is not available on the shell path.');
      return;
    }

    // Typically the corner colors will be unchanged. These colors are in the
    // order of top-left, top-right, bottom-right, bottom-left.
    $default_corners = array($this->red, $this->green, $this->blue, $this->transparent);

    // A list of files that will be tested.
    $files = array(
      'image-test.png',
      'image-test.gif',
      'image-test-no-transparency.gif',
      'image-test.jpg',
    );

    // Setup a list of tests to perform on each type.
    $operations = array(
      'resize' => array(
        'function' => 'resize',
        'arguments' => array('width' => 20, 'height' => 10),
        'width' => 20,
        'height' => 10,
        'corners' => $default_corners,
        'tolerance' => 0,
      ),
      'scale_x' => array(
        'function' => 'scale',
        'arguments' => array('width' => 20),
        'width' => 20,
        'height' => 10,
        'corners' => $default_corners,
        'tolerance' => 0,
      ),
      'scale_y' => array(
        'function' => 'scale',
        'arguments' => array('height' => 10),
        'width' => 20,
        'height' => 10,
        'corners' => $default_corners,
        'tolerance' => 0,
      ),
      'upscale_x' => array(
        'function' => 'scale',
        'arguments' => array('width' => 80, 'upscale' => TRUE),
        'width' => 80,
        'height' => 40,
        'corners' => $default_corners,
        'tolerance' => 0,
      ),
      'upscale_y' => array(
        'function' => 'scale',
        'arguments' => array('height' => 40, 'upscale' => TRUE),
        'width' => 80,
        'height' => 40,
        'corners' => $default_corners,
        'tolerance' => 0,
      ),
      'crop' => array(
        'function' => 'crop',
        'arguments' => array('x' => 12, 'y' => 4, 'width' => 16, 'height' => 12),
        'width' => 16,
        'height' => 12,
        'corners' => array_fill(0, 4, $this->white),
        'tolerance' => 0,
      ),
      'scale_and_crop' => array(
        'function' => 'scale_and_crop',
        'arguments' => array('width' => 10, 'height' => 8),
        'width' => 10,
        'height' => 8,
        'corners' => array_fill(0, 4, $this->black),
        'tolerance' => 100,
      ),
      'convert_jpg' => array(
        'function' => 'convert',
        'width' => 40,
        'height' => 20,
        'arguments' => array('extension' => 'jpeg'),
        'mimetype' => 'image/jpeg',
        'corners' => $default_corners,
        'tolerance' => 0,
      ),
      'convert_gif' => array(
        'function' => 'convert',
        'width' => 40,
        'height' => 20,
        'arguments' => array('extension' => 'gif'),
        'mimetype' => 'image/gif',
        'corners' => $default_corners,
        'tolerance' => 15,
      ),
      'convert_png' => array(
        'function' => 'convert',
        'width' => 40,
        'height' => 20,
        'arguments' => array('extension' => 'png'),
        'mimetype' => 'image/png',
        'corners' => $default_corners,
        'tolerance' => 5,
      ),
      'rotate_5' => array(
        'function' => 'rotate',
        'arguments' => array('degrees' => 5, 'background' => '#FF00FF'), // Fuchsia background.
        'width' => 41,
        'height' => 23,
        'corners' => array_fill(0, 4, $this->fuchsia),
        'tolerance' => 5,
      ),
      'rotate_minus_10' => array(
        'function' => 'rotate',
        'arguments' => array('degrees' => -10, 'background' => '#FF00FF'), // Fuchsia background.
        'width' => 41,
        'height' => 26,
        'corners' => array_fill(0, 4, $this->fuchsia),
        'tolerance' => 15,
      ),
      'rotate_90' => array(
        'function' => 'rotate',
        'arguments' => array('degrees' => 90, 'background' => '#FF00FF'), // Fuchsia background.
        'width' => 20,
        'height' => 40,
        'corners' => array($this->transparent, $this->red, $this->green, $this->blue),
        'tolerance' => 0,
      ),
      'rotate_transparent_5' => array(
        'function' => 'rotate',
        'arguments' => array('degrees' => 5),
        'width' => 41,
        'height' => 23,
        'corners' => array_fill(0, 4, $this->transparent),
        'tolerance' => 0,
      ),
      'rotate_transparent_90' => array(
        'function' => 'rotate',
        'arguments' => array('degrees' => 90),
        'width' => 20,
        'height' => 40,
        'corners' => array($this->transparent, $this->red, $this->green, $this->blue),
        'tolerance' => 0,
      ),
      'desaturate' => array(
        'function' => 'desaturate',
        'arguments' => array(),
        'height' => 20,
        'width' => 40,
        // Grayscale corners are a bit funky. Each of the corners are a shade of
        // gray. The values of these were determined simply by looking at the
        // final image to see what desaturated colors end up being.
        'corners' => array(
          array_fill(0, 3, 76) + array(3 => 0),
          array_fill(0, 3, 149) + array(3 => 0),
          array_fill(0, 3, 29) + array(3 => 0),
          array_fill(0, 3, 225) + array(3 => 127),
        ),
        // @todo tolerence here is too high. Check reasons.
        'tolerance' => 17000,
      ),
    );

    // Prepare a copy of test files.
    $this->drupalGetTestFiles('image');

    foreach ($files as $file) {
      foreach ($operations as $op => $values) {
        // Load up a fresh image.
        $image = $this->imageFactory->get('public://' . $file);
        if (!$image->isValid()) {
          $this->fail("Could not load image $file.");
          continue 2;
        }

        // Check that no multi-frame information is set.
        $this->assertNull($image->getToolkit()->getFrames());

        // Perform our operation.
        $image->apply($values['function'], $values['arguments']);

        // Save image.
        $file_path = $this->testDirectory . '/' . $op . substr($file, -4);
        $image->save($file_path);

        // Reload with GD to be able to check results at pixel level.
        $image = $this->imageFactory->get($file_path, 'gd');
        $toolkit = $image->getToolkit();

        // Check MIME type if needed.
        if (isset($values['mimetype'])) {
          $this->assertEqual($values['mimetype'], $toolkit->getMimeType(), "Image '$file' after '$op' action has proper MIME type ({$values['mimetype']}).");
        }

        // To keep from flooding the test with assert values, make a general
        // value for whether each group of values fail.
        $correct_dimensions_real = TRUE;
        $correct_dimensions_object = TRUE;

        // Check the real dimensions of the image first.
        $actual_toolkit_width = imagesx($toolkit->getResource());
        $actual_toolkit_height = imagesy($toolkit->getResource());
        if ($actual_toolkit_height != $values['height'] || $actual_toolkit_width != $values['width']) {
          $correct_dimensions_real = FALSE;
        }

        // Check that the image object has an accurate record of the dimensions.
        $actual_image_width = $image->getWidth();
        $actual_image_height = $image->getHeight();
        if ($actual_image_width != $values['width'] || $actual_image_height != $values['height']) {
          $correct_dimensions_object = FALSE;
        }

        $this->assertTrue($correct_dimensions_real, "Image '$file' after '$op' action has proper dimensions. Expected {$values['width']}x{$values['height']}, actual {$actual_toolkit_width}x{$actual_toolkit_height}.");
        $this->assertTrue($correct_dimensions_object, "Image '$file' object after '$op' action is reporting the proper height and width values.  Expected {$values['width']}x{$values['height']}, actual {$actual_image_width}x{$actual_image_height}.");

        // JPEG colors will always be messed up due to compression.
        if ($image->getToolkit()->getType() != IMAGETYPE_JPEG) {
          // Now check each of the corners to ensure color correctness.
          foreach ($values['corners'] as $key => $corner) {
            // The test gif that does not have transparency has yellow where the
            // others have transparent.
            if ($file === 'image-test-no-transparency.gif' && $corner === $this->transparent && $op != 'rotate_transparent_5') {
              $corner = $this->yellow;
            }
            // The test jpg when converted to other formats has yellow where the
            // others have transparent.
            if ($file === 'image-test.jpg' && $corner === $this->transparent && in_array($op, ['convert_gif', 'convert_png'])) {
              $corner = $this->yellow;
            }
            // Get the location of the corner.
            switch ($key) {
              case 0:
                $x = 0;
                $y = 0;
                break;

              case 1:
                $x = $image->getWidth() - 1;
                $y = 0;
                break;

              case 2:
                $x = $image->getWidth() - 1;
                $y = $image->getHeight() - 1;
                break;

              case 3:
                $x = 0;
                $y = $image->getHeight() - 1;
                break;

            }
            $color = $this->getPixelColor($image, $x, $y);
            $correct_colors = $this->colorsAreClose($color, $corner, $values['tolerance']);
            $this->assertTrue($correct_colors, "Image '$file' object after '$op' action has the correct color placement at corner $key.");
          }
        }
      }
    }

    // Test creation of image from scratch, and saving to storage.
    foreach (array(IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_JPEG) as $type) {
      $image = $this->imageFactory->get();
      $image->createNew(50, 20, image_type_to_extension($type, FALSE), '#ffff00');
      $file = 'from_null' . image_type_to_extension($type);
      $file_path = $this->testDirectory . '/' . $file;
      $this->assertEqual(50, $image->getWidth(), "Image file '$file' has the correct width.");
      $this->assertEqual(20, $image->getHeight(), "Image file '$file' has the correct height.");
      $this->assertEqual(image_type_to_mime_type($type), $image->getMimeType(), "Image file '$file' has the correct MIME type.");
      $this->assertTrue($image->save($file_path), "Image '$file' created anew from a null image was saved.");

      // Reload saved image.
      $image_reloaded = $this->imageFactory->get($file_path, 'gd');
      if (!$image_reloaded->isValid()) {
        $this->fail("Could not load image '$file'.");
        continue;
      }
      $this->assertEqual(50, $image_reloaded->getWidth(), "Image file '$file' has the correct width.");
      $this->assertEqual(20, $image_reloaded->getHeight(), "Image file '$file' has the correct height.");
      $this->assertEqual(image_type_to_mime_type($type), $image_reloaded->getMimeType(), "Image file '$file' has the correct MIME type.");
      if ($image_reloaded->getToolkit()->getType() == IMAGETYPE_GIF) {
        $this->assertEqual('#ffff00', $image_reloaded->getToolkit()->getTransparentColor(), "Image file '$file' has the correct transparent color channel set.");
      }
      else {
        $this->assertEqual(NULL, $image_reloaded->getToolkit()->getTransparentColor(), "Image file '$file' has no color channel set.");
      }
    }

    // Test failures of CreateNew.
    $image = $this->imageFactory->get();
    $image->createNew(-50, 20);
    $this->assertFalse($image->isValid(), 'CreateNew with negative width fails.');
    $image->createNew(50, 20, 'foo');
    $this->assertFalse($image->isValid(), 'CreateNew with invalid extension fails.');
    $image->createNew(50, 20, 'gif', '#foo');
    $this->assertFalse($image->isValid(), 'CreateNew with invalid color hex string fails.');
    $image->createNew(50, 20, 'gif', '#ff0000');
    $this->assertTrue($image->isValid(), 'CreateNew with valid arguments validates the Image.');

    // Test saving image files with filenames having non-ascii characters.

    $file_names = [
      'greek εικόνα δοκιμής.png',
      'russian Тестовое изображение.png',
      'simplified chinese 测试图片.png',
      'japanese 試験画像.png',
      'arabic صورة الاختبار.png',
      'armenian փորձարկման պատկերը.png',
      'bengali পরীক্ষা ইমেজ.png',
      'hebraic תמונת בדיקה.png',
      'hindi परीक्षण छवि.png',
      'viet hình ảnh thử nghiệm.png',
      'viet \'with quotes\' hình ảnh thử nghiệm.png',
      'viet "with double quotes" hình ảnh thử nghiệm.png',
    ];
    foreach ($file_names as $file) {
      $file_path = $this->testDirectory . '/' . $file;
      $image->save($file_path);
      $image_reloaded = $this->imageFactory->get($file_path);
      $this->assertTrue($image_reloaded->isValid(), "Image file '$file' loaded successfully.");
    }

    // Test handling a file stored through a remote stream wrapper.

    $image = $this->imageFactory->get('dummy-remote://image-test.png');
    // Source file should be equal to the copied local temp source file.
    $this->assertEqual(filesize('dummy-remote://image-test.png'), filesize($image->getToolkit()->getSourceLocalPath()));
    $image->desaturate();
    $image->save('dummy-remote://remote-image-test.png');
    // Destination file should exists, and destination local temp file should
    // have been reset.
    $this->assertTrue(file_exists($image->getToolkit()->getDestination()));
    $this->assertEqual('dummy-remote://remote-image-test.png', $image->getToolkit()->getDestination());
    $this->assertIdentical('', $image->getToolkit()->getDestinationLocalPath());

    // Test retrieval of EXIF information.

    // The image files that will be tested.
    $image_files = [
      [
        'path' => drupal_get_path('module', 'imagemagick') . '/misc/test-exif.jpeg',
        'orientation' => 8,
      ],
      [
        'path' => 'public://image-test.jpg',
        'orientation' => NULL,
      ],
      [
        'path' => 'public://image-test.png',
        'orientation' => NULL,
      ],
      [
        'path' => 'public://image-test.gif',
        'orientation' => NULL,
      ],
      [
        'path' => NULL,
        'orientation' => NULL,
      ],
    ];

    foreach($image_files as $image_file) {
      // Get image using 'identify'.
      \Drupal::configFactory()->getEditable('imagemagick.settings')
        ->set('use_identify', TRUE)
        ->save();
      $image = $this->imageFactory->get($image_file['path']);
      $this->assertIdentical($image_file['orientation'], $image->getToolkit()->getExifOrientation());

      // Get image using 'getimagesize'.
      \Drupal::configFactory()->getEditable('imagemagick.settings')
        ->set('use_identify', FALSE)
        ->save();
      $image = $this->imageFactory->get($image_file['path']);
      $this->assertIdentical($image_file['orientation'], $image->getToolkit()->getExifOrientation());
    }

    // Test multi-frame GIF image.

    // The image files that will be tested.
    $image_files = [
      [
        'source' => drupal_get_path('module', 'imagemagick') . '/misc/test-multi-frame.gif',
        'destination' => $this->testDirectory . '/test-multi-frame.gif',
        'width' => 60,
        'height' => 29,
        'frames' => 13,
        'scaled_width' => 30,
        'scaled_height' => 15,
        'rotated_width' => 33,
        'rotated_height' => 26,
      ],
    ];

    foreach($image_files as $image_file) {
      // Get image using 'identify'.
      \Drupal::configFactory()->getEditable('imagemagick.settings')
        ->set('use_identify', TRUE)
        ->save();
      $image = $this->imageFactory->get($image_file['source']);
      $this->assertIdentical($image_file['width'], $image->getWidth());
      $this->assertIdentical($image_file['height'], $image->getHeight());
      $this->assertIdentical($image_file['frames'], $image->getToolkit()->getFrames());

      // Scaling should preserve frames.
      $image->scale(30);
      $image->save($image_file['destination']);
      $image = $this->imageFactory->get($image_file['destination']);
      $this->assertIdentical($image_file['scaled_width'], $image->getWidth());
      $this->assertIdentical($image_file['scaled_height'], $image->getHeight());
      $this->assertIdentical($image_file['frames'], $image->getToolkit()->getFrames());

      // Rotating should preserve frames.
      $image->rotate(24);
      $image->save($image_file['destination']);
      $image = $this->imageFactory->get($image_file['destination']);
      $this->assertIdentical($image_file['rotated_width'], $image->getWidth());
      $this->assertIdentical($image_file['rotated_height'], $image->getHeight());
      $this->assertIdentical($image_file['frames'], $image->getToolkit()->getFrames());

      // Converting to PNG should drop frames.
      $image->convert('png');
      $this->assertNull($image->getToolkit()->getFrames());
      $image->save($image_file['destination']);
      $image = $this->imageFactory->get($image_file['destination']);
      $this->assertIdentical($image_file['rotated_width'], $image->getWidth());
      $this->assertIdentical($image_file['rotated_height'], $image->getHeight());
      $this->assertNull($image->getToolkit()->getFrames());
    }
  }

  /**
   * Test ImageMagick subform and settings.
   */
  public function testFormAndSettings() {
    // Change the toolkit.
    \Drupal::configFactory()->getEditable('system.image')
      ->set('toolkit', 'imagemagick')
      ->save();

    // Test form is accepting wrong binaries path while setting toolkit to GD.
    $this->drupalGet('admin/config/media/image-toolkit');
    $this->assertFieldByName('image_toolkit', 'imagemagick');
    $edit = [
      'image_toolkit' => 'gd',
      'imagemagick[suite][path_to_binaries]' => '/foo/bar',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertFieldByName('image_toolkit', 'gd');

    // Change the toolkit.
    \Drupal::configFactory()->getEditable('system.image')
      ->set('toolkit', 'imagemagick')
      ->save();
    $this->imageFactory->setToolkitId('imagemagick');
    $this->assertEqual('imagemagick', $this->imageFactory->getToolkitId());

    // Test default supported image extensions.
    $this->assertEqual('gif jpe jpeg jpg png', implode(' ', $this->imageFactory->getSupportedExtensions()));

    $config = \Drupal::configFactory()->getEditable('imagemagick.settings');

    // Enable TIFF.
    $image_formats = $config->get('image_formats');
    $image_formats['TIFF']['enabled'] = true;
    $config->set('image_formats', $image_formats)->save();
    $this->assertEqual('gif jpe jpeg jpg png tif tiff', implode(' ', $this->imageFactory->getSupportedExtensions()));

    // Disable PNG.
    $image_formats['PNG']['enabled'] = false;
    $config->set('image_formats', $image_formats)->save();
    $this->assertEqual('gif jpe jpeg jpg tif tiff', implode(' ', $this->imageFactory->getSupportedExtensions()));

    // Disable some extensions.
    $image_formats['TIFF']['exclude_extensions'] = 'tif, gif';
    $config->set('image_formats', $image_formats)->save();
    $this->assertEqual('gif jpe jpeg jpg tiff', implode(' ', $this->imageFactory->getSupportedExtensions()));
    $image_formats['JPEG']['exclude_extensions'] = 'jpe, jpg';
    $config->set('image_formats', $image_formats)->save();
    $this->assertEqual('gif jpeg tiff', implode(' ', $this->imageFactory->getSupportedExtensions()));
  }

  /**
   * Function to compare two colors by RGBa, within a tolerance.
   *
   * Very basic, just compares the sum of the squared differences for each of
   * the R, G, B, a components of two colors against a 'tolerance' value.
   *
   * @param int[] $color_a
   *   An RGBa array.
   * @param int[] $color_b
   *   An RGBa array.
   * @param int $tolerance
   *   The accepteable difference between the colors.
   *
   * @return bool
   *   TRUE if the colors differences are within tolerance, FALSE otherwise.
   */
  protected function colorsAreClose(array $color_a, array $color_b, $tolerance) {
    // Fully transparent colors are equal, regardless of RGB.
    if ($color_a[3] == 127 && $color_b[3] == 127) {
      return TRUE;
    }
    $distance = pow(($color_a[0] - $color_b[0]), 2) + pow(($color_a[1] - $color_b[1]), 2) + pow(($color_a[2] - $color_b[2]), 2) + pow(($color_a[3] - $color_b[3]), 2);
    if ($distance > $tolerance) {
      debug("Color A: {" . implode(',', $color_a) . "}, Color B: {" . implode(',', $color_b) . "}, Distance: " . $distance . ", Tolerance: " . $tolerance);
      return FALSE;
    }
    return TRUE;
  }

}
