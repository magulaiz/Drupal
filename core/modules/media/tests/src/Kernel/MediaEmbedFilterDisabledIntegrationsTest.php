<?php

declare(strict_types=1);

namespace Drupal\Tests\media\Kernel;

use PHPUnit\Framework\Attributes\CoversClass;
/**
 * Tests that media embed disables certain integrations.
 *
 * @group media
 */
#[CoversClass(\Drupal\media\Plugin\Filter\MediaEmbed::class)]
class MediaEmbedFilterDisabledIntegrationsTest extends MediaEmbedFilterTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'contextual',
    // @see media_test_embed_entity_view_alter()
    'media_test_embed',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container->get('current_user')
      ->getAccount()
      ->addRole($this->drupalCreateRole([
        'access contextual links',
      ]));
  }

  public function testDisabledIntegrations() {
    $text = $this->createEmbedCode([
      'data-entity-type' => 'media',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
    ]);

    $this->applyFilter($text);
    $this->assertCount(1, $this->cssSelect('div[data-media-embed-test-view-mode]'));
    $this->assertCount(0, $this->cssSelect('div[data-media-embed-test-view-mode].contextual-region'));
  }

}
