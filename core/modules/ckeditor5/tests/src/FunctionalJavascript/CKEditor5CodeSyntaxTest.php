<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\editor\Entity\Editor;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;

/**
 * Tests code block configured languages are respected.
 *
 * @group ckeditor5
 * @internal
 */
class CKEditor5CodeSyntaxTest extends CKEditor5TestBase {

  use CKEditor5TestTrait;

  /**
   * Tests if CKEditor 5 tooltips can be interacted with in dialogs.
   */
  public function testCKEditor5CodeSyntax(): void {
    $this->addNewTextFormat();
    /** @var \Drupal\editor\Entity\Editor $editor */
    $editor = Editor::load('ckeditor5');
    $editor->setSettings([
      'toolbar' => [
        'items' => [
          'codeBlock',
          'sourceEditing',
        ],
      ],
      'plugins' => [
        'ckeditor5_codeBlock' => [
          'languages' => [
            ['label' => 'Twig', 'language' => 'twig'],
            ['label' => 'YML', 'language' => 'yml'],
          ],
        ],
        'ckeditor5_sourceEditing' => [
          'allowed_tags' => [],
        ],
      ],
    ])->save();
    $this->drupalGet('/node/add/page');

    $this->waitForEditor();
    // Open code block dropdown, and verify that correct languages are present.
    $assertSession = $this->assertSession();
    $page = $this->getSession()->getPage();
    $page->find('css', '.ck-code-block-dropdown .ck-dropdown__button .ck-splitbutton__arrow')->click();
    $assertSession->waitForElementVisible('css', '.ck-code-block-dropdown .ck-dropdown__panel .ck-list__item .ck-button__label');
    $codeBlockOptions = $page->findAll('css', '.ck-code-block-dropdown .ck-dropdown__panel .ck-list__item .ck-button__label');
    $this->assertCount(2, $codeBlockOptions);
    $this->assertEquals([
      'Twig',
      'YML',
    ], \array_map(static fn (NodeElement $el) => $el->getText(), $codeBlockOptions));

    // Insert the Twig code block and verify that correct CSS class is added.
    $this->pressEditorButton('Insert code block');
    $assertSession->waitForElementVisible('css', '.ck-editor__main pre[data-language="Twig"]');
    // @todo: figure out how to type in the CKEditor. We need this to manually
    // type in order to demonstrate the code tags being added.
    $assertSession->waitForElement('css', '.ck-content')->keyPress('x');
    $source = $this->getEditorDataAsHtmlString();
    $this->assertStringContainsString('<pre><code class="language-twig">', $source);
  }

}
