<?php

namespace Drupal\Tests\search_api\Unit\Plugin\Processor;

use Drupal\search_api\Plugin\search_api\data_type\value\TextToken;
use Drupal\search_api\Plugin\search_api\data_type\value\TextValue;
use Drupal\search_api\Query\Condition;
use Drupal\search_api\Utility\Utility;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the base class for fields-based processors.
 *
 * @coversDefaultClass \Drupal\search_api\Processor\FieldsProcessorPluginBase
 *
 * @group search_api
 */
class FieldsProcessorPluginBaseTest extends UnitTestCase {

  use TestItemsTrait;

  /**
   * A search index mock to use in this test case.
   *
   * @var \Drupal\search_api\IndexInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $index;

  /**
   * The class under test.
   *
   * @var \Drupal\Tests\search_api\Unit\Plugin\Processor\TestFieldsProcessorPlugin
   */
  protected $processor;

  /**
   * Creates a new processor object for use in the tests.
   */
  public function setUp() {
    parent::setUp();

    $this->setUpMockContainer();
    $this->index = $this->getMock('Drupal\search_api\IndexInterface');
    $this->index->expects($this->any())
      ->method('status')
      ->will($this->returnValue(TRUE));
    $items = $this->getTestItem();
    $fields = $items[$this->itemIds[0]]->getFields();
    $this->index->expects($this->any())
      ->method('getFields')
      ->will($this->returnValue($fields));

    $this->processor = new TestFieldsProcessorPlugin(array('#index' => $this->index), '', array());
  }

  /**
   * Tests whether the processor handles field ID changes correctly.
   */
  public function testFieldRenaming() {
    $configuration['fields'] = array('text_field', 'float_field');
    $this->processor->setConfiguration($configuration);

    $this->index->method('getFieldRenames')
      ->willReturn(array(
        'text_field' => 'foobar',
      ));

    $this->processor->preIndexSave();

    $fields = $this->processor->getConfiguration()['fields'];
    sort($fields);
    $this->assertEquals(array('float_field', 'foobar'), $fields);
  }

  /**
   * Tests whether the default implementation of testType() works correctly.
   */
  public function testTestTypeDefault() {
    $items = $this->getTestItem();
    $this->processor->preprocessIndexItems($items);
    $this->assertFieldsProcessed($items, array('text_field', 'string_field'));
  }

  /**
   * Tests whether overriding of testType() works correctly.
   */
  public function testTestTypeOverride() {
    $override = function ($type) {
      return Utility::isTextType($type, array('string', 'integer'));
    };
    $this->processor->setMethodOverride('testType', $override);

    $items = $this->getTestItem();
    $this->processor->preprocessIndexItems($items);
    $this->assertFieldsProcessed($items, array('string_field', 'integer_field'));
  }

  /**
   * Tests whether selecting fields works correctly.
   */
  public function testTestField() {
    // testType() shouldn't have any effect anymore when fields are configured.
    $override = function () {
      return FALSE;
    };
    $this->processor->setMethodOverride('testType', $override);
    $configuration['fields'] = array('text_field', 'float_field');
    $this->processor->setConfiguration($configuration);

    $items = $this->getTestItem();
    $this->processor->preprocessIndexItems($items);
    $this->assertFieldsProcessed($items, array('text_field', 'float_field'));
  }

  /**
   * Tests whether overriding of processFieldValue() works correctly.
   */
  public function testProcessFieldValueOverride() {
    $override = function (&$value, &$type) {
      // Check whether the passed $type matches the one included in the value.
      if (strpos($value, "{$type}_field") !== FALSE) {
        $value = "&$value";
      }
      else {
        $value = "/$value";
      }
    };
    $this->processor->setMethodOverride('processFieldValue', $override);

    $items = $this->getTestItem();
    $this->processor->preprocessIndexItems($items);
    $this->assertFieldsProcessed($items, array('text_field', 'string_field'), '&');
  }

