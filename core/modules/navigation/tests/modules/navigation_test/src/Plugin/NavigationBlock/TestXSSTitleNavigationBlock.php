<?php

namespace Drupal\navigation_test\Plugin\NavigationBlock;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;

/**
 * Provides a navigation block to test XSS in title.
 */
#[NavigationBlock(
  id: "test_xss_title",
  admin_label: new TranslatableMarkup("<script>alert('XSS subject');</script>"),
  category: new TranslatableMarkup("<script>alert('XSS category');</script>"),
)]
class TestXSSTitleNavigationBlock extends TestCacheNavigationBlock {
}
