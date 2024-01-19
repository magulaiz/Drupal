<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;
use Drupal\user\RoleInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Tests for CKEditor 5 to ensure correct focus management in dialogs.
 *
 * @group ckeditor5
 * @internal
 */
class CKEditor5DialogTest extends CKEditor5TestBase {

  use CKEditor5TestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'ckeditor5',
    'ckeditor5_test',
  ];

  /**
   * Tests if CKEditor 5 tooltips can be interacted with in dialogs.
   */
  public function testCKEditor5FocusInTooltipsInDialog() {
    FilterFormat::create([
      'format' => 'test_format',
      'name' => 'CKEditor 5 with link',
      'roles' => [RoleInterface::AUTHENTICATED_ID],
    ])->save();
    Editor::create([
      'format' => 'test_format',
      'editor' => 'ckeditor5',
      'settings' => [
        'toolbar' => [
          'items' => ['link'],
        ],
      ],
    ])->save();

    $this->assertSame([], array_map(
      function (ConstraintViolation $v) {
        return (string) $v->getMessage();
      },
      iterator_to_array(CKEditor5::validatePair(
        Editor::load('test_format'),
        FilterFormat::load('test_format')
      ))
    ));

    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->drupalGet('/ckeditor5_test/dialog');
    $page->clickLink('Add Node');
    $assert_session->waitForElementVisible('css', '[role="dialog"]');
    $assert_session->assertWaitOnAjaxRequest();
  }

}