  /**
   * Tests whether removing values in processFieldValue() works correctly.
   */
  public function testProcessFieldRemoveValue() {
    $override = function (&$value) {
      if ($value != 'bar') {
        $value = "*$value";
      }
      else {
        $value = '';
      }
    };
    $this->processor->setMethodOverride('processFieldValue', $override);

    $fields = array(
      'field1' => array(
        'type' => 'string',
        'values' => array(
          'foo',
          'bar',
        ),
      ),
    );
    $items = $this->createItems($this->index, 1, $fields);

    $this->processor->preprocessIndexItems($items);

    $item_fields = $items[$this->itemIds[0]]->getFields();
    $this->assertEquals(array('*foo'), $item_fields['field1']->getValues(), 'Field value was correctly removed.');
  }

  /**
   * Tests whether the processField() method operates correctly.
   */
  public function testProcessFieldsTokenized() {
    $override = function (&$value, $type) {
      switch ($type) {
        case 'integer':
          ++$value;
          return;

        case 'string':
          $value = "++$value";
          return;
      }

      if (strpos($value, ' ')) {
        $value = TestFieldsProcessorPlugin::createTokenizedText($value, 4)->getTokens();
      }
      elseif ($value == 'bar') {
        $value = TestFieldsProcessorPlugin::createTokenizedText('*bar', 2)->getTokens();
      }
      elseif ($value == 'baz') {
        $value = '';
      }
      else {
        $value = "*$value";
      }
    };
    $this->processor->setMethodOverride('processFieldValue', $override);

    $value = TestFieldsProcessorPlugin::createTokenizedText('foobar baz', 3);
    $tokens = $value->getTokens();
    $tokens[] = new TextToken('foo bar', 2);
    $value->setTokens($tokens);
    $fields = array(
      'field1' => array(
        'type' => 'text',
        'values' => array(
          TestFieldsProcessorPlugin::createTokenizedText('foo bar baz', 3),
          $value,
          new TextValue('foo'),
          new TextValue('foo bar'),
          new TextValue('bar'),
          new TextValue('baz'),
        ),
      ),
      'field2' => array(
        'type' => 'integer',
        'values' => array(
          1,
          3,
        ),
      ),
      'field3' => array(
        'type' => 'string',
        'values' => array(
          'foo',
          'foo bar baz',
        ),
      ),
    );
    $items = $this->createItems($this->index, 1, $fields);

    $this->processor->setConfiguration(array(
      'fields' => array('field1', 'field2', 'field3'),
    ));

    $this->processor->preprocessIndexItems($items);

    $fields = $items[$this->itemIds[0]]->getFields();

    /** @var \Drupal\search_api\Plugin\search_api\data_type\value\TextValueInterface[] $values */
    $values = $fields['field1']->getValues();
    $summary = array();
    foreach ($values as $i => $value) {
      $summary[$i]['text'] = $value->toText();
      $tokens = $value->getTokens();
      if ($tokens !== NULL) {
        $summary[$i]['tokens'] = array();
        foreach ($tokens as $token) {
          $summary[$i]['tokens'][] = array(
            'text' => $token->getText(),
            'boost' => $token->getBoost(),
          );
        }
      }
    }
    $expected = array(
      array(
        'text' => '*foo *bar',
        'tokens' => array(
          array(
            'text' => '*foo',
            'boost' => 3,
          ),
          array(
            'text' => '*bar',
            'boost' => 6,
          ),
        ),
      ),
      array(
        'text' => '*foobar foo bar',
        'tokens' => array(
          array(
            'text' => '*foobar',
            'boost' => 3,
          ),
          array(
            'text' => 'foo',
            'boost' => 8,
          ),
          array(
            'text' => 'bar',
            'boost' => 8,
          ),
        ),
      ),
      array(
        'text' => '*foo',
      ),
      array(
        'text' => 'foo bar',
        'tokens' => array(
          array(
            'text' => 'foo',
            'boost' => 4,
          ),
          array(
            'text' => 'bar',
            'boost' => 4,
          ),
        ),
      ),
      array(
        'text' => '*bar',
        'tokens' => array(
          array(
            'text' => '*bar',
            'boost' => 2,
          ),
        ),
      ),
    );
    $this->assertEquals($expected, $summary);

    $expected = array(2, 4);
    $this->assertEquals('integer', $fields['field2']->getType());
    $this->assertEquals($expected, $fields['field2']->getValues());

    $expected = array('++foo', '++foo bar baz');
    $this->assertEquals('string', $fields['field3']->getType());
    $this->assertEquals($expected, $fields['field3']->getValues());
  }

