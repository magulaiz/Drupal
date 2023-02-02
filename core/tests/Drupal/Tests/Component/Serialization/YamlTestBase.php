<?php

namespace Drupal\Tests\Component\Serialization;

use Drupal\Component\Serialization\TaggedSerializationInterface;
use PHPUnit\Framework\TestCase;

/**
 * Provides standard data to validate different YAML implementations.
 */
abstract class YamlTestBase extends TestCase {

  /**
   * Asserts that a serializer can support YAML 1.2 tags.
   *
   * @param string $serializer
   *   The class name of the serializer to test.
   */
  protected function assertYamlTags($serializer) {
    /** @var \Drupal\Component\Serialization\TaggedSerializationInterface $serializer */

    // Ensure the serializer supports the tagged interface.
    $this->assertTrue(is_subclass_of($serializer, TaggedSerializationInterface::class));

    // Ensure the component's default callbacks.
    $this->assertEquals($serializer::getDefaultTagCallbacks(), $serializer::getTagCallbacks());

    $yaml = 'value: !sum [1, 2, 3]';

    // Ensure that without a callback, the tag is ignored.
    $data = $serializer::decode($yaml);
    $this->assertEquals($data['value'], [1, 2, 3]);

    // Now, add the custom tag callback.
    $sum = function ($value, $tag) {
      return array_sum($value);
    };
    $serializer::addTagCallback('!sum', $sum);

    // Ensure that with a tag callback, the value is converted.
    $data = $serializer::decode($yaml);
    $this->assertEquals($data['value'], 6);

    // Remove the custom tag callback and ensure it returns original callback.
    $callback = $serializer::removeTagCallback('!sum');
    $this->assertEquals($sum, $callback);

    // Ensure that without a callback, the tag is ignored.
    $data = $serializer::decode($yaml);
    $this->assertEquals($data['value'], [1, 2, 3]);

    // Reset tag callbacks.
    $serializer::setTagCallbacks();
    $callbacks = new \ReflectionProperty($serializer, 'tagCallbacks');
    $callbacks->setAccessible(TRUE);
    $this->assertEquals(NULL, $callbacks->getValue($serializer));

    // Ensure the component's default callbacks are restored.
    $this->assertEquals($serializer::getDefaultTagCallbacks(), $serializer::getTagCallbacks());
  }

  /**
   * Some data that should be able to be serialized.
   */
  public function providerEncodeDecodeTests() {
    return [
      [
        'foo' => 'bar',
        'id' => 'schnitzel',
        'ponies' => ['nope', 'thanks'],
        'how' => [
          'about' => 'if',
          'i' => 'ask',
          'nicely',
        ],
        'the' => [
          'answer' => [
            'still' => 'would',
            'be' => 'Y',
          ],
        ],
        'how_many_times' => 123,
        'should_i_ask' => FALSE,
        1,
        FALSE,
        [1, FALSE],
        [10],
        [0 => '123456'],
      ],
      [NULL],
    ];
  }

  /**
   * Some data that should be able to be de-serialized.
   */
  public function providerDecodeTests() {
    $data = [
      // NULL files.
      ['', NULL],
      ["\n", NULL],
      ["---\n...\n", NULL],

      // Node anchors.
      [
        "
jquery.ui:
  version: &jquery_ui 1.10.2

jquery.ui.accordion:
  version: *jquery_ui
",
        [
          'jquery.ui' => [
            'version' => '1.10.2',
          ],
          'jquery.ui.accordion' => [
            'version' => '1.10.2',
          ],
        ],
      ],
    ];

    // 1.2 Bool values.
    foreach ($this->providerBoolTest() as $test) {
      $data[] = ['bool: ' . $test[0], ['bool' => $test[1]]];
    }
    $data = array_merge($data, $this->providerBoolTest());

    return $data;
  }

  /**
   * Tests different boolean serialization and de-serialization.
   */
  public function providerBoolTest() {
    return [
      ['true', TRUE],
      ['TRUE', TRUE],
      ['True', TRUE],
      ['y', 'y'],
      ['Y', 'Y'],
      ['false', FALSE],
      ['FALSE', FALSE],
      ['False', FALSE],
      ['n', 'n'],
      ['N', 'N'],
    ];
  }

}
