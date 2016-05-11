<?php

namespace Drupal\Tests\cdn\Unit;

use Drupal\cdn\CdnSettings;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\cdn\CdnSettings
 * @group cdn
 */
class CdnSettingsTest extends UnitTestCase {

  /**
   * @covers ::getLookupTable
   * @covers ::getDomains
   * @dataProvider settingsProvider
   */
  public function test(array $raw_config, array $expected_lookup_table, array $expected_domains) {
    $cdn_settings = $this->createCdnSettings($raw_config);
    $this->assertTrue($cdn_settings->isEnabled());
    $this->assertSame($expected_lookup_table, $cdn_settings->getLookupTable());
    $this->assertSame(array_values($expected_domains), array_values($cdn_settings->getDomains()));
  }

  public function settingsProvider() {
    return [
      'simple, no conditions' => [
        [
          'status' => 2,
          'mapping' => [
            'type' => 'simple',
            'domain' => 'cdn.example.com',
            'conditions' => [],
          ],
        ],
        ['*' => 'cdn.example.com'],
        ['cdn.example.com'],
      ],
      'simple, oone empty condition' => [
        [
          'status' => 2,
          'mapping' => [
            'type' => 'simple',
            'domain' => 'cdn.example.com',
            'conditions' => [
              'extensions' => [],
            ],
          ],
        ],
        ['*' => 'cdn.example.com'],
        ['cdn.example.com'],
      ],
      'simple, on, one condition' => [
        [
          'status' => 2,
          'mapping' => [
            'type' => 'simple',
            'domain' => 'cdn.example.com',
            'conditions' => [
              'extensions' => ['jpg', 'jpeg', 'png'],
            ],
          ],
        ],
        [
          'jpg' => 'cdn.example.com',
          'jpeg' => 'cdn.example.com',
          'png' => 'cdn.example.com',
        ],
        ['cdn.example.com'],
      ],
      'auto-balanced, on, no fallback' => [
        [
          'status' => 2,
          'mapping' => [
            'type' => 'auto-balanced',
            'domains' => [
              'img1.example.com',
              'img2.example.com',
            ],
            'conditions' => [
              'extensions' => ['jpg', 'png'],
            ]
          ],
        ],
        [
          'jpg' => ['img1.example.com', 'img2.example.com'],
          'png' => ['img1.example.com', 'img2.example.com'],
        ],
        ['img1.example.com', 'img2.example.com'],
      ],
      'complex containing two simple mappings, with fallback' => [
        [
          'status' => 2,
          'mapping' => [
            'type' => 'complex',
            'fallback_domain' => 'cdn.example.com',
            'domains' => [
              0 => [
                'type' => 'simple',
                'domain' => 'static.example.com',
                'conditions' => [
                  'extensions' => ['css', 'jpg', 'jpeg', 'png'],
                ],
              ],
              1 => [
                'type' => 'simple',
                'domain' => 'downloads.example.com',
                'conditions' => [
                  'extensions' => ['zip'],
                ],
              ]
            ],
          ],
        ],
        [
          '*' => 'cdn.example.com',
          'css' => 'static.example.com',
          'jpg' => 'static.example.com',
          'jpeg' => 'static.example.com',
          'png' => 'static.example.com',
          'zip' => 'downloads.example.com',
        ],
        ['cdn.example.com', 'static.example.com', 'downloads.example.com'],
      ],
      'complex containing two simple mappings, without fallback' => [
        [
          'status' => 2,
          'mapping' => [
            'type' => 'complex',
            'fallback_domain' => NULL,
            'domains' => [
              0 => [
                'type' => 'simple',
                'domain' => 'static.example.com',
                'conditions' => [
                  'extensions' => ['css', 'jpg', 'jpeg', 'png'],
                ],
              ],
              1 => [
                'type' => 'simple',
                'domain' => 'downloads.example.com',
                'conditions' => [
                  'extensions' => ['zip'],
                ],
              ]
            ],
          ],
        ],
        [
          'css' => 'static.example.com',
          'jpg' => 'static.example.com',
          'jpeg' => 'static.example.com',
          'png' => 'static.example.com',
          'zip' => 'downloads.example.com',
        ],
        ['static.example.com', 'downloads.example.com'],
      ],
      'complex containing one simple and one auto-balanced mapping, without fallback' => [
        [
          'status' => 2,
          'mapping' => [
            'type' => 'complex',
            'fallback_domain' => NULL,
            'domains' => [
              0 => [
                'type' => 'simple',
                'domain' => 'static.example.com',
                'conditions' => [
                  'extensions' => ['css', 'js'],
                ],
              ],
              1 => [
                'type' => 'auto-balanced',
                'domains' => [
                  'img1.example.com',
                  'img2.example.com',
                ],
                'conditions' => [
                  'extensions' => ['jpg', 'jpeg', 'png'],
                ],
              ]
            ],
          ],
        ],
        [
          'css' => 'static.example.com',
          'js' => 'static.example.com',
          'jpg' => ['img1.example.com', 'img2.example.com'],
          'jpeg' => ['img1.example.com', 'img2.example.com'],
          'png' => ['img1.example.com', 'img2.example.com'],
        ],
        ['static.example.com', 'img1.example.com', 'img2.example.com'],
      ],
    ];
  }

