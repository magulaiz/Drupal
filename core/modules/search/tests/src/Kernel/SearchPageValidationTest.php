<?php

namespace Drupal\Tests\search\Kernel;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\search\Entity\SearchPage;

/**
 * Tests validation of search_page entities.
 *
 * @group search
 */
class SearchPageValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['search', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = SearchPage::create([
      'id' => 'test',
      'label' => 'Test',
      'plugin' => 'user_search',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the search page's ID is validated as a machine name.
   *
   * @param string $invalid_id
   *   An invalid machine name that should raise a validation error.
   *
   * @testWith ["invalid name"]
   *  ["invalid-name"]
   *  ["Invalid_Name"]
   */
  public function testMachineName(string $invalid_id): void {
    $this->entity->set('id', $invalid_id);
    $this->assertValidationErrors(['This value is not valid.']);
  }

}
