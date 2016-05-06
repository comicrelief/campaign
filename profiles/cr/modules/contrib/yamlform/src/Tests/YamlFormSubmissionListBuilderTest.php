<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormSubmissionListBuilderTest.
 */

namespace Drupal\yamlform\Tests;

/**
 * Tests for YAML form submission list builder.
 *
 * @group YamlForm
 */
class YamlFormSubmissionListBuilderTest extends YamlFormTestBase {

  /**
   * Tests results.
   */
  public function testResults() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\yamlform\Entity\YamlFormSubmission[] $submissions */
    list($yamlform, $submissions) = $this->createYamlFormWithSubmissions();

    $this->drupalLogin($this->adminSubmissionUser);

    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table');

    // Check results.
    $this->assertLinkByHref($submissions[0]->toUrl()->toString());
    $this->assertLinkByHref($submissions[1]->toUrl()->toString());
    $this->assertLinkByHref($submissions[2]->toUrl()->toString());
    $this->assertRaw($submissions[0]->getData('first_name'));
    $this->assertRaw($submissions[1]->getData('first_name'));
    $this->assertRaw($submissions[2]->getData('first_name'));
    $this->assertNoFieldById('edit-reset', 'reset');

    // Check results filtered.
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table', ['filter' => $submissions[0]->getData('first_name')], t('Filter'));
    $this->assertUrl('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table?search=' . $submissions[0]->getData('first_name'));
    $this->assertRaw($submissions[0]->getData('first_name'));
    $this->assertNoRaw($submissions[1]->getData('first_name'));
    $this->assertNoRaw($submissions[2]->getData('first_name'));
    $this->assertFieldById('edit-reset', 'Reset');
  }

}
