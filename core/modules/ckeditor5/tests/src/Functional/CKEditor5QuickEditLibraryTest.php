<?php

namespace Drupal\Tests\ckeditor5\Functional;

use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\RoleInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Tests that Quickedit-specific library loads when Quickedit is enabled.
 *
 * @group ckeditor5
 * @group legacy
 */
class CKEditor5QuickEditLibraryTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'ckeditor5',
    'quickedit',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $filtered_html_format = FilterFormat::create([
      'format' => 'llama',
      'name' => 'Llama',
      'filters' => [],
      'roles' => [RoleInterface::AUTHENTICATED_ID],
    ]);
    $filtered_html_format->save();
    $this->editor = Editor::create([
      'format' => 'llama',
      'editor' => 'ckeditor5',
      'settings' => [
        'toolbar' => [
          'items' => [],
        ],
      ],
    ]);
    $this->editor->save();
    $this->assertSame([], array_map(
      function (ConstraintViolation $v) {
        return (string) $v->getMessage();
      },
      iterator_to_array(CKEditor5::validatePair($this->editor, $filtered_html_format))
    ));
    // Create node type.
    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    $this->adminUser = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
      'use text format llama',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  public function testQuickeditTemporaryWorkaround() {
    $assert_session = $this->assertSession();

    $this->expectDeprecation('Temporary work-around until https://www.drupal.org/project/drupal/issues/3196689 lands.');
    /** @var \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer */
    $theme_installer = \Drupal::service('theme_installer');
    // Install a theme which has an absolute external CSS URL.
    $theme_installer->install(['test_ckeditor_stylesheets_relative']);
    $this->config('system.theme')->set('default', 'test_ckeditor_stylesheets_relative')->save();
    $this->config('system.theme')->set('admin', 'stark')->save();

    $this->drupalGet('node/add/article');
    $assert_session->responseContains('css/quickedit-override.css');
  }

}
