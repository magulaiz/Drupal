<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Functional\Update;

use Drupal\Component\Gettext\PoItem;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests views_post_update_plural_variants().
 *
 * @group views
 * @group legacy
 */
class PluralVariantsSchemaUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
    ];
  }

  /**
   * Test views_post_update_plural_variants().
   *
   * @see views_post_update_plural_variants()
   */
  public function testViewsPostUpdatePluralVariants(): void {
    $trail = 'display.page_2.display_options.fields.count.format_plural_string';

    $config = $this->config('views.view.files');
    $plural_variants = $config->get($trail);
    // Check that before update the plural variants are a concatenated string.
    $this->assertSame('1' . PoItem::DELIMITER . '@count', $plural_variants);

    $this->runUpdates();

    $config = $this->config('views.view.files');
    $plural_variants = $config->get($trail);
    // Check that after update the plural variants are an array.
    $this->assertSame(['1', '@count'], $plural_variants);
  }

}
