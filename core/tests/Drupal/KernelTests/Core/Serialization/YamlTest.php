<?php

namespace Drupal\KernelTests\Core\Serialization;

use Drupal\Core\Serialization\Yaml;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests service provider registration to the DIC.
 *
 * @covers \Drupal\Core\Serialization\Yaml
 *
 * @group Serialization
 */
class YamlTest extends KernelTestBase {

  /**
   * Test the YAML !translate tag functionality.
   *
   * @covers \Drupal\Core\Serialization\Yaml::applyTranslateCallback
   */
  public function testTranslateTag() {
    $yaml = 'label: !translate [ "Label with @arg", { "@arg": "value" }, { context: "Something" } ]';
    $data = Yaml::decode($yaml);

    /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $label */
    $label = $data['label'];

    // Ensure the label is a TranslatableMarkup instance.
    $this->assertInstanceOf(TranslatableMarkup::class, $label);

    // Ensure that the argument was set properly.
    $this->assertEquals(['@arg' => 'value'], $label->getArguments());

    // Ensure that the context option was set properly.
    $this->assertEquals('Something', $label->getOption('context'));
  }

}
