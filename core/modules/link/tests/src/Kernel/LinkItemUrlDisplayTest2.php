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
 *
 * @group link
 */
class LinkItemUrlDisplayTest2 extends FieldKernelTestBase {

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
  public function testLinkFormatter(): void {
    foreach ($this->getTestCases() as $case_name => $case_options) {
      [$display_settings, $expected_results] = array_values($case_options);
      $this->checkInternalLinksRender($display_settings, $expected_results, "Test case '$case_name' failed.");
    }
  }

  /**
   * Test rendered entity field with complex internal URL.
   *
   * @param array $display_settings
   *   Display settings for link field formatter.
   * @param array $expected_results
   *   Render result using these display settings.
   * @param string $message
   *   Error message.
   */
  protected function checkInternalLinksRender(array $display_settings, array $expected_results, string $message = ''): void {
    // Render link field using display settings.
    $render_array = $this->entity->field_test->view(['settings' => $display_settings]);
    $output = (string) \Drupal::service('renderer')->renderRoot($render_array);

    // Check results.
    foreach ($expected_results as $expected_result) {
      $this->assertStringContainsString($expected_result, $output, $message);

      // With url_plain should be no links.
      if (!empty($display_settings['url_only']) && !empty($display_settings['url_plain'])) {
        $this->assertStringNotContainsString('<a href="', $output, $message);
      }
    }

  }

  /**
   * Provides an array of link field display settings and expected results.
   *
   * @return array
   *   Test cases.
   */
  protected function getTestCases(): array {
    $cases = [];
    $cases['trim title null'] = [
      'display settings' => ['trim_length' => NULL],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '">' . $values['expected_title'] . '</a>';
      }, $this->getLinkFieldsValues()),
    ];
    $cases['trim title to 6'] = [
      'display settings' => ['trim_length' => 6],
      'results' => array_map(function ($values) {
        $title = Unicode::truncate($values['expected_title'], 6, FALSE, TRUE);
        return '<a href="' . $values['expected_href'] . '">' . $title . '</a>';
      }, $this->getLinkFieldsValues()),
    ];
    $cases['attribute rel null'] = [
      'display settings' => ['rel' => NULL],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '">' . $values['expected_title'] . '</a>';
      }, $this->getLinkFieldsValues()),
    ];
    $cases['attribute rel nofollow'] = [
      'display settings' => ['rel' => 'nofollow'],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '" rel="nofollow">' . $values['expected_title'] . '</a>';
      }, $this->getLinkFieldsValues()),
    ];
    $cases['attribute target null'] = [
      'display settings' => ['target' => NULL],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '">' . $values['expected_title'] . '</a>';
      }, $this->getLinkFieldsValues()),
    ];
    $cases['attribute target blank'] = [
      'display settings' => ['target' => '_blank'],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '" target="_blank">' . $values['expected_title'] . '</a>';
      }, $this->getLinkFieldsValues()),
    ];
    $cases['url_only false'] = [
      'display settings' => ['url_only' => FALSE],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '">' . $values['expected_title'] . '</a>';
      }, $this->getLinkFieldsValues()),
    ];
    $cases['url_only false, url_plain true'] = [
      'display settings' => ['url_only' => FALSE, 'url_plain' => TRUE],
      'results' => array_map(function ($values) {
        return '<a href="' . $values['expected_href'] . '">' . $values['expected_title'] . '</a>';
      }, $this->getLinkFieldsValues()),
    ];
    $cases['url_only, url_plain - true'] = [
      'display settings' => ['url_only' => TRUE, 'url_plain' => TRUE],
      'results' => array_map(function ($values) {
        return $values['expected_href'];
      }, $this->getLinkFieldsValues()),
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
    return [
      [
        'uri'            => 'http://www.example.com/content/articles/archive?author=John&year=2012#com',
        'expected_href'  => 'http://www.example.com/content/articles/archive?author=John&amp;year=2012#com',
        // Note that title is empty.
        'title'          => '',
        'expected_title' => 'http://www.example.com/content/articles/archive?author=John&amp;year=2012#com',
      ],
      [
        'uri'            => 'http://www.example.org/content/articles/archive?author=John&year=2012#org',
        'expected_href'  => 'http://www.example.org/content/articles/archive?author=John&amp;year=2012#org',
        'title'          => 'A very long & strange example title that could break the nice layout of the site',
        'expected_title' => 'A very long &amp; strange example title that could break the nice layout of the site',
      ],
      [
        'uri'            => 'internal:#net',
        'expected_href'  => '#net',
        'title'          => 'Fragment only',
        'expected_title' => 'Fragment only',
      ],
    ];
  }

}
