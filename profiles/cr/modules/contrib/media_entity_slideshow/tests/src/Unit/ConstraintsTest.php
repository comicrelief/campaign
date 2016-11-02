<?php

namespace Drupal\Tests\media_entity_slideshow\Unit;

use Drupal\media_entity_slideshow\Plugin\Validation\Constraint\ItemsCountConstraint;
use Drupal\media_entity_slideshow\Plugin\Validation\Constraint\ItemsCountConstraintValidator;
use Drupal\Tests\UnitTestCase;

/**
 * Tests media_entity_slideshow constraints.
 *
 * @group media_entity
 */
class ConstraintsTest extends UnitTestCase {

  /**
   * Tests ItemsCount constraint.
   *
   * @covers \Drupal\media_entity_slideshow\Plugin\Validation\Constraint\ItemsCountConstraintValidator
   * @covers \Drupal\media_entity_slideshow\Plugin\Validation\Constraint\ItemsCountConstraint
   */
  public function testValidation() {
    // Check message in constraint.
    $constraint = new ItemsCountConstraint(['sourceFieldName' => 'test_field']);
    $this->assertEquals('At least one slideshow item must exist.', $constraint->message, 'Correct constraint message found.');

    // Test the validator with valid values.
    $execution_context = $this->getMockBuilder('\Drupal\Core\TypedData\Validation\ExecutionContext')
      ->disableOriginalConstructor()
      ->getMock();

    $execution_context->expects($this->exactly(0))
      ->method('addViolation');

    $value = new TestMediaEntityConstraints('test_field', 'Some text');

    $validator = new ItemsCountConstraintValidator();
    $validator->initialize($execution_context);
    $validator->validate($value, $constraint);

    // Test the validator with invalid values.
    $execution_context = $this->getMockBuilder('\Drupal\Core\TypedData\Validation\ExecutionContext')
      ->disableOriginalConstructor()
      ->getMock();

    $execution_context->expects($this->exactly(1))
      ->method('addViolation')
      ->with($constraint->message);

    $value = new TestMediaEntityConstraints('test_field');
    $validator = new ItemsCountConstraintValidator();
    $validator->initialize($execution_context);
    $validator->validate($value, $constraint);
  }

}

/**
 * Mock class to test the ItemsCount constraint.
 */
class TestMediaEntityConstraints {

  /**
   * The source field names.
   *
   * @var array
   */
  protected $sourceFields = array();

  /**
   * TestMediaEntityConstraints constructor.
   *
   * @param string $name
   *   The source field name used for this test.
   * @param string|null $value
   *   (optional) The source field value used for this test.
   */
  public function __construct($name, $value = NULL) {
    $this->sourceFields[$name] = new TestField($value);
  }

  /**
   * Mocks get() on \Drupal\Core\Entity\FieldableEntityInterface.
   */
  public function get($name) {
    return $this->sourceFields[$name];
  }

}

/**
 * Mock class for fields to test the ItemsCount constraint.
 */
class TestField {

  /**
   * The field property.
   *
   * @var string
   */
  protected $property;

  /**
   * TestField constructor.
   *
   * @param string|null $value
   *   (optional) The property value used for this test.
   */
  public function __construct($value = NULL) {
    $this->property = $value;
  }

  /**
   * Mocks isEmpty() on \Drupal\Core\Entity\Plugin\DataType\EntityAdapter.
   */
  public function isEmpty() {
    return !isset($this->property);
  }

}
