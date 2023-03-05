<?php

declare(strict_types=1);

namespace Drupal\Tests\field\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests Resave all entity view/form displays with recalculated dependencies
 * after field_post_update_resave_all_entity_view_or_form_displays executed.
 *
 * @group Update
 */
class FieldEntityViewOrFormDisplayPostUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests Resave all entity view/form displays with recalculated dependencies.
   *
   * @see field_post_update_resave_all_entity_view_or_form_displays()
   */
  public function testEntityViewOrFormDisplaysResave(): void {

    // @todo : Add Logi to test post update hook.
    // $this->runUpdates();
  }

}