  /**
   * @covers ::getLookupTable
   * @expectedException \AssertionError
   * @expectedExceptionMessage The provided domain http://cdn.example.com is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.
   */
  public function testAbsoluteUrlAsSimpleDomain() {
    $this->createCdnSettings([
      'status' => 2,
      'mapping' => [
        'type' => 'simple',
        'domain' => 'http://cdn.example.com'
      ],
    ])->getLookupTable();
  }

  /**
   * @covers ::getLookupTable
   * @expectedException \AssertionError
   * @expectedExceptionMessage The provided domain //cdn.example.com is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.
   */
  public function testProtocolRelativeUrlAsSimpleDomain() {
    $this->createCdnSettings([
      'status' => 2,
      'mapping' => [
        'type' => 'simple',
        'domain' => '//cdn.example.com'
      ],
    ])->getLookupTable();
  }

  /**
   * @covers ::getLookupTable
   * @expectedException \AssertionError
   * @expectedExceptionMessage The provided fallback domain http://cdn.example.com is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.
   */
  public function testAbsoluteUrlAsComplexFallbackDomain() {
    $this->createCdnSettings([
      'status' => 2,
      'mapping' => [
        'type' => 'complex',
        'fallback_domain' => 'http://cdn.example.com'
      ],
    ])->getLookupTable();
  }

  /**
   * @covers ::getLookupTable
   * @expectedException \AssertionError
   * @expectedExceptionMessage The provided fallback domain //cdn.example.com is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.
   */
  public function testProtocolRelativeUrlAsComplexFallbackDomain() {
    $this->createCdnSettings([
      'status' => 2,
      'mapping' => [
        'type' => 'complex',
        'fallback_domain' => '//cdn.example.com'
      ],
    ])->getLookupTable();
  }

  /**
   * @covers ::getLookupTable
   * @expectedException \AssertionError
   * @expectedExceptionMessage The provided domain http://foo.example.com is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.
   */
  public function testAbsoluteUrlAsAutobalancedDomain() {
    $this->createCdnSettings([
      'status' => 2,
      'mapping' => [
        'type' => 'auto-balanced',
        'domains' => [
          'http://foo.example.com',
          'http://bar.example.com',
        ],
        'conditions' => [
          'extensions' => [
            'png',
          ],
        ],
      ],
    ])->getLookupTable();
  }

  /**
   * @covers ::getLookupTable
   * @expectedException \AssertionError
   * @expectedExceptionMessage The provided domain //foo.example.com is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.
   */
  public function testProtocolRelativeUrlAsAutobalancedDomain() {
    $this->createCdnSettings([
      'status' => 2,
      'mapping' => [
        'type' => 'auto-balanced',
        'domains' => [
          '//foo.example.com',
          '//bar.example.com',
        ],
        'conditions' => [
          'extensions' => [
            'png',
          ],
        ],
      ],
    ])->getLookupTable();
  }

  /**
   * @covers ::getLookupTable
   * @expectedException \Drupal\Core\Config\ConfigValueException
   * @expectedExceptionMessage It does not make sense to apply auto-balancing to all files, regardless of extension.
   */
  public function testAutobalancedWithoutConditions() {
    $this->createCdnSettings([
      'status' => 2,
      'mapping' => [
        'type' => 'auto-balanced',
        'fallback_domain' => NULL,
        'domains' => [
          'foo.example.com',
          'bar.example.com',
        ],
      ],
    ])->getLookupTable();
  }

  /**
   * Creates a CdnSettings object from raw config.
   *
   * @param array $raw_config
   *   The raw config for the cdn.settings.yml config.
   *
   * @return \Drupal\cdn\CdnSettings
   *   The CdnSettings object to test.
   */
  protected function createCdnSettings(array $raw_config) {
    return new CdnSettings($this->getConfigFactoryStub(['cdn.settings' => $raw_config]));
  }

}
