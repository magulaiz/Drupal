<?php

namespace Drupal\Tests\quickedit\FunctionalJavascript;

use Drupal\Tests\layout_builder\FunctionalJavascript\InlineBlockTest;

/**
 * Tests inline block with a user that has access to Quickedit functionality.
 *
 * @group quickedit
 */
class InlineBlockQuickeditEnabledTest extends InlineBlockTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['quickedit'];

  /**
   * {@inheritdoc}
   */
  protected function drupalCreateUser(array $permissions = [], $name = NULL, $admin = FALSE, array $values = []) {
    $permissions[] = 'access in-place editing';
    return parent::drupalCreateUser($permissions, $name, $admin, $values);
  }

}
