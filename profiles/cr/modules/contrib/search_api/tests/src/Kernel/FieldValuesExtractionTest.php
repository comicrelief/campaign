<?php

namespace Drupal\Tests\search_api\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Utility\Utility;
use Drupal\user\Entity\Role;

/**
 * Tests extraction of field values, as used during indexing.
 *
 * @coversDefaultClass \Drupal\search_api\Utility\FieldsHelper
 *
 * @group search_api
 */
class FieldValuesExtractionTest extends KernelTestBase {

  /**
   * The search index used for testing.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * The test entities used in this test.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities = array();

  /**
   * The fields helper service.
   *
   * @var \Drupal\search_api\Utility\FieldsHelperInterface
   */
  protected $fieldsHelper;

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = array(
    'entity_test',
    'field',
    'search_api',
    'search_api_test_extraction',
    'user',
  );

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installEntitySchema('entity_test_mulrev_changed');
    $this->installConfig(array('search_api_test_extraction'));
    $entity_storage = \Drupal::entityTypeManager()->getStorage('entity_test_mulrev_changed');

    $this->entities[0] = $entity_storage->create(array(
      'type' => 'article',
      'name' => 'Article 1',
      'links' => array(),
    ));
    $this->entities[0]->save();
    $this->entities[1] = $entity_storage->create(array(
      'type' => 'article',
      'name' => 'Article 2',
      'links' => array(),
    ));
    $this->entities[1]->save();
    $this->entities[2] = $entity_storage->create(array(
      'type' => 'article',
      'name' => 'Article 3',
      'links' => array(
        array('target_id' => $this->entities[0]->id()),
        array('target_id' => $this->entities[1]->id()),
      ),
    ));
    $this->entities[2]->save();
    $this->entities[3] = $entity_storage->create(array(
      'type' => 'article',
      'name' => 'Article 4',
      'links' => array(
        array('target_id' => $this->entities[0]->id()),
        array('target_id' => $this->entities[2]->id()),
      ),
    ));
    $this->entities[2]->save();

    Role::create(array(
      'id' => 'anonymous',
      'label' => 'anonymous',
    ))->save();
    user_role_grant_permissions('anonymous', array('view test entity'));

    $this->index = Index::create(array(
      'field_settings' => array(
        'foo' => array(
          'type' => 'text',
          'datasource_id' => 'entity:entity_test_mulrev_changed',
          'property_path' => 'name',
        ),
        'bar' => array(
          'type' => 'text',
          'property_path' => 'rendered_item',
          'configuration' => array(
            'roles' => array(
              'anonymous' => 'anonymous',
            ),
            'view_mode' => array(
              'entity:entity_test_mulrev_changed' => array(
                'article' => 'default',
              ),
            ),
          ),
        ),
      ),
      'datasource_settings' => array(
        'entity:entity_test_mulrev_changed' => array(
          'plugin_id' => 'entity:entity_test_mulrev_changed',
          'settings' => array(),
        ),
      ),
    ));

