<?php

declare(strict_types=1);

namespace Drupal\Tests\layout_builder\Functional;

use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\file\Functional\FileFieldCreationTrait;
use Drupal\Tests\layout_builder\Traits\EnableLayoutBuilderTrait;
use Drupal\Tests\layout_builder\Traits\LayoutBuilderTestTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Test access to private files in block fields on the Layout Builder.
 *
 * @group layout_builder
 */
class InlineBlockPrivateFilesTest extends BrowserTestBase {

  use FileFieldCreationTrait;
  use TestFileCreationTrait;
  use LayoutBuilderTestTrait;
  use EnableLayoutBuilderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file',
    'contextual',
    'block_content',
    'layout_builder',
    'block',
    'node',
    'field_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->setupTestContent();

    // Update the test node type to not create new revisions by default. This
    // allows testing for cases when a new revision is made and when it isn't.
    $node_type = NodeType::load('bundle_with_section_field');
    $node_type->setNewRevision(FALSE);
    $node_type->save();

    $field_settings = [
      'file_extensions' => 'txt',
      'uri_scheme' => 'private',
    ];
    $this->createFileField('field_file', 'block_content', 'basic', $field_settings);
    $this->fileSystem = $this->container->get('file_system');
  }

  /**
   * Tests access to private files added to inline blocks in the layout builder.
   */
  public function testPrivateFiles(): void {
    $assert_session = $this->assertSession();

    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    // Log in as user you can only configure layouts and access content.
    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'access content',
      'create and edit custom blocks',
    ]));
    $this->drupalGet('node/1/layout');
    $file = $this->createPrivateFile('drupal.txt');

    $file_real_path = $this->fileSystem->realpath($file->getFileUri());
    $this->assertFileExists($file_real_path);
    $node = Node::load(1);
    $this->addInlineFileBlockToLayout($node, 'The file', $file);

    $this->drupalGet('node/1');
    $private_href1 = $this->getFileHrefAccessibleOnNode($file);

    // Remove the inline block with the private file.
    $this->drupalGet('node/1/layout');
    $this->removeInlineBlockViaUi('The file', 'overrides', 'node.1', 'content', 0);

    $this->drupalGet('node/1');
    $assert_session->pageTextNotContains($file->label());
    // Try to access file directly after it has been removed. Since a new
    // revision was not created for the node the inline block is not in the
    // layout of a previous revision of the node.
    $this->drupalGet($private_href1);
    $assert_session->pageTextContains('You are not authorized to access this page');
    $assert_session->pageTextNotContains($this->getFileSecret($file));
    $this->assertFileExists($file_real_path);

    $file2 = $this->createPrivateFile('2ndFile.txt');

    $this->drupalGet('node/1/layout');
    $this->addInlineFileBlockToLayout($node, 'Number2', $file2);

    $this->drupalGet('node/1');
    $private_href2 = $this->getFileHrefAccessibleOnNode($file2);

    $this->createNewNodeRevision(1);

    $file3 = $this->createPrivateFile('3rdFile.txt');
    $this->drupalGet('node/1/layout');
    $this->replaceFileInBlock($file3);

    $this->drupalGet('node/1');
    $private_href3 = $this->getFileHrefAccessibleOnNode($file3);

    // $file2 is on a previous revision of the block which is on a previous
    // revision of the node. The user does not have access to view the previous
    // revision of the node.
    $this->drupalGet($private_href2);
    $assert_session->pageTextContains('You are not authorized to access this page');

    $node = Node::load(1);
    $node->setUnpublished();
    $node->save();
    $this->drupalGet('node/1');
    $assert_session->pageTextContains('You are not authorized to access this page');
    $this->drupalGet($private_href3);
    $assert_session->pageTextNotContains($this->getFileSecret($file3));
    $assert_session->pageTextContains('You are not authorized to access this page');

    $node = Node::load(2);
    $this->drupalGet('node/2/layout');
    $file4 = $this->createPrivateFile('drupal_4.txt');
    $this->addInlineFileBlockToLayout($node, 'The file', $file4);

    $this->drupalGet('node/2');
    $private_href4 = $this->getFileHrefAccessibleOnNode($file4);

    $this->createNewNodeRevision(2);

    // Remove the inline block with the private file.
    // The inline block will still be attached to the previous revision of the
    // node.
    $this->drupalGet('node/2/layout');
    $this->removeInlineBlockViaUi('The file', 'overrides', 'node.2', 'content', 0);

    // Ensure that since the user cannot view the previous revision of the node
    // they can not view the file which is only used on that revision.
    $this->drupalGet($private_href4);
    $assert_session->pageTextContains('You are not authorized to access this page');
  }

  /**
   * Replaces the file in the block with another one.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity.
   */
  protected function replaceFileInBlock(FileInterface $file): void {
    $page = $this->getSession()->getPage();
    $this->drupalGet('node/1/layout');
    $uuid = $this->getComponentUuidFromPlaceholderLabel('Number2');
    $this->drupalGet('layout_builder/update/block/overrides/node.1/0/content/' . $uuid);
    $page->pressButton('Remove');
    $page->attachFileToField("files[settings_block_form_field_file_0]", $this->fileSystem->realpath($file->getFileUri()));

    $this->submitForm([], 'Update');
    $this->submitForm([], 'Save layout');

  }

  /**
   * Adds an entity block with a file.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param string $title
   *   The title field value.
   * @param \Drupal\file\Entity\File $file
   *   The file entity.
   */
  protected function addInlineFileBlockToLayout(NodeInterface $node, string $title, File $file): void {
    $page = $this->getSession()->getPage();
    $this->drupalGet(\sprintf('/layout_builder/add/block/overrides/node.%s/0/content/inline_block:basic', $node->id()));
    $page->findField('Title')->setValue($title);
    $page->attachFileToField("files[settings_block_form_field_file_0]", $this->fileSystem->realpath($file->getFileUri()));
    $this->submitForm([], 'Add block');
    $this->submitForm([], 'Save layout');

  }

  /**
   * Creates a private file.
   *
   * @param string $file_name
   *   The file name.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\file\Entity\File
   *   The file entity.
   */
  protected function createPrivateFile($file_name) {
    // Create a new file entity.
    $file = File::create([
      'uid' => 1,
      'filename' => $file_name,
      'uri' => "private://$file_name",
      'filemime' => 'text/plain',
    ]);
    $file->setPermanent();
    file_put_contents($file->getFileUri(), $this->getFileSecret($file));
    $file->save();
    return $file;
  }

  /**
   * Returns the href of a file, asserting it is accessible on the page.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity.
   *
   * @return string
   *   The file href.
   */
  protected function getFileHrefAccessibleOnNode(FileInterface $file): string {
    $page = $this->getSession()->getPage();
    $this->assertSession()->linkExists($file->label());
    $private_href = $page->findLink($file->label())->getAttribute('href');
    $page->clickLink($file->label());
    $this->assertSession()->pageTextContains($this->getFileSecret($file));

    // Access file directly.
    $this->drupalGet($private_href);
    $this->assertSession()->pageTextContains($this->getFileSecret($file));
    return $private_href;
  }

  /**
   * Gets the text secret for a file.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity.
   *
   * @return string
   *   The text secret.
   */
  protected function getFileSecret(FileInterface $file): string {
    return "The secret in {$file->label()}";
  }

  /**
   * Create a new revision of the node.
   *
   * @param int $node_id
   *   The node id.
   */
  protected function createNewNodeRevision($node_id): void {
    $node = Node::load($node_id);
    $node->setTitle('Update node');
    $node->setNewRevision();
    $node->save();
  }

}