  /**
   * Tests whether preprocessing of queries without search keys works correctly.
   */
  public function testProcessKeysNoKeys() {
    $query = Utility::createQuery($this->index);

    $this->processor->preprocessSearchQuery($query);

    $this->assertNull($query->getKeys(), 'Query without keys was correctly ignored.');
  }

  /**
   * Tests whether preprocessing of simple search keys works correctly.
   */
  public function testProcessKeysSimple() {
    $query = Utility::createQuery($this->index);
    $keys = &$query->getKeys();
    $keys = 'foo';

    $this->processor->preprocessSearchQuery($query);

    $this->assertEquals('*foo', $query->getKeys(), 'Search keys were correctly preprocessed.');
  }

  /**
   * Tests whether preprocessing of complex search keys works correctly.
   */
  public function testProcessKeysComplex() {
    $query = Utility::createQuery($this->index);
    $keys = &$query->getKeys();
    $keys = array(
      '#conjunction' => 'OR',
      'foo',
      array(
        '#conjunction' => 'AND',
        'bar',
        'baz',
        '#negation' => TRUE,
      ),
    );

    $this->processor->preprocessSearchQuery($query);

    $expected = array(
      '#conjunction' => 'OR',
      '*foo',
      array(
        '#conjunction' => 'AND',
        '*bar',
        '*baz',
        '#negation' => TRUE,
      ),
    );
    $this->assertEquals($expected, $query->getKeys(), 'Search keys were correctly preprocessed.');
  }

  /**
   * Tests whether overriding of processKey() works correctly.
   */
  public function testProcessKeyOverride() {
    $override = function (&$value) {
      if ($value != 'baz') {
        $value = "&$value";
      }
      else {
        $value = '';
      }
    };
    $this->processor->setMethodOverride('processKey', $override);

    $query = Utility::createQuery($this->index);
    $keys = &$query->getKeys();
    $keys = array(
      '#conjunction' => 'OR',
      'foo',
      array(
        '#conjunction' => 'AND',
        'bar',
        'baz',
        '#negation' => TRUE,
      ),
    );

    $this->processor->preprocessSearchQuery($query);

    $expected = array(
      '#conjunction' => 'OR',
      '&foo',
      array(
        '#conjunction' => 'AND',
        '&bar',
        '#negation' => TRUE,
      ),
    );
    $this->assertEquals($expected, $query->getKeys(), 'Search keys were correctly preprocessed.');
  }

  /**
   * Tests whether preprocessing search conditions works correctly.
   */
  public function testProcessConditions() {
    $query = Utility::createQuery($this->index);
    $query->addCondition('text_field', 'foo');
    $query->addCondition('text_field', array('foo', 'bar'), 'IN');
    $query->addCondition('string_field', NULL, '<>');
    $query->addCondition('integer_field', 'bar');

    $this->processor->preprocessSearchQuery($query);

    $expected = array(
      new Condition('text_field', '*foo'),
      new Condition('text_field', array('*foo', '*bar'), 'IN'),
      new Condition('string_field', 'undefined', '<>'),
      new Condition('integer_field', 'bar'),
    );
    $this->assertEquals($expected, $query->getConditionGroup()->getConditions(), 'Conditions were preprocessed correctly.');
  }

  /**
   * Tests whether preprocessing nested search conditions works correctly.
   */
  public function testProcessConditionsNestedConditions() {
    $query = Utility::createQuery($this->index);
    $conditions = $query->createConditionGroup();
    $conditions->addCondition('text_field', 'foo');
    $conditions->addCondition('text_field', array('foo', 'bar'), 'IN');
    $conditions->addCondition('string_field', NULL, '<>');
    $conditions->addCondition('integer_field', 'bar');
    $query->addConditionGroup($conditions);

    $this->processor->preprocessSearchQuery($query);

    $expected = array(
      new Condition('text_field', '*foo'),
      new Condition('text_field', array('*foo', '*bar'), 'IN'),
      new Condition('string_field', 'undefined', '<>'),
      new Condition('integer_field', 'bar'),
    );
    $this->assertEquals($expected, $query->getConditionGroup()->getConditions()[0]->getConditions(), 'Conditions were preprocessed correctly.');
  }

