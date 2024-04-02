<?php

declare(strict_types=1);

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;

/**
 * Tests demo_umami profile performance.
 *
 * @group #slow
 */
class AssetAggregationAcrossPagesTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * Checks the asset requests made when the front and recipe pages are visited.
   */
  public function testFrontAndRecipesPages() {
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
      $this->drupalGet('recipes');
    }, 'umamiFrontAndRecipePages');
    $this->assertSame(2, $performance_data->getStylesheetCount());
    $this->assertSame(45495, $performance_data->getStylesheetBytes());
    $this->assertSame(1, $performance_data->getScriptCount());
    $this->assertSame(14150, $performance_data->getScriptBytes());
  }

}
