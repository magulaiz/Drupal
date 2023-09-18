<?php

namespace Drupal\Tests\forum\Functional;

use Drupal\Tests\system\Functional\Module\GenericModuleTestBase;

/**
 * Generic module test for forum.
 *
 * @group forum
 */
class GenericTest extends GenericModuleTestBase {

  /**
   * {@inheritdoc}
   */
  protected function preUninstallSteps(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $result = $storage->getQuery()
      ->condition('vid', 'forums')
      ->accessCheck(FALSE)
      ->execute();
    $terms = $storage->loadMultiple($result);
    $storage->delete($terms);
  }

}