  /**
   * Tests whether overriding processConditionValue() works correctly.
   */
  public function testProcessConditionValueOverride() {
    $override = function (&$value) {
      if (isset($value)) {
        $value = '';
      }
    };
    $this->processor->setMethodOverride('processConditionValue', $override);

    $query = Utility::createQuery($this->index);
    $query->addCondition('text_field', 'foo');
    $query->addCondition('string_field', NULL, '<>');
    $query->addCondition('integer_field', 'bar');

    $this->processor->preprocessSearchQuery($query);

    $expected = array(
      new Condition('string_field', NULL, '<>'),
      new Condition('integer_field', 'bar'),
    );
    $this->assertEquals($expected, array_merge($query->getConditionGroup()->getConditions()), 'Conditions were preprocessed correctly.');
  }

  /**
   * Tests whether overriding processConditionValue() works correctly.
   */
  public function testProcessConditionValueArrayHandling() {
    $override = function (&$value) {
      $length = strlen($value);
      if ($length == 2) {
        $value = '';
      }
      elseif ($length == 3) {
        $value .= '*';
      }
    };
    $this->processor->setMethodOverride('process', $override);

    $query = Utility::createQuery($this->index);
    $query->addCondition('text_field', array('a', 'b'), 'NOT IN');
    $query->addCondition('text_field', array('a', 'bo'), 'IN');
    $query->addCondition('text_field', array('ab', 'bo'), 'NOT IN');
    $query->addCondition('text_field', array('a', 'bo'), 'BETWEEN');
    $query->addCondition('text_field', array('ab', 'bo'), 'NOT BETWEEN');
    $query->addCondition('text_field', array('a', 'bar'), 'IN');
    $query->addCondition('text_field', array('abo', 'baz'), 'BETWEEN');

    $this->processor->preprocessSearchQuery($query);

    $expected = array(
      new Condition('text_field', array('a', 'b'), 'NOT IN'),
      new Condition('text_field', array('a'), 'IN'),
      new Condition('text_field', array('a', 'bo'), 'BETWEEN'),
      new Condition('text_field', array('ab', 'bo'), 'NOT BETWEEN'),
      new Condition('text_field', array('a', 'bar*'), 'IN'),
      new Condition('text_field', array('abo*', 'baz*'), 'BETWEEN'),
    );
    $this->assertEquals($expected, array_merge($query->getConditionGroup()->getConditions()), 'Conditions were preprocessed correctly.');
  }

  /**
   * Returns an array with one test item suitable for this test case.
   *
   * @param string[]|null $types
   *   (optional) The types of fields to create. Defaults to using "text",
   *   "string", "integer" and "float".
   *
   * @return \Drupal\search_api\Item\ItemInterface[]
   *   An array containing one item.
   */
  protected function getTestItem($types = NULL) {
    if ($types === NULL) {
      $types = array('text', 'string', 'integer', 'float');
    }

    $fields = array();
    foreach ($types as $type) {
      $field_id = "{$type}_field";
      $fields[$field_id] = array(
        'type' => $type,
        'values' => array(
          "$field_id value 1",
          "$field_id value 2",
        ),
      );
    }
    return $this->createItems($this->index, 1, $fields);
  }

  /**
   * Asserts that the given fields have been correctly processed.
   *
   * @param \Drupal\search_api\Item\ItemInterface[] $items
   *   An array containing one item.
   * @param string[] $processed_fields
   *   The fields which should be processed.
   * @param string $prefix
   *   (optional) The prefix that processed fields receive.
   */
  protected function assertFieldsProcessed(array $items, array $processed_fields, $prefix = "*") {
    $processed_fields = array_fill_keys($processed_fields, TRUE);
    foreach ($items as $item) {
      foreach ($item->getFields() as $field_id => $field) {
        if (!empty($processed_fields[$field_id])) {
          $expected = array(
            "$prefix$field_id value 1",
            "$prefix$field_id value 2",
          );
        }
        else {
          $expected = array(
            "$field_id value 1",
            "$field_id value 2",
          );
        }
        $this->assertEquals($expected, $field->getValues(), "Field $field_id is correct.");
      }
    }
  }

}
