<?php

declare(strict_types=1);

namespace Drupal\Tests\link\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\link\LinkItemInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\Tests\Traits\Core\PathAliasTestTrait;

/**
 * Tests link field widgets and formatters.
 *
 * @group link
 * @group #slow
 */
class LinkFieldTest extends FieldKernelTestBase {

  use PathAliasTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_test',
    'link',
    'node',
    'link_test_base_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'link_test_theme';

  /**
   * A field to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The instance used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * Tests the functionality and rendering of the link field.
   *
   * This is being as one to avoid multiple Drupal install.
   */
  public function testLinkField(): void {
    $this->doTestLinkTypeOnLinkWidget();
  }

  /**
   * Tests '#link_type' property exists on 'link_default' widget.
   *
   * Make sure the 'link_default' widget exposes a '#link_type' property on
   * its element. Modules can use it to understand if a text form element is
   * a link and also which LinkItemInterface::LINK_* is (EXTERNAL, GENERIC,
   * INTERNAL).
   */
  protected function doTestLinkTypeOnLinkWidget(): void {

    $link_type = LinkItemInterface::LINK_EXTERNAL;
    $field_name = $this->randomMachineName();

    // Create a field with settings to validate.
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'entity_test',
      'type' => 'link',
      'cardinality' => 1,
    ]);
    $this->fieldStorage->save();
    FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'label' => 'Read more about this entity',
      'bundle' => 'entity_test',
      'settings' => [
        'title' => DRUPAL_OPTIONAL,
        'link_type' => $link_type,
      ],
    ])->save();

    $this->container->get('entity_type.manager')
      ->getStorage('entity_form_display')
      ->load('entity_test.entity_test.default')
      ->setComponent($field_name, [
        'type' => 'link_default',
      ])
      ->save();

    $form = \Drupal::service('entity.form_builder')->getForm(EntityTest::create());
    $this->assertEquals($link_type, $form[$field_name]['widget'][0]['uri']['#link_type']);
  }

}
