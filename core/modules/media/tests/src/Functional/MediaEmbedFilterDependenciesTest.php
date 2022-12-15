<?php

namespace Drupal\Tests\media\Functional;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Url;
use Drupal\filter\Entity\FilterFormat;
use Drupal\user\Entity\Role;

/**
 * Tests dependency calculation of the `media_embed` filter plugin.
 *
 * @group media
 */
class MediaEmbedFilterDependenciesTest extends MediaFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['filter'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the `media_embed` filter doesn't add unexpected dependencies.
   */
  public function testDependencies(): void {
    $view_mode = EntityViewMode::create([
      'id' => 'media.test',
      'label' => 'Test',
      'targetEntityType' => 'media',
    ]);
    $view_mode->save();

    $media_type = $this->createMediaType('test');
    $this->container->get('entity_display.repository')
      ->getViewDisplay('media', $media_type->id(), 'test')
      ->setStatus(TRUE)
      ->save();

    $role_id = $this->drupalCreateRole([], NULL, 'Test role');

    $filter_format = FilterFormat::create([
      'format' => 'media_embed_dependencies_test',
      'name' => 'Media embed dependencies test',
      'roles' => [$role_id],
    ]);
    $filter_format->setFilterConfig('media_embed', [
      'settings' => [
        'allowed_view_modes' => [
          EntityDisplayRepositoryInterface::DEFAULT_DISPLAY_MODE,
          'test',
        ],
      ],
    ]);
    $filter_format->save();

    $role = Role::load($role_id);
    $this->assertContains($filter_format->getPermissionName(), $role->getPermissions());

    $account = $this->drupalCreateUser(['administer display modes']);
    $this->drupalLogin($account);
    $url = Url::fromRoute('entity.entity_view_mode.delete_form', [
      'entity_view_mode' => 'media.test',
    ]);
    $this->drupalGet($url);
    $assert_session = $this->assertSession();
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextNotContains($role->label());
    $assert_session->pageTextNotContains($filter_format->label());
  }

}
