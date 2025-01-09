<?php

declare(strict_types=1);

namespace Drupal\Tests\link\Kernel;

use Drupal\Component\Utility\Unicode;
use Drupal\entity_test\Entity\EntityTest;

/**
 * Tests the default 'link' field formatter.
 *
 * The formatter is tested with several forms of complex query parameters. And
 * each form is tested with different display settings.
 *
 * @group link
 */
class LinkFormatterDisplayTest extends LinkFormatterDisplayTestBase {

  /**
   * Tests that links are rendered correctly.
   *
   * Run tests without dataProvider to improve speed.
   */
  public function testLinkFormatter(): void {
    // Create an entity with link field values provided.
    $entity = EntityTest::create();
    $entity->field_test->setValue($this->getTestValues());

    foreach ($this->getTestCases() as $case_name => $case_options) {
      [$display_settings, $expected_results] = array_values($case_options);

      // Render link field with default 'link' formatter and custom
      // display settings.
      $render_array = $entity->field_test->view(['settings' => $display_settings]);
      $output = (string) \Drupal::service('renderer')->renderRoot($render_array);

      // Check results.
      foreach ($expected_results as $expected_result) {
        $this->assertStringContainsString($expected_result, $output, 'Test case failed: ' . $case_name);

        // With url_plain should be no links.
        if (!empty($display_settings['url_only']) && !empty($display_settings['url_plain'])) {
          $this->assertStringNotContainsString('<a href="', $output, 'Test case failed: ' . $case_name);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getTestValues(): array {
    return [
      // From doTestLinkFormatter().
      [
        'uri'             => 'http://www.example.com/content/articles/archive?author=John&year=2012#com',
        '#expected_href'  => 'http://www.example.com/content/articles/archive?author=John&amp;year=2012#com',
        // Note that title is empty.
        'title'           => '',
        '#expected_title' => 'http://www.example.com/content/articles/archive?author=John&amp;year=2012#com',
      ],
      [
        'uri'             => 'http://www.example.org/content/articles/archive?author=John&year=2012#org',
        '#expected_href'  => 'http://www.example.org/content/articles/archive?author=John&amp;year=2012#org',
        'title'           => 'A very long & strange example title that could break the nice layout of the site',
        '#expected_title' => 'A very long &amp; strange example title that could break the nice layout of the site',
      ],
      [
        'uri'             => 'internal:#net',
        '#expected_href'  => '#net',
        'title'           => 'Fragment only',
        '#expected_title' => 'Fragment only',
      ],

      // From testLinkFormatterQueryParametersDuplication().
      [
        'uri' => 'internal:?a[]=1&a[]=2',
        // Result link: '?a[0]=1&a[1]=2'.
        '#expected_href'  => '?a%5B0%5D=1&amp;a%5B1%5D=2',
        '#expected_title' => '?a%5B0%5D=1&amp;a%5B1%5D=2',
      ],
      [
        'uri' => 'internal:?b[0]=1&b[1]=2',
        // Result link: '?b[0]=1&b[1]=2'.
        '#expected_href'  => '?b%5B0%5D=1&amp;b%5B1%5D=2',
        '#expected_title' => '?b%5B0%5D=1&amp;b%5B1%5D=2',
      ],
      // UrlHelper::buildQuery will change order of params.
      [
        'uri' => 'internal:?c[]=1&d=3&c[]=2',
        // Result link: '?c[0]=1&c[1]=2&d=3'.
        '#expected_href'  => '?c%5B0%5D=1&amp;c%5B1%5D=2&amp;d=3',
        '#expected_title' => '?c%5B0%5D=1&amp;c%5B1%5D=2&amp;d=3',
      ],
      [
        'uri' => 'internal:?e[f][g]=h',
        // Result link: '?e[f][g]=h'.
        '#expected_href'  => '?e%5Bf%5D%5Bg%5D=h',
        '#expected_title' => '?e%5Bf%5D%5Bg%5D=h',
      ],
      [
        'uri' => 'internal:?i[j[k]]=l',
        // Result link: '?i[j[k]]=l'.
        '#expected_href'  => '?i%5Bj%5Bk%5D=l',
        '#expected_title' => '?i%5Bj%5Bk%5D=l',
      ],

      // Query string replace value.
      [
        'uri' => 'internal:?x=1&x=2',
        '#expected_href'  => '?x=2',
        '#expected_title' => '?x=2',
      ],
      [
        'uri' => 'internal:?z[0]=1&z[0]=2',
        // Result link: '?z[0]=2'.
        '#expected_href'  => '?z%5B0%5D=2',
        '#expected_title' => '?z%5B0%5D=2',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getTestCases(): array {
    $cases = [];
    $defaultExpectedResults = array_map(function ($values) {
      return '<div><a href="' . $values['#expected_href'] . '">' . $values['#expected_title'] . '</a></div>';
    }, $this->getTestValues());

    $cases['default settings'] = [
      'display settings' => [],
      'expected_results' => $defaultExpectedResults,
    ];
    $cases['trim_length=null'] = [
      'display_settings' => ['trim_length' => NULL],
      'expected_results' => $defaultExpectedResults,
    ];
    $cases['trim_length=6'] = [
      'display settings' => ['trim_length' => 6],
      'expected_results' => array_map(function ($values) {
        $title = Unicode::truncate($values['#expected_title'], 6, FALSE, TRUE);
        return '<div><a href="' . $values['#expected_href'] . '">' . $title . '</a></div>';
      }, $this->getTestValues()),
    ];
    $cases['attribute rel=null'] = [
      'display_settings' => ['rel' => NULL],
      'expected_results' => $defaultExpectedResults,
    ];
    $cases['attribute rel=nofollow'] = [
      'display_settings' => ['rel' => 'nofollow'],
      'expected_results' => array_map(function ($values) {
        return '<div><a href="' . $values['#expected_href'] . '" rel="nofollow">' . $values['#expected_title'] . '</a></div>';
      }, $this->getTestValues()),
    ];
    $cases['attribute target=null'] = [
      'display_settings' => ['target' => NULL],
      'expected_results' => $defaultExpectedResults,
    ];
    $cases['attribute target=_blank'] = [
      'display_settings' => ['target' => '_blank'],
      'expected_results' => array_map(function ($values) {
        return '<div><a href="' . $values['#expected_href'] . '" target="_blank">' . $values['#expected_title'] . '</a></div>';
      }, $this->getTestValues()),
    ];
    $cases['url_only=false'] = [
      'display_settings' => ['url_only' => FALSE],
      'expected_results' => $defaultExpectedResults,
    ];
    $cases['url_only=true'] = [
      'display_settings' => ['url_only' => TRUE],
      'expected_results' => array_map(function ($values) {
        return '<div><a href="' . $values['#expected_href'] . '">' . $values['#expected_href'] . '</a></div>';
      }, $this->getTestValues()),
    ];
    $cases['url_only=false, url_plain=true'] = [
      'display_settings' => ['url_only' => FALSE, 'url_plain' => TRUE],
      'expected_results' => $defaultExpectedResults,
    ];
    $cases['url_only=true, url_plain=true'] = [
      'display_settings' => ['url_only' => TRUE, 'url_plain' => TRUE],
      'expected_results' => array_map(function ($values) {
        return '<div>' . $values['#expected_href'] . '</div>';
      }, $this->getTestValues()),
    ];

    return $cases;
  }

}
