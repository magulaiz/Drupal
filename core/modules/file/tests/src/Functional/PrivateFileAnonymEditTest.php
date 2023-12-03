<?php

namespace Drupal\Tests\file\Functional;

use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\user\RoleInterface;

/**
 * Check node edit with private file work for anonymous visitors.
 *
 * @group file
 */
class PrivateFileAnonymEditTest extends FileFieldTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The name of the file field used in the test.
   *
   * @var string
   */
  protected string $fieldName;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Set up permissions for anonymous user.
    user_role_change_permissions(RoleInterface::ANONYMOUS_ID, [
      'create article content' => TRUE,
      'edit own article content' => TRUE,
      'access content' => TRUE,
    ]);

    // Create a file field on the "Article" node type.
    $this->fieldName = mb_strtolower($this->randomMachineName());
    $this->createFileField($this->fieldName, 'node', 'article', ['uri_scheme' => 'private'], ['file_extensions' => 'txt png']);
  }

  /**
   * Tests node edit for an anonymous visitor.
   */
  public function testAnonymousNodePrivateFileEdit() {
    $type = 'Article';
    $title = 'Test page';

    // Load the node form.
    $this->drupalLogout();
    $this->drupalGet('node/add/article');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains("Create $type");

    // Generate an image file.
    $image = $this->getTestFile('image');

    // Submit the form.
    $edit = [
      'title[0][value]' => $title,
      'body[0][value]' => 'Test article',
      "files[{$this->fieldName}_0]" => $this->container->get('file_system')->realpath($image->getFileUri()),
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains("$type $title has been created.");
    $matches = [];
    if (preg_match('@node/(\d+)$@', $this->getUrl(), $matches)) {
      $nid = end($matches);
      $this->assertNotEquals(0, $nid, 'The node ID was extracted from the URL.');
      $node = Node::load($nid);
      $this->assertNotNull($node, 'The node was loaded successfully.');
      $this->assertFileExists(File::load($node->{$this->fieldName}->target_id)->getFileUri());

      // Edit created node.
      $this->drupalGet('node/' . $node->id() . '/edit');
      $edit = [
        'title[0][value]' => 'Title edit',
      ];
      $this->submitForm($edit, 'Save');
      $this->assertSession()->pageTextNotContains('You do not have access to the referenced entity');
    }
  }

}
