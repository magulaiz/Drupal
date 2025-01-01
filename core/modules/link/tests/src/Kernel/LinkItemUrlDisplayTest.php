<?php

declare(strict_types=1);

namespace Drupal\Tests\link\Kernel;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\link\LinkItemInterface;

/**
 * Tests the link field formatter.
 *
 * The formatter is tested with several forms of complex query parameters. And
 * each form is tested with different display settings.
 * @group link
 */
class LinkItemUrlDisplayTest extends FieldKernelTestBase {

  /**
   * The test entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected EntityInterface $entity;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['link'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_test',
      'type' => 'link',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_test',
      'bundle' => 'entity_test',
      'settings' => ['link_type' => LinkItemInterface::LINK_GENERIC],
    ])->save();

    // Create an entity with link field values provided.
    $this->entity = EntityTest::create();
    $links = $this->getLinkFieldsValues();
    $this->entity->field_test->setValue($links);
  }

  /**
   * Tests that internal links are rendered correctly.
   *
   * Run tests without dataProvider to improve speed.
   */
  public function testLinkFormatterQueryParametersDuplication(): void {
    foreach ($this->getTestCases() as $case_options) {
      [$display_settings, $expected_results] = array_values($case_options);
      $this->checkInternalLinksRender($display_settings, $expected_results);
    }
  }

  /**
   * Test rendered entity field with complex internal URL.
   *
   * @param array $display_settings
   *   Display settings for link field formatter.
   * @param array $expected_results
   *   Render result using these display settings.
   */
  protected function checkInternalLinksRender(array $display_settings, array $expected_results): void {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');

    // Render link field using display settings.
    $render_array = $this->entity->field_test->view(['settings' => $display_settings]);
    $output = (string) $renderer->renderRoot($render_array);

    // Check results.
    foreach ($expected_results as $expected_result) {
      $this->assertStringContainsString($expected_result, $output);

      // With url_plain should be no links.
      if (!empty($display_settings['url_only']) && !empty($display_settings['url_plain'])) {
        $this->assertStringNotContainsString('<a href="', $output);
      }
    }

  }

  /**
   * Gets an array of URLs with complex query parameters.
   *
   * @return array
   *   The URLs to test.
   */
  protected function getTestingUrls(): array {
    return [
      [
        'input' => 'internal:?a[]=1&a[]=2',
        // Result link: '?a[0]=1&a[1]=2'.
        'expected_href'  => '?a%5B0%5D=1&amp;a%5B1%5D=2',
      ],
      [
        'input' => 'internal:?b[0]=1&b[1]=2',
        // Result link: '?b[0]=1&b[1]=2'.
        'expected_href'  => '?b%5B0%5D=1&amp;b%5B1%5D=2',
      ],
      // UrlHelper::buildQuery will change order of params.
      [
        'input' => 'internal:?c[]=1&d=3&c[]=2',
        // Result link: '?c[0]=1&c[1]=2&d=3'.
        'expected_href'  => '?c%5B0%5D=1&amp;c%5B1%5D=2&amp;d=3',
      ],
      [
        'input' => 'internal:?e[f][g]=h',
        // Result link: '?e[f][g]=h'.
        'expected_href'  => '?e%5Bf%5D%5Bg%5D=h',
      ],
      [
        'input' => 'internal:?i[j[k]]=l',
        // Result link: '?i[j[k]]=l'.
        'expected_href'  => '?i%5Bj%5Bk%5D=l',
      ],

      // Query string replace value.
      [
        'input' => 'internal:?x=1&x=2',
        'expected_href'  => '?x=2',
      ],
      [
        'input' => 'internal:?z[0]=1&z[0]=2',
        // Result link: '?z[0]=2'.
        'expected_href'  => '?z%5B0%5D=2',
      ],
    ];
  }

  /**
   * Provides an array of link field display settings and expected results.
   *
   * @return array
   *   Test cases.
   */
  protected function getTestCases(): array {
    $cases = [];
    $cases['default settings'] = [
      'display settings' => [],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '">' . $values['expected_href'] . '</a>';
      }, $this->getTestingUrls()),
    ];
    $cases['trim title to 6'] = [
      'display settings' => ['trim_length' => 6],
      'results' => array_map(function ($values) {
        $title = Unicode::truncate($values['expected_href'], 6, FALSE, TRUE);
        return '<a href="' . $values['expected_href'] . '">' . $title . '</a>';
      }, $this->getTestingUrls()),
    ];
    $cases['attribute rel'] = [
      'display settings' => ['rel' => 'nofollow'],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '" rel="nofollow">' . $values['expected_href'] . '</a>';
      }, $this->getTestingUrls()),
    ];
    $cases['attribute target'] = [
      'display settings' => ['target' => '_blank'],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '" target="_blank">' . $values['expected_href'] . '</a>';
      }, $this->getTestingUrls()),
    ];
    $cases['url_only'] = [
      'display settings' => ['url_only' => TRUE],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '">' . $values['expected_href'] . '</a>';
      }, $this->getTestingUrls()),
    ];
    $cases['url_only and url_plain'] = [
      'display settings' => ['url_only' => TRUE, 'url_plain' => TRUE],
      'results' => array_map(function ($values) {
        return $values['expected_href'];
      }, $this->getTestingUrls()),
    ];

    return $cases;
  }

  /**
   * Prepares values for the link field.
   *
   * @return array
   *   Values to use at link field setter.
   */
  protected function getLinkFieldsValues(): array {
    $links = [];
    foreach ($this->getTestingUrls() as $key => $test_url) {
      $links[$key] = [
        'uri' => $test_url['input'],
      ];
    }
    return $links;
  }

}