    $this->fieldsHelper = $this->container->get('search_api.fields_helper');
  }

  /**
   * Tests extraction of field values, as used during indexing.
   *
   * @covers ::extractFields
   * @covers ::extractField
   * @covers ::extractFieldValues
   */
  public function testFieldValuesExtraction() {
    $object = $this->entities[3]->getTypedData();
    /** @var \Drupal\search_api\Item\FieldInterface[][] $fields */
    $fields = array(
      'type' => array($this->fieldsHelper->createField($this->index, 'type')),
      'name' => array($this->fieldsHelper->createField($this->index, 'name')),
      'links:entity:name' => array(
        $this->fieldsHelper->createField($this->index, 'links'),
        $this->fieldsHelper->createField($this->index, 'links_1'),
      ),
      'links:entity:links:entity:name' => array(
        $this->fieldsHelper->createField($this->index, 'links_links'),
      ),
    );
    $this->fieldsHelper->extractFields($object, $fields);

    $values = array();
    foreach ($fields as $property_path => $property_fields) {
      foreach ($property_fields as $field) {
        $field_values = $field->getValues();
        sort($field_values);
        if (!isset($values[$property_path])) {
          $values[$property_path] = $field_values;
        }
        else {
          $this->assertEquals($field_values, $values[$property_path], 'Second extraction provided the same results as the first.');
        }
      }
    }

    $expected = array(
      'type' => array('article'),
      'name' => array('Article 4'),
      'links:entity:name' => array(
        'Article 1',
        'Article 3',
      ),
      'links:entity:links:entity:name' => array(
        'Article 1',
        'Article 2',
      ),
    );
    $this->assertEquals($expected, $values, 'Field values were correctly extracted');
  }

  /**
   * Tests extraction of properties, as used in processors or for result lists.
   *
   * @covers ::extractItemValues
   */
  public function testPropertyValuesExtraction() {
    $items['foobar'] = $this->fieldsHelper->createItemFromObject(
      $this->index,
      $this->entities[0]->getTypedData(),
      Utility::createCombinedId('entity:entity_test_mulrev_changed', '0:en')
    );

    $properties = array(
      NULL => array(
        'rendered_item' => 'a',
        // Since there is no field defined on "aggregated_field" for the index,
        // we won't be able to extract it.
        'aggregated_field' => 'b',
        'search_api_url' => 'c',
      ),
      'entity:entity_test_mulrev_changed' => array(
        'name' => 'd',
        'type' => 'e',
      ),
      'unknown_datasource' => array(
        'name' => 'x',
      ),
    );

    $expected = array(
      'foobar' => array(
        'a' => array(),
        'b' => array(),
        'c' => array(),
        'd' => array(),
        'e' => array(),
      ),
    );
    $values = $this->fieldsHelper->extractItemValues($items, $properties, FALSE);
    ksort($values['foobar']);
    $this->assertEquals($expected, $values);

    $expected = array(
      'foobar' => array(
        'b' => array(),
        'c' => array('/entity_test_mulrev_changed/manage/1'),
        'd' => array('Article 1'),
        'e' => array('article'),
      ),
    );
    $values = $this->fieldsHelper->extractItemValues($items, $properties);
    ksort($values['foobar']);
    $this->assertArrayHasKey('a', $values['foobar']);
    $this->assertNotEmpty($values['foobar']['a']);
    $this->assertContains('Article 1', $values['foobar']['a'][0]);
    unset($values['foobar']['a']);
    $this->assertEquals($expected, $values);

    $items['foobar']->setFields(array(
      'aa' => $this->fieldsHelper->createField($this->index, 'aa_foo', array(
        'property_path' => 'aggregated_field',
        'values' => array(1, 2),
      )),
      'bb' => $this->fieldsHelper->createField($this->index, 'aa_foo', array(
        'property_path' => 'rendered_item',
        'values' => array(3),
      )),
      'cc' => $this->fieldsHelper->createField($this->index, 'aa_foo', array(
        'datasource_id' => 'entity:entity_test_mulrev_changed',
        'property_path' => 'type',
        'values' => array(4),
      )),
    ));

    $expected = array(
      'foobar' => array(
        'a' => array(3),
        'b' => array(1, 2),
        'c' => array(),
        'd' => array(),
        'e' => array(4),
      ),
    );
    $values = $this->fieldsHelper->extractItemValues($items, $properties, FALSE);
    ksort($values['foobar']);
    $this->assertEquals($expected, $values);

    $expected = array(
      'foobar' => array(
        'a' => array(3),
        'b' => array(1, 2),
        'c' => array('/entity_test_mulrev_changed/manage/1'),
        'd' => array('Article 1'),
        'e' => array(4),
      ),
    );
    $values = $this->fieldsHelper->extractItemValues($items, $properties);
    ksort($values['foobar']);
    $this->assertEquals($expected, $values);
  }

}
