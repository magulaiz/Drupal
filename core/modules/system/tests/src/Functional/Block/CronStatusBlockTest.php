<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Block;

use Drupal\block\BlockInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests clear cache block behavior.
 *
 * @group Block
 *
 * @see \Drupal\system\Plugin\Block\ClearCacheBlock
 */
class CronStatusBlockTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The clear cache block instance.
   *
   * @var \Drupal\block\BlockInterface
   */
  protected BlockInterface $clearCacheBlock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $admin_user = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($admin_user);
    $this->clearCacheBlock = $this->placeBlock('system_cron_status_block', [
      'label' => 'Cron status block',
    ]);
  }

  /**
   * Tests block access based on permissions.
   */
  public function testCacheClearBlockAccess(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Cron status block');
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains('Cron status block');
  }

  /**
   * Tests block behavior.
   */
  public function testCacheClearBlock(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Cron status block');
    $page = $this->getSession()->getPage();
    $page->pressButton('Run cron');
    $this->assertSession()->statusMessageContains('Cron ran successfully.');
  }

}
